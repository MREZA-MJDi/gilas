<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
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
            'reservation_date' => is_string($this->reservation_date) ? trim($this->reservation_date) : $this->reservation_date,
            'start_time' => is_string($this->start_time) ? trim($this->start_time) : $this->start_time,
            'customer_note' => is_string($this->customer_note) ? trim($this->customer_note) : $this->customer_note,
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'min:8', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'reservation_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:20'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
        ];
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
