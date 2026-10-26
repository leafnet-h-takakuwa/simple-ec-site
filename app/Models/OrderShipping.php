<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\CoolType;

class OrderShipping extends Model
{
    protected $fillable = [
        'order_id',
        'tracking_number',
        'cool_type',
        'item_name',
    ];

    protected $casts = [
        'cool_type' => CoolType::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
