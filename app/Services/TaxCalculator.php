<?php

namespace App\Services;

use InvalidArgumentException;

class TaxCalculator
{
    /**
     * 税率別の税込金額と内税額を計算
     *
     * @param array $items
     * @return array
     */
    public function calculate(
        array $items,
        int $shippingFee = 0,
        int $codFee = 0
    ): array {
        $totals = [];

        foreach ($items as $item) {
            $rate = (int) $item['tax_rate'];
            $subtotal = (int) $item['subtotal'];

            if (!array_key_exists($rate, config('shop.tax.rates'))) {
                throw new InvalidArgumentException('対応していない税率です。');
            }

            $totals[$rate] = ($totals[$rate] ?? 0) + $subtotal;
        }

        // 送料・代引き手数料は10%対象
        $fees = $shippingFee + $codFee;
        $feeRate = config('shop.tax.shipping_rate');

        if ($fees !== 0) {
            $totals[$feeRate] = ($totals[$feeRate] ?? 0) + $fees;
        }

        ksort($totals);

        $summaries = [];

        foreach ($totals as $rate => $amount) {
            if ($amount < 0) {
                throw new InvalidArgumentException(
                    '税率別の税込金額がマイナスになっています。'
                );
            }

            if ($amount === 0) {
                continue;
            }

            // 税率ごとに1回だけ切り捨て
            $taxAmount = intdiv($amount * $rate, 100 + $rate);

            $summaries[] = [
                'tax_rate' => $rate,
                'tax_included_amount' => $amount,
                'tax_amount' => $taxAmount,
            ];
        }

        return $summaries;
    }
}
