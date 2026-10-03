<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GilasDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_provisions_real_editable_database_records(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\GilasDemoSeeder']);

        $restaurant = Restaurant::where('slug', 'gilas')->firstOrFail();

        $this->assertSame('گیلاس', $restaurant->name);
        $this->assertGreaterThanOrEqual(5, $restaurant->categories()->count());
        $this->assertGreaterThanOrEqual(10, $restaurant->items()->count());
        $this->assertGreaterThanOrEqual(6, $restaurant->tables()->count());
        $this->assertGreaterThanOrEqual(7, $restaurant->orders()->count());

        $this->assertDatabaseHas('orders', [
            'order_number' => 'GILAS-DEMO-1001',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => 'GILAS-DEMO-1004',
            'status' => 'ready',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => 'GILAS-DEMO-1006',
            'order_type' => 'delivery',
            'status' => 'out_for_delivery',
        ]);

        $this->assertGreaterThan(0, $restaurant->reservations()->count());
        $this->assertGreaterThan(0, $restaurant->activeUsers()->count());
        $this->assertGreaterThan(0, $restaurant->tables()->with('qrCode')->get()->filter(fn ($table) => $table->qrCode)->count());

        $this->assertTrue(MenuItem::where('slug', 'gilas-latte')->exists());
        $this->assertTrue(Order::where('order_number', 'GILAS-DEMO-1003')->whereHas('payment')->exists());
    }

    public function test_running_demo_seed_twice_does_not_duplicate_fixed_demo_records(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\GilasDemoSeeder']);
        $firstCounts = [
            'restaurants' => Restaurant::where('slug', 'gilas')->count(),
            'orders' => Order::where('order_number', 'like', 'GILAS-DEMO-%')->count(),
        ];

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\GilasDemoSeeder']);
        $secondCounts = [
            'restaurants' => Restaurant::where('slug', 'gilas')->count(),
            'orders' => Order::where('order_number', 'like', 'GILAS-DEMO-%')->count(),
        ];

        $this->assertSame($firstCounts, $secondCounts);
    }
}
