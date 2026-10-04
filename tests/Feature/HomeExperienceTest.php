<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_is_a_landing_only_page_with_real_navigation_routes(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'gilas-home',
            'description' => 'یک کافه برای قهوه و دسر.',
            'currency' => 'IRR',
            'status' => 'active',
        ]);

        $restaurant->settings()->create([
            'ordering_enabled' => true,
            'reservation_enabled' => true,
        ]);

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee(route('menu.index'), false)
            ->assertSee(route('public.reservation'), false)
            ->assertSee(route('public.location'), false);

        $response
            ->assertDontSee(route('public.experience'), false)
            ->assertDontSee(route('public.story'), false)
            ->assertDontSee('GILAS');
        $response->assertDontSee('Today at');
        $response->assertDontSee('چیزهایی که امروز');
        $response->assertDontSee('انتخاب‌های امروز');
    }

    public function test_home_still_works_when_no_restaurant_has_been_provisioned(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('گیلاس');
    }
}
