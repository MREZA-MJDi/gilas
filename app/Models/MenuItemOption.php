<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItemOption extends Model
{
    protected $fillable = ['menu_item_id','name','min_select','max_select','is_required','sort_order'];

    protected function casts(): array { return ['min_select'=>'integer','max_select'=>'integer','is_required'=>'boolean']; }

    public function item(): BelongsTo { return $this->belongsTo(MenuItem::class,'menu_item_id'); }
    public function values(): HasMany { return $this->hasMany(MenuItemOptionValue::class); }
}
