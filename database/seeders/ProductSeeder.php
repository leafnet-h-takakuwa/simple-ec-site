<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // 魚卵
            [
                'name' => '特選すじこ 500g',
                'description' => 'ご飯のお供にぴったりな、旨み豊かなすじこです。',
                'category' => 'gyoran',
                'volume' => '500g',
                'price' => 4500,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '特選すじこ 250g',
                'description' => '特選すじこをお試しいただきやすいサイズでご用意しました。',
                'category' => 'gyoran',
                'volume' => '250g',
                'price' => 2500,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '甘口すじこ 500g',
                'description' => 'まろやかな味わいに仕上げた甘口すじこです。',
                'category' => 'gyoran',
                'volume' => '500g',
                'price' => 4200,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '甘口すじこ 250g',
                'description' => '食べやすい甘口すじこの少量パックです。',
                'category' => 'gyoran',
                'volume' => '250g',
                'price' => 2300,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '特選たらこ 500g',
                'description' => '粒立ちのよいたらこを丁寧に仕上げました。',
                'category' => 'gyoran',
                'volume' => '500g',
                'price' => 3500,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '特選たらこ 250g',
                'description' => '毎日の食卓に使いやすいたらこです。',
                'category' => 'gyoran',
                'volume' => '250g',
                'price' => 1900,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '辛子明太子 500g',
                'description' => 'ほどよい辛さと旨みが特徴の辛子明太子です。',
                'category' => 'gyoran',
                'volume' => '500g',
                'price' => 3800,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '辛子明太子 250g',
                'description' => '辛子明太子を手軽に楽しめるサイズです。',
                'category' => 'gyoran',
                'volume' => '250g',
                'price' => 2100,
                'image_path' => null,
                'is_active' => true,
            ],

            // ヤマモト食品
            [
                'name' => 'ねぶた漬 250g',
                'description' => '青森の郷土の味として親しまれているねぶた漬です。',
                'category' => 'yamamoto',
                'volume' => '250g',
                'price' => 1200,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'つがる漬 250g',
                'description' => 'ご飯のお供におすすめのつがる漬です。',
                'category' => 'yamamoto',
                'volume' => '250g',
                'price' => 1100,
                'image_path' => null,
                'is_active' => true,
            ],

            // その他商品
            [
                'name' => '塩鮭切り身 5切',
                'description' => '朝食やお弁当に便利な塩鮭の切り身です。',
                'category' => 'other',
                'volume' => '5切',
                'price' => 1800,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '鮭ほぐし身 150g',
                'description' => 'ご飯やおにぎりに使いやすい鮭のほぐし身です。',
                'category' => 'other',
                'volume' => '150g',
                'price' => 850,
                'image_path' => null,
                'is_active' => false,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
