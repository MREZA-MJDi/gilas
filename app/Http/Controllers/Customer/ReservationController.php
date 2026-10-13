<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $restaurant = $this->primaryRestaurantOrFail();
        $settings = $restaurant->settings()->firstOrCreate([], [
            'ordering_enabled' => true,
            'dine_in_enabled' => true,
            'pickup_enabled' => true,
            'delivery_enabled' => true,
            'reservation_enabled' => true,
            'min_order_amount' => 0,
            'default_delivery_minutes' => 45,
            'tax_percent' => 0,
            'service_charge_percent' => 0,
        ]);

        if (!$settings->reservation_enabled) {
            throw ValidationException::withMessages([
                'reservation' => 'Reservations are currently disabled for this restaurant.',
            ]);
        }

        $this->assertWithinConfiguredHours($restaurant, $data['reservation_date'], $data['start_time']);

        $reservation = DB::transaction(function () use ($restaurant, $data): Reservation {
            $customer = Customer::query()->where('phone', $data['phone'])->lockForUpdate()->first();

            if ($customer) {
                $customer->update([
                    'name' => $data['customer_name'],
                    'email' => $data['email'] ?? $customer->email,
                    'status' => 'active',
                ]);
            } else {
                try {
                    $customer = Customer::create([
                        'name' => $data['customer_name'],
                        'phone' => $data['phone'],
                        'email' => $data['email'] ?? null,
                        'status' => 'active',
                    ]);
                } catch (QueryException $exception) {
                    $customer = Customer::query()->where('phone', $data['phone'])->first();

                    if (!$customer) {
                        throw $exception;
                    }
                }
            }

            $start = Carbon::createFromFormat(
                'Y-m-d H:i',
                $data['reservation_date'].' '.$data['start_time'],
                $restaurant->timezone ?: config('app.timezone')
            );
            $end = $start->copy()->addMinutes(90);

            return $restaurant->reservations()->create([
                'customer_id' => $customer->id,
                'reservation_date' => $data['reservation_date'],
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'guest_count' => (int) $data['guest_count'],
                'status' => ReservationStatus::Pending,
                'customer_note' => $data['customer_note'] ?? null,
            ])->load('customer');
        });

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'reservation_id' => $reservation->id,
                    'status' => $reservation->status->value,
                    'reservation_date' => $reservation->reservation_date->toDateString(),
                    'start_time' => substr($reservation->start_time, 0, 5),
                    'guest_count' => $reservation->guest_count,
                ],
            ], 201);
        }

        return redirect()
            ->route('public.reservation')
            ->with('status', 'درخواست رزرو ثبت شد و پس از بررسی مجموعه تأیید می‌شود.');
    }

    private function assertWithinConfiguredHours(Restaurant $restaurant, string $date, string $time): void
    {
        $dayOfWeek = Carbon::parse($date, $restaurant->timezone ?: config('app.timezone'))->dayOfWeek;
        $hours = $restaurant->hours()->where('day_of_week', $dayOfWeek)->first();

        // If no schedule is configured, accept a request as pending for staff confirmation.
        if (!$hours) {
            return;
        }

        if ($hours->is_closed || !$hours->open_time || !$hours->close_time) {
            throw ValidationException::withMessages([
                'reservation_date' => 'The restaurant is closed on the selected date.',
            ]);
        }

        $start = Carbon::createFromFormat('H:i', $time);
        $end = $start->copy()->addMinutes(90);
        $open = Carbon::createFromFormat('H:i:s', $hours->open_time);
        $close = Carbon::createFromFormat('H:i:s', $hours->close_time);

        if ($start->lt($open) || $end->gt($close)) {
            throw ValidationException::withMessages([
                'start_time' => 'The requested time falls outside the restaurant opening hours.',
            ]);
        }
    }

    private function primaryRestaurantOrFail(): Restaurant
    {
        $query = Restaurant::query()->where('status', 'active')->with('settings');
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
