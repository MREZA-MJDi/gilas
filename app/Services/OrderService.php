<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(Restaurant $restaurant, array $data): Order
    {
        return DB::transaction(function () use ($restaurant, $data) {
            $type = $data['order_type'] instanceof OrderType
                ? $data['order_type']
                : OrderType::from($data['order_type']);

            $order = $restaurant->orders()->create([
                'customer_id' => $data['customer_id'] ?? null,
                'restaurant_table_id' => $data['restaurant_table_id'] ?? null,
                'customer_address_id' => $data['customer_address_id'] ?? null,
                'order_number' => $data['order_number'] ?? $this->generateOrderNumber(),
                'order_type' => $type,
                'status' => OrderStatus::Pending,
                'customer_note' => $data['customer_note'] ?? null,
                'subtotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'delivery_fee' => 0,
                'service_charge' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;

            foreach ($data['items'] ?? [] as $itemData) {
                $quantity = max(1, (int) ($itemData['quantity'] ?? 1));
                $unitPrice = (int) $itemData['unit_price'];
                $lineTotal = $unitPrice * $quantity;

                $item = $order->items()->create([
                    'menu_item_id' => $itemData['menu_item_id'] ?? null,
                    'menu_item_variant_id' => $itemData['menu_item_variant_id'] ?? null,
                    'name' => $itemData['name'],
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'total_price' => $lineTotal,
                    'note' => $itemData['note'] ?? null,
                ]);

                foreach ($itemData['options'] ?? [] as $option) {
                    $item->options()->create([
                        'option_name' => $option['option_name'],
                        'value_name' => $option['value_name'],
                        'price_delta' => (int) ($option['price_delta'] ?? 0),
                    ]);
                }

                $subtotal += $lineTotal;
            }

            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            $order->statusHistory()->create([
                'from_status' => null,
                'to_status' => OrderStatus::Pending,
            ]);

            return $order->load('items.options', 'statusHistory');
        });
    }

    public function changeStatus(Order $order, OrderStatus $next, ?User $actor = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $next, $actor, $note) {
            $order = Order::query()->lockForUpdate()->find($order->id);

            if (!$order) {
                throw (new ModelNotFoundException)->setModel(Order::class, [$order?->id]);
            }

            $current = $order->status;

            if (!$current->canTransitionTo($next, $order->order_type)) {
                throw ValidationException::withMessages([
                    'status' => "Order cannot transition from {$current->value} to {$next->value}.",
                ]);
            }

            if ($current === $next) {
                return $order;
            }

            $timestamps = [];
            if ($next === OrderStatus::Confirmed) {
                $timestamps['confirmed_at'] = now();
            }
            if ($next === OrderStatus::Completed) {
                $timestamps['completed_at'] = now();
            }
            if ($next === OrderStatus::Cancelled) {
                $timestamps['cancelled_at'] = now();
            }

            $order->update(array_merge(['status' => $next], $timestamps));

            $order->statusHistory()->create([
                'from_status' => $current,
                'to_status' => $next,
                'changed_by' => $actor?->id,
                'note' => $note,
            ]);

            return $order->fresh(['statusHistory']);
        });
    }

    private function generateOrderNumber(): string
    {
        return 'GLS-' . now()->format('ymd-His') . '-' . Str::upper(Str::random(5));
    }
}
