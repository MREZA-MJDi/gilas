<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_name' => is_string($this->customer_name) ? trim($this->customer_name) : $this->customer_name,
            'phone' => $this->normalizeDigits($this->phone),
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
            'address' => is_string($this->address) ? trim($this->address) : $this->address,
            'postal_code' => $this->normalizeDigits($this->postal_code),
            'customer_note' => is_string($this->customer_note) ? trim($this->customer_note) : $this->customer_note,
        ]);
    }

    public function rules(): array
    {
        return [
            'order_type' => ['required', Rule::in(['pickup', 'delivery'])],
            'payment_method' => ['required', Rule::in(['online', 'cashier'])],
            'customer_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'min:8', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required_if:order_type,delivery', 'nullable', 'string', 'min:8', 'max:2000'],
            'postal_code' => ['nullable', 'string', 'max:30'],
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

    private function normalizeDigits(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
