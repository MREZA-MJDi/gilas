<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Courier extends Model
{
    protected $fillable = ['restaurant_id','user_id','phone','vehicle_type','status'];

    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function deliveries(): HasMany { return $this->hasMany(Delivery::class); }
}
