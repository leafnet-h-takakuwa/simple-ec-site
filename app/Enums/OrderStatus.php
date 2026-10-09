<?php

namespace App\Enums;

enum OrderStatus: string
{
    case RECEIVED = 'received';
    case PREPARING = 'preparing';
    case SHIPPED = 'shipped';

    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => '受付',
            self::PREPARING => '発送準備中',
            self::SHIPPED => '発送済',
        };
    }
}
