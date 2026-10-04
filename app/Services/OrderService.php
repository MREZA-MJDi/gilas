<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\OrderCreated;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly MenuPricingService $pricing,
    ) {
    }

    public function create(Restaurant $restaurant, array $data): Order
    {
        $type = $this->resolveOrderType($data['order_type'] ?? null);
        $idempotencyKey = $this->normalizeIdempotencyKey($data['idempotency_key'] ?? null);
        $requestHash = $idempotencyKey ? $this->requestHash($type, $data) : null;

        if ($idempotencyKey) {
            $existing = $this->findByIdempotencyKey($restaurant, $idempotencyKey);
            if ($existing) {
                return $this->returnOrRejectIdempotent($existing, $requestHash);
            }
        }

        try {
            $order = DB::transaction(function () use ($restaurant, $data, $type, $idempotencyKey, $requestHash) {
                $this->assertOrderIsAllowed($restaurant, $type, $data);
                $items = $this->prepareMenuItems($restaurant, $data['items'] ?? []);

                $settings = $this->settingsFor($restaurant);
                $pricedItems = [];
                $subtotal = 0;

                foreach ($data['items'] as $index => $itemData) {
                    $menuItemId = $this->positiveInteger(
                        $itemData['menu_item_id'] ?? null,
                        "items.{$index}.menu_item_id"
                    );
                    $quantity = $this->positiveInteger(
                        $itemData['quantity'] ?? null,
                        "items.{$index}.quantity"
                    );

                    if ($quantity > 1000) {
                        throw ValidationException::withMessages([
                            "items.{$index}.quantity" => 'Quantity cannot exceed 1000.',
                        ]);
                    }

                    $variantId = $itemData['menu_item_variant_id'] ?? null;
                    if ($variantId !== null) {
                        $variantId = $this->positiveInteger(
                            $variantId,
                            "items.{$index}.menu_item_variant_id"
                        );
                    }

                    $optionValueIds = $itemData['option_value_ids'] ?? [];
                    if (!is_array($optionValueIds)) {
                        throw ValidationException::withMessages([
                            "items.{$index}.option_value_ids" => 'Option values must be an array.',
                        ]);
                    }

                    $pricing = $this->pricing->resolve(
                        $items[$menuItemId],
                        $variantId,
                        $this->normalizeOptionValueIds($optionValueIds, $index),
                    );

                    $lineTotal = $pricing['unit_price'] * $quantity;

                    if ($lineTotal < 0 || $subtotal > PHP_INT_MAX - $lineTotal) {
                        throw ValidationException::withMessages([
                            'items' => 'The order total is outside the supported range.',
                        ]);
                    }

                    $subtotal += $lineTotal;
                    $pricedItems[] = [
                        'input' => $itemData,
                        'quantity' => $quantity,
                        'pricing' => $pricing,
                    ];
                }

                $discount = 0;
                $taxBase = max(0, $subtotal - $discount);
                $tax = $this->percentageAmount($taxBase, $settings->tax_percent);
                $serviceCharge = $this->percentageAmount($taxBase, $settings->service_charge_percent);
                $deliveryFee = 0;
                $total = $taxBase + $tax + $serviceCharge + $deliveryFee;

                if ($settings->min_order_amount > 0 && $subtotal < $settings->min_order_amount) {
                    throw ValidationException::withMessages([
                        'items' => 'The order does not meet the restaurant minimum order amount.',
                    ]);
                }

                $order = $restaurant->orders()->create([
                    'customer_id' => $data['customer_id'] ?? null,
                    'restaurant_table_id' => $data['restaurant_table_id'] ?? null,
                    'customer_address_id' => $data['customer_address_id'] ?? null,
                    'order_number' => $this->generateOrderNumber(),
                    'idempotency_key' => $idempotencyKey,
                    'public_token' => $this->generatePublicTrackingToken(),
                    'idempotency_hash' => $requestHash,
                    'order_type' => $type,
                    'status' => OrderStatus::Pending,
                    'customer_note' => $data['customer_note'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'delivery_fee' => $deliveryFee,
                    'service_charge' => $serviceCharge,
                    'total' => $total,
                ]);

                foreach ($pricedItems as $priced) {
                    $pricing = $priced['pricing'];
                    $item = $order->items()->create([
                        'menu_item_id' => $pricing['menu_item_id'],
                        'menu_item_variant_id' => $pricing['menu_item_variant_id'],
                        'name' => $pricing['name'],
                        'variant_name' => $pricing['variant_name'],
                        'unit_price' => $pricing['unit_price'],
                        'quantity' => $priced['quantity'],
                        'total_price' => $pricing['unit_price'] * $priced['quantity'],
                        'note' => $priced['input']['note'] ?? null,
                    ]);

                    foreach ($pricing['options'] as $option) {
                        $item->options()->create($option);
                    }
                }

                $this->createPendingPayment(
                    $order,
                    $data['payment_method'] ?? PaymentMethod::Cashier->value
                );

                $order->statusHistory()->create([
                    'from_status' => null,
                    'to_status' => OrderStatus::Pending,
                ]);

                return $order->load(['items.options', 'statusHistory', 'payment']);
            });

            event(new OrderCreated($order->id));

            return $order;
        } catch (QueryException $exception) {
            if (!$idempotencyKey) {
                throw $exception;
            }

            $existing = $this->findByIdempotencyKey($restaurant, $idempotencyKey);

            if (!$existing) {
                throw $exception;
            }

            return $this->returnOrRejectIdempotent($existing, $requestHash);
        }
    }

    public function changeStatus(
        Order $order,
        OrderStatus $next,
        ?User $actor = null,
        ?string $note = null
    ): Order {
        return DB::transaction(function () use ($order, $next, $actor, $note) {
            $orderId = $order->id;
            $order = Order::query()->lockForUpdate()->find($orderId);

            if (!$order) {
                throw (new ModelNotFoundException)->setModel(Order::class, [$orderId]);
            }

            $current = $order->status;

            if (!$current->canTransitionTo($next, $order->order_type)) {
                throw ValidationException::withMessages([
                    'status' => "Order cannot transition from {$current->value} to {$next->value}.",
                ]);
            }

            if ($current === $next) {
                return $order->load('statusHistory');
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

    private function prepareMenuItems(Restaurant $restaurant, array $itemData): array
    {
        if ($itemData === []) {
            throw ValidationException::withMessages([
                'items' => 'An order must contain at least one item.',
            ]);
        }

        $ids = [];

        foreach ($itemData as $index => $item) {
            if (!is_array($item)) {
                throw ValidationException::withMessages([
                    "items.{$index}" => 'Each order item must be an object.',
                ]);
            }

            $ids[] = $this->positiveInteger($item['menu_item_id'] ?? null, "items.{$index}.menu_item_id");
        }

        $models = MenuItem::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('id', array_values(array_unique($ids)))
            ->with(['variants', 'options.values'])
            ->get()
            ->keyBy('id');

        if ($models->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'items' => 'One or more menu items do not belong to this restaurant.',
            ]);
        }

        return $models->all();
    }

    private function assertOrderIsAllowed(Restaurant $restaurant, OrderType $type, array $data): void
    {
        if (($restaurant->status ?? 'active') !== 'active') {
            throw ValidationException::withMessages([
                'order' => 'This restaurant is not accepting orders.',
            ]);
        }

        $settings = $this->settingsFor($restaurant);

        if (!$settings->ordering_enabled) {
            throw ValidationException::withMessages([
                'order' => 'Ordering is currently disabled.',
            ]);
        }

        $typeEnabled = match ($type) {
            OrderType::DineIn => $settings->dine_in_enabled,
            OrderType::Pickup => $settings->pickup_enabled,
            OrderType::Delivery => $settings->delivery_enabled,
        };

        if (!$typeEnabled) {
            throw ValidationException::withMessages([
                'order_type' => 'This order type is currently disabled.',
            ]);
        }

        $paymentMethod = $data['payment_method'] ?? PaymentMethod::Cashier->value;

        try {
            PaymentMethod::from((string) $paymentMethod);
        } catch (\ValueError) {
            throw ValidationException::withMessages([
                'payment_method' => 'The selected payment method is invalid.',
            ]);
        }

        $customerId = $data['customer_id'] ?? null;

        if ($customerId !== null) {
            $customerId = $this->positiveInteger($customerId, 'customer_id');
            if (!Customer::query()->whereKey($customerId)->exists()) {
                throw ValidationException::withMessages([
                    'customer_id' => 'The selected customer does not exist.',
                ]);
            }
        }

        $tableId = $data['restaurant_table_id'] ?? null;
        $addressId = $data['customer_address_id'] ?? null;

        if ($type === OrderType::DineIn) {
            if ($tableId === null) {
                throw ValidationException::withMessages([
                    'restaurant_table_id' => 'A dine-in order requires a table.',
                ]);
            }

            $tableId = $this->positiveInteger($tableId, 'restaurant_table_id');

            $validTable = RestaurantTable::query()
                ->where('restaurant_id', $restaurant->id)
                ->whereKey($tableId)
                ->where('is_active', true)
                ->exists();

            if (!$validTable) {
                throw ValidationException::withMessages([
                    'restaurant_table_id' => 'The selected table is invalid or inactive.',
                ]);
            }
        } elseif ($tableId !== null) {
            throw ValidationException::withMessages([
                'restaurant_table_id' => 'Only dine-in orders may reference a restaurant table.',
            ]);
        }

        if ($type === OrderType::Delivery) {
            if ($customerId === null) {
                throw ValidationException::withMessages([
                    'customer_id' => 'A delivery order requires a customer.',
                ]);
            }

            if ($addressId === null) {
                throw ValidationException::withMessages([
                    'customer_address_id' => 'A delivery order requires a customer address.',
                ]);
            }

            $addressId = $this->positiveInteger($addressId, 'customer_address_id');

            $validAddress = CustomerAddress::query()
                ->whereKey($addressId)
                ->where('customer_id', $customerId)
                ->exists();

            if (!$validAddress) {
                throw ValidationException::withMessages([
                    'customer_address_id' => 'The selected address does not belong to the customer.',
                ]);
            }
        } elseif ($addressId !== null) {
            throw ValidationException::withMessages([
                'customer_address_id' => 'Only delivery orders may reference a customer address.',
            ]);
        }
    }

    private function settingsFor(Restaurant $restaurant)
    {
        $restaurant->loadMissing('settings');

        if ($restaurant->settings) {
            return $restaurant->settings;
        }

        $lockedRestaurant = Restaurant::query()
            ->lockForUpdate()
            ->findOrFail($restaurant->id);

        return $lockedRestaurant->settings()->firstOrCreate([], [
            'ordering_enabled' => true,
            'dine_in_enabled' => true,
            'pickup_enabled' => true,
            'delivery_enabled' => true,
            'reservation_enabled' => true,
        ]);
    }

    private function createPendingPayment(Order $order, mixed $method): Payment
    {
        try {
            $paymentMethod = $method instanceof PaymentMethod
                ? $method
                : PaymentMethod::from((string) $method);
        } catch (\ValueError) {
            throw ValidationException::withMessages([
                'payment_method' => 'The selected payment method is invalid.',
            ]);
        }

        return $order->payment()->create([
            'method' => $paymentMethod->value,
            'status' => PaymentStatus::Pending,
            'amount' => $order->total,
            'paid_at' => null,
        ]);
    }

    private function resolveOrderType(mixed $value): OrderType
    {
        if ($value instanceof OrderType) {
            return $value;
        }

        try {
            return OrderType::from((string) $value);
        } catch (\ValueError) {
            throw ValidationException::withMessages([
                'order_type' => 'The selected order type is invalid.',
            ]);
        }
    }

    private function normalizeIdempotencyKey(mixed $key): ?string
    {
        if ($key === null) {
            return null;
        }

        $key = trim((string) $key);

        if ($key === '') {
            return null;
        }

        if (strlen($key) > 100) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'The idempotency key may not exceed 100 characters.',
            ]);
        }

        return $key;
    }

    private function requestHash(OrderType $type, array $data): string
    {
        $items = array_map(function (array $item, int $index) {
            $optionIds = array_map('intval', $item['option_value_ids'] ?? []);
            sort($optionIds);

            return [
                'menu_item_id' => (int) ($item['menu_item_id'] ?? 0),
                'menu_item_variant_id' => ($item['menu_item_variant_id'] ?? null) !== null
                    ? (int) $item['menu_item_variant_id']
                    : null,
                'quantity' => (int) ($item['quantity'] ?? 0),
                'option_value_ids' => $optionIds,
                'note' => (string) ($item['note'] ?? ''),
                '_index' => $index,
            ];
        }, $data['items'] ?? [], array_keys($data['items'] ?? []));

        usort($items, fn (array $a, array $b) => strcmp(
            json_encode($a, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            json_encode($b, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        ));

        $canonical = [
            'order_type' => $type->value,
            'customer_id' => $data['customer_id'] ?? null,
            'restaurant_table_id' => $data['restaurant_table_id'] ?? null,
            'customer_address_id' => $data['customer_address_id'] ?? null,
            'customer_note' => (string) ($data['customer_note'] ?? ''),
            'items' => $items,
        ];

        return hash(
            'sha256',
            json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );
    }

    private function findByIdempotencyKey(Restaurant $restaurant, string $key): ?Order
    {
        return $restaurant->orders()
            ->where('idempotency_key', $key)
            ->first();
    }

    private function returnOrRejectIdempotent(Order $order, ?string $requestHash): Order
    {
        if ($requestHash === null || $order->idempotency_hash !== $requestHash) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'This idempotency key has already been used for a different order payload.',
            ]);
        }

        return $order->load(['items.options', 'statusHistory', 'payment']);
    }

    private function normalizeOptionValueIds(array $ids, int $index): array
    {
        $normalized = [];

        foreach ($ids as $valueId) {
            $normalized[] = $this->positiveInteger($valueId, "items.{$index}.option_value_ids");
        }

        return $normalized;
    }

    private function positiveInteger(mixed $value, string $field): int
    {
        $number = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($number === false) {
            throw ValidationException::withMessages([
                $field => 'The value must be a positive integer.',
            ]);
        }

        return (int) $number;
    }

    private function percentageAmount(int $amount, mixed $percent): int
    {
        $normalized = number_format((float) $percent, 2, '.', '');
        [$whole, $fraction] = array_pad(explode('.', $normalized), 2, '0');
        $basisPoints = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        return intdiv(($amount * $basisPoints) + 5000, 10000);
    }

    private function generatePublicTrackingToken(): string
    {
        do {
            $token = Str::lower(Str::random(48));
        } while (Order::query()->where('public_token', $token)->exists());

        return $token;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'GLS-' . now()->format('ymd-His') . '-' . Str::upper(Str::random(8));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
