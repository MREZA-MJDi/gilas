<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\View\View;

class PublicMenuItemController extends Controller
{
    public function show(string $slug): View
    {
        $restaurant = $this->primaryRestaurantOrFail();

        $item = MenuItem::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('is_available', true)
            ->with([
                'category:id,restaurant_id,name,slug',
                'variants' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
                'options' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with([
                        'values' => fn ($valueQuery) => $valueQuery
                            ->where('is_active', true)
                            ->orderBy('sort_order'),
                    ]),
            ])
            ->firstOrFail();

        return view('customer.menu-item', compact('restaurant', 'item'));
    }

    private function primaryRestaurantOrFail(): Restaurant
    {
        $query = Restaurant::query()
            ->where('status', 'active')
            ->with('settings');

        $configuredSlug = config('gilas.primary_restaurant_slug');

        if (is_string($configuredSlug) && trim($configuredSlug) !== '') {
            $restaurant = (clone $query)->where('slug', trim($configuredSlug))->first();
            if ($restaurant) {
                return $restaurant;
            }
        }

        return $query->orderBy('id')->firstOrFail();
    }
}
