<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'restaurant_id','menu_category_id','name','slug','description','image_path',
        'price','sort_order','is_active','is_available',
    ];

    protected function casts(): array { return ['price'=>'integer','is_active'=>'boolean','is_available'=>'boolean']; }

    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function category(): BelongsTo { return $this->belongsTo(MenuCategory::class,'menu_category_id'); }
    public function variants(): HasMany { return $this->hasMany(MenuItemVariant::class); }
    public function options(): HasMany { return $this->hasMany(MenuItemOption::class); }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Str::startsWith($this->image_path, ['http://', 'https://'])
            ? $this->image_path
            : \Illuminate\Support\Facades\Storage::url($this->image_path);
    }
}
