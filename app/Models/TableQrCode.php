<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableQrCode extends Model
{
    protected $fillable = ['restaurant_table_id','token','is_active','generated_at'];

    protected function casts(): array { return ['is_active'=>'boolean','generated_at'=>'datetime']; }

    public function table(): BelongsTo { return $this->belongsTo(RestaurantTable::class,'restaurant_table_id'); }
}
