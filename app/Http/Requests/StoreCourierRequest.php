<?php

namespace App\Http\Requests;

use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $restaurant = $this->route('restaurant');
        $courier = $this->route('courier');

        $phone = Rule::unique('couriers', 'phone')
            ->where('restaurant_id', $restaurant->id);
        $userId = Rule::unique('couriers', 'user_id')
            ->where('restaurant_id', $restaurant->id);

        if ($courier instanceof Courier) {
            $phone->ignore($courier->id);
            $userId->ignore($courier->id);
        }

        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'min:8', 'max:40', $phone],
            'user_id' => ['nullable', 'integer', 'min:1', 'exists:users,id', $userId],
            'vehicle_type' => ['nullable', 'string', 'max:80'],
            'status' => ['sometimes', Rule::in(['available', 'online', 'offline'])],
        ];
    }
}
