<?php

namespace App\Enums;

enum RestaurantUserRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Waiter = 'waiter';
    case Kitchen = 'kitchen';
    case Cashier = 'cashier';
    case Courier = 'courier';
    case Staff = 'staff';
}
