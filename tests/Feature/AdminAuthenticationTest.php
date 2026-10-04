<?php

namespace Tests\Feature;

use App\Enums\RestaurantUserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login_and_is_sent_to_a_dashboard_they_can_access(): void
    {
        $restaurant = $this->restaurant();
        $user = User::factory()->create([
            'email' => 'owner@example.test',
            'password' => 'secret-password',
        ]);

        $restaurant->users()->attach($user->id, [
            'role' => RestaurantUserRole::Owner->value,
            'is_active' => true,
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'owner@example.test',
                'password' => 'secret-password',
                'remember' => true,
            ])
            ->assertRedirect(route('admin.dashboard', $restaurant->slug));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_do_not_authenticate(): void
    {
        $this->post(route('login.store'), [
            'email' => 'missing@example.test',
            'password' => 'wrong-password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    private function restaurant(): Restaurant
    {
        return Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'gilas-auth-test',
            'description' => 'تست',
            'currency' => 'IRR',
            'status' => 'active',
        ]);
    }
}
