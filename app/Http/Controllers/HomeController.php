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
                ->get(['id', 'name', 'slug', 'description'])
            : collect();

        $icons = [
            'coffee' => 'coffee', 'breakfast' => 'sun', 'dessert' => 'cake',
            'cold' => 'glass', 'special' => 'spark', 'burgers' => 'burger',
            'sides' => 'fries', 'pasta' => 'pasta',
        ];

        $honeycombItems = $categories->map(function (MenuCategory $category) use ($icons): array {
            $slug = strtolower($category->slug);

            return [
                'title' => $category->name,
                'text' => $category->description ?: 'انتخابی از منوی خانه گیلاسی.',
                'cta' => 'دیدن ' . $category->name,
                'url' => route('menu.category', ['slug' => $category->slug]),
                'icon' => $icons[$slug] ?? 'plate',
            ];
        })->values();

        $primaryLinks = [
            ['label' => 'منو', 'url' => route('menu.index')],
            ['label' => 'رزرو', 'url' => route('public.reservation')],
            ['label' => 'باشگاه گیلاس', 'url' => route('public.club')],
            ['label' => 'مسیریابی', 'url' => route('public.location')],
        ];

        return view('welcome', compact('restaurant', 'honeycombItems', 'primaryLinks'));
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
