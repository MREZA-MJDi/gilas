<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RestaurantPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierRequest;
use App\Models\Courier;
use App\Models\Restaurant;
use App\Services\RestaurantAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CourierManagementController extends Controller
{
    public function index(Restaurant $restaurant, RestaurantAccessService $access): JsonResponse
    {
        $this->assertCanManage($access, request()->user(), $restaurant);

        return response()->json([
            'data' => $restaurant->couriers()
                ->with('user')
                ->orderBy('name')
                ->paginate(50),
        ]);
    }

    public function store(
        StoreCourierRequest $request,
        Restaurant $restaurant,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        $data = $request->validated();

        if (isset($data['user_id'])) {
            $this->assertUserHasActiveCourierMembership($restaurant, (int) $data['user_id']);
            $this->assertCourierUserIsNotAssignedElsewhere($restaurant, (int) $data['user_id']);
        }

        $courier = $restaurant->couriers()->create([
            ...$data,
            'status' => $data['status'] ?? 'available',
        ]);

        return response()->json(['data' => $courier->load('user')], 201);
    }

    public function update(
        StoreCourierRequest $request,
        Restaurant $restaurant,
        Courier $courier,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, $request->user(), $restaurant);
        abort_unless((int) $courier->restaurant_id === (int) $restaurant->id, 404);

        $data = $request->validated();

        if (isset($data['user_id'])) {
            $this->assertUserHasActiveCourierMembership($restaurant, (int) $data['user_id']);
            $this->assertCourierUserIsNotAssignedElsewhere($restaurant, (int) $data['user_id'], $courier->id);
        }

        if (isset($data['status']) && $data['status'] === 'offline') {
            $hasActiveDeliveries = $courier->deliveries()
                ->whereIn('status', ['assigned', 'dispatched', 'picked_up'])
                ->exists();

            if ($hasActiveDeliveries) {
                throw ValidationException::withMessages([
                    'status' => 'A courier with active deliveries cannot be taken offline.',
                ]);
            }
        }

        $courier->update($data);

        return response()->json(['data' => $courier->fresh('user')]);
    }

    public function destroy(
        Restaurant $restaurant,
        Courier $courier,
        RestaurantAccessService $access
    ): JsonResponse {
        $this->assertCanManage($access, request()->user(), $restaurant);
        abort_unless((int) $courier->restaurant_id === (int) $restaurant->id, 404);

        if ($courier->deliveries()->whereIn('status', ['assigned', 'dispatched', 'picked_up'])->exists()) {
            throw ValidationException::withMessages([
                'courier' => 'Reassign or finish active deliveries before removing this courier.',
            ]);
        }

        $courier->delete();

        return response()->json(['data' => ['deleted' => true]]);
    }

    private function assertCanManage(RestaurantAccessService $access, $user, Restaurant $restaurant): void
    {
        abort_unless(
            $user && $access->can($user, $restaurant, RestaurantPermission::ManageCouriers),
            403
        );
    }

    private function assertUserHasActiveCourierMembership(Restaurant $restaurant, int $userId): void
    {
        $member = $restaurant->users()
            ->where('users.id', $userId)
            ->wherePivot('is_active', true)
            ->first();

        if (!$member || $member->pivot->role !== 'courier') {
            throw ValidationException::withMessages([
                'user_id' => 'The selected user must have an active courier role for this restaurant.',
            ]);
        }
    }

    private function assertCourierUserIsNotAssignedElsewhere(
        Restaurant $restaurant,
        int $userId,
        ?int $exceptCourierId = null
    ): void {
        $query = $restaurant->couriers()->where('user_id', $userId);

        if ($exceptCourierId !== null) {
            $query->where('id', '!=', $exceptCourierId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'user_id' => 'This user is already assigned as a courier for this restaurant.',
            ]);
        }
    }
}
