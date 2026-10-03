<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RestaurantTable extends Model
{
    protected $fillable = ['restaurant_id','name','number','capacity','floor','zone','status','is_active'];

    protected function casts(): array { return ['capacity'=>'integer','is_active'=>'boolean']; }

    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function qrCode(): HasOne { return $this->hasOne(TableQrCode::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function reservations(): HasMany { return $this->hasMany(Reservation::class); }
}
