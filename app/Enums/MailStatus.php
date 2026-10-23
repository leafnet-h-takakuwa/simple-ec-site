<?php

namespace App\Enums;

enum MailStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => '送信待ち',
            self::SENT => '送信成功',
            self::FAILED => '送信失敗',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge-warning',
            self::SENT => 'badge-success',
            self::FAILED => 'badge-error',
        };
    }
}
