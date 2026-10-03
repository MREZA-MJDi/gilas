<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Customer extends Model
{
    protected $fillable = ['name','phone','email','status','last_order_at'];

    protected function casts(): array { return ['last_order_at'=>'datetime']; }

    public function addresses(): HasMany { return $this->hasMany(CustomerAddress::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function reservations(): HasMany { return $this->hasMany(Reservation::class); }
}
