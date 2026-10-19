<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTaxSummary extends Model
{
    protected $fillable = [
        'order_id',
        'tax_rate',
        'tax_included_amount',
        'tax_amount',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'integer',
            'tax_included_amount' => 'integer',
            'tax_amount' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }
}
