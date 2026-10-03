<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Services\MenuCatalogService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly MenuCatalogService $catalog,
    ) {
    }

    public function __invoke(): View
    {
        $restaurant = $this->primaryRestaurant();

        if (! $restaurant) {
            return view('welcome', [
                'restaurant' => null,
                'menu' => collect(),
                'featuredItems' => collect(),
                'honeycombItems' => [],
            ]);
        }

        $menu = $this->catalog->forRestaurant($restaurant);
        $categories = $menu->values();

        $featuredItems = $categories
            ->flatMap(fn ($category) => $category->items->map(
                fn ($item) => ['item' => $item, 'category' => $category]
            ))
            ->take(6)
            ->values();

        $honeycombItems = collect([
            [
                'title' => 'منوی خانه',
                'text' => 'تمام طعم‌های امروز، با قیمت و موجودی واقعی.',
                'cta' => 'ورود به منو',
                'url' => route('menu.index'),
                'icon' => '🍒',
            ],
            [
                'title' => 'تجربه گیلاس',
                'text' => 'فضا، موسیقی و جزئیاتی که خانه گیلاسی را می‌سازند.',
                'cta' => 'کشف تجربه',
                'url' => route('public.experience'),
                'icon' => '✨',
            ],
        ])->merge(
            $categories->take(5)->map(fn ($category, $index) => [
                'title' => $category->name,
                'text' => $category->description ?: 'از این دسته، انتخابت را از منوی واقعی خانه گیلاسی شروع کن.',
                'cta' => 'دیدن ' . $category->name,
                'url' => route('menu.category', ['slug' => $category->slug]),
                'icon' => ['☕', '🍰', '🍳', '🥐', '🧃'][$index % 5],
            ])
        )->merge([
            [
                'title' => 'رزرو میز',
                'text' => $restaurant->settings?->reservation_enabled
                    ? 'میز بعدی‌ات را از همین مسیر برنامه‌ریزی کن.'
                    : 'برای هماهنگی میز با ما تماس بگیر.',
                'cta' => 'رزرو / هماهنگی',
                'url' => route('public.reservation'),
                'icon' => '🪑',
            ],
            [
                'title' => 'مسیریابی',
                'text' => $restaurant->address ?: 'آدرس خانه گیلاسی در اینجا نمایش داده می‌شود.',
                'cta' => 'پیدا کردن ما',
                'url' => route('public.location'),
                'icon' => '📍',
            ],
            [
                'title' => 'باشگاه گیلاس',
                'text' => 'برای خبرها، طعم‌های تازه و اتفاق‌های خانه همراه ما بمان.',
                'cta' => 'باشگاه گیلاس',
                'url' => route('public.club'),
                'icon' => '❤️',
            ],
        ])->values()->all();

        return view('welcome', compact(
            'restaurant',
            'menu',
            'featuredItems',
            'honeycombItems',
        ));
    }

    private function primaryRestaurant(): ?Restaurant
    {
        $query = Restaurant::query()
            ->where('status', 'active')
            ->with(['settings', 'hours']);

        $configuredSlug = config('gilas.primary_restaurant_slug');

        if (is_string($configuredSlug) && trim($configuredSlug) !== '') {
            $configured = (clone $query)->where('slug', trim($configuredSlug))->first();

            if ($configured) {
                return $configured;
            }
        }

        return $query->orderBy('id')->first();
    }
}
