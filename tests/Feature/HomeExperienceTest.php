<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_is_rendered_from_active_restaurant_and_menu_data(): void
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

        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'قهوه',
            'slug' => 'coffee',
            'description' => 'انتخاب‌های گرم گیلاس.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $category->id,
            'name' => 'لاته',
            'slug' => 'latte',
            'price' => 150000,
            'sort_order' => 1,
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('گیلاس')
            ->assertSee('لاته')
            ->assertSee(route('menu.index'), false)
            ->assertSee(route('menu.category', ['slug' => 'coffee']), false);

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('لاته');

        $this->get(route('menu.category', ['slug' => 'coffee']))
            ->assertOk()
            ->assertSee('لاته');

        $this->get(route('menu.item', ['slug' => 'latte']))
            ->assertOk()
            ->assertSee('لاته');
    }

    public function test_home_still_works_when_no_restaurant_has_been_provisioned(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('گیلاس');
    }
}
