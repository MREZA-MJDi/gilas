<?php

namespace App\Listeners;

use App\Enums\RestaurantUserRole;
use App\Events\OrderCreated;
use App\Models\Order;
use App\Notifications\NewOrderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyRestaurantStaffOfOrder implements ShouldQueue
{
    public string $queue = 'notifications';
    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function handle(OrderCreated $event): void
    {
        $order = Order::query()
            ->with('restaurant')
            ->find($event->orderId);

        if (!$order) {
            return;
        }

        $roles = [
            RestaurantUserRole::Owner->value,
            RestaurantUserRole::Manager->value,
            RestaurantUserRole::Cashier->value,
            RestaurantUserRole::Kitchen->value,
        ];

        $users = $order->restaurant
            ->activeUsers()
            ->wherePivotIn('role', $roles)
            ->get();

        foreach ($users as $user) {
            $alreadyNotified = $user->notifications()
                ->where('type', NewOrderNotification::class)
                ->where('data->order_id', $order->id)
                ->exists();

            if (!$alreadyNotified) {
                $user->notify(new NewOrderNotification($order->id));
            }
        }
    }

    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error('Failed to notify restaurant staff about created order.', [
            'order_id' => $event->orderId,
            'exception' => $exception::class,
        ]);
    }
}
