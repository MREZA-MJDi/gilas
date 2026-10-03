<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'restaurant_id','customer_id','restaurant_table_id','customer_address_id',
        'order_number','idempotency_key','idempotency_hash','public_token','order_type','status',
        'subtotal','discount','tax','delivery_fee','service_charge','total',
        'customer_note','confirmed_at','completed_at','cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'order_type'=>OrderType::class,'status'=>OrderStatus::class,
            'subtotal'=>'integer','discount'=>'integer','tax'=>'integer',
            'delivery_fee'=>'integer','service_charge'=>'integer','total'=>'integer',
            'confirmed_at'=>'datetime','completed_at'=>'datetime','cancelled_at'=>'datetime',
        ];
    }

    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function table(): BelongsTo { return $this->belongsTo(RestaurantTable::class,'restaurant_table_id'); }
    public function address(): BelongsTo { return $this->belongsTo(CustomerAddress::class,'customer_address_id'); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function statusHistory(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
    public function delivery(): HasOne { return $this->hasOne(Delivery::class); }
    public function payment(): HasOne { return $this->hasOne(Payment::class); }
}
