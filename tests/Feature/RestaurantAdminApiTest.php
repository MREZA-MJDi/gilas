<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\RestaurantUserRole;
use App\Models\Courier;
use App\Models\Delivery;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantAdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_and_configure_menu_catalog(): void
    {
        $restaurant = $this->makeRestaurant('catalog-manager');
        $manager = $this->member($restaurant, RestaurantUserRole::Manager);

        $category = $this->actingAs($manager)->postJson(
            route('admin.menu.categories.store', ['restaurant' => $restaurant->slug]),
            [
                'name' => 'قهوه گرم',
                'slug' => 'hot-coffee',
                'sort_order' => 1,
                'is_active' => true,
            ]
        )->assertCreated()->assertJsonPath('data.slug', 'hot-coffee');

        $categoryId = $category->json('data.id');

        $item = $this->postJson(
            route('admin.menu.items.store', ['restaurant' => $restaurant->slug]),
            [
                'menu_category_id' => $categoryId,
                'name' => 'لاته',
                'slug' => 'latte',
                'description' => 'اسپرسو و شیر',
                'price' => 120000,
                'sort_order' => 1,
                'is_active' => true,
                'is_available' => true,
            ]
        )->assertCreated()->assertJsonPath('data.price', 120000);

        $itemId = $item->json('data.id');

        $this->postJson(
            route('admin.menu.variants.store', ['restaurant' => $restaurant->slug, 'item' => $itemId]),
            ['name' => 'بزرگ', 'price' => 150000, 'sort_order' => 1, 'is_active' => true]
        )->assertCreated()->assertJsonPath('data.price', 150000);

        $option = $this->postJson(
            route('admin.menu.options.store', ['restaurant' => $restaurant->slug, 'item' => $itemId]),
            ['name' => 'نوع شیر', 'min_select' => 0, 'max_select' => 1, 'is_required' => false]
        )->assertCreated();

        $optionId = $option->json('data.id');

        $this->postJson(
            route('admin.menu.option-values.store', [
                'restaurant' => $restaurant->slug,
                'item' => $itemId,
                'option' => $optionId,
            ]),
            ['name' => 'شیر بادام', 'price_delta' => 20000, 'sort_order' => 1, 'is_active' => true]
        )->assertCreated()->assertJsonPath('data.price_delta', 20000);

        $this->getJson(route('admin.menu.index', ['restaurant' => $restaurant->slug]))
            ->assertOk()
            ->assertJsonPath('data.0.items.0.name', 'لاته')
            ->assertJsonPath('data.0.items.0.variants.0.name', 'بزرگ')
            ->assertJsonPath('data.0.items.0.options.0.values.0.name', 'شیر بادام');

        $this->assertDatabaseHas('menu_items', [
            'id' => $itemId,
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $categoryId,
            'price' => 120000,
        ]);
    }

    public function test_waiter_cannot_mutate_the_menu_and_foreign_menu_records_are_not_exposed(): void
    {
        $restaurant = $this->makeRestaurant('catalog-owner');
        $otherRestaurant = $this->makeRestaurant('catalog-other');
        $waiter = $this->member($restaurant, RestaurantUserRole::Waiter);
        $category = $restaurant->categories()->create([
            'name' => 'قهوه',
            'slug' => 'coffee',
            'is_active' => true,
        ]);
        $foreignCategory = $otherRestaurant->categories()->create([
            'name' => 'دسر',
            'slug' => 'dessert',
            'is_active' => true,
        ]);
        $foreignItem = $otherRestaurant->items()->create([
            'menu_category_id' => $foreignCategory->id,
            'name' => 'کیک',
            'slug' => 'cake',
            'price' => 50000,
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->actingAs($waiter)->postJson(
            route('admin.menu.categories.store', ['restaurant' => $restaurant->slug]),
            ['name' => 'دسر', 'slug' => 'dessert']
        )->assertForbidden();

        $owner = $this->member($restaurant, RestaurantUserRole::Owner);

        $this->actingAs($owner)->patchJson(
            route('admin.menu.items.update', [
                'restaurant' => $restaurant->slug,
                'item' => $foreignItem->id,
            ]),
            [
                'menu_category_id' => $category->id,
                'name' => 'کیک دست‌کاری‌شده',
                'slug' => 'cake-tampered',
                'price' => 1,
            ]
        )->assertNotFound();

        $this->postJson(
            route('admin.menu.items.store', ['restaurant' => $restaurant->slug]),
            [
                'menu_category_id' => $foreignCategory->id,
                'name' => 'آیتم خارج از رستوران',
                'slug' => 'foreign-item',
                'price' => 10000,
            ]
        )->assertUnprocessable()->assertJsonValidationErrors('menu_category_id');

        $this->assertDatabaseHas('menu_items', ['id' => $foreignItem->id, 'name' => 'کیک']);
        $this->assertDatabaseMissing('menu_items', ['slug' => 'foreign-item']);
    }

    public function test_menu_option_selection_range_and_category_deletion_are_guarded(): void
    {
        $restaurant = $this->makeRestaurant('catalog-constraints');
        $manager = $this->member($restaurant, RestaurantUserRole::Manager);
        $category = $restaurant->categories()->create([
            'name' => 'قهوه',
            'slug' => 'coffee',
            'is_active' => true,
        ]);
        $item = $restaurant->items()->create([
            'menu_category_id' => $category->id,
            'name' => 'موکا',
            'slug' => 'mocha',
            'price' => 100000,
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->actingAs($manager)->postJson(
            route('admin.menu.options.store', ['restaurant' => $restaurant->slug, 'item' => $item->id]),
            ['name' => 'افزودنی', 'min_select' => 3, 'max_select' => 1]
        )->assertUnprocessable()->assertJsonValidationErrors('max_select');

        $this->deleteJson(
            route('admin.menu.categories.destroy', [
                'restaurant' => $restaurant->slug,
                'category' => $category->id,
            ])
        )->assertUnprocessable()->assertJsonValidationErrors('category');

        $this->assertDatabaseHas('menu_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('menu_items', ['id' => $item->id]);
    }

    public function test_manager_can_assign_and_complete_delivery_with_audited_order_transitions(): void
    {
        $restaurant = $this->makeRestaurant('delivery-ops');
        $manager = $this->member($restaurant, RestaurantUserRole::Manager);
        $order = $restaurant->orders()->create([
            'order_number' => 'GLS-DELIVERY-OPS-001',
            'order_type' => OrderType::Delivery,
            'status' => OrderStatus::Ready,
            'subtotal' => 130000,
            'delivery_fee' => 25000,
            'total' => 155000,
        ]);
        $delivery = $order->delivery()->create([
            'status' => DeliveryStatus::Pending,
            'delivery_address_snapshot' => 'آدرس ذخیره شده',
        ]);

        $courierResponse = $this->actingAs($manager)->postJson(
            route('admin.couriers.store', ['restaurant' => $restaurant->slug]),
            [
                'name' => 'پیک تست',
                'phone' => '09120001111',
                'vehicle_type' => 'bicycle',
            ]
        )->assertCreated();

        $courierId = $courierResponse->json('data.id');

        $this->patchJson(
            route('admin.deliveries.status', [
                'restaurant' => $restaurant->slug,
                'delivery' => $delivery->id,
            ]),
            ['status' => DeliveryStatus::Assigned->value, 'courier_id' => $courierId]
        )->assertOk()->assertJsonPath('data.status', DeliveryStatus::Assigned->value);

        $this->assertDatabaseHas('couriers', ['id' => $courierId, 'status' => 'busy']);

        $this->patchJson(
            route('admin.deliveries.status', [
                'restaurant' => $restaurant->slug,
                'delivery' => $delivery->id,
            ]),
            ['status' => DeliveryStatus::Dispatched->value, 'note' => 'پیک حرکت کرد']
        )->assertOk()
            ->assertJsonPath('data.status', DeliveryStatus::Dispatched->value)
            ->assertJsonPath('data.order_status', OrderStatus::OutForDelivery->value);

        $this->patchJson(
            route('admin.deliveries.status', [
                'restaurant' => $restaurant->slug,
                'delivery' => $delivery->id,
            ]),
            ['status' => DeliveryStatus::PickedUp->value]
        )->assertOk()->assertJsonPath('data.status', DeliveryStatus::PickedUp->value);

        $this->patchJson(
            route('admin.deliveries.status', [
                'restaurant' => $restaurant->slug,
                'delivery' => $delivery->id,
            ]),
            ['status' => DeliveryStatus::Delivered->value, 'note' => 'تحویل انجام شد']
        )->assertOk()
            ->assertJsonPath('data.status', DeliveryStatus::Delivered->value)
            ->assertJsonPath('data.order_status', OrderStatus::Delivered->value);

        $this->assertNotNull($delivery->fresh()->delivered_at);
        $this->assertSame(4, $delivery->fresh()->statusHistories()->count());
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Delivered->value]);
        $this->assertDatabaseHas('couriers', ['id' => $courierId, 'status' => 'available']);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => OrderStatus::OutForDelivery->value,
            'changed_by' => $manager->id,
        ]);
        $this->assertDatabaseHas('delivery_status_histories', [
            'delivery_id' => $delivery->id,
            'to_status' => DeliveryStatus::Delivered->value,
            'changed_by' => $manager->id,
        ]);
    }

    public function test_delivery_rejects_foreign_couriers_and_out_of_order_transitions(): void
    {
        $restaurant = $this->makeRestaurant('delivery-own');
        $foreignRestaurant = $this->makeRestaurant('delivery-foreign');
        $manager = $this->member($restaurant, RestaurantUserRole::Manager);
        $order = $restaurant->orders()->create([
            'order_number' => 'GLS-DELIVERY-OPS-002',
            'order_type' => OrderType::Delivery,
            'status' => OrderStatus::Ready,
            'subtotal' => 30000,
            'total' => 30000,
        ]);
        $delivery = $order->delivery()->create(['status' => DeliveryStatus::Pending]);
        $foreignCourier = $foreignRestaurant->couriers()->create([
            'name' => 'پیک خارجی',
            'phone' => '09120002222',
            'status' => 'available',
        ]);

        $this->actingAs($manager)->patchJson(
            route('admin.deliveries.status', [
                'restaurant' => $restaurant->slug,
                'delivery' => $delivery->id,
            ]),
            ['status' => DeliveryStatus::Assigned->value, 'courier_id' => $foreignCourier->id]
        )->assertUnprocessable()->assertJsonValidationErrors('courier_id');

        $this->patchJson(
            route('admin.deliveries.status', [
                'restaurant' => $restaurant->slug,
                'delivery' => $delivery->id,
            ]),
            ['status' => DeliveryStatus::Delivered->value]
        )->assertUnprocessable()->assertJsonValidationErrors('status');

        $this->assertSame(DeliveryStatus::Pending, $delivery->fresh()->status);
        $this->assertSame(OrderStatus::Ready, $order->fresh()->status);
        $this->assertDatabaseCount('delivery_status_histories', 0);
    }

    public function test_couriers_only_see_and_update_deliveries_assigned_to_their_account(): void
    {
        $restaurant = $this->makeRestaurant('delivery-courier-scope');
        $courierUser = $this->member($restaurant, RestaurantUserRole::Courier);
        $manager = $this->member($restaurant, RestaurantUserRole::Manager);

        $assignedCourier = $restaurant->couriers()->create([
            'user_id' => $courierUser->id,
            'name' => 'پیک واردشده',
            'phone' => '09120003333',
            'status' => 'busy',
        ]);
        $otherCourier = $restaurant->couriers()->create([
            'name' => 'پیک دیگر',
            'phone' => '09120004444',
            'status' => 'busy',
        ]);

        $orderA = $restaurant->orders()->create([
            'order_number' => 'GLS-DELIVERY-OPS-003',
            'order_type' => OrderType::Delivery,
            'status' => OrderStatus::OutForDelivery,
            'subtotal' => 10000,
            'total' => 10000,
        ]);
        $orderB = $restaurant->orders()->create([
            'order_number' => 'GLS-DELIVERY-OPS-004',
            'order_type' => OrderType::Delivery,
            'status' => OrderStatus::OutForDelivery,
            'subtotal' => 10000,
            'total' => 10000,
        ]);
        $assignedDelivery = $orderA->delivery()->create([
            'courier_id' => $assignedCourier->id,
            'status' => DeliveryStatus::PickedUp,
        ]);
        $otherDelivery = $orderB->delivery()->create([
            'courier_id' => $otherCourier->id,
            'status' => DeliveryStatus::PickedUp,
        ]);

        $this->actingAs($courierUser)->getJson(
            route('admin.deliveries.index', ['restaurant' => $restaurant->slug])
        )->assertOk()->assertJsonCount(1, 'data.data');

        $this->patchJson(
            route('admin.deliveries.status', [
                'restaurant' => $restaurant->slug,
                'delivery' => $otherDelivery->id,
            ]),
            ['status' => DeliveryStatus::Delivered->value]
        )->assertForbidden();

        $this->patchJson(
            route('admin.deliveries.status', [
                'restaurant' => $restaurant->slug,
                'delivery' => $assignedDelivery->id,
            ]),
            ['status' => DeliveryStatus::Delivered->value]
        )->assertOk()->assertJsonPath('data.order_status', OrderStatus::Delivered->value);
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

    private function member(Restaurant $restaurant, RestaurantUserRole $role): User
    {
        $user = User::factory()->create();
        $restaurant->users()->attach($user->id, [
            'role' => $role->value,
            'is_active' => true,
        ]);

        return $user;
    }
}
