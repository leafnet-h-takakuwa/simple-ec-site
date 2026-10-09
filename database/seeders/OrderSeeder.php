<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // ProductSeederで登録した商品を取得
        $products = Product::query()
            ->get()
            ->keyBy('name');

        // テスト用会員
        $user = User::where('email', 'user@example.com')->firstOrFail();

        $orders = [
            [
                'user_id' => null,
                'status' => OrderStatus::RECEIVED,
                'orderer_name' => '佐藤 太郎',
                'orderer_postal_code' => '030-0801',
                'orderer_address' => '青森県青森市新町1-1-1',
                'orderer_phone' => '090-0000-0001',
                'orderer_email' => null,
                'shipping_name' => '佐藤 太郎',
                'shipping_postal_code' => '030-0801',
                'shipping_address' => '青森県青森市新町1-1-1',
                'shipping_phone' => '090-0000-0001',
                'items' => [
                    ['name' => '特選すじこ 500g', 'quantity' => 1],
                    ['name' => '特選すじこ 250g', 'quantity' => 1],
                ],
            ],
            [
                'user_id' => null,
                'status' => OrderStatus::PREPARING,
                'orderer_name' => '鈴木 花子',
                'orderer_postal_code' => '036-8003',
                'orderer_address' => '青森県弘前市駅前町2-2',
                'orderer_phone' => '090-0000-0002',
                'orderer_email' => 'hanako@example.com',
                'shipping_name' => '鈴木 一郎',
                'shipping_postal_code' => '030-0801',
                'shipping_address' => '青森県青森市新町3-3',
                'shipping_phone' => '090-0000-0003',
                'items' => [
                    ['name' => '特選たらこ 500g', 'quantity' => 1],
                    ['name' => '辛子明太子 250g', 'quantity' => 1],
                ],
            ],
            [
                'user_id' => $user?->id,
                'status' => OrderStatus::SHIPPED,
                'orderer_name' => '田中 次郎',
                'orderer_postal_code' => '030-0845',
                'orderer_address' => '青森県青森市緑1-1',
                'orderer_phone' => '090-0000-0004',
                'orderer_email' => 'jiro@example.com',
                'shipping_name' => '田中 次郎',
                'shipping_postal_code' => '030-0845',
                'shipping_address' => '青森県青森市緑1-1',
                'shipping_phone' => '090-0000-0004',
                'items' => [
                    ['name' => '甘口すじこ 500g', 'quantity' => 1],
                    ['name' => '特選たらこ 250g', 'quantity' => 1],
                    ['name' => '鮭ほぐし身 150g', 'quantity' => 1],
                ],
            ],
            [
                'user_id' => $user?->id,
                'status' => OrderStatus::RECEIVED,
                'orderer_name' => '田中 次郎',
                'orderer_postal_code' => '030-0845',
                'orderer_address' => '青森県青森市緑1-1',
                'orderer_phone' => '090-0000-0004',
                'orderer_email' => 'jiro@example.com',
                'shipping_name' => '田中 次郎',
                'shipping_postal_code' => '030-0845',
                'shipping_address' => '青森県青森市緑1-1',
                'shipping_phone' => '090-0000-0004',
                'items' => [
                    ['name' => '特選たらこ 500g', 'quantity' => 1],
                ],
            ],
            [
                'user_id' => null,
                'status' => OrderStatus::SHIPPED,
                'orderer_name' => '高橋 美咲',
                'orderer_postal_code' => '031-0086',
                'orderer_address' => '青森県八戸市八日町4-4',
                'orderer_phone' => '090-0000-0005',
                'orderer_email' => null,
                'shipping_name' => '高橋 美咲',
                'shipping_postal_code' => '031-0086',
                'shipping_address' => '青森県八戸市八日町4-4',
                'shipping_phone' => '090-0000-0005',
                'items' => [
                    ['name' => '甘口すじこ 250g', 'quantity' => 1],
                    ['name' => '特選たらこ 250g', 'quantity' => 1],
                ],
            ],
        ];

        DB::transaction(function () use ($orders, $products) {
            foreach ($orders as $data) {
                $items = [];
                $totalAmount = 0;

                foreach ($data['items'] as $item) {
                    $product = $products->get($item['name']);

                    if (!$product) {
                        throw new RuntimeException(
                            "商品が見つかりません: {$item['name']}"
                        );
                    }

                    $quantity = $item['quantity'];
                    $subtotal = $product->price * $quantity;

                    $items[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->price,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal,
                    ];

                    $totalAmount += $subtotal;
                }

                unset($data['items']);

                $order = Order::create([
                    ...$data,
                    'total_amount' => $totalAmount,
                ]);

                $order->orderItems()->createMany($items);
            }
        });
    }
}
