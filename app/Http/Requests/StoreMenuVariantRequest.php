<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
