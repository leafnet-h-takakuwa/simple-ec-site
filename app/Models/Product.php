<?php

namespace App\Models;

use App\Enums\ProductCategory;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'volume',
        'price',
        'image_path',
        'is_active',
        'is_standard',
        'tax_rate',
    ];

    protected function casts(): array
    {
        return [
            'category' => ProductCategory::class,
            'price' => 'integer',
            'is_active' => 'boolean',
            'is_standard' => 'boolean',
            'tax_rate' => 'integer',
        ];
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->withSum('orderItems', 'quantity')
            ->orderByRaw('order_items_sum_quantity DESC NULLS LAST')
            ->orderByDesc('id');
    }

    public function scopeNewest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    // 設定した日数以内に登録された商品かチェック
    public function isNew(): bool
    {
        return $this->created_at !== null
            && $this->created_at->gte(now()->subDays(config('shop.products.new_days')));
    }
}
