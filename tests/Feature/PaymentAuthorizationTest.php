<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RestaurantUserRole;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_cashier_with_payment_permission_can_mark_cash_payment_paid(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'gilas-payment-auth',
            'status' => 'active',
        ]);

        $cashier = User::factory()->create();
        $kitchen = User::factory()->create();

        $restaurant->users()->attach($cashier->id, [
            'role' => RestaurantUserRole::Cashier->value,
            'is_active' => true,
        ]);
        $restaurant->users()->attach($kitchen->id, [
            'role' => RestaurantUserRole::Kitchen->value,
            'is_active' => true,
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_number' => 'GLS-PAY-1',
            'order_type' => 'pickup',
            'status' => 'pending',
            'subtotal' => 100000,
            'total' => 100000,
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => PaymentMethod::Cashier,
            'status' => PaymentStatus::Pending,
            'amount' => 100000,
        ]);

        $this->actingAs($kitchen)
            ->post(route('admin.payments.cashier-paid', [$restaurant->slug, $payment->id]))
            ->assertForbidden();

        $this->actingAs($cashier)
            ->post(route('admin.payments.cashier-paid', [$restaurant->slug, $payment->id]))
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Paid->value,
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'payment_id' => $payment->id,
            'provider' => 'cashier',
            'status' => 'paid',
        ]);
    }

    public function test_payment_marking_is_idempotent_for_already_paid_cashier_payment(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'گیلاس',
            'slug' => 'gilas-payment-idempotent',
            'status' => 'active',
        ]);

        $cashier = User::factory()->create();
        $restaurant->users()->attach($cashier->id, [
            'role' => RestaurantUserRole::Cashier->value,
            'is_active' => true,
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_number' => 'GLS-PAY-2',
            'order_type' => 'pickup',
            'status' => 'pending',
            'subtotal' => 50000,
            'total' => 50000,
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => PaymentMethod::Cashier,
            'status' => PaymentStatus::Paid,
            'amount' => 50000,
            'paid_at' => now(),
        ]);

        $this->actingAs($cashier)
            ->post(route('admin.payments.cashier-paid', [$restaurant->slug, $payment->id]))
            ->assertRedirect();

        $this->assertSame(0, DB::table('payment_transactions')->where('payment_id', $payment->id)->count());
    }
}
