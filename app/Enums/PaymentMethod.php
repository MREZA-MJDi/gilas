<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Online = 'online';
    case Cashier = 'cashier';

    // Legacy seeded/production records use "cash"; keep them readable and payable.
    case Cash = 'cash';

    public function isCashierSettlement(): bool
    {
        return $this === self::Cashier || $this === self::Cash;
    }
}
