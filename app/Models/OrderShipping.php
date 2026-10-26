<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\CoolType;

class OrderShipping extends Model
{
    protected $fillable = [
        'order_id',
        'tracking_number',
        'cool_type',
        'item_name',
    ];

    protected $casts = [
        'cool_type' => CoolType::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * B2クラウドCSV出力に必要な項目の不足一覧
     *
     * @return string[]
     */
    public function b2ExportMissingFields(): array
    {
        $order = $this->order;
        $missing = [];

        if (!$order) {
            return ['注文情報が見つかりません'];
        }

        if ($this->cool_type === null) {
            $missing[] = '温度帯未設定';
        }

        if (blank($this->item_name)) {
            $missing[] = '品名未設定';
        }

        if (blank($order->shipping_name)) {
            $missing[] = 'お届け先氏名未設定';
        }

        if (blank($order->shipping_postal_code)) {
            $missing[] = 'お届け先郵便番号未設定';
        }

        if (blank($order->shipping_address)) {
            $missing[] = 'お届け先住所未設定';
        }

        if (blank($order->shipping_phone)) {
            $missing[] = 'お届け先電話番号未設定';
        }

        if ($order->shipping_fee === null) {
            $missing[] = '送料未設定';
        }

        if (
            $order->payment_method === \App\Enums\PaymentMethod::CASH_ON_DELIVERY
            && $order->cod_fee === null
        ) {
            $missing[] = '代引手数料未設定';
        }

        return $missing;
    }

    /**
     * B2クラウドCSVを出力できるか
     */
    public function canExportB2(): bool
    {
        return empty($this->b2ExportMissingFields());
    }
}
