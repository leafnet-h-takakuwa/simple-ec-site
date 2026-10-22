<?php

namespace App\Models;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'checkout_token',
        'status',
        'payment_method',
        'total_amount',
        'orderer_name',
        'orderer_postal_code',
        'orderer_address',
        'orderer_phone',
        'orderer_email',
        'shipping_name',
        'shipping_postal_code',
        'shipping_address',
        'shipping_phone',
        'order_source',
        'memo',
        'shipping_fee',
        'cod_fee',
        'customer_note',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'total_amount' => 'integer',
            'order_source' => OrderSource::class,
            'shipping_fee' => 'integer',
            'cod_fee' => 'integer',
        ];
    }

    // 注文した会員（ゲスト注文の場合はnull）
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    // 注文明細
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // 配送情報
    public function orderShippings(): HasMany
    {
        return $this->hasMany(OrderShipping::class)->orderBy('id');
    }

    // 税率別集計
    public function taxSummaries(): HasMany
    {
        return $this->hasMany(OrderTaxSummary::class);
    }

    // ステータス変更履歴
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    // 注文に直接紐づくメール送信履歴
    public function mailHistories(): MorphMany
    {
        return $this->morphMany(MailHistory::class, 'related');
    }

    // 管理画面：編集できるステータスを定義
    public function isEditable(): bool
    {
        return !in_array($this->status, [
            OrderStatus::SHIPPED,
            OrderStatus::CANCELLED,
        ], true);
    }

    // 管理画面：編集できるが警告が必要なステータスを定義
    public function needsEditWarning(): bool
    {
        return in_array($this->status, [
            OrderStatus::AWAITING_PAYMENT,
            OrderStatus::PREPARING,
        ], true);
    }

    // 納品書が発行可能かチェック
    public function canIssueDeliverySlip(): bool
    {
        // 発行可能な注文ステータス
        if (!in_array($this->status, [
            OrderStatus::AWAITING_PAYMENT,
            OrderStatus::PREPARING,
            OrderStatus::SHIPPED,
        ], true)) {
            return false;
        }

        // 送料が未確定
        if ($this->shipping_fee === null) {
            return false;
        }

        // 代引の場合、代引手数料も確定している必要がある
        if (
            $this->payment_method === PaymentMethod::CASH_ON_DELIVERY
            && $this->cod_fee === null
        ) {
            return false;
        }

        return true;
    }
}
