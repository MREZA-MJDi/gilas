<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = ['order_id','method','status','amount','paid_at'];

    protected function casts(): array { return ['status'=>PaymentStatus::class,'amount'=>'integer','paid_at'=>'datetime']; }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function transactions(): HasMany { return $this->hasMany(PaymentTransaction::class); }
}
