<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'min_select' => ['required', 'integer', 'min:0', 'max:50'],
            'max_select' => ['required', 'integer', 'min:0', 'max:50'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                if (
                    $this->filled('min_select')
                    && $this->filled('max_select')
                    && (int) $this->input('min_select') > (int) $this->input('max_select')
                ) {
                    $validator->errors()->add('max_select', 'max_select must be greater than or equal to min_select.');
                }
            },
        ];
    }
}
