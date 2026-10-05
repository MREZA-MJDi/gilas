<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const HERO_LAYOUT = [
        ['pic22','pic23','pic23','pic24','pic24','pic25','pic25'],
        ['pic22','pic11','pic12','pic12','pic13','pic13','pic14'],
        ['pic21','pic11','pic4','pic5','pic5','pic6','pic14'],
        ['pic21','pic10','pic4','pic1','pic2','pic6','pic15'],
        ['pic20','pic10','pic3','pic3','pic2','pic7','pic15'],
        ['pic20','pic9','pic9','pic8','pic8','pic7','pic16'],
        ['pic19','pic19','pic18','pic18','pic17','pic17','pic16'],
    ];

    public function __invoke(): View
    {
        $restaurant = $this->primaryRestaurant();
        $heroItems = collect();
        $heroGridAreas = '"."';

        if ($restaurant) {
            $limit = (int) config('gilas.hero_items_limit', 15);

            $heroItems = MenuItem::query()
                ->select([
                    'id',
                    'menu_category_id',
                    'name',
                    'slug',
                    'description',
                    'image_path',
                    'price',
                    'sort_order',
                ])
                ->where('restaurant_id', $restaurant->id)
                ->where('is_active', true)
                ->where('is_available', true)
                ->whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->with('category:id,name,slug')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit($limit)
                ->get();

            $heroGridAreas = $this->heroGridAreas($heroItems->count());
        }

        return view('welcome', [
            'restaurant' => $restaurant,
            'heroItems' => $heroItems,
            'heroGridAreas' => $heroGridAreas,
        ]);
    }

    private function heroGridAreas(int $count): string
    {
        $count = min(25, max(0, $count));

        if ($count === 0) {
            return '"."';
        }

        $active = array_fill_keys(range(1, $count), true);
        $cells = array_map(
            static fn (array $row): array => array_map(
                static fn (string $cell): string => isset($active[(int) substr($cell, 3)]) ? $cell : '.',
                $row
            ),
            self::HERO_LAYOUT
        );

        $rows = count($cells);
        $cols = count($cells[0]);

        $activeRows = [];
        $activeCols = [];

        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                if ($cells[$row][$col] !== '.') {
                    $activeRows[] = $row;
                    $activeCols[] = $col;
                }
            }
        }

        $minRow = min($activeRows);
        $maxRow = max($activeRows);
        $minCol = min($activeCols);
        $maxCol = max($activeCols);

        $templates = [];

        for ($row = $minRow; $row <= $maxRow; $row++) {
            $templates[] = '"' . implode(' ', array_slice($cells[$row], $minCol, $maxCol - $minCol + 1)) . '"';
        }

        return implode(' ', $templates);
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
