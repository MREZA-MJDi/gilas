<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $restaurant = $this->primaryRestaurant();
        $featuredItems = collect();

        if ($restaurant) {
            $featuredItems = MenuItem::query()
                ->where('restaurant_id', $restaurant->id)
                ->where('is_active', true)
                ->where('is_available', true)
                ->with('category:id,name,slug')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(8)
                ->get();
        }

        return view('welcome', [
            'restaurant' => $restaurant,
            'featuredItems' => $featuredItems,
        ]);
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
