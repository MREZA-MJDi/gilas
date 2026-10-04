<?php

namespace Tests\Feature;

use App\Enums\RestaurantPermission;
use App\Enums\RestaurantUserRole;
use App\Events\OrderCreated;
use App\Listeners\NotifyRestaurantStaffOfOrder;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\RestaurantAccessService;
use App\Services\TableQrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthorizationAndEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_has_full_restaurant_permissions_and_inactive_membership_has_none(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Gilas',
            'slug' => 'gilas-permission-owner',
        ]);

        $owner = User::factory()->create();
        $staff = User::factory()->create();

        $restaurant->users()->attach($owner->id, [
            'role' => RestaurantUserRole::Owner->value,
            'is_active' => true,
        ]);

        $restaurant->users()->attach($staff->id, [
            'role' => RestaurantUserRole::Manager->value,
            'is_active' => false,
        ]);

        $access = app(RestaurantAccessService::class);

        foreach (RestaurantPermission::cases() as $permission) {
            $this->assertTrue($access->can($owner, $restaurant, $permission));
            $this->assertFalse($access->can($staff, $restaurant, $permission));
        }
    }

    public function test_role_boundaries_are_enforced(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Gilas',
            'slug' => 'gilas-permission-roles',
        ]);

        $kitchen = User::factory()->create();
        $cashier = User::factory()->create();
        $waiter = User::factory()->create();

        $restaurant->users()->attach($kitchen->id, [
            'role' => RestaurantUserRole::Kitchen->value,
            'is_active' => true,
        ]);
        $restaurant->users()->attach($cashier->id, [
            'role' => RestaurantUserRole::Cashier->value,
            'is_active' => true,
        ]);
        $restaurant->users()->attach($waiter->id, [
            'role' => RestaurantUserRole::Waiter->value,
            'is_active' => true,
        ]);

        $access = app(RestaurantAccessService::class);

        $this->assertTrue($access->can($kitchen, $restaurant, RestaurantPermission::ViewOrders));
        $this->assertTrue($access->can($kitchen, $restaurant, RestaurantPermission::ChangeOrderStatus));
        $this->assertFalse($access->can($kitchen, $restaurant, RestaurantPermission::ManagePayments));

        $this->assertTrue($access->can($cashier, $restaurant, RestaurantPermission::ManagePayments));
        $this->assertFalse($access->can($cashier, $restaurant, RestaurantPermission::ManageMenu));

        $this->assertTrue($access->can($waiter, $restaurant, RestaurantPermission::CreateOrders));
        $this->assertFalse($access->can($waiter, $restaurant, RestaurantPermission::ManageSettings));
    }

    public function test_repeated_permission_checks_use_one_membership_query(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Gilas',
            'slug' => 'gilas-permission-cache',
        ]);

        $cashier = User::factory()->create();
        $restaurant->users()->attach($cashier->id, [
            'role' => RestaurantUserRole::Cashier->value,
            'is_active' => true,
        ]);

        $queries = 0;
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$queries): void {
            if (str_starts_with(strtolower(trim($query->sql)), 'select')) {
                $queries++;
            }
        });

        $access = app(RestaurantAccessService::class);

        $this->assertTrue($access->can($cashier, $restaurant, RestaurantPermission::ViewOrders));
        $this->assertTrue($access->can($cashier, $restaurant, RestaurantPermission::ManagePayments));

        $this->assertSame(1, $queries);
    }

    public function test_order_created_listener_notifies_only_relevant_active_staff(): void
    {
        Notification::fake();

        $restaurant = Restaurant::create([
            'name' => 'Gilas',
            'slug' => 'gilas-notification',
        ]);

        $kitchen = User::factory()->create();
        $cashier = User::factory()->create();
        $courier = User::factory()->create();

        $restaurant->users()->attach($kitchen->id, [
            'role' => RestaurantUserRole::Kitchen->value,
            'is_active' => true,
        ]);
        $restaurant->users()->attach($cashier->id, [
            'role' => RestaurantUserRole::Cashier->value,
            'is_active' => true,
        ]);
        $restaurant->users()->attach($courier->id, [
            'role' => RestaurantUserRole::Courier->value,
            'is_active' => true,
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_number' => 'GLS-EVENT-1',
            'order_type' => 'dine_in',
            'status' => 'pending',
            'subtotal' => 100000,
            'total' => 100000,
        ]);

        (new NotifyRestaurantStaffOfOrder())->handle(new OrderCreated($order->id));

        Notification::assertSentTo(
            [$kitchen, $cashier],
            NewOrderNotification::class
        );

        Notification::assertNotSentTo(
            $courier,
            NewOrderNotification::class
        );
    }

    public function test_public_table_order_cannot_accept_customer_id_from_request(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Gilas',
            'slug' => 'gilas-customer-identity',
        ]);

        $table = $restaurant->tables()->create([
            'name' => 'میز 1',
            'number' => 1,
            'capacity' => 2,
            'status' => 'available',
            'is_active' => true,
        ]);

        $qr = app(TableQrCodeService::class)->issue($table);

        $response = $this->withHeader('Idempotency-Key', 'identity-test-001')
            ->postJson(route('table.orders.store', ['token' => $qr->token]), [
                'customer_id' => 99999999,
                'items' => [],
            ]);

        $response->assertStatus(422);
        $this->assertSame(0, Order::query()->count());
    }
}
