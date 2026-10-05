<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $restaurant = $this->primaryRestaurant();

        $featuredItems = $restaurant
            ? $restaurant->items()
                ->where('is_active', true)
                ->where('is_available', true)
                ->with(['category:id,name,slug'])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(6)
                ->get(['id','restaurant_id','menu_category_id','name','slug','description','image_path','price'])
            : collect();

        return view('welcome', compact('restaurant', 'featuredItems'));
    }

    private function primaryRestaurant(): ?Restaurant
    {
        $query = Restaurant::query()->where('status', 'active')->with('settings');
        $configuredSlug = config('gilas.primary_restaurant_slug');

        if (is_string($configuredSlug) && trim($configuredSlug) !== '') {
            $configured = (clone $query)->where('slug', trim($configuredSlug))->first();
            if ($configured) return $configured;
        }

        return $query->orderBy('id')->first();
    }
}
