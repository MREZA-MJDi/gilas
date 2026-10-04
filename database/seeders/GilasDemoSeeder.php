<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Enums\RestaurantUserRole;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Delivery;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionValue;
use App\Models\MenuItemVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantHour;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Services\TableQrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GilasDemoSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::firstOrCreate(
            ['slug' => 'gilas'],
            [
                'name' => 'گیلاس',
                'description' => 'یک کافه برای قهوه، صبحانه، دسر و چند ساعت خوش.',
                'phone' => '02100000000',
                'email' => 'hello@gilas.test',
                'address' => 'تهران، خیابان نمونه، پلاک ۱۲',
                'latitude' => 35.7219,
                'longitude' => 51.3347,
                'timezone' => 'Asia/Tehran',
                'currency' => 'IRR',
                'status' => 'active',
            ],
        );

        $restaurant->settings()->firstOrCreate([], [
            'ordering_enabled' => true,
            'dine_in_enabled' => true,
            'pickup_enabled' => true,
            'delivery_enabled' => true,
            'reservation_enabled' => true,
            'min_order_amount' => 0,
            'default_delivery_minutes' => 45,
            'tax_percent' => 9,
            'service_charge_percent' => 0,
        ]);

        $this->seedHours($restaurant);

        $categories = collect([
            ['coffee', 'قهوه', 'اسپرسو، لاته و قهوه‌های امضای گیلاس.'],
            ['breakfast', 'صبحانه', 'صبحانه‌هایی برای شروع آرام روز.'],
            ['dessert', 'دسر', 'چیزهای شیرین برای آخرِ یک تجربه خوب.'],
            ['cold', 'نوشیدنی سرد', 'نوشیدنی‌های خنک و تازه.'],
            ['special', 'پیشنهاد گیلاس', 'انتخاب‌های خاص و محبوب خانه.'],
        ])->mapWithKeys(function (array $data, int $index) use ($restaurant): array {
            $category = MenuCategory::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'slug' => $data[0]],
                [
                    'name' => $data[1],
                    'description' => $data[2],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );

            return [$data[0] => $category];
        });

        $items = [];
        $items[] = $this->seedItem($categories['coffee'], 'آمریکانو', 'americano', 'اسپرسوی دوبل با آب داغ.', 90000, 1);
        $items[] = $this->seedItem($categories['coffee'], 'لاته گیلاس', 'gilas-latte', 'لاته نرم با امضای گیلاس.', 150000, 2);
        $items[] = $this->seedItem($categories['coffee'], 'کاپوچینو', 'cappuccino', 'فوم شیر سبک روی اسپرسو.', 145000, 3);
        $items[] = $this->seedItem($categories['breakfast'], 'تخم‌مرغ و تست', 'egg-toast', 'تخم‌مرغ، نان تست، پنیر و سبزی تازه.', 220000, 1);
        $items[] = $this->seedItem($categories['breakfast'], 'کروسان پنیر', 'cheese-croissant', 'کروسان کره‌ای با پنیر.', 180000, 2);
        $items[] = $this->seedItem($categories['dessert'], 'چیزکیک گیلاس', 'gilas-cheesecake', 'چیزکیک خامه‌ای با سس گیلاس.', 210000, 1);
        $items[] = $this->seedItem($categories['dessert'], 'براونی گرم', 'warm-brownie', 'براونی شکلاتی با بافت نرم.', 195000, 2);
        $items[] = $this->seedItem($categories['cold'], 'آیس‌لاته', 'iced-latte', 'لاته سرد با یخ.', 165000, 1);
        $items[] = $this->seedItem($categories['cold'], 'لیموناد گیلاس', 'gilas-lemonade', 'لیموناد تازه با حال‌وهوای گیلاس.', 140000, 2);
        $items[] = $this->seedItem($categories['special'], 'صبحانه دو نفره گیلاس', 'gilas-duo-breakfast', 'سینی کامل برای دو نفر.', 490000, 1);
        $burgers = MenuCategory::firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'slug' => 'burgers'],
            ['name' => 'برگرها', 'description' => 'برگرهای داغ و سیرکننده برای یک انتخاب جدی.', 'sort_order' => 6, 'is_active' => true],
        );
        $sides = MenuCategory::firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'slug' => 'sides'],
            ['name' => 'سرخ‌کردنی‌ها', 'description' => 'کنارغذاهای ترد و خوش‌نمک.', 'sort_order' => 7, 'is_active' => true],
        );
        $pasta = MenuCategory::firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'slug' => 'pasta'],
            ['name' => 'پاستا', 'description' => 'پاستاهای گرم و تازه برای یک وعده کامل.', 'sort_order' => 8, 'is_active' => true],
        );
        $items[] = $this->seedItem($burgers, 'برگر گوساله', 'beef-burger', 'برگر گوساله با پنیر چدار، کاهو و سس مخصوص.', 280000, 1);
        $items[] = $this->seedItem($burgers, 'چیزبرگر گیلاس', 'gilas-cheeseburger', 'گوشت گوساله، پنیر، پیاز کاراملی و سس گیلاس.', 315000, 2);
        $items[] = $this->seedItem($sides, 'سیب‌زمینی مخصوص', 'gilas-fries', 'سیب‌زمینی ترد با ادویه مخصوص گیلاس.', 95000, 1);
        $items[] = $this->seedItem($sides, 'سیب‌زمینی پنیر', 'cheese-fries', 'سیب‌زمینی ترد با پنیر آب‌شده.', 135000, 2);
        $items[] = $this->seedItem($pasta, 'پاستا آلفردو', 'alfredo-pasta', 'پاستای خامه‌ای با قارچ و پنیر پارمزان.', 265000, 1);
        $items[] = $this->seedItem($pasta, 'پاستا گوجه و ریحان', 'tomato-basil-pasta', 'پاستای تازه با گوجه، ریحان و پارمزان.', 240000, 2);

        $latte = $items[1];
        $latte->variants()->firstOrCreate(['name' => 'متوسط'], [
            'price' => 150000,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $latte->variants()->firstOrCreate(['name' => 'بزرگ'], [
            'price' => 180000,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $milk = $latte->options()->firstOrCreate(['name' => 'انتخاب شیر'], [
            'min_select' => 0,
            'max_select' => 1,
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $milk->values()->firstOrCreate(['name' => 'شیر بادام'], [
            'price_delta' => 30000,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $milk->values()->firstOrCreate(['name' => 'شیر معمولی'], [
            'price_delta' => 0,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $tables = [];
        foreach ([
            [1, 2, 'سالن', 'پنجره'],
            [2, 2, 'سالن', 'پنجره'],
            [3, 4, 'سالن', 'مرکز'],
            [4, 4, 'سالن', 'مرکز'],
            [5, 6, 'حیاط', 'باغچه'],
            [6, 6, 'حیاط', 'باغچه'],
        ] as [$number, $capacity, $floor, $zone]) {
            $table = RestaurantTable::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'number' => $number],
                [
                    'name' => 'میز ' . $number,
                    'capacity' => $capacity,
                    'floor' => $floor,
                    'zone' => $zone,
                    'status' => 'available',
                    'is_active' => true,
                ],
            );
            $tables[$number] = $table;

            if (! $table->qrCode()->where('is_active', true)->exists()) {
                app(TableQrCodeService::class)->issue($table);
            }
        }

        $owner = User::firstOrCreate(
            ['email' => 'owner@gilas.test'],
            ['name' => 'مدیر گیلاس', 'password' => 'password'],
        );
        $this->attachRestaurantUser($restaurant, $owner, RestaurantUserRole::Owner->value);

        $kitchen = User::firstOrCreate(
            ['email' => 'kitchen@gilas.test'],
            ['name' => 'آشپزخانه گیلاس', 'password' => 'password'],
        );
        $this->attachRestaurantUser($restaurant, $kitchen, RestaurantUserRole::Kitchen->value);

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@gilas.test'],
            ['name' => 'صندوقدار گیلاس', 'password' => 'password'],
        );
        $this->attachRestaurantUser($restaurant, $cashier, RestaurantUserRole::Cashier->value);

        $customers = [
            $this->customer('09120000001', 'سارا احمدی'),
            $this->customer('09120000002', 'علی رضایی'),
            $this->customer('09120000003', 'مریم کریمی'),
            $this->customer('09120000004', 'امیر نادری'),
            $this->customer('09120000005', 'نیلوفر مرادی'),
        ];

        $address = CustomerAddress::firstOrCreate(
            ['customer_id' => $customers[4]->id, 'title' => 'خانه'],
            [
                'recipient_name' => $customers[4]->name,
                'phone' => $customers[4]->phone,
                'address' => 'تهران، خیابان نمونه، کوچه سوم، پلاک ۸',
                'postal_code' => '1234567890',
                'is_default' => true,
            ],
        );

        $orderSpecs = [
            ['GILAS-DEMO-1001', $customers[0], OrderType::DineIn, OrderStatus::Pending, $tables[1], null, 0, 150000, 'مشتری منتظر تأیید آشپزخانه است.'],
            ['GILAS-DEMO-1002', $customers[1], OrderType::DineIn, OrderStatus::Confirmed, $tables[3], null, 0, 330000, 'لطفاً لاته را کم‌شیرین آماده کنید.'],
            ['GILAS-DEMO-1003', $customers[2], OrderType::DineIn, OrderStatus::Preparing, $tables[4], null, 0, 440000, 'بدون قند برای نوشیدنی.'],
            ['GILAS-DEMO-1004', $customers[3], OrderType::DineIn, OrderStatus::Ready, $tables[5], null, 0, 490000, 'سفارش آماده تحویل است.'],
            ['GILAS-DEMO-1005', $customers[0], OrderType::DineIn, OrderStatus::Completed, $tables[2], null, 0, 360000, 'سفارش تست completed.'],
            ['GILAS-DEMO-1006', $customers[4], OrderType::Delivery, OrderStatus::OutForDelivery, null, $address, 60000, 390000, 'جلوی درب تحویل شود.'],
            ['GILAS-DEMO-1007', $customers[2], OrderType::Pickup, OrderStatus::Cancelled, null, null, 0, 210000, 'لغو توسط مشتری برای تست state.'],
        ];

        foreach ($orderSpecs as [$number, $customer, $type, $status, $table, $customerAddress, $deliveryFee, $total, $note]) {
            $order = Order::firstOrCreate(
                ['order_number' => $number],
                [
                    'restaurant_id' => $restaurant->id,
                    'customer_id' => $customer->id,
                    'restaurant_table_id' => $table?->id,
                    'customer_address_id' => $customerAddress?->id,
                    'idempotency_key' => 'demo-' . strtolower(str_replace('-', '-', $number)),
                    'idempotency_hash' => hash('sha256', $number),
                    'public_token' => Str::lower(Str::random(48)),
                    'order_type' => $type,
                    'status' => $status,
                    'subtotal' => $total - $deliveryFee,
                    'discount' => 0,
                    'tax' => 0,
                    'delivery_fee' => $deliveryFee,
                    'service_charge' => 0,
                    'total' => $total,
                    'customer_note' => $note,
                    'confirmed_at' => in_array($status, [OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Ready, OrderStatus::Served, OrderStatus::OutForDelivery, OrderStatus::Delivered, OrderStatus::Completed], true) ? now()->subMinutes(20) : null,
                    'completed_at' => $status === OrderStatus::Completed ? now()->subMinutes(8) : null,
                    'cancelled_at' => $status === OrderStatus::Cancelled ? now()->subMinutes(12) : null,
                ],
            );

            if (! $order->items()->exists()) {
                $seedItem = $items[$order->id % count($items)] ?? $latte;
                $orderItem = $order->items()->create([
                    'menu_item_id' => $seedItem->id,
                    'menu_item_variant_id' => $seedItem->variants()->where('is_active', true)->first()?->id,
                    'name' => $seedItem->name,
                    'variant_name' => $seedItem->variants()->where('is_active', true)->first()?->name,
                    'unit_price' => $total - $deliveryFee,
                    'quantity' => 1,
                    'total_price' => $total - $deliveryFee,
                    'note' => null,
                ]);

                if ($seedItem->id === $latte->id) {
                    $optionValue = $milk->values()->where('is_active', true)->first();
                    if ($optionValue) {
                        OrderItemOption::create([
                            'order_item_id' => $orderItem->id,
                            'option_name' => $milk->name,
                            'value_name' => $optionValue->name,
                            'price_delta' => (int) $optionValue->price_delta,
                        ]);
                    }
                }
            }

            if (! $order->statusHistory()->exists()) {
                $from = null;
                $path = $this->statusPath($status, $type);
                foreach ($path as $to) {
                    $order->statusHistory()->create([
                        'from_status' => $from,
                        'to_status' => $to,
                        'changed_by' => $owner->id,
                        'note' => null,
                        'created_at' => now()->subMinutes(max(1, 40 - count($path) * 3)),
                        'updated_at' => now()->subMinutes(max(1, 40 - count($path) * 3)),
                    ]);
                    $from = $to;
                }
            }

            $paymentStatus = match ($status) {
                OrderStatus::Cancelled => PaymentStatus::Cancelled,
                OrderStatus::Completed, OrderStatus::OutForDelivery, OrderStatus::Delivered, OrderStatus::Ready => PaymentStatus::Paid,
                default => PaymentStatus::Pending,
            };

            $payment = $order->payment()->firstOrCreate([], [
                 'method' => $type === OrderType::Delivery ? PaymentMethod::Online : PaymentMethod::Cashier,
                'status' => $paymentStatus,
                'amount' => $total,
                'paid_at' => $paymentStatus === PaymentStatus::Paid ? now()->subMinutes(7) : null,
            ]);

            if ($paymentStatus === PaymentStatus::Paid && ! $payment->transactions()->exists()) {
                $payment->transactions()->create([
                    'provider' => $payment->method === PaymentMethod::Online ? 'demo_gateway' : 'cashier',
                    'transaction_id' => 'DEMO-' . $order->id,
                    'reference' => 'REF-' . str_pad((string) $order->id, 8, '0', STR_PAD_LEFT),
                    'amount' => $total,
                    'status' => 'paid',
                    'response' => ['mode' => 'demo', 'seeded' => true],
                ]);
            }

            if ($type === OrderType::Delivery) {
                $delivery = $order->delivery()->firstOrCreate([], [
                    'courier_id' => null,
                    'status' => $status === OrderStatus::OutForDelivery ? DeliveryStatus::Dispatched : DeliveryStatus::Pending,
                    'delivery_address_snapshot' => $customerAddress?->address,
                    'customer_note' => $note,
                    'dispatched_at' => $status === OrderStatus::OutForDelivery ? now()->subMinutes(5) : null,
                ]);
            }
        }

        $this->reservation($restaurant, $customers[3], $tables[6], ReservationStatus::Confirmed, 4);
        $this->reservation($restaurant, $customers[1], $tables[4], ReservationStatus::Pending, 2);
    }

    private function seedHours(Restaurant $restaurant): void
    {
        foreach (range(0, 6) as $day) {
            RestaurantHour::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'day_of_week' => $day],
                ['open_time' => '08:00:00', 'close_time' => '23:30:00', 'is_closed' => false],
            );
        }
    }

    private function seedItem(MenuCategory $category, string $name, string $slug, string $description, int $price, int $sortOrder): MenuItem
    {
        $images = [
            'americano' => 'https://images.unsplash.com/photo-1578928158469-d07d1fe0610f?auto=format&fit=crop&w=1200&q=85',
            'gilas-latte' => 'https://images.unsplash.com/photo-1578928158469-d07d1fe0610f?auto=format&fit=crop&w=1200&q=85',
            'cappuccino' => 'https://images.unsplash.com/photo-1578928158469-d07d1fe0610f?auto=format&fit=crop&w=1200&q=85',
            'egg-toast' => 'https://images.unsplash.com/photo-1559332167-dd24746aa6f5?auto=format&fit=crop&w=1200&q=85',
            'cheese-croissant' => 'https://images.unsplash.com/photo-1757124034342-3bbd5364a41d?auto=format&fit=crop&w=1200&q=85',
            'gilas-cheesecake' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=1200&q=85',
            'warm-brownie' => 'https://images.unsplash.com/photo-1777647364657-2319b02095de?auto=format&fit=crop&w=1200&q=85',
            'iced-latte' => 'https://images.unsplash.com/photo-1578928158469-d07d1fe0610f?auto=format&fit=crop&w=1200&q=85',
            'gilas-lemonade' => 'https://images.unsplash.com/photo-1714968382548-9234f0c4e342?auto=format&fit=crop&w=1200&q=85',
            'gilas-duo-breakfast' => 'https://images.unsplash.com/photo-1559332167-dd24746aa6f5?auto=format&fit=crop&w=1200&q=85',
            'beef-burger' => 'https://images.unsplash.com/photo-1610440042657-612c34d95e9f?auto=format&fit=crop&w=1200&q=85',
            'gilas-cheeseburger' => 'https://images.unsplash.com/photo-1610440042657-612c34d95e9f?auto=format&fit=crop&w=1200&q=85',
            'gilas-fries' => 'https://images.unsplash.com/photo-1615485290836-4ebcebf44aaf?auto=format&fit=crop&w=1200&q=85',
            'cheese-fries' => 'https://images.unsplash.com/photo-1615485290836-4ebcebf44aaf?auto=format&fit=crop&w=1200&q=85',
            'alfredo-pasta' => 'https://images.unsplash.com/photo-1579349443343-73da56a71a20?auto=format&fit=crop&w=1200&q=85',
            'tomato-basil-pasta' => 'https://images.unsplash.com/photo-1579349443343-73da56a71a20?auto=format&fit=crop&w=1200&q=85',
        ];

        $item = MenuItem::firstOrCreate(
            ['restaurant_id' => $category->restaurant_id, 'slug' => $slug],
            [
                'menu_category_id' => $category->id,
                'name' => $name,
                'description' => $description,
                'image_path' => $images[$slug] ?? null,
                'price' => $price,
                'sort_order' => $sortOrder,
                'is_active' => true,
                'is_available' => true,
            ],
        );

        if (blank($item->image_path) && isset($images[$slug])) {
            $item->update(['image_path' => $images[$slug]]);
        }

        return $item;
    }

    private function customer(string $phone, string $name): Customer
    {
        return Customer::firstOrCreate(['phone' => $phone], [
            'name' => $name,
            'status' => 'active',
        ]);
    }

    private function attachRestaurantUser(Restaurant $restaurant, User $user, string $role): void
    {
        $exists = $restaurant->users()
            ->where('users.id', $user->id)
            ->exists();

        if (! $exists) {
            $restaurant->users()->attach($user->id, [
                'role' => $role,
                'is_active' => true,
            ]);
        }
    }

    private function reservation(Restaurant $restaurant, Customer $customer, RestaurantTable $table, ReservationStatus $status, int $guests): void
    {
        Reservation::firstOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'customer_id' => $customer->id,
                'restaurant_table_id' => $table->id,
                'reservation_date' => today()->addDay(),
                'start_time' => $guests === 4 ? '19:30:00' : '20:15:00',
            ],
            [
                'end_time' => $guests === 4 ? '21:00:00' : '21:15:00',
                'guest_count' => $guests,
                'status' => $status,
                'customer_note' => $status === ReservationStatus::Pending ? 'برای تست حالت pending.' : 'میز کنار پنجره ترجیح داده می‌شود.',
            ],
        );
    }

    private function statusPath(OrderStatus $status, OrderType $type): array
    {
        $all = match ($type) {
            OrderType::DineIn => [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Preparing,
                OrderStatus::Ready,
                OrderStatus::Served,
                OrderStatus::Completed,
            ],
            OrderType::Pickup => [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Preparing,
                OrderStatus::Ready,
                OrderStatus::Completed,
            ],
            OrderType::Delivery => [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Preparing,
                OrderStatus::Ready,
                OrderStatus::OutForDelivery,
                OrderStatus::Delivered,
                OrderStatus::Completed,
            ],
        };

        if ($status === OrderStatus::Cancelled) {
            return [OrderStatus::Pending, OrderStatus::Cancelled];
        }

        $targetIndex = array_search($status, $all, true);

        return $targetIndex === false ? [$status] : array_slice($all, 0, $targetIndex + 1);
    }
}
