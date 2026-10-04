<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function markCashierPaid(Restaurant $restaurant, Payment $payment): RedirectResponse
    {
        $payment->loadMissing('order');

        abort_unless($payment->order?->restaurant_id === $restaurant->id, 404);
        $this->authorize('markCashierPaid', $payment);

        DB::transaction(function () use ($payment): void {
            $locked = Payment::query()->lockForUpdate()->with('order')->findOrFail($payment->id);

            if ($locked->order?->restaurant_id !== $payment->order->restaurant_id) {
                abort(404);
            }

            if ($locked->status === PaymentStatus::Paid) {
                return;
            }

            if ($locked->status !== PaymentStatus::Pending) {
                throw ValidationException::withMessages([
                    'payment' => 'This payment can no longer be marked as paid.',
                ]);
            }

            $locked->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ]);

            $locked->transactions()->create([
                'provider' => 'cashier',
                'transaction_id' => 'CASH-' . $locked->id,
                'reference' => null,
                'amount' => $locked->amount,
                'status' => 'paid',
                'response' => ['source' => 'admin_cashier'],
            ]);
        });

        return back()->with('status', 'پرداخت صندوق ثبت شد.');
    }
}
