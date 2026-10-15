<?php

namespace App\Enums;

enum OrderSource: string
{
    case Web = 'web';
    case Phone = 'phone';
    case Fax = 'fax';
    case Store = 'store';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'WEB',
            self::Phone => '電話',
            self::Fax => 'FAX',
            self::Store => '店頭',
            self::Other => 'その他',
        };
    }
}
