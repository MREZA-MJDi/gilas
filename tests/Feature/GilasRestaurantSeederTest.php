<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\GilasRestaurantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class GilasRestaurantSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_restaurant_settings_and_categories_without_fake_operational_data(): void
    {
        Artisan::call('db:seed', ['--class' => GilasRestaurantSeeder::class]);

        $restaurant = Restaurant::where('slug', 'gilas')->firstOrFail();

        $this->assertSame('گیلاس', $restaurant->name);
        $this->assertSame(6, $restaurant->categories()->count());
        $this->assertTrue($restaurant->settings()->exists());

        $this->assertSame(0, $restaurant->items()->count());
        $this->assertSame(0, $restaurant->tables()->count());
        $this->assertSame(0, $restaurant->orders()->count());
        $this->assertSame(0, $restaurant->reservations()->count());
        $this->assertSame(0, MenuItem::count());
        $this->assertSame(0, Customer::count());
        $this->assertSame(0, Order::count());
        $this->assertSame(0, User::count());

        $this->assertDatabaseHas('menu_categories', [
            'restaurant_id' => $restaurant->id,
            'slug' => 'coffee',
            'name' => 'قهوه و نوشیدنی گرم',
        ]);

        $this->assertDatabaseHas('restaurant_settings', [
            'restaurant_id' => $restaurant->id,
            'ordering_enabled' => 1,
            'delivery_enabled' => 0,
            'reservation_enabled' => 0,
            'tax_percent' => 0,
        ]);
    }

    public function test_seeding_is_idempotent_and_preserves_dashboard_edits(): void
    {
        Artisan::call('db:seed', ['--class' => GilasRestaurantSeeder::class]);

        $restaurant = Restaurant::where('slug', 'gilas')->firstOrFail();
        $restaurant->update(['name' => 'نام واقعی ثبت‌شده در پنل']);
        $restaurant->categories()->where('slug', 'coffee')->update(['name' => 'قهوه تخصصی']);
        $restaurant->settings()->update(['ordering_enabled' => false]);

        Artisan::call('db:seed', ['--class' => GilasRestaurantSeeder::class]);

        $this->assertSame(1, Restaurant::where('slug', 'gilas')->count());
        $this->assertSame('نام واقعی ثبت‌شده در پنل', $restaurant->fresh()->name);
        $this->assertSame('قهوه تخصصی', $restaurant->categories()->where('slug', 'coffee')->value('name'));
        $this->assertFalse($restaurant->settings()->firstOrFail()->ordering_enabled);
        $this->assertSame(6, $restaurant->categories()->count());
        $this->assertSame(0, Order::count());
    }

    public function test_owner_is_created_only_when_credentials_are_explicitly_configured(): void
    {
        config([
            'gilas.owner.name' => 'مالک واقعی',
            'gilas.owner.email' => 'owner@gilas.local',
            'gilas.owner.password' => 'A-strong-local-password-934!',
        ]);

        Artisan::call('db:seed', ['--class' => GilasRestaurantSeeder::class]);

        $restaurant = Restaurant::where('slug', 'gilas')->firstOrFail();
        $owner = User::where('email', 'owner@gilas.local')->firstOrFail();

        $this->assertTrue(Hash::check('A-strong-local-password-934!', $owner->password));
        $this->assertDatabaseHas('restaurant_user', [
            'restaurant_id' => $restaurant->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'is_active' => 1,
        ]);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, Customer::count());
    }

    public function test_partial_owner_credentials_fail_before_creating_records(): void
    {
        config([
            'gilas.owner.name' => 'مالک',
            'gilas.owner.email' => 'owner@gilas.local',
            'gilas.owner.password' => '',
        ]);

        try {
            app(GilasRestaurantSeeder::class)->run();
            $this->fail('Expected incomplete owner credentials to be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('GILAS_OWNER_NAME', $exception->getMessage());
        }

        $this->assertSame(0, Restaurant::count());
        $this->assertSame(0, User::count());
    }
}
