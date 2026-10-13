<?php

namespace App\Http\Requests;

use App\Models\MenuCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $restaurant = $this->route('restaurant');
        $category = $this->route('category');

        $slug = Rule::unique('menu_categories', 'slug')
            ->where('restaurant_id', $restaurant->id);

        if ($category instanceof MenuCategory) {
            $slug->ignore($category->id);
        }

        return [
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/', $slug],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
