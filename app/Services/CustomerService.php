<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;

class CustomerService
{

    /**
     * 注文者と配送先を顧客台帳に反映する。
     * 呼び出し元の注文保存トランザクション内で実行すること。
     */
    public function registerFromOrder(Order $order): void
    {
        $orderer = [
            'name' => $order->orderer_name,
            'postal_code' => $order->orderer_postal_code,
            'address' => $order->orderer_address,
            'phone' => $order->orderer_phone,
            'email' => $order->orderer_email,
        ];

        $recipient = [
            'name' => $order->shipping_name,
            'postal_code' => $order->shipping_postal_code,
            'address' => $order->shipping_address,
            'phone' => $order->shipping_phone,
            'email' => null,
        ];

        $this->register($orderer, 'is_orderer');

        // 注文者本人への配送は、配送先としての利用実績に含めない。
        if (! $this->samePerson($orderer, $recipient)) {
            $this->register($recipient, 'is_recipient');
        }
    }

    private function register(array $data, string $role): void
    {
        $customer = $this->findMatchingCustomer($data);

        if ($customer) {
            // 既存の氏名・住所・連絡先は上書きしない。
            if (! $customer->{$role}) {
                $customer->update([$role => true]);
            }

            return;
        }

        Customer::create([
            ...$data,
            'is_orderer' => $role === 'is_orderer',
            'is_recipient' => $role === 'is_recipient',
        ]);
    }


    private function normalizeName(string $name): string
    {
        return str_replace([' ', '　'], '', $name);
    }

    private function normalizeNumber(string $value): string
    {
        return str_replace(['-', '－'], '', mb_convert_kana($value, 'n'));
    }

    private function samePerson(array $first, array $second): bool
    {
        return $this->normalizeName($first['name']) === $this->normalizeName($second['name'])
            && $this->normalizeNumber($first['phone']) === $this->normalizeNumber($second['phone'])
            && $this->normalizeNumber($first['postal_code']) === $this->normalizeNumber($second['postal_code']);
    }

    public function findMatchingCustomer(array $data, ?int $exceptId = null): ?Customer
    {
        return Customer::query()
            ->whereRaw("REPLACE(REPLACE(name, ' ', ''), '　', '') = ?", [
                $this->normalizeName($data['name']),
            ])
            ->whereRaw("REPLACE(REPLACE(TRANSLATE(phone, '０１２３４５６７８９', '0123456789'), '-', ''), '－', '') = ?", [
                $this->normalizeNumber($data['phone']),
            ])
            ->whereRaw("REPLACE(REPLACE(TRANSLATE(postal_code, '０１２３４５６７８９', '0123456789'), '-', ''), '－', '') = ?", [
                $this->normalizeNumber($data['postal_code']),
            ])
            ->when($exceptId !== null, fn($query) => $query->where('id', '!=', $exceptId))
            ->first();
    }
}
