<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Enums\RestaurantUserRole;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantBusinessFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pickup_checkout_prices_on_the_server_and_is_idempotent(): void
    {
        $restaurant = $this->makeRestaurant('gilas-pickup-flow');
        $item = $this->makeMenuItem($restaurant, 'espresso', 85000);

        $payload = [
            'order_type' => 'pickup',
            'payment_method' => 'cashier',
            'customer_name' => 'مشتری آزمایشی',
            'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
            'items' => [[
                'menu_item_id' => $item->id,
                'quantity' => 2,
                'unit_price' => 1,
            ]],
        ];

        $response = $this->withHeader('Idempotency-Key', 'pickup-business-flow-1')
            ->postJson(route('customer.orders.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('data.total', 170000)
            ->assertJsonPath('data.payment_status', PaymentStatus::Pending->value);

        $orderId = $response->json('data.order_id');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'restaurant_id' => $restaurant->id,
            'order_type' => 'pickup',
            'subtotal' => 170000,
            'delivery_fee' => 0,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $orderId,
            'method' => 'cashier',
            'status' => 'pending',
            'amount' => 170000,
        ]);
        $this->assertDatabaseHas('customers', ['phone' => '09123456789']);

        $retry = $this->withHeader('Idempotency-Key', 'pickup-business-flow-1')
            ->postJson(route('customer.orders.store'), $payload);

        $retry->assertCreated()->assertJsonPath('data.order_id', $orderId);
        $this->assertSame(1, $restaurant->orders()->count());
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_public_delivery_checkout_applies_zone_fee_and_persists_delivery_snapshot(): void
    {
        $restaurant = $this->makeRestaurant('gilas-delivery-flow');
        $item = $this->makeMenuItem($restaurant, 'cherry-cake', 120000);
        $zone = $restaurant->deliveryZones()->create([
            'name' => 'مرکز شهر',
            'min_order_amount' => 100000,
            'delivery_fee' => 25000,
            'estimated_minutes' => 35,
            'is_active' => true,
        ]);

        $response = $this->withHeader('Idempotency-Key', 'delivery-business-flow-1')
            ->postJson(route('customer.orders.store'), [
                'order_type' => 'delivery',
                'payment_method' => 'cashier',
                'customer_name' => 'مشتری ارسال',
                'phone' => '09121112222',
                'address' => 'خیابان نمونه، کوچه دوم، پلاک ۱۰',
                'postal_code' => '1234567890',
                'delivery_zone_id' => $zone->id,
                'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
            ]);

        $response->assertCreated()->assertJsonPath('data.total', 145000);
        $orderId = $response->json('data.order_id');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'order_type' => 'delivery',
            'delivery_zone_id' => $zone->id,
            'subtotal' => 120000,
            'delivery_fee' => 25000,
            'total' => 145000,
        ]);
        $this->assertDatabaseHas('deliveries', [
            'order_id' => $orderId,
            'status' => DeliveryStatus::Pending->value,
        ]);
        $this->assertDatabaseHas('deliveries', [
            'order_id' => $orderId,
            'delivery_address_snapshot' => 'مشتری ارسال, 09121112222, خیابان نمونه، کوچه دوم، پلاک ۱۰, کدپستی 1234567890',
        ]);
    }

    public function test_delivery_checkout_rejects_another_restaurants_zone_without_orphaning_customer_or_order(): void
    {
        $restaurant = $this->makeRestaurant('gilas-delivery-owner');
        $otherRestaurant = $this->makeRestaurant('other-delivery-owner');
        $item = $this->makeMenuItem($restaurant, 'soup', 80000);
        $foreignZone = $otherRestaurant->deliveryZones()->create([
            'name' => 'منطقه خارجی',
            'min_order_amount' => 0,
            'delivery_fee' => 0,
            'is_active' => true,
        ]);

        $this->withHeader('Idempotency-Key', 'delivery-foreign-zone')
            ->postJson(route('customer.orders.store'), [
                'order_type' => 'delivery',
                'payment_method' => 'cashier',
                'customer_name' => 'مشتری نامعتبر',
                'phone' => '09123334444',
                'address' => 'خیابان نامعتبر، پلاک ۲',
                'delivery_zone_id' => $foreignZone->id,
                'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('delivery_zone_id');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('customer_addresses', 0);
    }

    public function test_public_checkout_does_not_claim_to_support_an_unconfigured_online_gateway(): void
    {
        $restaurant = $this->makeRestaurant('gilas-online-disabled');
        $item = $this->makeMenuItem($restaurant, 'tea', 30000);

        $this->postJson(route('customer.orders.store'), [
            'order_type' => 'pickup',
            'payment_method' => 'online',
            'customer_name' => 'مشتری پرداخت',
            'phone' => '09125556666',
            'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_public_reservation_is_saved_as_pending_and_normalizes_persian_phone_digits(): void
    {
        $restaurant = $this->makeRestaurant('gilas-reservation-flow');

        $response = $this->postJson(route('public.reservations.store'), [
            'customer_name' => 'مهمان رزرو',
            'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
            'email' => 'guest@example.test',
            'reservation_date' => now()->addDays(2)->toDateString(),
            'start_time' => '19:00',
            'guest_count' => 3,
            'customer_note' => 'میز آرام',
        ]);

        $response->assertCreated()->assertJsonPath('data.status', ReservationStatus::Pending->value);
        $reservationId = $response->json('data.reservation_id');

        $this->assertDatabaseHas('customers', [
            'phone' => '09123456789',
            'name' => 'مهمان رزرو',
        ]);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservationId,
            'restaurant_id' => $restaurant->id,
            'guest_count' => 3,
            'status' => ReservationStatus::Pending->value,
            'start_time' => '19:00:00',
            'end_time' => '20:30:00',
        ]);
    }

    public function test_reservations_respect_disabled_setting_and_configured_closing_hours(): void
    {
        $restaurant = $this->makeRestaurant('gilas-reservation-disabled');
        $restaurant->settings()->update(['reservation_enabled' => false]);

        $payload = [
            'customer_name' => 'مهمان',
            'phone' => '09129990000',
            'reservation_date' => now()->addDays(2)->toDateString(),
            'start_time' => '19:00',
            'guest_count' => 2,
        ];

        $this->postJson(route('public.reservations.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reservation');
        $this->assertDatabaseCount('reservations', 0);
        $this->assertDatabaseCount('customers', 0);

        $restaurant->settings()->update(['reservation_enabled' => true]);
        $date = now()->addDays(3);
        $restaurant->hours()->create([
            'day_of_week' => $date->dayOfWeek,
            'open_time' => '09:00:00',
            'close_time' => '18:00:00',
            'is_closed' => false,
        ]);

        $this->postJson(route('public.reservations.store'), [
            ...$payload,
            'reservation_date' => $date->toDateString(),
            'start_time' => '17:30',
        ])->assertUnprocessable()->assertJsonValidationErrors('start_time');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_authorized_kitchen_staff_can_transition_order_status_and_invalid_transitions_are_rejected(): void
    {
        $restaurant = $this->makeRestaurant('gilas-order-status');
        $kitchen = User::factory()->create();
        $restaurant->users()->attach($kitchen->id, [
            'role' => RestaurantUserRole::Kitchen->value,
            'is_active' => true,
        ]);
        $order = $restaurant->orders()->create([
            'order_number' => 'GLS-STATUS-001',
            'order_type' => 'pickup',
            'status' => OrderStatus::Pending,
            'subtotal' => 50000,
            'total' => 50000,
        ]);

        $this->actingAs($kitchen)
            ->patchJson(route('admin.orders.status', [$restaurant->slug, $order->id]), [
                'status' => OrderStatus::Confirmed->value,
                'note' => 'پذیرش آشپزخانه',
            ])->assertOk()->assertJsonPath('data.status', OrderStatus::Confirmed->value);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => OrderStatus::Pending->value,
            'to_status' => OrderStatus::Confirmed->value,
            'changed_by' => $kitchen->id,
        ]);

        $this->actingAs($kitchen)
            ->patchJson(route('admin.orders.status', [$restaurant->slug, $order->id]), [
                'status' => OrderStatus::Completed->value,
            ])->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_only_staff_with_reservation_permission_can_confirm_and_assign_tables_without_overlap(): void
    {
        $restaurant = $this->makeRestaurant('gilas-reservation-admin');
        $owner = User::factory()->create();
        $waiter = User::factory()->create();
        $restaurant->users()->attach($owner->id, [
            'role' => RestaurantUserRole::Owner->value,
            'is_active' => true,
        ]);
        $restaurant->users()->attach($waiter->id, [
            'role' => RestaurantUserRole::Waiter->value,
            'is_active' => true,
        ]);

        $customer1 = \App\Models\Customer::create(['name' => 'یک', 'phone' => '09121110001']);
        $customer2 = \App\Models\Customer::create(['name' => 'دو', 'phone' => '09121110002']);
        $table = $restaurant->tables()->create([
            'name' => 'میز یک',
            'number' => 1,
            'capacity' => 4,
            'status' => 'available',
            'is_active' => true,
        ]);
        $date = now()->addDays(4)->toDateString();
        $existing = $restaurant->reservations()->create([
            'customer_id' => $customer1->id,
            'restaurant_table_id' => $table->id,
            'reservation_date' => $date,
            'start_time' => '19:00:00',
            'end_time' => '20:30:00',
            'guest_count' => 2,
            'status' => ReservationStatus::Confirmed,
        ]);
        $pending = $restaurant->reservations()->create([
            'customer_id' => $customer2->id,
            'reservation_date' => $date,
            'start_time' => '19:30:00',
            'end_time' => '21:00:00',
            'guest_count' => 3,
            'status' => ReservationStatus::Pending,
        ]);

        $this->actingAs($waiter)
            ->patchJson(route('admin.reservations.status', [$restaurant->slug, $pending->id]), [
                'status' => ReservationStatus::Confirmed->value,
                'restaurant_table_id' => $table->id,
            ])->assertForbidden();

        $this->actingAs($owner)
            ->patchJson(route('admin.reservations.status', [$restaurant->slug, $pending->id]), [
                'status' => ReservationStatus::Confirmed->value,
                'restaurant_table_id' => $table->id,
            ])->assertUnprocessable()->assertJsonValidationErrors('restaurant_table_id');

        $this->assertSame(ReservationStatus::Pending, $pending->fresh()->status);
        $this->assertSame(ReservationStatus::Confirmed, $existing->fresh()->status);
    }

    private function makeRestaurant(string $slug): Restaurant
    {
        $restaurant = Restaurant::create([
            'name' => 'خانه گیلاسی',
            'slug' => $slug,
            'status' => 'active',
            'timezone' => 'Asia/Tehran',
            'currency' => 'IRR',
        ]);

        $restaurant->settings()->create([
            'ordering_enabled' => true,
            'dine_in_enabled' => true,
            'pickup_enabled' => true,
            'delivery_enabled' => true,
            'reservation_enabled' => true,
            'min_order_amount' => 0,
            'default_delivery_minutes' => 45,
            'tax_percent' => 0,
            'service_charge_percent' => 0,
        ]);

        return $restaurant;
    }

    private function makeMenuItem(Restaurant $restaurant, string $slug, int $price): MenuItem
    {
        $category = $restaurant->categories()->create([
            'name' => 'منوی تست',
            'slug' => 'category-'.$slug,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return $category->items()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'آیتم تست',
            'slug' => $slug,
            'price' => $price,
            'sort_order' => 1,
            'is_active' => true,
            'is_available' => true,
        ]);
    }
}
