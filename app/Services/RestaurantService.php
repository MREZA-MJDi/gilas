<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class RestaurantService
{
    public function create(array $data): Restaurant
    {
        return DB::transaction(function () use ($data) {
            $restaurant = Restaurant::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'timezone' => $data['timezone'] ?? 'Asia/Tehran',
                'currency' => $data['currency'] ?? 'IRR',
                'status' => $data['status'] ?? 'active',
            ]);

            $restaurant->settings()->create([
                'ordering_enabled' => $data['ordering_enabled'] ?? true,
                'dine_in_enabled' => $data['dine_in_enabled'] ?? true,
                'pickup_enabled' => $data['pickup_enabled'] ?? true,
                'delivery_enabled' => $data['delivery_enabled'] ?? true,
                'reservation_enabled' => $data['reservation_enabled'] ?? true,
            ]);

            return $restaurant->load('settings');
        });
    }

    public function provisionTables(Restaurant $restaurant, int $count, int $capacity = 2): void
    {
        if ($count < 1) {
            return;
        }

        $existing = $restaurant->tables()->pluck('number')->all();
        $next = empty($existing) ? 1 : max($existing) + 1;

        $rows = [];

        for ($number = $next; $number < $next + $count; $number++) {
            $rows[] = [
                'restaurant_id' => $restaurant->id,
                'name' => "میز {$number}",
                'number' => $number,
                'capacity' => $capacity,
                'status' => 'available',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('restaurant_tables')->insert($rows);
    }
}
