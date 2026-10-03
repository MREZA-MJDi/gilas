<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Served = 'served';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next, OrderType $type): bool
    {
        if ($this === $next) {
            return true;
        }

        return match ($this) {
            self::Pending => in_array($next, [self::Confirmed, self::Cancelled], true),
            self::Confirmed => in_array($next, [self::Preparing, self::Cancelled], true),
            self::Preparing => $next === self::Ready,
            self::Ready => match ($type) {
                OrderType::DineIn => $next === self::Served,
                OrderType::Pickup => $next === self::Completed,
                OrderType::Delivery => $next === self::OutForDelivery,
            },
            self::Served => $next === self::Completed,
            self::OutForDelivery => $next === self::Delivered,
            self::Delivered => $next === self::Completed,
            self::Completed, self::Cancelled => false,
        };
    }
}
