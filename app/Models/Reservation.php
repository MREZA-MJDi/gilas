<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'restaurant_id','customer_id','restaurant_table_id','reservation_date',
        'start_time','end_time','guest_count','status','customer_note',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date'=>'date',
            'status'=>ReservationStatus::class,
            'guest_count'=>'integer',
        ];
    }

    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function table(): BelongsTo { return $this->belongsTo(RestaurantTable::class,'restaurant_table_id'); }
}
