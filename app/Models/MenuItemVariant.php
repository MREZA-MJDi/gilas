<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemVariant extends Model
{
    protected $fillable = ['menu_item_id','name','price','sort_order','is_active'];

    protected function casts(): array { return ['price'=>'integer','is_active'=>'boolean']; }

    public function item(): BelongsTo { return $this->belongsTo(MenuItem::class,'menu_item_id'); }
}
