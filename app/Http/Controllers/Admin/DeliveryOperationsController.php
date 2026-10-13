<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\RestaurantPermission;
use App\Enums\RestaurantUserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryStatusRequest;
use App\Models\Courier;
use App\Models\Delivery;
use App\Models\Restaurant;
use App\Services\OrderService;
use App\Services\RestaurantAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryOperationsController extends Controller
{
    public function index(Restaurant $restaurant, RestaurantAccessService $access): JsonResponse
    {
        $this->assertCanManage($access, request()->user(), $restaurant);

        $user = request()->user();
        $membership = $restaurant->users()
            ->where('users.id', $user->id)
            ->wherePivot('is_active', true)
            ->first();

        $query = $restaurant->orders()->where('order_type', 'delivery');

        // A courier's operational queue must not disclose another courier's deliveries.
        if ($membership?->pivot?->role === RestaurantUserRole::Courier->value) {
            $query->whereHas('delivery.courier', fn ($couriers) => $couriers->where('user_id', $user->id));
        }

        $deliveries = $query
            ->with(['delivery.courier.user', 'delivery.statusHistories.actor', 'customer', 'items'])
            ->latest()
            ->paginate(50);

        return response()->json(['data' => $deliveries]);
    }

    public function update(
        StoreDeliveryStatusRequest $request,
        Restaurant $restaurant,
        Delivery $delivery,
        RestaurantAccessService $access,
        OrderService $orders
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $delivery->loadMissing(['order', 'courier']);

        abort_unless(
            $delivery->order !== null
            && (int) $delivery->order->restaurant_id === (int) $restaurant->id,
            404
        );

        $membership = $restaurant->users()
            ->where('users.id', $request->user()->id)
            ->wherePivot('is_active', true)
            ->first();

        $isCourier = $membership?->pivot?->role === RestaurantUserRole::Courier->value;

        if ($isCourier && (int) ($delivery->courier?->user_id ?? 0) !== (int) $request->user()->id) {
            abort(403);
        }

        $data = $request->validated();
        $next = DeliveryStatus::from($data['status']);

        if ($isCourier && (
            $next === DeliveryStatus::Assigned
            || $next === DeliveryStatus::Cancelled
            || (array_key_exists('courier_id', $data)
                && (int) $data['courier_id'] !== (int) $delivery->courier_id)
        )) {
            abort(403);
        }

        $updated = DB::transaction(function () use ($delivery, $restaurant, $data, $next, $request, $orders): Delivery {
            $locked = Delivery::query()->lockForUpdate()->with('order')->findOrFail($delivery->id);
            $current = $locked->status;

            if ($current === $next && (
                $next !== DeliveryStatus::Assigned
                || !isset($data['courier_id'])
                || (int) $data['courier_id'] === (int) $locked->courier_id
            )) {
                return $locked->load(['courier.user', 'statusHistories.actor']);
            }

            if (!in_array($next, $this->allowedTransitions($current), true)) {
                throw ValidationException::withMessages([
                    'status' => "Delivery cannot transition from {$current->value} to {$next->value}.",
                ]);
            }

            $courierId = $data['courier_id'] ?? $locked->courier_id;
            $courier = null;

            if ($next === DeliveryStatus::Assigned) {
                if (!$courierId) {
                    throw ValidationException::withMessages([
                        'courier_id' => 'A courier must be assigned before dispatch.',
                    ]);
                }

                $courier = $restaurant->couriers()->whereKey($courierId)->first();

                if (!$courier) {
                    throw ValidationException::withMessages([
                        'courier_id' => 'The selected courier does not belong to this restaurant.',
                    ]);
                }

                if (
                    (int) $courier->id !== (int) $locked->courier_id
                    && !in_array($courier->status, ['available', 'online'], true)
                ) {
                    throw ValidationException::withMessages([
                        'courier_id' => 'The selected courier is not available.',
                    ]);
                }
            }

            if (in_array($next, [DeliveryStatus::Dispatched, DeliveryStatus::PickedUp, DeliveryStatus::Delivered], true)
                && !$courierId) {
                throw ValidationException::withMessages([
                    'courier_id' => 'A courier must be assigned before this delivery can progress.',
                ]);
            }

            if ($next === DeliveryStatus::Dispatched) {
                if ($locked->order->status !== OrderStatus::Ready) {
                    throw ValidationException::withMessages([
                        'status' => 'The kitchen must mark this delivery order ready before dispatch.',
                    ]);
                }

                $orders->changeStatus($locked->order, OrderStatus::OutForDelivery, $request->user(), $data['note'] ?? null);
            }

            if ($next === DeliveryStatus::Delivered) {
                if ($locked->order->status !== OrderStatus::OutForDelivery) {
                    throw ValidationException::withMessages([
                        'status' => 'The order must be out for delivery before it can be delivered.',
                    ]);
                }

                $orders->changeStatus($locked->order, OrderStatus::Delivered, $request->user(), $data['note'] ?? null);
            }

            $timestamps = match ($next) {
                DeliveryStatus::Dispatched => ['dispatched_at' => now()],
                DeliveryStatus::PickedUp => ['picked_up_at' => now()],
                DeliveryStatus::Delivered => ['delivered_at' => now()],
                default => [],
            };

            $locked->update([
                'status' => $next,
                'courier_id' => $courierId,
                ...$timestamps,
            ]);

            $locked->statusHistories()->create([
                'changed_by' => $request->user()->id,
                'from_status' => $current,
                'to_status' => $next,
                'note' => $data['note'] ?? null,
            ]);

            if ($next === DeliveryStatus::Assigned && $courier) {
                $courier->update(['status' => 'busy']);
            }

            if (in_array($next, [DeliveryStatus::Delivered, DeliveryStatus::Cancelled], true) && $locked->courier_id) {
                $assignedCourier = Courier::query()->find($locked->courier_id);

                if ($assignedCourier && !$assignedCourier->deliveries()
                    ->where('id', '!=', $locked->id)
                    ->whereIn('status', [
                        DeliveryStatus::Assigned->value,
                        DeliveryStatus::Dispatched->value,
                        DeliveryStatus::PickedUp->value,
                    ])->exists()) {
                    $assignedCourier->update(['status' => 'available']);
                }
            }

            return $locked->fresh(['order', 'courier.user', 'statusHistories.actor']);
        });

        return response()->json([
            'data' => [
                'delivery_id' => $updated->id,
                'status' => $updated->status->value,
                'courier_id' => $updated->courier_id,
                'dispatched_at' => $updated->dispatched_at?->toISOString(),
                'picked_up_at' => $updated->picked_up_at?->toISOString(),
                'delivered_at' => $updated->delivered_at?->toISOString(),
                'order_status' => $updated->order?->status?->value,
            ],
        ]);
    }

    private function allowedTransitions(DeliveryStatus $current): array
    {
        return match ($current) {
            DeliveryStatus::Pending => [DeliveryStatus::Assigned, DeliveryStatus::Cancelled],
            DeliveryStatus::Assigned => [DeliveryStatus::Dispatched, DeliveryStatus::Cancelled],
            DeliveryStatus::Dispatched => [DeliveryStatus::PickedUp],
            DeliveryStatus::PickedUp => [DeliveryStatus::Delivered],
            DeliveryStatus::Delivered, DeliveryStatus::Cancelled => [],
        };
    }

    private function assertCanManage(RestaurantAccessService $access, $user, Restaurant $restaurant): void
    {
        abort_unless(
            $user && $access->can($user, $restaurant, RestaurantPermission::ManageDelivery),
            403
        );
    }
}
