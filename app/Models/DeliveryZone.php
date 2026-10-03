<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryZone extends Model
{
    protected $fillable = ['restaurant_id','name','min_order_amount','delivery_fee','estimated_minutes','is_active'];

    protected function casts(): array { return ['min_order_amount'=>'integer','delivery_fee'=>'integer','estimated_minutes'=>'integer','is_active'=>'boolean']; }

    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
}
