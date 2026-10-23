<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^\+?[0-9][0-9\s()\-]{5,38}[0-9]$/'],
            'reservation_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . now()->addDays(60)->toDateString(),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:20'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نامت را وارد کن.',
            'phone.required' => 'شماره تماس برای هماهنگی رزرو لازم است.',
            'phone.regex' => 'شماره تماس را با قالب معتبر وارد کن.',
            'reservation_date.after_or_equal' => 'تاریخ رزرو نمی‌تواند در گذشته باشد.',
            'reservation_date.before_or_equal' => 'رزرو آنلاین تا ۶۰ روز آینده امکان‌پذیر است.',
            'start_time.date_format' => 'ساعت را به شکل ساعت و دقیقه انتخاب کن.',
            'guest_count.min' => 'تعداد مهمان‌ها حداقل یک نفر است.',
            'guest_count.max' => 'برای رزرو آنلاین حداکثر ۲۰ مهمان ثبت می‌شود.',
        ];
    }
}
