<?php

namespace App\Enums;

enum OrderStatus: string
{
    case RECEIVED = 'received';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PREPARING = 'preparing';
    case SHIPPED = 'shipped';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => '受付',
            self::AWAITING_PAYMENT => '入金待ち',
            self::PREPARING => '発送準備中',
            self::SHIPPED => '発送済',
            self::CANCELLED => 'キャンセル',
        };
    }

    // 次のステータスを返す
    public function next(PaymentMethod $paymentMethod): ?self
    {
        return match ($this) {
            self::RECEIVED => match ($paymentMethod) {
                PaymentMethod::BANK_TRANSFER => self::AWAITING_PAYMENT,
                PaymentMethod::CASH_ON_DELIVERY => self::PREPARING,
            },
            self::AWAITING_PAYMENT => self::PREPARING,
            self::PREPARING => self::SHIPPED,
            self::SHIPPED, self::CANCELLED => null,
        };
    }
}
