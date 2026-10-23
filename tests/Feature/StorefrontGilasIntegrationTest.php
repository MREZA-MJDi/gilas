<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontGilasIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function restaurantWithMenu(): array
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'gilas',
            'description' => 'منوی واقعی رستوران',
            'status' => 'active',
            'currency' => 'IRR',
        ]);

        $restaurant->settings()->create([
            'ordering_enabled' => true,
            'dine_in_enabled' => true,
            'reservation_enabled' => true,
        ]);

        $category = $restaurant->categories()->create([
            'name' => 'قهوه',
            'slug' => 'coffee',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $latte = $category->items()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'لاته واقعی',
            'slug' => 'real-latte',
            'description' => 'لاته از دیتابیس',
            'price' => 125000,
            'is_active' => true,
            'is_available' => true,
        ]);

        $category->items()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'آمریکانو واقعی',
            'slug' => 'real-americano',
            'description' => 'قهوه از دیتابیس',
            'price' => 95000,
            'is_active' => true,
            'is_available' => true,
            'sort_order' => 2,
        ]);

        return [$restaurant, $category, $latte];
    }

    public function test_home_uses_real_restaurant_and_menu_records(): void
    {
        [$restaurant] = $this->restaurantWithMenu();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($restaurant->name)
            ->assertSee('لاته واقعی')
            ->assertSee(route('menu.index'), false)
            ->assertDontSee('VISION');
    }

    public function test_storefront_menu_alias_and_search_use_gilas_menu_items(): void
    {
        [, , $latte] = $this->restaurantWithMenu();

        $this->get(route('shop.index', ['q' => 'لاته']))
            ->assertOk()
            ->assertSee($latte->name)
            ->assertDontSee('آمریکانو واقعی')
            ->assertDontSee('VISION');

        $this->get(route('products.show', ['slug' => $latte->slug]))
            ->assertOk()
            ->assertSee($latte->name)
            ->assertSee('قیمت پایه')
            ->assertDontSee('VISION');
    }

    public function test_cart_shortcut_requires_a_valid_table_qr_and_reopens_the_existing_cart(): void
    {
        [$restaurant] = $this->restaurantWithMenu();

        $this->get(route('cart.index'))
            ->assertRedirect(route('menu.index'))
            ->assertSessionHas('status');

        $table = $restaurant->tables()->create([
            'name' => 'میز ۱',
            'number' => 1,
            'capacity' => 2,
            'status' => 'available',
            'is_active' => true,
        ]);

        $token = str_repeat('a', 48);
        $table->qrCode()->create([
            'token' => $token,
            'is_active' => true,
        ]);

        $this->withCookie('gilas_table_token', $token)
            ->get(route('cart.index'))
            ->assertRedirect(route('table.menu', ['token' => $token]) . '?openCart=1');
    }
}
