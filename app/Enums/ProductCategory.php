<?php

namespace App\Enums;

use App\Models\Product;

enum ProductCategory: string
{
    case YAMAMOTO = 'yamamoto';
    case GYORAN = 'gyoran';
    case IZUSHI = 'izushi';
    case PROCESSED = 'processed';
    case BOTTLED = 'bottled';
    case DRIED_FISH = 'dried_fish';
    case KAMATAYA = 'kamataya';
    case KOMBU = 'kombu';
    case SET = 'set';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::YAMAMOTO => 'ヤマモト食品',
            self::GYORAN => '魚卵',
            self::IZUSHI => '飯寿司',
            self::PROCESSED => '加工品',
            self::BOTTLED => 'ビン物',
            self::DRIED_FISH => '干物',
            self::KAMATAYA => '鎌田屋商店',
            self::KOMBU => '昆布類',
            self::SET => 'セット販売',
            self::OTHER => 'その他商品',
        };
    }

    // 公開中の商品が存在するカテゴリのみ取得
    public static function availableCases(): array
    {
        $usedCategories = Product::query()
            ->where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->map(fn($category) => $category instanceof self
                ? $category->value
                : $category)
            ->all();

        return array_values(array_filter(
            self::cases(),
            fn(self $category) => in_array($category->value, $usedCategories, true)
        ));
    }
}
