<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\MenuItemOption;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MenuPricingService
{
    public function resolve(
        MenuItem $item,
        ?int $variantId = null,
        array $optionValueIds = []
    ): array {
        $item->loadMissing(['variants', 'options.values']);

        if (!$item->is_active || !$item->is_available) {
            throw ValidationException::withMessages([
                'menu_item_id' => 'This menu item is not currently available.',
            ]);
        }

        $variant = null;

        if ($variantId !== null) {
            $variant = $item->variants->firstWhere('id', $variantId);

            if (!$variant || !$variant->is_active) {
                throw ValidationException::withMessages([
                    'menu_item_variant_id' => 'This menu variant is invalid or inactive.',
                ]);
            }
        }

        $normalizedIds = array_map('intval', array_values($optionValueIds));

        if (count($normalizedIds) !== count(array_unique($normalizedIds))) {
            throw ValidationException::withMessages([
                'options' => 'The same menu option value cannot be selected twice.',
            ]);
        }

        $selected = collect($normalizedIds)
            ->map(fn (int $id) => $this->findValue($item, $id))
            ->values();

        $this->validateOptions($item->options, $selected);

        $basePrice = (int) ($variant?->price ?? $item->price);
        $optionTotal = (int) $selected->sum('price_delta');
        $unitPrice = $basePrice + $optionTotal;

        return [
            'menu_item_id' => $item->id,
            'menu_item_variant_id' => $variant?->id,
            'name' => $item->name,
            'variant_name' => $variant?->name,
            'base_unit_price' => $basePrice,
            'unit_price' => $unitPrice,
            'options' => $selected->map(fn ($value) => [
                'option_name' => $value->option->name,
                'value_name' => $value->name,
                'price_delta' => (int) $value->price_delta,
            ])->all(),
        ];
    }

    private function findValue(MenuItem $item, int $valueId)
    {
        foreach ($item->options as $option) {
            $value = $option->values->firstWhere('id', $valueId);

            if ($value) {
                if (!$option->is_active || !$value->is_active) {
                    throw ValidationException::withMessages([
                        'options' => 'One of the selected menu options is inactive.',
                    ]);
                }

                return $value;
            }
        }

        throw ValidationException::withMessages([
            'options' => 'A selected menu option does not belong to this menu item.',
        ]);
    }

    private function validateOptions(Collection $options, Collection $selected): void
    {
        foreach ($options as $option) {
            $count = $selected
                ->filter(fn ($value) => $value->menu_item_option_id === $option->id)
                ->count();

            if (!$option->is_active) {
                if ($count > 0 || $option->is_required || $option->min_select > 0) {
                    throw ValidationException::withMessages([
                        'options' => "The option {$option->name} is not currently available.",
                    ]);
                }

                continue;
            }

            $minimum = $option->is_required
                ? max(1, $option->min_select)
                : $option->min_select;

            if ($count < $minimum) {
                throw ValidationException::withMessages([
                    'options' => "The option {$option->name} requires at least {$minimum} selection(s).",
                ]);
            }

            if ($count > $option->max_select) {
                throw ValidationException::withMessages([
                    'options' => "The option {$option->name} allows at most {$option->max_select} selection(s).",
                ]);
            }
        }
    }
}
