<?php

namespace App\Enums;

enum MailType: string
{
    case ORDER_RECEIVED = 'order_received';
    case ORDER_NOTICE = 'order_notice';
    case PAYMENT_GUIDE = 'payment_guide';
    case TOTAL_CONFIRMED = 'total_confirmed';
    case SHIPPING_NOTICE = 'shipping_notice';
    case ORDER_CANCELLED = 'order_cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ORDER_RECEIVED => '注文受付完了',
            self::ORDER_NOTICE => '新規注文通知',
            self::PAYMENT_GUIDE => 'お振込先のご案内',
            self::TOTAL_CONFIRMED => 'お支払い金額確定',
            self::SHIPPING_NOTICE => '商品発送通知',
            self::ORDER_CANCELLED => '注文キャンセル',
        };
    }
}
