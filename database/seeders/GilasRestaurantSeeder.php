<?php

namespace Database\Seeders;

use App\Enums\RestaurantUserRole;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GilasRestaurantSeeder extends Seeder
{
    /**
     * Catalog structure only: real menu items, prices and photos must come
     * from the restaurant rather than invented sample data.
     *
     * @var array<array{0: string, 1: string}>
     */
    private const CATEGORIES = [
        ['coffee', 'قهوه و نوشیدنی گرم'],
        ['cold-drinks', 'نوشیدنی سرد'],
        ['breakfast', 'صبحانه'],
        ['desserts', 'دسر'],
        ['food', 'غذا'],
        ['specials', 'پیشنهاد ویژه'],
    ];

    public function run(): void
    {
        $this->validateOptionalOwnerCredentials();

        DB::transaction(function (): void {
            $slug = trim((string) config('gilas.restaurant.slug', 'gilas')) ?: 'gilas';
            $name = trim((string) config('gilas.restaurant.name', 'گیلاس')) ?: 'گیلاس';

            // Idempotent: existing records edited in the dashboard are preserved.
            $restaurant = Restaurant::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $this->nullableValue(config('gilas.restaurant.description')),
                    'logo_path' => $this->nullableValue(config('gilas.restaurant.logo_path')),
                    'cover_image_path' => $this->nullableValue(config('gilas.restaurant.cover_image_path')),
                    'phone' => $this->nullableValue(config('gilas.restaurant.phone')),
                    'email' => $this->nullableValue(config('gilas.restaurant.email')),
                    'address' => $this->nullableValue(config('gilas.restaurant.address')),
                    'latitude' => $this->nullableValue(config('gilas.restaurant.latitude')),
                    'longitude' => $this->nullableValue(config('gilas.restaurant.longitude')),
                    'timezone' => config('gilas.restaurant.timezone', 'Asia/Tehran'),
                    'currency' => config('gilas.restaurant.currency', 'IRR'),
                    'status' => 'active',
                ],
            );

            // Delivery/reservations remain off until the actual operating details
            // have been configured by the restaurant owner.
            $restaurant->settings()->firstOrCreate([], [
                'ordering_enabled' => true,
                'dine_in_enabled' => true,
                'pickup_enabled' => false,
                'delivery_enabled' => false,
                'reservation_enabled' => false,
                'min_order_amount' => 0,
                'default_delivery_minutes' => 45,
                'tax_percent' => 0,
                'service_charge_percent' => 0,
            ]);

            foreach (self::CATEGORIES as $index => [$categorySlug, $categoryName]) {
                MenuCategory::firstOrCreate(
                    ['restaurant_id' => $restaurant->id, 'slug' => $categorySlug],
                    [
                        'name' => $categoryName,
                        'description' => null,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ],
                );
            }

            $this->seedConfiguredOwner($restaurant);
        });
    }

    private function validateOptionalOwnerCredentials(): void
    {
        $values = [
            config('gilas.owner.name'),
            config('gilas.owner.email'),
            config('gilas.owner.password'),
        ];

        $configured = array_map(
            static fn ($value): bool => is_string($value) && trim($value) !== '',
            $values,
        );

        if (in_array(true, $configured, true) && in_array(false, $configured, true)) {
            throw new RuntimeException(
                'Set GILAS_OWNER_NAME, GILAS_OWNER_EMAIL and GILAS_OWNER_PASSWORD together in the private .env file.',
            );
        }
    }

    private function seedConfiguredOwner(Restaurant $restaurant): void
    {
        $name = config('gilas.owner.name');
        $email = config('gilas.owner.email');
        $password = config('gilas.owner.password');

        if (! is_string($name) || trim($name) === ''
            || ! is_string($email) || trim($email) === ''
            || ! is_string($password) || trim($password) === '') {
            return;
        }

        // No known/default password is shipped. Existing user passwords are
        // never reset by a subsequent seed run.
        $owner = User::firstOrCreate(
            ['email' => trim($email)],
            ['name' => trim($name), 'password' => $password],
        );

        $restaurant->users()->syncWithoutDetaching([
            $owner->id => [
                'role' => RestaurantUserRole::Owner->value,
                'is_active' => true,
            ],
        ]);
    }

    private function nullableValue(mixed $value): mixed
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }

        return $value;
    }
}
