<?php

namespace Tests\Feature;

use App\Enums\RestaurantUserRole;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_staff_login(): void
    {
        $restaurant = $this->restaurant();

        $this->get(route('admin.dashboard', $restaurant->slug))
            ->assertRedirect(route('login'));
    }

    public function test_owner_can_view_data_driven_operations_dashboard(): void
    {
        $restaurant = $this->restaurant();
        $user = User::factory()->create(['name' => 'مدیر گیلاس']);

        $restaurant->users()->attach($user->id, [
            'role' => RestaurantUserRole::Owner->value,
            'is_active' => true,
        ]);

        RestaurantTable::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'میز ۱',
            'number' => 1,
            'capacity' => 2,
            'floor' => 'سالن',
            'zone' => 'اصلی',
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard', $restaurant->slug))
            ->assertOk()
            ->assertSee('Operations Room')
            ->assertSee('صف آشپزخانه')
            ->assertSee('آخرین سفارش‌ها')
            ->assertSee('میزها و QR')
            ->assertSee('فروش ۷ روز اخیر')
            ->assertSee('میز ۱');
    }

    public function test_kitchen_member_cannot_open_dashboard_until_a_dashboard_permission_exists(): void
    {
        $restaurant = $this->restaurant();
        $user = User::factory()->create();

        $restaurant->users()->attach($user->id, [
            'role' => RestaurantUserRole::Kitchen->value,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard', $restaurant->slug))
            ->assertForbidden();
    }

    private function restaurant(): Restaurant
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'gilas-dashboard-test-' . fake()->unique()->numberBetween(1000, 9999),
            'description' => 'تست داشبورد',
            'currency' => 'IRR',
            'status' => 'active',
        ]);

        $restaurant->settings()->create([
            'ordering_enabled' => true,
            'dine_in_enabled' => true,
            'pickup_enabled' => true,
            'delivery_enabled' => true,
            'reservation_enabled' => true,
        ]);

        return $restaurant;
    }
}
