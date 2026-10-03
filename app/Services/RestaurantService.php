<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestaurantService
{
    public function __construct(
        private readonly TableQrCodeService $qrCodes,
    ) {
    }

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

        if ($count > 500) {
            throw ValidationException::withMessages([
                'count' => 'A maximum of 500 tables may be provisioned at once.',
            ]);
        }

        if ($capacity < 1 || $capacity > 255) {
            throw ValidationException::withMessages([
                'capacity' => 'Table capacity must be between 1 and 255.',
            ]);
        }

        DB::transaction(function () use ($restaurant, $count, $capacity) {
            $lockedRestaurant = Restaurant::query()
                ->lockForUpdate()
                ->findOrFail($restaurant->id);

            $next = ((int) $lockedRestaurant->tables()->max('number')) + 1;

            for ($number = $next; $number < $next + $count; $number++) {
                $table = RestaurantTable::create([
                    'restaurant_id' => $lockedRestaurant->id,
                    'name' => "میز {$number}",
                    'number' => $number,
                    'capacity' => $capacity,
                    'status' => 'available',
                    'is_active' => true,
                ]);

                $this->qrCodes->issue($table);
            }
        });
    }
}
