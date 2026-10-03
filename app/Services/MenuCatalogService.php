<?php

namespace App\Services;

use App\Models\MenuCategory;
use App\Models\Restaurant;
use Illuminate\Support\Collection;

class MenuCatalogService
{
    public function forRestaurant(Restaurant $restaurant): Collection
    {
        return MenuCategory::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with([
                'items' => fn ($query) => $query
                    ->where('is_active', true)
                    ->where('is_available', true)
                    ->orderBy('sort_order')
                    ->with([
                        'variants' => fn ($variantQuery) => $variantQuery
                            ->where('is_active', true)
                            ->orderBy('sort_order'),
                        'options' => fn ($optionQuery) => $optionQuery
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->with([
                                'values' => fn ($valueQuery) => $valueQuery
                                    ->where('is_active', true)
                                    ->orderBy('sort_order'),
                            ]),
                    ]),
            ])
            ->orderBy('sort_order')
            ->get();
    }
}
