<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case Dispatched = 'dispatched';
    case PickedUp = 'picked_up';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
