<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OrderStatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'contact_method',
        'contact_note',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mailHistories(): MorphMany
    {
        return $this->morphMany(MailHistory::class, 'related');
    }
}
