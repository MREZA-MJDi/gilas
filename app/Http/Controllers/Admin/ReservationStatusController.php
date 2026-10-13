<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationStatusRequest;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Services\RestaurantAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationStatusController extends Controller
{
    public function update(
        StoreReservationStatusRequest $request,
        Restaurant $restaurant,
        Reservation $reservation,
        RestaurantAccessService $access
    ): RedirectResponse|JsonResponse {
        abort_unless((int) $reservation->restaurant_id === (int) $restaurant->id, 404);
        abort_unless($access->can($request->user(), $restaurant, 'reservations.manage'), 403);

        $data = $request->validated();
        $next = ReservationStatus::from($data['status']);
        $current = $reservation->status;

        if ($current !== $next && !in_array($next, $this->allowedTransitions($current), true)) {
            throw ValidationException::withMessages([
                'status' => "Reservation cannot transition from {$current->value} to {$next->value}.",
            ]);
        }

        DB::transaction(function () use ($restaurant, $reservation, $data, $next): void {
            $tableId = $data['restaurant_table_id'] ?? $reservation->restaurant_table_id;

            if ($next === ReservationStatus::Seated && !$tableId) {
                throw ValidationException::withMessages([
                    'restaurant_table_id' => 'A table must be assigned before seating a reservation.',
                ]);
            }

            if ($tableId) {
                $table = RestaurantTable::query()
                    ->where('restaurant_id', $restaurant->id)
                    ->whereKey($tableId)
                    ->where('is_active', true)
                    ->first();

                if (!$table || $table->capacity < $reservation->guest_count) {
                    throw ValidationException::withMessages([
                        'restaurant_table_id' => 'The selected table is unavailable or too small for this party.',
                    ]);
                }

                if (in_array($next, [ReservationStatus::Confirmed, ReservationStatus::Seated], true)) {
                    $requestedStart = $this->minutesAfterMidnight($reservation->start_time);
                    $requestedEnd = $this->minutesAfterMidnight($reservation->end_time ?: $reservation->start_time);
                    $conflict = Reservation::query()
                        ->where('restaurant_id', $restaurant->id)
                        ->where('restaurant_table_id', $table->id)
                        ->whereDate('reservation_date', $reservation->reservation_date->toDateString())
                        ->whereIn('status', [
                            ReservationStatus::Confirmed->value,
                            ReservationStatus::Seated->value,
                        ])
                        ->where('id', '!=', $reservation->id)
                        ->get(['id', 'start_time', 'end_time'])
                        ->contains(function (Reservation $existing) use ($requestedStart, $requestedEnd): bool {
                            $existingStart = $this->minutesAfterMidnight($existing->start_time);
                            $existingEnd = $this->minutesAfterMidnight($existing->end_time ?: $existing->start_time);

                            return $existingStart < $requestedEnd && $existingEnd > $requestedStart;
                        });

                    if ($conflict) {
                        throw ValidationException::withMessages([
                            'restaurant_table_id' => 'The selected table already has an overlapping reservation.',
                        ]);
                    }
                }
            }

            $reservation->update([
                'status' => $next,
                'restaurant_table_id' => $tableId,
            ]);
        });

        $reservation->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'reservation_id' => $reservation->id,
                    'status' => $reservation->status->value,
                    'restaurant_table_id' => $reservation->restaurant_table_id,
                ],
            ]);
        }

        return back()->with('status', 'وضعیت رزرو به‌روزرسانی شد.');
    }

    private function minutesAfterMidnight(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', substr($time, 0, 5)));

        return ($hours * 60) + $minutes;
    }

    private function allowedTransitions(ReservationStatus $current): array
    {
        return match ($current) {
            ReservationStatus::Pending => [ReservationStatus::Confirmed, ReservationStatus::Cancelled],
            ReservationStatus::Confirmed => [ReservationStatus::Seated, ReservationStatus::Cancelled, ReservationStatus::NoShow],
            ReservationStatus::Seated => [ReservationStatus::Completed],
            ReservationStatus::Completed, ReservationStatus::Cancelled, ReservationStatus::NoShow => [],
        };
    }
}
