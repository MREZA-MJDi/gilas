<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryStatusHistory extends Model
{
    protected $fillable = ['delivery_id', 'changed_by', 'from_status', 'to_status', 'note'];

    protected function casts(): array
    {
        return [
            'from_status' => DeliveryStatus::class,
            'to_status' => DeliveryStatus::class,
        ];
    }

    public function delivery(): BelongsTo { return $this->belongsTo(Delivery::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
