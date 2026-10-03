<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemOptionValue extends Model
{
    protected $fillable = ['menu_item_option_id','name','price_delta','is_active','sort_order'];

    protected function casts(): array { return ['price_delta'=>'integer','is_active'=>'boolean']; }

    public function option(): BelongsTo { return $this->belongsTo(MenuItemOption::class,'menu_item_option_id'); }
}
