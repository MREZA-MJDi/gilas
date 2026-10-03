<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_note' => is_string($this->customer_note) ? trim($this->customer_note) : $this->customer_note,
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_note' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['required', 'array'],
            'items.*.menu_item_id' => ['required', 'integer', 'min:1'],
            'items.*.menu_item_variant_id' => ['nullable', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.option_value_ids' => ['nullable', 'array', 'max:50'],
            'items.*.option_value_ids.*' => ['integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function idempotencyKey(): ?string
    {
        $key = trim((string) $this->header('Idempotency-Key'));

        return $key !== '' ? $key : null;
    }
}
