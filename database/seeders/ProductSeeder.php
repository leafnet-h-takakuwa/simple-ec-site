<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => '特選すじこ 500g',
                'description' => '厳選したすじこを丁寧に仕上げました。',
                'category' => 'sujiko',
                'volume' => '500g',
                'price' => 4500,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '特選すじこ 250g',
                'description' => 'ご家庭で楽しみやすい少量サイズです。',
                'category' => 'sujiko',
                'volume' => '250g',
                'price' => 2500,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '甘口すじこ 500g',
                'description' => 'まろやかな味付けのすじこです。',
                'category' => 'sujiko',
                'volume' => '500g',
                'price' => 4200,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '甘口すじこ 250g',
                'description' => '甘口すじこのお試しサイズです。',
                'category' => 'sujiko',
                'volume' => '250g',
                'price' => 2300,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '特選たらこ 500g',
                'description' => '粒立ちのよいたらこを使用しています。',
                'category' => 'tarako',
                'volume' => '500g',
                'price' => 3500,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '特選たらこ 250g',
                'description' => 'ご家庭向けの食べきりサイズです。',
                'category' => 'tarako',
                'volume' => '250g',
                'price' => 1900,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '辛子明太子 500g',
                'description' => 'ほどよい辛さに仕上げた明太子です。',
                'category' => 'tarako',
                'volume' => '500g',
                'price' => 3800,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '辛子明太子 250g',
                'description' => '少量から楽しめる明太子です。',
                'category' => 'tarako',
                'volume' => '250g',
                'price' => 2100,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '塩鮭切り身 5切',
                'description' => '朝食やお弁当におすすめの塩鮭です。',
                'category' => 'other',
                'volume' => '5切',
                'price' => 1800,
                'image_path' => null,
                'is_active' => true,
            ],
            [
                'name' => '鮭ほぐし身 150g',
                'description' => 'ご飯のお供にぴったりの鮭ほぐし身です。',
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
