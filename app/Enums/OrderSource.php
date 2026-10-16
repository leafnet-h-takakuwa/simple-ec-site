<?php

namespace App\Enums;

enum OrderSource: string
{
    case WEB = 'web';
    case PHONE = 'phone';
    case FAX = 'fax';
    case STORE = 'store';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WEB => 'WEB',
            self::PHONE => '電話',
            self::FAX => 'FAX',
            self::STORE => '店頭',
            self::OTHER => 'その他',
        };
    }
}
