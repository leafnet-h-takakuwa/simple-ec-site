<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
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
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'total_amount' => 'integer',
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
}
