<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $restaurant = $this->route('restaurant');
        $item = $this->route('item');

        $slug = Rule::unique('menu_items', 'slug')
            ->where('restaurant_id', $restaurant->id);

        if ($item instanceof MenuItem) {
            $slug->ignore($item->id);
        }

        return [
            'menu_category_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('menu_categories', 'id')->where('restaurant_id', $restaurant->id),
            ],
            'name' => ['required', 'string', 'min:1', 'max:160'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/', $slug],
            'description' => ['nullable', 'string', 'max:3000'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
            'is_available' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
