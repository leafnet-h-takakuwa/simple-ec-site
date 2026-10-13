<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CashOnDelivery = 'cod';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::CashOnDelivery => '代金引換',
            self::BankTransfer => '銀行振込',
        };
    }
}
