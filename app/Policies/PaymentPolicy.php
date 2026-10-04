<?php

namespace App\Policies;

use App\Enums\PaymentMethod;
use App\Enums\RestaurantPermission;
use App\Models\Payment;
use App\Models\User;
use App\Services\RestaurantAccessService;

class PaymentPolicy
{
    public function markCashierPaid(User $user, Payment $payment): bool
    {
        $order = $payment->order;

        return $payment->method === PaymentMethod::Cashier
            && $order?->restaurant !== null
            && app(RestaurantAccessService::class)->can(
                $user,
                $order->restaurant,
                RestaurantPermission::ManagePayments
            );
    }
}
