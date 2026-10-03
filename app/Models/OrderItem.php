<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = ['order_id','menu_item_id','menu_item_variant_id','name','unit_price','quantity','total_price','note'];

    protected function casts(): array { return ['unit_price'=>'integer','quantity'=>'integer','total_price'=>'integer']; }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function menuItem(): BelongsTo { return $this->belongsTo(MenuItem::class); }
    public function variant(): BelongsTo { return $this->belongsTo(MenuItemVariant::class,'menu_item_variant_id'); }
    public function options(): HasMany { return $this->hasMany(OrderItemOption::class); }
}
