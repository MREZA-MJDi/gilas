<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionValue;
use App\Models\MenuItemVariant;
use App\Models\Restaurant;
use App\Services\TableQrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_menu_eager_loads_only_active_customer_menu_data(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'خانه گیلاسی',
            'slug' => 'gilas-customer-menu',
        ]);

        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'قهوه',
            'slug' => 'coffee',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $category->id,
            'name' => 'لاته',
            'slug' => 'latte',
            'price' => 150000,
            'sort_order' => 1,
            'is_active' => true,
            'is_available' => true,
        ]);

        MenuItemVariant::create([
            'menu_item_id' => $item->id,
            'name' => 'متوسط',
            'price' => 150000,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        MenuItemVariant::create([
            'menu_item_id' => $item->id,
            'name' => 'قدیمی',
            'price' => 120000,
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $option = MenuItemOption::create([
            'menu_item_id' => $item->id,
            'name' => 'شیر',
            'min_select' => 0,
            'max_select' => 1,
            'is_required' => false,
            'sort_order' => 1,
        ]);

        MenuItemOptionValue::create([
            'menu_item_option_id' => $option->id,
            'name' => 'بادام',
            'price_delta' => 30000,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        MenuItemOptionValue::create([
            'menu_item_option_id' => $option->id,
            'name' => 'غیرفعال',
            'price_delta' => 10000,
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $table = $restaurant->tables()->create([
            'name' => 'میز 1',
            'number' => 1,
            'capacity' => 4,
            'status' => 'available',
            'is_active' => true,
        ]);

        $qr = app(TableQrCodeService::class)->issue($table);

        $response = $this->get(route('table.menu', ['token' => $qr->token]));

        $response
            ->assertOk()
            ->assertSee('لاته')
            ->assertSee('بادام')
            ->assertSee('میز 1')
            ->assertDontSee('غیرفعال');

        $this->assertStringNotContainsString('قدیمی', $response->getContent());
    }

    public function test_qr_menu_rejects_invalid_and_inactive_codes(): void
    {
        $this->get(route('table.menu', ['token' => 'doesnotexist']))
            ->assertNotFound();

        $restaurant = Restaurant::create([
            'name' => 'Gilas',
            'slug' => 'gilas-invalid-qr',
        ]);

        $table = $restaurant->tables()->create([
            'name' => 'میز 1',
            'number' => 1,
            'capacity' => 2,
            'status' => 'available',
            'is_active' => true,
        ]);

        $qr = app(TableQrCodeService::class)->issue($table);
        $qr->update(['is_active' => false]);

        $this->get(route('table.menu', ['token' => $qr->token]))
            ->assertNotFound();
    }

    public function test_customer_can_submit_a_table_order_without_supplying_server_side_price(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Gilas',
            'slug' => 'gilas-table-order',
        ]);

        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'قهوه',
            'slug' => 'coffee-order',
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $category->id,
            'name' => 'آمریکانو',
            'slug' => 'americano',
            'price' => 90000,
            'is_active' => true,
            'is_available' => true,
        ]);

        $table = $restaurant->tables()->create([
            'name' => 'میز 4',
            'number' => 4,
            'capacity' => 2,
            'status' => 'available',
            'is_active' => true,
        ]);

        $qr = app(TableQrCodeService::class)->issue($table);

        $payload = [
            'items' => [[
                'menu_item_id' => $item->id,
                'quantity' => 2,
                'unit_price' => 1,
            ]],
        ];

        $response = $this->withHeader('Idempotency-Key', 'table-order-001')
            ->postJson(route('table.orders.store', ['token' => $qr->token]), $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', OrderStatus::Pending->value)
            ->assertJsonPath('data.total', 180000);

        $this->assertDatabaseHas('orders', [
            'restaurant_id' => $restaurant->id,
            'restaurant_table_id' => $table->id,
            'order_type' => 'dine_in',
            'total' => 180000,
        ]);

        $retry = $this->withHeader('Idempotency-Key', 'table-order-001')
            ->postJson(route('table.orders.store', ['token' => $qr->token]), $payload);

        $retry->assertCreated();

        $this->assertSame(
            $response->json('data.order_id'),
            $retry->json('data.order_id')
        );

        $this->assertSame(1, $restaurant->orders()->count());
    }
}
