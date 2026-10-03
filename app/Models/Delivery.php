<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = ['order_id','courier_id','status','delivery_address_snapshot','customer_note','dispatched_at','picked_up_at','delivered_at'];

    protected function casts(): array
    {
        return [
            'status'=>DeliveryStatus::class,
            'dispatched_at'=>'datetime','picked_up_at'=>'datetime','delivered_at'=>'datetime',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function courier(): BelongsTo { return $this->belongsTo(Courier::class); }
}
