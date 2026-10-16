<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH_ON_DELIVERY = 'cod';
    case BANK_TRANSFER = 'bank_transfer';
    case PAY_AT_STORE = 'pay_at_store';

    public function label(): string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => '代金引換',
            self::BANK_TRANSFER => '銀行振込',
            self::PAY_AT_STORE => '店頭支払',
        };
    }
}
