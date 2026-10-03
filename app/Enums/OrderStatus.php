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
}
