<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Delivery;
use App\Models\DeliveryZone;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionValue;
use App\Models\MenuItemVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantHour;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use App\Models\TableQrCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RestaurantFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_foundation_schema_is_migrated(): void
    {
        $tables = [
            'restaurants',
            'restaurant_settings',
            'restaurant_hours',
            'menu_categories',
            'menu_items',
            'menu_item_variants',
            'menu_item_options',
            'menu_item_option_values',
            'restaurant_tables',
            'table_qr_codes',
            'customers',
            'customer_addresses',
            'orders',
            'order_items',
            'order_item_options',
            'order_status_histories',
            'couriers',
            'deliveries',
            'delivery_zones',
            'payments',
            'payment_transactions',
            'reservations',
            'restaurant_user',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                DB::getSchemaBuilder()->hasTable($table),
                "Expected table [{$table}] to exist."
            );
        }
    }

    public function test_menu_relationship_tree_is_connected(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'خانه گیلاسی',
            'slug' => 'gilas',
        ]);

        $category = $restaurant->categories()->create([
            'name' => 'قهوه',
            'slug' => 'coffee',
        ]);

        $item = $category->items()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'لاته',
            'slug' => 'latte',
            'price' => 120000,
        ]);

        $variant = $item->variants()->create([
            'name' => 'بزرگ',
            'price' => 150000,
        ]);

        $option = $item->options()->create([
            'name' => 'افزودنی',
            'max_select' => 2,
        ]);

        $value = $option->values()->create([
            'name' => 'سیروپ وانیل',
            'price_delta' => 30000,
        ]);

        $this->assertTrue($restaurant->categories->contains($category));
        $this->assertTrue($category->items->contains($item));
        $this->assertTrue($item->variants->contains($variant));
        $this->assertTrue($item->options->contains($option));
        $this->assertTrue($option->values->contains($value));
    }

    public function test_table_qr_belongs_to_the_table_and_table_belongs_to_restaurant(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas']);
        $table = $restaurant->tables()->create([
            'name' => 'میز ۱',
            'number' => 1,
            'capacity' => 4,
        ]);

        $qr = $table->qrCode()->create([
            'token' => str()->random(48),
            'is_active' => true,
        ]);

        $this->assertTrue($table->is($qr->table));
        $this->assertTrue($restaurant->tables->contains($table));
    }

    public function test_order_keeps_historical_item_and_option_snapshots(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas']);
        $category = $restaurant->categories()->create(['name' => 'غذا', 'slug' => 'food']);
        $item = $category->items()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'برگر',
            'slug' => 'burger',
            'price' => 220000,
        ]);
        $option = $item->options()->create(['name' => 'پنیر', 'max_select' => 1]);
        $option->values()->create(['name' => 'چدار', 'price_delta' => 30000]);

        $customer = Customer::create(['phone' => '09120000000', 'name' => 'مشتری']);
        $order = $restaurant->orders()->create([
            'customer_id' => $customer->id,
            'order_number' => 'GLS-1001',
            'order_type' => OrderType::Delivery,
            'status' => OrderStatus::Pending,
            'subtotal' => 250000,
            'total' => 250000,
        ]);

        $orderItem = $order->items()->create([
            'menu_item_id' => $item->id,
            'name' => $item->name,
            'unit_price' => 220000,
            'quantity' => 1,
            'total_price' => 250000,
            'note' => 'پیاز کمتر',
        ]);

        $snapshot = $orderItem->options()->create([
            'option_name' => 'پنیر',
            'value_name' => 'چدار',
            'price_delta' => 30000,
        ]);

        $item->update(['name' => 'برگر جدید', 'price' => 280000]);

        $this->assertSame('برگر', $orderItem->fresh()->name);
        $this->assertSame(220000, $orderItem->fresh()->unit_price);
        $this->assertSame('چدار', $snapshot->value_name);
        $this->assertSame(30000, $snapshot->price_delta);
    }

    public function test_order_status_history_records_actor_and_enum_values(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas']);
        $user = User::factory()->create();

        $order = $restaurant->orders()->create([
            'order_number' => 'GLS-1002',
            'order_type' => OrderType::DineIn,
            'status' => OrderStatus::Confirmed,
        ]);

        $history = $order->statusHistory()->create([
            'from_status' => OrderStatus::Pending,
            'to_status' => OrderStatus::Confirmed,
            'changed_by' => $user->id,
            'note' => 'تأیید شد',
        ]);

        $history->refresh();

        $this->assertSame(OrderStatus::Pending, $history->from_status);
        $this->assertSame(OrderStatus::Confirmed, $history->to_status);
        $this->assertTrue($history->changedBy->is($user));
    }

    public function test_delivery_payment_and_reservation_models_cast_statuses(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas']);
        $customer = Customer::create(['phone' => '09121111111']);
        $order = $restaurant->orders()->create([
            'customer_id' => $customer->id,
            'order_number' => 'GLS-1003',
            'order_type' => OrderType::Delivery,
            'status' => OrderStatus::Preparing,
        ]);

        $delivery = $order->delivery()->create(['status' => DeliveryStatus::Assigned]);
        $payment = $order->payment()->create([
            'method' => 'online',
            'status' => PaymentStatus::Pending,
            'amount' => 300000,
        ]);
        $payment->transactions()->create([
            'provider' => 'demo',
            'transaction_id' => 'txn-1003',
            'amount' => 300000,
            'status' => 'pending',
        ]);

        $reservation = $restaurant->reservations()->create([
            'customer_id' => $customer->id,
            'reservation_date' => now()->toDateString(),
            'start_time' => '19:00:00',
            'guest_count' => 2,
            'status' => ReservationStatus::Confirmed,
        ]);

        $this->assertSame(DeliveryStatus::Assigned, $delivery->fresh()->status);
        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertSame(ReservationStatus::Confirmed, $reservation->fresh()->status);
    }

    public function test_restaurant_user_membership_is_scoped_and_has_role(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas']);
        $user = User::factory()->create();

        $restaurant->users()->attach($user->id, [
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->assertTrue($restaurant->users->contains($user));
        $this->assertTrue($user->activeRestaurants->contains($restaurant));
        $this->assertSame('manager', $user->restaurants->first()->pivot->role);
    }

    public function test_restaurant_settings_are_one_to_one(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas']);

        $settings = $restaurant->settings()->create([
            'ordering_enabled' => true,
            'delivery_enabled' => true,
            'tax_percent' => 9.00,
        ]);

        $this->assertTrue($restaurant->settings->is($settings));
        $this->assertSame('9.00', (string) $settings->fresh()->tax_percent);
    }
}
