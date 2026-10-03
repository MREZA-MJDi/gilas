<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\ImageService;
use App\Services\OrderService;
use App\Services\RestaurantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FoundationServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_service_creates_default_settings_and_provisions_tables(): void
    {
        $service = app(RestaurantService::class);

        $restaurant = $service->create([
            'name' => 'خانه گیلاسی',
            'slug' => 'gilas-service-test',
        ]);

        $service->provisionTables($restaurant, 3, 4);

        $this->assertTrue($restaurant->fresh()->settings()->exists());
        $this->assertSame([1, 2, 3], $restaurant->fresh()->tables()->pluck('number')->all());
        $this->assertSame([4, 4, 4], $restaurant->fresh()->tables()->pluck('capacity')->all());
    }

    public function test_order_service_creates_a_transactional_order_snapshot(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-order-service']);
        $customer = Customer::create(['name' => 'مشتری', 'phone' => '09129999999']);

        $order = app(OrderService::class)->create($restaurant, [
            'customer_id' => $customer->id,
            'order_type' => OrderType::Delivery,
            'customer_note' => 'بدون قند',
            'items' => [
                [
                    'name' => 'لاته',
                    'unit_price' => 150000,
                    'quantity' => 2,
                    'note' => 'گرم',
                    'options' => [
                        [
                            'option_name' => 'شیر',
                            'value_name' => 'بادام',
                            'price_delta' => 30000,
                        ],
                    ],
                ],
            ],
        ]);

        $order->load('items.options');

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(300000, $order->subtotal);
        $this->assertSame(300000, $order->total);
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $order->items->first()->options);
        $this->assertCount(1, $order->statusHistory);
    }

    public function test_order_service_includes_option_price_in_line_total(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-option-price']);
        
        $order = app(OrderService::class)->create($restaurant, [
            'order_type' => OrderType::Pickup,
            'items' => [
                [
                    'name' => 'قهوه',
                    'unit_price' => 100000,
                    'quantity' => 2,
                    'options' => [
                        ['option_name' => 'شیر', 'value_name' => 'بادام', 'price_delta' => 25000],
                    ],
                ],
            ],
        ]);

        $item = $order->items->first();

        $this->assertSame(125000, $item->unit_price);
        $this->assertSame(250000, $item->total_price);
        $this->assertSame(250000, $order->subtotal);
        $this->assertSame(250000, $order->total);
    }

    public function test_order_service_requires_the_right_context_for_order_type(): void
    {
        $restaurant = Restaurant::create(['name' => 'Gilas', 'slug' => 'gilas-context-test']);

        $this->expectException(ValidationException::class);

        app(OrderService::class)->create($restaurant, [
            'order_type' => OrderType::Delivery,
            'items' => [['name' => 'قهوه', 'unit_price' => 100000, 'quantity' => 1]],
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
}
