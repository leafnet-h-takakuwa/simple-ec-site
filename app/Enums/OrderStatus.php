<?php

namespace App\Enums;

enum OrderStatus: string
{
    case RECEIVED = 'received';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PREPARING = 'preparing';
    case SHIPPED = 'shipped';
    case CANCELLED = 'cancelled';

    public static function activeValues(): array
    {
        return [self::RECEIVED->value, self::AWAITING_PAYMENT->value, self::PREPARING->value];
    }

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
                PaymentMethod::PAY_AT_STORE => self::PREPARING,
            },
            self::AWAITING_PAYMENT => self::PREPARING,
            self::PREPARING => self::SHIPPED,
            self::SHIPPED, self::CANCELLED => null,
        };
    }

    // ステータス更新時にメール送信対象かチェック
    public static function mailTypeForTransition(
        self $oldStatus,
        self $newStatus,
        PaymentMethod $paymentMethod
    ): ?string {
        if ($newStatus === self::CANCELLED) {
            return 'cancelled';
        }

        if ($oldStatus === self::RECEIVED) {
            if (
                $paymentMethod === PaymentMethod::BANK_TRANSFER
                && $newStatus === self::AWAITING_PAYMENT
            ) {
                return 'payment_guide';
            }

            if (
                $paymentMethod === PaymentMethod::CASH_ON_DELIVERY
                && $newStatus === self::PREPARING
            ) {
                return 'total_confirmed';
            }
        }

        if (
            $oldStatus === self::PREPARING
            && $newStatus === self::SHIPPED
        ) {
            return 'shipped';
        }

        return null;
    }

    public static function canSendMail(
        self $oldStatus,
        self $newStatus,
        PaymentMethod $paymentMethod
    ): bool {
        return self::mailTypeForTransition(
            $oldStatus,
            $newStatus,
            $paymentMethod
        ) === 'payment_guide';
    }
}
