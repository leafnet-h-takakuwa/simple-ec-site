<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ProductCategory;

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
    ];

    protected function casts(): array
    {
        return [
            'category' => ProductCategory::class,
            'price' => 'integer',
            'is_active' => 'boolean',
            'is_standard' => 'boolean',
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

    // その商品が1か月以内に登録されたものかチェック
    public function isNew(): bool
    {
        return $this->created_at !== null
            && $this->created_at->gte(now()->subDays(30));
    }
}
