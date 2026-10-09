<?php

namespace App\Enums;

enum ProductCategory: string
{
    case GYORAN = 'gyoran';
    case YAMAMOTO = 'yamamoto';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::GYORAN => '魚卵',
            self::YAMAMOTO => 'ヤマモト食品',
            self::OTHER => 'その他商品',
        };
    }
}
