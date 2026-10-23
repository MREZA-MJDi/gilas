<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicReservationRequest;
use App\Models\Customer;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PublicReservationController extends Controller
{
    public function store(StorePublicReservationRequest $request): RedirectResponse
    {
        $restaurant = $this->primaryRestaurantOrFail();

        abort_unless((bool) $restaurant->settings?->reservation_enabled, 404);

        $data = $request->validated();
        $timezone = $restaurant->timezone ?: config('app.timezone');
        $now = now($timezone);

        if ($data['reservation_date'] === $now->toDateString() && $data['start_time'] <= $now->format('H:i')) {
            return back()
                ->withInput()
                ->withErrors(['start_time' => 'برای امروز یک ساعت آینده‌تر انتخاب کن.']);
        }

        $digits = preg_replace('/\D+/', '', $data['phone']);
        $phone = str_starts_with(trim($data['phone']), '+') ? '+' . $digits : $digits;

        DB::transaction(function () use ($restaurant, $data, $phone): void {
            // Phone is unique in the schema, so repeated visits update/reuse the same customer.
            $customer = Customer::query()->where('phone', $phone)->lockForUpdate()->first();

            if (!$customer) {
                $customer = Customer::query()->create([
                    'name' => trim($data['name']),
                    'phone' => $phone,
                    'status' => 'active',
                ]);
            } elseif (blank($customer->name)) {
                $customer->forceFill(['name' => trim($data['name'])])->save();
            }

            $restaurant->reservations()->create([
                'customer_id' => $customer->id,
                'restaurant_table_id' => null,
                'reservation_date' => $data['reservation_date'],
                'start_time' => $data['start_time'],
                'end_time' => null,
                'guest_count' => (int) $data['guest_count'],
                'status' => ReservationStatus::Pending,
                'customer_note' => filled($data['customer_note'] ?? null)
                    ? trim($data['customer_note'])
                    : null,
            ]);
        });

        return to_route('public.reservation')->with('reservation_confirmation', [
            'date' => $data['reservation_date'],
            'time' => $data['start_time'],
            'guests' => (int) $data['guest_count'],
        ]);
    }

    private function primaryRestaurantOrFail(): Restaurant
    {
        $query = Restaurant::query()
            ->where('status', 'active')
            ->with('settings');

        $configuredSlug = config('gilas.primary_restaurant_slug');

        if (is_string($configuredSlug) && trim($configuredSlug) !== '') {
            $restaurant = (clone $query)->where('slug', trim($configuredSlug))->first();

            if ($restaurant) {
                return $restaurant;
            }
        }

        return $query->orderBy('id')->firstOrFail();
    }
}
