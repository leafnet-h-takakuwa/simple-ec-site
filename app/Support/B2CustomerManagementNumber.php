<?php

namespace App\Support;

class B2CustomerManagementNumber
{
    public static function generate(int $orderId, int $shippingId): string
    {
        return sprintf('TS-%08d-%08d', $orderId, $shippingId);
    }
}
