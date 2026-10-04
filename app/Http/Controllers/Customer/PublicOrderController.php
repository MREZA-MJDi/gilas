<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicOrderRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\OrderService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PublicOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
    ) {
    }

    public function store(StorePublicOrderRequest $request): JsonResponse
    {
        $restaurant = $this->primaryRestaurantOrFail();
        $data = $request->validated();

        $customer = $this->resolveCustomer($data);
        $address = $data['order_type'] === OrderType::Delivery->value
            ? $this->resolveAddress($customer, $data)
            : null;

        $order = $this->orders->create($restaurant, [
            'customer_id' => $customer->id,
            'customer_address_id' => $address?->id,
            'order_type' => $data['order_type'],
            'payment_method' => $data['payment_method'],
            'customer_note' => $data['customer_note'] ?? null,
            'items' => $data['items'],
            'idempotency_key' => $request->idempotencyKey(),
        ]);

        return response()->json([
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'total' => $order->total,
                'payment_method' => $order->payment?->method?->value,
                'payment_status' => $order->payment?->status?->value,
                'public_token' => $order->public_token,
                'tracking_url' => route('customer.orders.show', $order->public_token),
            ],
        ], 201);
    }

    private function resolveCustomer(array $data): Customer
    {
        $phone = $data['phone'];
        $attributes = [
            'name' => $data['customer_name'],
            'email' => $data['email'] ?? null,
            'status' => 'active',
            'last_order_at' => now(),
        ];

        try {
            return DB::transaction(function () use ($phone, $attributes): Customer {
                $customer = Customer::query()
                    ->where('phone', $phone)
                    ->lockForUpdate()
                    ->first();

                if ($customer) {
                    $customer->update($attributes);

                    return $customer;
                }

                return Customer::create([
                    'phone' => $phone,
                    ...$attributes,
                ]);
            });
        } catch (QueryException $exception) {
            $customer = Customer::query()->where('phone', $phone)->first();

            if (!$customer) {
                throw $exception;
            }

            $customer->update($attributes);

            return $customer;
        }
    }

    private function resolveAddress(Customer $customer, array $data): CustomerAddress
    {
        $address = CustomerAddress::query()
            ->where('customer_id', $customer->id)
            ->where('address', $data['address'])
            ->when(
                filled($data['postal_code'] ?? null),
                fn ($query) => $query->where('postal_code', $data['postal_code'])
            )
            ->first();

        if ($address) {
            return $address;
        }

        return $customer->addresses()->create([
            'title' => 'سفارش آنلاین',
            'recipient_name' => $data['customer_name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'postal_code' => $data['postal_code'] ?? null,
            'is_default' => false,
        ]);
    }

    private function primaryRestaurantOrFail(): Restaurant
    {
        $query = Restaurant::query()
            ->where('status', 'active')
            ->with('settings');

        $configuredSlug = config('gilas.primary_restaurant_slug');

        if (is_string($configuredSlug) && trim($configuredSlug) !== '') {
            $restaurant = (clone $query)->where('slug', trim($configuredSlug))->first();

            if ($restaurant) {
                return $restaurant;
            }
        }

        return $query->orderBy('id')->firstOrFail();
    }
}
