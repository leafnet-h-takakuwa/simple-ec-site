<?php

namespace App\Enums;

enum OrderStatus: string
{
    case RECEIVED = 'received';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PREPARING = 'preparing';
    case SHIPPED = 'shipped';

    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => '受付',
            self::AWAITING_PAYMENT => '入金待ち',
            self::PREPARING => '発送準備中',
            self::SHIPPED => '発送済',
        };
    }
}
