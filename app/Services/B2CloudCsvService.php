<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\DeliveryTimeSlot;
use App\Models\Order;
use App\Models\OrderShipping;
use Illuminate\Support\Collection;
use RuntimeException;
use App\Support\B2CustomerManagementNumber;


class B2CloudCsvService
{
    private const HEADERS = [
        'お客様管理番号',
        '送り状種類',
        'クール区分',
        '伝票番号',
        '出荷予定日',
        'お届け予定日',
        '配達時間帯',
        'お届け先コード',
        'お届け先電話番号',
        'お届け先電話番号枝番',
        'お届け先郵便番号',
        'お届け先住所',
        'お届け先アパートマンション名',
        'お届け先会社・部門1',
        'お届け先会社・部門2',
        'お届け先名',
        'お届け先名(ｶﾅ)',
        '敬称',
        'ご依頼主コード',
        'ご依頼主電話番号',
        'ご依頼主電話番号枝番',
        'ご依頼主郵便番号',
        'ご依頼主住所',
        'ご依頼主アパートマンション',
        'ご依頼主名',
        'ご依頼主名(ｶﾅ)',
        '品名コード１',
        '品名1',
        '品名コード2',
        '品名2',
        '荷扱い1',
        '荷扱い2',
        '記事',
        'ｺﾚｸﾄ代金引換額（税込)',
        '内消費税額等',
        '止置き',
        '営業所コード',
        '発行枚数',
        '個数口表示フラグ',
        '請求先顧客コード',
        '請求先分類コード',
        '運賃管理番号',
    ];

    /**
     * @param Collection<int, OrderShipping> $shippings
     * @param array<int, array{amount: int, tax: int}> $collectAmounts
     */
    public function generate(
        Collection $shippings,
        string $shippingDate,
        array $collectAmounts = []
    ): string {
        $stream = fopen('php://temp', 'w+');

        if ($stream === false) {
            throw new RuntimeException('CSVの作成に失敗しました。');
        }

        try {
            fputcsv($stream, self::HEADERS);



            foreach ($shippings as $shipping) {
                $order = $shipping->order;
                $isCod = $order->payment_method === PaymentMethod::CASH_ON_DELIVERY;

                // 注文全体で最もIDが小さい個口だけをコレクト扱いにする
                $firstCod = $isCod
                    && (int) $shipping->id === (int) $order->first_shipping_id;

                $row = array_fill(0, count(self::HEADERS), '');

                $row[0] = B2CustomerManagementNumber::generate($order->id, $shipping->id);
                $row[1] = $firstCod ? '2' : '0';
                $row[2] = $shipping->cool_type->b2Code();
                // 出荷予定日
                $row[4] = $shippingDate;

                // お届け希望日（未指定なら最短日）
                $row[5] = $order->delivery_date
                    ? $order->delivery_date->format('Y/m/d')
                    : '最短日';

                // 配達時間帯（未指定なら空欄）
                $row[6] = $order->delivery_time_slot?->b2Code() ?? '';

                // お届け先
                $row[8] = $order->shipping_phone;
                $row[10] = $order->shipping_postal_code;
                $row[11] = $order->shipping_address;
                $row[15] = $order->shipping_name;

                // 依頼主
                if ($this->hasDifferentRecipient($order)) {
                    $row[19] = $order->orderer_phone;
                    $row[21] = $order->orderer_postal_code;
                    $row[22] = $order->orderer_address;
                    $row[24] = $order->orderer_name;
                } else {
                    $row[19] = config('company.phone');
                    $row[21] = config('company.postal_code');
                    $row[22] = config('company.address');
                    $row[23] = config('company.facility_name_in');
                    $row[24] = config('company.company_name_ari');
                }

                $row[27] = $shipping->item_name;
                $row[30] = config('b2cloud.handling.item1');
                $row[31] = config('b2cloud.handling.item2');

                if ($firstCod) {
                    if (!isset($collectAmounts[$order->id])) {
                        throw new RuntimeException(
                            "注文 #" . sprintf('%08d', $order->id) . " の代引金額・税額が未確定です。"
                        );
                    }

                    $row[33] = (string) $collectAmounts[$order->id]['amount'];
                    $row[34] = (string) $collectAmounts[$order->id]['tax'];
                }

                $row[39] = config('b2cloud.billing.customer_code');
                $row[40] = config('b2cloud.billing.classification_code');
                $row[41] = config('b2cloud.billing.freight_management_number');

                fputcsv($stream, $row);
            }

            rewind($stream);
            $csv = stream_get_contents($stream);

            if ($csv === false) {
                throw new RuntimeException('CSVの読み込みに失敗しました。');
            }

            $converted = mb_convert_encoding(
                $csv,
                config('b2cloud.encoding'),
                'UTF-8'
            );

            return $converted;
        } finally {
            fclose($stream);
        }
    }

    private function hasDifferentRecipient(Order $order): bool
    {
        // 注文者と配送先の住所・氏名を比較
        return $order->orderer_name !== $order->shipping_name
            || $order->orderer_postal_code !== $order->shipping_postal_code
            || $order->orderer_address !== $order->shipping_address;
    }
}
