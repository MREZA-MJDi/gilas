<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_reservation_and_it_is_persisted_for_the_active_restaurant(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'reservation-flow',
            'status' => 'active',
            'timezone' => config('app.timezone'),
        ]);
        $restaurant->settings()->create(['reservation_enabled' => true]);

        $date = now()->addDay()->toDateString();

        $response = $this->post(route('public.reservations.store'), [
            'name' => 'مهمان گیلاس',
            'phone' => '+98 (912) 123-4567',
            'reservation_date' => $date,
            'start_time' => '19:30',
            'guest_count' => 4,
            'customer_note' => 'میز آرام، لطفاً',
        ]);

        $response->assertRedirect(route('public.reservation'))
            ->assertSessionHas('reservation_confirmation', [
                'date' => $date,
                'time' => '19:30',
                'guests' => 4,
            ]);

        $customer = Customer::query()->where('phone', '+989121234567')->firstOrFail();

        $this->assertSame('مهمان گیلاس', $customer->name);
        $this->assertDatabaseHas('reservations', [
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer->id,
            'guest_count' => 4,
            'status' => ReservationStatus::Pending->value,
            'customer_note' => 'میز آرام، لطفاً',
        ]);

        // SQLite and MySQL serialize DATE/TIME columns differently; assert their normalized values.
        $reservation = Reservation::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $this->assertSame($date, $reservation->reservation_date->format('Y-m-d'));
        $this->assertSame('19:30', substr((string) $reservation->start_time, 0, 5));
    }

    public function test_reservation_request_is_rejected_when_online_reservations_are_disabled(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'reservation-disabled',
            'status' => 'active',
        ]);
        $restaurant->settings()->create(['reservation_enabled' => false]);

        $this->post(route('public.reservations.store'), [
            'name' => 'مهمان',
            'phone' => '09121234567',
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '19:30',
            'guest_count' => 2,
        ])->assertNotFound();

        $this->assertSame(0, Reservation::query()->count());
    }

    public function test_reservation_form_rejects_a_date_in_the_past(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'reservation-date-validation',
            'status' => 'active',
        ]);
        $restaurant->settings()->create(['reservation_enabled' => true]);

        $this->from(route('public.reservation'))
            ->post(route('public.reservations.store'), [
                'name' => 'مهمان',
                'phone' => '09121234567',
                'reservation_date' => now()->subDay()->toDateString(),
                'start_time' => '19:30',
                'guest_count' => 2,
            ])
            ->assertRedirect(route('public.reservation'))
            ->assertSessionHasErrors('reservation_date');

        $this->assertSame(0, Reservation::query()->count());
    }
}
