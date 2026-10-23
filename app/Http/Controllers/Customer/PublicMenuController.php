<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\MenuCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicMenuController extends Controller
{
    public function __construct(
        private readonly MenuCatalogService $catalog,
    ) {
    }

    public function index(Request $request): View
    {
        return $this->render(
            $request->query('category'),
            is_string($request->query('q')) ? trim($request->query('q')) : '',
            is_string($request->query('sort')) ? $request->query('sort') : 'default',
        );
    }

    public function category(Request $request, string $slug): View
    {
        return $this->render(
            $slug,
            is_string($request->query('q')) ? trim($request->query('q')) : '',
            is_string($request->query('sort')) ? $request->query('sort') : 'default',
        );
    }

    private function render(?string $categorySlug, string $search, string $sort): View
    {
        $restaurant = $this->primaryRestaurantOrFail();
        $categories = $this->catalog->forRestaurant($restaurant)->values();
        $selectedSlug = is_string($categorySlug) ? trim($categorySlug) : null;

        if ($selectedSlug !== null && $selectedSlug !== '') {
            abort_unless($categories->contains(fn ($category) => $category->slug === $selectedSlug), 404);
        } else {
            $selectedSlug = null;
        }

        $matchingCategories = $categories->map(function ($category) use ($search, $sort) {
            $items = $category->items;

            if ($search !== '') {
                $needle = mb_strtolower($search, 'UTF-8');
                $items = $items->filter(function ($item) use ($needle) {
                    $haystack = mb_strtolower($item->name . ' ' . ($item->description ?? ''), 'UTF-8');

                    return mb_strpos($haystack, $needle) !== false;
                });
            }

            $items = match ($sort) {
                'price_asc' => $items->sortBy(fn ($item) => (int) ($item->variants->first()?->price ?? $item->price)),
                'price_desc' => $items->sortByDesc(fn ($item) => (int) ($item->variants->first()?->price ?? $item->price)),
                'name' => $items->sortBy('name', SORT_NATURAL),
                'newest' => $items->sortByDesc('id'),
                default => $items->sortBy('sort_order'),
            };

            $category->setRelation('items', $items->values());

            return $category;
        });

        $menu = $selectedSlug !== null
            ? $matchingCategories->filter(fn ($category) => $category->slug === $selectedSlug)->values()
            : ($search !== ''
                ? $matchingCategories->filter(fn ($category) => $category->items->isNotEmpty())->values()
                : $matchingCategories);

        return view('store.index', [
            'restaurant' => $restaurant,
            'categories' => $categories,
            'menu' => $menu,
            'selectedSlug' => $selectedSlug,
            'search' => $search,
            'sort' => $sort,
        ]);
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
