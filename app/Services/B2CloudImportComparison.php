<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\OrderShipping;

trait B2CloudImportComparison
{
    private function checkWarnings(array $row, OrderShipping $shipping): array
    {
        $order = $shipping->order;
        if (!$order) {
            return [];
        }

        $isCod = $order->payment_method === PaymentMethod::CASH_ON_DELIVERY;
        $firstCod = $isCod
            && (int) $shipping->id === (int) $order->orderShippings()->min('id');

        $differentRecipient = $order->orderer_name !== $order->shipping_name
            || $order->orderer_postal_code !== $order->shipping_postal_code
            || $order->orderer_address !== $order->shipping_address;

        $sender = $differentRecipient ? [
            'name' => $order->orderer_name,
            'postal' => $order->orderer_postal_code,
            'address' => $order->orderer_address,
            'building' => '',
            'phone' => $order->orderer_phone,
        ] : [
            'name' => config('company.company_name_ari'),
            'postal' => config('company.postal_code'),
            'address' => config('company.address'),
            'building' => config('company.facility_name_in'),
            'phone' => config('company.phone'),
        ];

        // 発行結果CSVの列名 => [表示名, Laravel側の期待値, 比較形式]
        $fields = [
            '送り状種類' => ['送り状種類', $firstCod ? '2' : '0', 'code'],
            'クール区分' => ['クール区分', $shipping->cool_type?->b2Code() ?? '', 'code'],
            'お届け先電話番号' => ['お届け先電話番号', $order->shipping_phone, 'phone'],
            'お届け先郵便番号' => ['お届け先郵便番号', $order->shipping_postal_code, 'postal'],
            'お届け先住所' => ['お届け先住所', $order->shipping_address, 'address'],
            'お届け先名' => ['お届け先氏名', $order->shipping_name, 'text'],
            'お届け予定（指定）日' => [
                'お届け希望日',
                $order->delivery_date?->format('Y/m/d') ?? '最短日',
                'date',
            ],
            '配達時間帯' => ['お届け時間帯', $order->delivery_time_slot?->b2Code() ?? '', 'time'],
            'ご依頼主電話番号' => ['ご依頼主電話番号', $sender['phone'], 'phone'],
            'ご依頼主郵便番号' => ['ご依頼主郵便番号', $sender['postal'], 'postal'],
            'ご依頼主住所' => ['ご依頼主住所', $sender['address'] . $sender['building'], 'address'],
            'ご依頼主名' => ['ご依頼主氏名', $sender['name'], 'text'],
            '品名１' => ['品名1', $shipping->item_name, 'text'],
            '荷扱い１' => ['荷扱い1', config('b2cloud.handling.item1'), 'text'],
            '荷扱い２' => ['荷扱い2', config('b2cloud.handling.item2'), 'text'],
            'コレクト代金引換額（税込）' => [
                '代引金額',
                $firstCod
                    ? (int) $order->total_amount + (int) $order->shipping_fee + (int) $order->cod_fee
                    : '',
                'amount',
            ],
            'コレクト内消費税額等' => [
                '代引内消費税額',
                $firstCod ? (int) $order->taxSummaries()->sum('tax_amount') : '',
                'amount',
            ],
            'ご請求先顧客コード' => ['請求先顧客コード', config('b2cloud.billing.customer_code'), 'code'],
            'ご請求先分類コード' => ['請求先分類コード', config('b2cloud.billing.classification_code'), 'code'],
            '運賃管理番号' => ['運賃管理番号', config('b2cloud.billing.freight_management_number'), 'code'],
        ];

        $warnings = [];
        foreach ($fields as $column => [$label, $current, $type]) {
            if (!array_key_exists($column, $row)) {
                continue;
            }

            $csv = $row[$column] ?? '';
            if ($type === 'address') {
                $buildingColumn = $column === 'お届け先住所'
                    ? 'お届け先住所（アパートマンション名）'
                    : 'ご依頼主住所（アパートマンション名）';
                $csv .= $row[$buildingColumn] ?? '';
            }

            if (
                $this->normalizeB2CompareValue($current, $type)
                !== $this->normalizeB2CompareValue($csv, $type)
            ) {
                $warnings[] = [
                    'label' => $label,
                    'current' => (string) ($current ?? ''),
                    'csv' => (string) $csv,
                ];
            }
        }

        return $warnings;
    }

    private function normalizeB2CompareValue(mixed $value, string $type): string
    {
        $value = trim((string) ($value ?? ''));

        if ($type === 'date') {
            if ($value === '' || $value === '最短日') {
                return '最短日';
            }
            $value = str_replace('-', '/', $value);
            $date = \DateTimeImmutable::createFromFormat('!Y/n/j', $value);
            return $date ? $date->format('Y/m/d') : $value;
        }

        if ($type === 'time') {
            return $value === '0000' ? '' : $value;
        }

        if ($type === 'phone' || $type === 'postal') {
            return preg_replace('/[\s　\-()（）]+/u', '', mb_convert_kana($value, 'n'));
        }

        if ($type === 'amount') {
            return $value === '' ? '' : str_replace(',', '', $value);
        }

        if ($type === 'code') {
            // 請求先コード等は先頭ゼロも意味があるため文字列として比較
            return $value;
        }

        // 氏名・住所・品名などは空白と全角英数字の表記差を吸収
        return preg_replace('/[\s　]+/u', '', mb_convert_kana($value, 'as'));
    }
}
