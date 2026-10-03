<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Restaurant extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'logo_path', 'cover_image_path',
        'phone', 'email', 'address', 'latitude', 'longitude',
        'timezone', 'currency', 'status',
    ];

    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7'];
    }

    public function settings(): HasOne { return $this->hasOne(RestaurantSetting::class); }
    public function hours(): HasMany { return $this->hasMany(RestaurantHour::class); }
    public function categories(): HasMany { return $this->hasMany(MenuCategory::class); }
    public function items(): HasMany { return $this->hasMany(MenuItem::class); }
    public function tables(): HasMany { return $this->hasMany(RestaurantTable::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function reservations(): HasMany { return $this->hasMany(Reservation::class); }
    public function couriers(): HasMany { return $this->hasMany(Courier::class); }
    public function deliveryZones(): HasMany { return $this->hasMany(DeliveryZone::class); }
}
