<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\MailType;
use App\Enums\MailStatus;

class MailHistory extends Model
{
    protected $fillable = [
        'type',
        'status',
        'from_address',
        'to_address',
        'cc_address',
        'bcc_address',
        'subject',
        'body',
        'error_message',
        'sent_at',
        'resent_from_id',
        'send_note',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => MailType::class,
            'status' => MailStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resentFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'resent_from_id');
    }
}
