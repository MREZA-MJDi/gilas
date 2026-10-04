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
        return $this->render($request->query('category'));
    }

    public function category(string $slug): View
    {
        return $this->render($slug);
    }

    private function render(?string $categorySlug): View
    {
        $restaurant = $this->primaryRestaurantOrFail();
        $menu = $this->catalog->forRestaurant($restaurant)->values();

        $selectedSlug = is_string($categorySlug) ? trim($categorySlug) : null;

        if ($selectedSlug !== null && $selectedSlug !== '') {
            abort_unless($menu->contains(fn ($category) => $category->slug === $selectedSlug), 404);
            $menu = $menu
                ->filter(fn ($category) => $category->slug === $selectedSlug)
                ->values();
        }

        return view('customer.menu', [
            'restaurant' => $restaurant,
            'menu' => $menu,
            'selectedSlug' => $selectedSlug,
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
