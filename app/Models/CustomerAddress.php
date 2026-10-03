<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerAddress extends Model
{
    protected $fillable = ['customer_id','title','recipient_name','phone','address','postal_code','latitude','longitude','is_default'];

    protected function casts(): array
    {
        return ['latitude'=>'decimal:7','longitude'=>'decimal:7','is_default'=>'boolean'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
}
