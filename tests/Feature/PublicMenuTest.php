<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_menu_renders_only_active_categories_and_available_items(): void
    {
        $restaurant = $this->restaurant();

        $coffee = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'قهوه',
            'slug' => 'coffee',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $hiddenCategory = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'مخفی',
            'slug' => 'hidden',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $coffee->id,
            'name' => 'لاته گیلاس',
            'slug' => 'gilas-latte-test',
            'description' => 'لاته تستی',
            'price' => 120000,
            'sort_order' => 1,
            'is_active' => true,
            'is_available' => true,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $coffee->id,
            'name' => 'ناموجود',
            'slug' => 'unavailable-test',
            'price' => 90000,
            'sort_order' => 2,
            'is_active' => true,
            'is_available' => false,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $hiddenCategory->id,
            'name' => 'نباید دیده شود',
            'slug' => 'hidden-item',
            'price' => 90000,
            'sort_order' => 1,
            'is_active' => true,
            'is_available' => true,
        ]);

        $response = $this->get(route('menu.index'));

        $response
            ->assertOk()
            ->assertSee('لاته گیلاس')
            ->assertSee(route('menu.item', ['slug' => 'gilas-latte-test']), false)
            ->assertDontSee('ناموجود')
            ->assertDontSee('نباید دیده شود');
    }

    public function test_public_menu_catalog_avoids_n_plus_one_queries(): void
    {
        $restaurant = $this->restaurant();

        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'برگرها',
            'slug' => 'burgers-n-plus-one',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        foreach (range(1, 3) as $index) {
            MenuItem::create([
                'restaurant_id' => $restaurant->id,
                'menu_category_id' => $category->id,
                'name' => 'برگر ' . $index,
                'slug' => 'burger-' . $index,
                'description' => 'تست',
                'price' => 200000 + ($index * 10000),
                'sort_order' => $index,
                'is_active' => true,
                'is_available' => true,
            ]);
        }

        $queries = 0;
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$queries): void {
            if (str_starts_with(strtolower(trim($query->sql)), 'select')) {
                $queries++;
            }
        });

        $this->get(route('menu.index'))->assertOk();

        $this->assertLessThanOrEqual(5, $queries);
    }

    public function test_public_menu_category_and_item_routes_are_dynamic(): void
    {
        $restaurant = $this->restaurant();

        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'دسر',
            'slug' => 'dessert',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $category->id,
            'name' => 'چیزکیک گیلاس',
            'slug' => 'gilas-cheesecake-test',
            'description' => 'یک آیتم تستی',
            'image_path' => 'menu/gilas-cheesecake-test.jpg',
            'price' => 180000,
            'sort_order' => 1,
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->get(route('menu.category', ['slug' => $category->slug]))
            ->assertOk()
            ->assertSee('چیزکیک گیلاس');

        $this->get(route('menu.item', ['slug' => $item->slug]))
            ->assertOk()
            ->assertSee('چیزکیک گیلاس')
            ->assertSee('menu/gilas-cheesecake-test.jpg', false);
    }

    private function restaurant(): Restaurant
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'gilas-public-menu-test',
            'description' => 'منوی تست گیلاس',
            'currency' => 'IRR',
            'status' => 'active',
        ]);

        $restaurant->settings()->create([
            'ordering_enabled' => true,
            'reservation_enabled' => true,
        ]);

        return $restaurant;
    }
}
