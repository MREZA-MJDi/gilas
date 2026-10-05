<?php

namespace Tests\Feature;

use App\Enums\RestaurantUserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_requires_an_active_restaurant_membership(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'auth-flow',
            'status' => 'active',
        ]);

        $user->restaurants()->attach($restaurant->id, [
            'role' => RestaurantUserRole::Owner->value,
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => 'owner@example.com',
            'password' => 'secret-password',
        ]);

        $response
            ->assertRedirect(route('admin.dashboard', $restaurant))
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_rejects_user_without_active_membership(): void
    {
        User::factory()->create([
            'email' => 'orphan@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => 'orphan@example.com',
            'password' => 'secret-password',
        ]);

        $response
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_dashboard_is_forbidden_for_users_who_are_not_members(): void
    {
        $user = User::factory()->create();

        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'not-a-member',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.dashboard', $restaurant));

        $response->assertForbidden();
    }
}
