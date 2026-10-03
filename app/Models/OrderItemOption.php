<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemOption extends Model
{
    protected $fillable = ['order_item_id','option_name','value_name','price_delta'];

    protected function casts(): array { return ['price_delta'=>'integer']; }

    public function item(): BelongsTo { return $this->belongsTo(OrderItem::class,'order_item_id'); }
}
