<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantSetting extends Model
{
    protected $fillable = [
        'restaurant_id','ordering_enabled','dine_in_enabled','pickup_enabled','delivery_enabled',
        'reservation_enabled','min_order_amount','default_delivery_minutes','tax_percent','service_charge_percent',
    ];

    protected function casts(): array
    {
        return [
            'ordering_enabled'=>'boolean','dine_in_enabled'=>'boolean','pickup_enabled'=>'boolean',
            'delivery_enabled'=>'boolean','reservation_enabled'=>'boolean','tax_percent'=>'decimal:2',
            'service_charge_percent'=>'decimal:2',
        ];
    }

    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
}
