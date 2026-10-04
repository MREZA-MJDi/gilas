<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Services\TableQrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOrderingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pickup_checkout_creates_customer_order_and_pending_cashier_payment(): void
    {
        [$restaurant, $item] = $this->catalog();

        $response = $this->withHeader('Idempotency-Key', 'public-pickup-001')
            ->postJson(route('customer.orders.store'), [
                'order_type' => OrderType::Pickup->value,
                'payment_method' => PaymentMethod::Cashier->value,
                'customer_name' => 'سارا احمدی',
                'phone' => '۰۹۱۲۱۲۳۴۵۶۷',
                'items' => [[
                    'menu_item_id' => $item->id,
                    'quantity' => 2,
                ]],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', OrderStatus::Pending->value)
            ->assertJsonPath('data.payment_method', PaymentMethod::Cashier->value)
            ->assertJsonPath('data.payment_status', PaymentStatus::Pending->value);

        $this->assertDatabaseHas('customers', ['phone' => '09121234567']);
        $this->assertDatabaseHas('payments', [
            'method' => PaymentMethod::Cashier->value,
            'status' => PaymentStatus::Pending->value,
        ]);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_public_delivery_requires_address_and_accepts_valid_address(): void
    {
        [$restaurant, $item] = $this->catalog();

        $this->postJson(route('customer.orders.store'), [
            'order_type' => OrderType::Delivery->value,
            'payment_method' => PaymentMethod::Online->value,
            'customer_name' => 'علی رضایی',
            'phone' => '09123334444',
            'items' => [[
                'menu_item_id' => $item->id,
                'quantity' => 1,
            ]],
        ])->assertStatus(422)->assertJsonValidationErrors('address');

        $response = $this->withHeader('Idempotency-Key', 'public-delivery-001')
            ->postJson(route('customer.orders.store'), [
                'order_type' => OrderType::Delivery->value,
                'payment_method' => PaymentMethod::Online->value,
                'customer_name' => 'علی رضایی',
                'phone' => '09123334444',
                'address' => 'تهران، خیابان نمونه، پلاک ۱۰',
                'postal_code' => '۱۲۳۴۵۶۷۸۹۰',
                'items' => [[
                    'menu_item_id' => $item->id,
                    'quantity' => 1,
                ]],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.payment_method', PaymentMethod::Online->value);

        $this->assertDatabaseHas('customer_addresses', [
            'address' => 'تهران، خیابان نمونه، پلاک ۱۰',
            'postal_code' => '1234567890',
        ]);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_qr_order_accepts_cashier_or_online_payment_and_persists_one_payment(): void
    {
        [$restaurant, $item] = $this->catalog();

        $table = RestaurantTable::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'میز ۳',
            'number' => 3,
            'capacity' => 4,
            'status' => 'available',
            'is_active' => true,
        ]);

        $qr = app(TableQrCodeService::class)->issue($table);

        foreach ([PaymentMethod::Cashier, PaymentMethod::Online] as $index => $method) {
            $response = $this->withHeader('Idempotency-Key', 'qr-payment-' . $index)
                ->postJson(route('table.orders.store', ['token' => $qr->token]), [
                    'payment_method' => $method->value,
                    'items' => [[
                        'menu_item_id' => $item->id,
                        'quantity' => 1,
                    ]],
                ]);

            $response->assertCreated()
                ->assertJsonPath('data.payment_method', $method->value);
        }

        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseCount('orders', 2);
    }

    private function catalog(): array
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'gilas-public-order-' . fake()->unique()->numberBetween(1000, 9999),
            'status' => 'active',
        ]);

        $restaurant->settings()->create([
            'ordering_enabled' => true,
            'dine_in_enabled' => true,
            'pickup_enabled' => true,
            'delivery_enabled' => true,
            'reservation_enabled' => true,
        ]);

        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'برگرها',
            'slug' => 'burgers',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $category->id,
            'name' => 'برگر گوساله',
            'slug' => 'beef-burger',
            'description' => 'برگر گوساله با پنیر و سبزی تازه.',
            'price' => 280000,
            'sort_order' => 1,
            'is_active' => true,
            'is_available' => true,
        ]);

        return [$restaurant, $item];
    }
}
