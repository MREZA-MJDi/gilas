<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = ['payment_id','provider','transaction_id','reference','amount','status','response'];

    protected function casts(): array { return ['amount'=>'integer','response'=>'array']; }

    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
