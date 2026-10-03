<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionValue;
use App\Models\MenuItemVariant;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\ImageService;
use App\Services\OrderService;
use App\Services\RestaurantService;
use App\Services\TableQrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FoundationServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_service_creates_default_settings_and_provisions_tables_with_qr_codes(): void
    {
        $service = app(RestaurantService::class);

        $restaurant = $service->create([
            'name' => 'خانه گیلاسی',
            'slug' => 'gilas-service-test',
        ]);

        $service->provisionTables($restaurant, 3, 4);

        $tables = $restaurant->fresh()->tables()->with('qrCode')->orderBy('number')->get();

        $this->assertTrue($restaurant->fresh()->settings()->exists());
        $this->assertSame([1, 2, 3], $tables->pluck('number')->all());
        $this->assertSame([4, 4, 4], $tables->pluck('capacity')->all());
        $this->assertCount(3, $tables->pluck('qrCode')->filter());
        $this->assertCount(3, $tables->pluck('qrCode.token')->unique());
    }

    public function test_table_qr_regeneration_is_single_active_code(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-qr-regeneration']);
        $table = $restaurant->tables()->create([
            'name' => 'میز 1',
            'number' => 1,
            'capacity' => 4,
            'status' => 'available',
            'is_active' => true,
        ]);

        $service = app(TableQrCodeService::class);
        $first = $service->issue($table);
        $same = $service->issue($table);

        $this->assertSame($first->id, $same->id);

        $second = $service->issue($table, true);

        $this->assertNotSame($first->id, $second->id);
        $this->assertFalse($first->fresh()->is_active);
        $this->assertTrue($second->fresh()->is_active);
        $this->assertSame(1, $table->qrCode()->where('is_active', true)->count());
    }

    public function test_order_service_uses_authoritative_menu_price_and_snapshots_the_variant(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-order-service']);
        $customer = Customer::create(['name' => 'مشتری', 'phone' => '09129999999']);
        $address = CustomerAddress::create([
            'customer_id' => $customer->id,
            'title' => 'خانه',
            'recipient_name' => 'مشتری',
            'phone' => '09129999999',
            'address' => 'آدرس نمونه',
        ]);

        [$item, $variant, $optionValue] = $this->createMenuGraph(
            $restaurant,
            basePrice: 100000,
            variantPrice: 150000,
            optionDelta: 30000,
            itemName: 'لاته'
        );

        $order = app(OrderService::class)->create($restaurant, [
            'customer_id' => $customer->id,
            'customer_address_id' => $address->id,
            'order_type' => OrderType::Delivery,
            'customer_note' => 'بدون قند',
            'items' => [[
                'menu_item_id' => $item->id,
                'menu_item_variant_id' => $variant->id,
                'quantity' => 2,
                'note' => 'گرم',
                'unit_price' => 1,
                'option_value_ids' => [$optionValue->id],
            ]],
        ]);

        $order->load('items.options');
        $orderItem = $order->items->first();

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(360000, $order->subtotal);
        $this->assertSame(360000, $order->total);
        $this->assertSame(180000, $orderItem->unit_price);
        $this->assertSame('لاته', $orderItem->name);
        $this->assertSame('متوسط', $orderItem->variant_name);
        $this->assertSame(30000, $orderItem->options->first()->price_delta);
        $this->assertCount(1, $order->statusHistory);

        $item->update(['name' => 'لاته جدید']);
        $variant->update(['name' => 'بزرگ', 'price' => 999999]);
        $optionValue->update(['name' => 'شیر نارگیل', 'price_delta' => 999999]);

        $snapshot = $orderItem->fresh()->load('options');

        $this->assertSame('لاته', $snapshot->name);
        $this->assertSame('متوسط', $snapshot->variant_name);
        $this->assertSame(180000, $snapshot->unit_price);
        $this->assertSame('بادام', $snapshot->options->first()->value_name);
        $this->assertSame(30000, $snapshot->options->first()->price_delta);
    }

    public function test_order_service_rejects_tampered_or_cross_restaurant_menu_data(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-price-hardening']);
        $otherRestaurant = Restaurant::create(['name' => 'Other', 'slug' => 'other-price-hardening']);

        [$item, $variant, $optionValue] = $this->createMenuGraph($otherRestaurant);

        $this->expectException(ValidationException::class);

        app(OrderService::class)->create($restaurant, [
            'order_type' => OrderType::Pickup,
            'items' => [[
                'menu_item_id' => $item->id,
                'menu_item_variant_id' => $variant->id,
                'quantity' => 1,
                'unit_price' => 1,
                'option_value_ids' => [$optionValue->id],
            ]],
        ]);
    }

    public function test_order_service_idempotency_returns_the_same_order_for_retries(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-idempotency']);
        [$item] = $this->createMenuGraph($restaurant, basePrice: 100000);

        $payload = [
            'order_type' => OrderType::Pickup,
            'idempotency_key' => 'checkout-attempt-001',
            'items' => [[
                'menu_item_id' => $item->id,
                'quantity' => 2,
            ]],
        ];

        $first = app(OrderService::class)->create($restaurant, $payload);
        $retry = app(OrderService::class)->create($restaurant, $payload);

        $this->assertSame($first->id, $retry->id);
        $this->assertSame(1, $restaurant->orders()->count());
    }

    public function test_order_service_rejects_reusing_an_idempotency_key_for_a_different_payload(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-idempotency-mismatch']);
        [$item] = $this->createMenuGraph($restaurant, basePrice: 100000);

        app(OrderService::class)->create($restaurant, [
            'order_type' => OrderType::Pickup,
            'idempotency_key' => 'checkout-attempt-002',
            'items' => [[
                'menu_item_id' => $item->id,
                'quantity' => 1,
            ]],
        ]);

        $this->expectException(ValidationException::class);

        app(OrderService::class)->create($restaurant, [
            'order_type' => OrderType::Pickup,
            'idempotency_key' => 'checkout-attempt-002',
            'items' => [[
                'menu_item_id' => $item->id,
                'quantity' => 2,
            ]],
        ]);
    }

    public function test_order_service_requires_the_right_context_for_order_type(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-context-test']);
        [$item] = $this->createMenuGraph($restaurant);

        $this->expectException(ValidationException::class);

        app(OrderService::class)->create($restaurant, [
            'order_type' => OrderType::Delivery,
            'items' => [[
                'menu_item_id' => $item->id,
                'quantity' => 1,
            ]],
        ]);
    }

    public function test_order_service_rejects_invalid_transition_without_changing_order(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-transition-test']);
        $order = $restaurant->orders()->create([
            'order_number' => 'GLS-TEST-1',
            'order_type' => OrderType::DineIn,
            'status' => OrderStatus::Pending,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(OrderService::class)->changeStatus($order, OrderStatus::Delivered);
        } finally {
            $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
            $this->assertSame(0, $order->fresh()->statusHistory()->count());
        }
    }

    public function test_order_service_records_valid_status_transition_and_actor(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-transition-valid']);
        $user = User::factory()->create();

        $order = $restaurant->orders()->create([
            'order_number' => 'GLS-TEST-2',
            'order_type' => OrderType::DineIn,
            'status' => OrderStatus::Pending,
        ]);

        $updated = app(OrderService::class)->changeStatus(
            $order,
            OrderStatus::Confirmed,
            $user,
            'توسط صندوق تأیید شد'
        );

        $this->assertSame(OrderStatus::Confirmed, $updated->status);
        $this->assertNotNull($updated->confirmed_at);
        $history = $updated->statusHistory->last();
        $this->assertSame(OrderStatus::Pending, $history->from_status);
        $this->assertSame(OrderStatus::Confirmed, $history->to_status);
        $this->assertSame($user->id, $history->changed_by);
    }

    public function test_image_service_stores_replaces_and_deletes_images(): void
    {
        Storage::fake('public');

        $service = app(ImageService::class);

        $first = $service->store(UploadedFile::fake()->image('first.jpg'), 'menu');
        $this->assertTrue(Storage::disk('public')->exists($first));

        $second = $service->replace($first, UploadedFile::fake()->image('second.jpg'), 'menu');

        $this->assertFalse(Storage::disk('public')->exists($first));
        $this->assertTrue(Storage::disk('public')->exists($second));
        $this->assertNotNull($service->url($second));

        $service->delete($second);
        $this->assertFalse(Storage::disk('public')->exists($second));
    }

    private function createMenuGraph(
        Restaurant $restaurant,
        int $basePrice = 100000,
        int $variantPrice = 150000,
        int $optionDelta = 30000,
        string $itemName = 'قهوه'
    ): array {
        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'قهوه',
            'slug' => 'coffee-' . $restaurant->id . '-' . uniqid(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $category->id,
            'name' => $itemName,
            'slug' => 'item-' . $restaurant->id . '-' . uniqid(),
            'price' => $basePrice,
            'sort_order' => 1,
            'is_active' => true,
            'is_available' => true,
        ]);

        $variant = MenuItemVariant::create([
            'menu_item_id' => $item->id,
            'name' => 'متوسط',
            'price' => $variantPrice,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $option = MenuItemOption::create([
            'menu_item_id' => $item->id,
            'name' => 'شیر',
            'min_select' => 0,
            'max_select' => 1,
            'is_required' => false,
            'sort_order' => 1,
        ]);

        $optionValue = MenuItemOptionValue::create([
            'menu_item_option_id' => $option->id,
            'name' => 'بادام',
            'price_delta' => $optionDelta,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [$item, $variant, $optionValue];
    }
}
