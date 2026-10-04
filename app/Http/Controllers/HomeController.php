<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\Restaurant;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $restaurant = $this->primaryRestaurant();

        $categories = $restaurant
            ? MenuCategory::query()
                ->where('restaurant_id', $restaurant->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'name', 'slug', 'description', 'sort_order'])
            : collect();

        $iconMap = [
            'coffee' => '☕',
            'breakfast' => '🥐',
            'dessert' => '🍰',
            'cold' => '🥤',
            'special' => '🍒',
            'burgers' => '🍔',
            'sides' => '🍟',
            'pasta' => '🍝',
        ];

        $categoryItems = $categories->map(fn (MenuCategory $category): array => [
            'type' => 'category',
            'title' => $category->name,
            'text' => $category->description ?: 'این دسته را ببین و انتخابت را شروع کن.',
            'cta' => 'دیدن ' . $category->name,
            'url' => route('menu.category', $category->slug),
            'icon' => $iconMap[$category->slug] ?? '🍽️',
            'slug' => $category->slug,
        ])->values();

        $guideItems = collect([
            ['type' => 'guide', 'title' => 'راهنمای گیلاس', 'text' => 'با یک حرکت کوتاه، خود Landing را یاد بگیر.', 'cta' => 'شروع راهنما', 'icon' => '✦'],
            ['type' => 'guide', 'title' => 'انتخاب دسته', 'text' => 'یکی از دسته‌های غذا را انتخاب کن.', 'cta' => 'نمایش مرحله', 'icon' => '⌁'],
            ['type' => 'guide', 'title' => 'دیدن منو', 'text' => 'جزئیات هر دسته را در منوی گیلاس ببین.', 'cta' => 'نمایش مرحله', 'icon' => '◌'],
            ['type' => 'guide', 'title' => 'سفارش', 'text' => 'از منو انتخاب کن و سفارش را ادامه بده.', 'cta' => 'نمایش مرحله', 'icon' => '↗'],
            ['type' => 'guide', 'title' => 'رزرو', 'text' => 'برای میزت از مسیر رزرو استفاده کن.', 'cta' => 'رزرو میز', 'icon' => '◷'],
            ['type' => 'guide', 'title' => 'مسیریابی', 'text' => 'مسیر رسیدن به گیلاس را از ناوبری باز کن.', 'cta' => 'پیدا کردن گیلاس', 'icon' => '⌖'],
            ['type' => 'guide', 'title' => 'از اول', 'text' => 'راهنما تمام شد؛ حالا آزادانه انتخاب کن.', 'cta' => 'شروع دوباره', 'icon' => '↺'],
        ]);

        $honeycombItems = $categoryItems->concat($guideItems)->values();

        return view('welcome', compact('restaurant', 'honeycombItems'));
    }

    private function primaryRestaurant(): ?Restaurant
    {
        $query = Restaurant::query()
            ->where('status', 'active')
            ->with('settings');

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
