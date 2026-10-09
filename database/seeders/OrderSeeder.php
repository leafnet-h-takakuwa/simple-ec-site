<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@example.com')->firstOrFail();

        $products = Product::query()
            ->get()
            ->keyBy('name');

        $orders = [
            [
                'user_id' => $user->id,
                'status' => OrderStatus::RECEIVED,
                'orderer_name' => 'テストユーザー',
                'orderer_postal_code' => '030-0801',
                'orderer_address' => '青森県青森市新町1-1-1',
                'orderer_phone' => '017-000-0001',
                'orderer_email' => 'user@example.com',
                'shipping_name' => 'テストユーザー',
                'shipping_postal_code' => '030-0801',
                'shipping_address' => '青森県青森市新町1-1-1',
                'shipping_phone' => '017-000-0001',
                'items' => [
                    ['name' => '特選すじこ 500g', 'quantity' => 1],
                    ['name' => 'ねぶた漬 250g', 'quantity' => 2],
                ],
            ],
            [
                'user_id' => null,
                'status' => OrderStatus::PREPARING,
                'orderer_name' => '青森 太郎',
                'orderer_postal_code' => '036-8003',
                'orderer_address' => '青森県弘前市駅前町2-2-2',
                'orderer_phone' => '0172-000-0002',
                'orderer_email' => null,
                'shipping_name' => '青森 太郎',
                'shipping_postal_code' => '036-8003',
                'shipping_address' => '青森県弘前市駅前町2-2-2',
                'shipping_phone' => '0172-000-0002',
                'items' => [
                    ['name' => '特選たらこ 500g', 'quantity' => 1],
                    ['name' => 'つがる漬 250g', 'quantity' => 1],
                ],
            ],
            [
                'user_id' => $user->id,
                'status' => OrderStatus::SHIPPED,
                'orderer_name' => 'テストユーザー',
                'orderer_postal_code' => '030-0801',
                'orderer_address' => '青森県青森市新町1-1-1',
                'orderer_phone' => '017-000-0001',
                'orderer_email' => 'user@example.com',
                'shipping_name' => '青森 花子',
                'shipping_postal_code' => '031-0032',
                'shipping_address' => '青森県八戸市三日町3-3-3',
                'shipping_phone' => '0178-000-0003',
                'items' => [
                    ['name' => '甘口すじこ 500g', 'quantity' => 1],
                    ['name' => '特選たらこ 250g', 'quantity' => 1],
                    ['name' => '鮭ほぐし身 150g', 'quantity' => 1],
                ],
            ],
            [
                'user_id' => null,
                'status' => OrderStatus::RECEIVED,
                'orderer_name' => '弘前 次郎',
                'orderer_postal_code' => '036-8004',
                'orderer_address' => '青森県弘前市大町4-4-4',
                'orderer_phone' => '0172-000-0004',
                'orderer_email' => null,
                'shipping_name' => '弘前 次郎',
                'shipping_postal_code' => '036-8004',
                'shipping_address' => '青森県弘前市大町4-4-4',
                'shipping_phone' => '0172-000-0004',
                'items' => [
                    ['name' => '辛子明太子 500g', 'quantity' => 2],
                    ['name' => 'ねぶた漬 250g', 'quantity' => 1],
                ],
            ],
            [
                'user_id' => $user->id,
                'status' => OrderStatus::PREPARING,
                'orderer_name' => 'テストユーザー',
                'orderer_postal_code' => '030-0801',
                'orderer_address' => '青森県青森市新町1-1-1',
                'orderer_phone' => '017-000-0001',
                'orderer_email' => 'user@example.com',
                'shipping_name' => 'テストユーザー',
                'shipping_postal_code' => '030-0801',
                'shipping_address' => '青森県青森市新町1-1-1',
                'shipping_phone' => '017-000-0001',
                'items' => [
                    ['name' => '甘口すじこ 250g', 'quantity' => 1],
                    ['name' => 'つがる漬 250g', 'quantity' => 1],
                    ['name' => '塩鮭切り身 5切', 'quantity' => 1],
                ],
            ],
        ];

        DB::transaction(function () use ($orders, $products) {
            foreach ($orders as $data) {
                $items = [];

                foreach ($data['items'] as $item) {
                    $product = $products->get($item['name']);

                    if (!$product) {
                        throw new \RuntimeException(
                            "商品が見つかりません: {$item['name']}"
                        );
                    }

                    $items[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->price,
                        'quantity' => $item['quantity'],
                        'subtotal' => $product->price * $item['quantity'],
                    ];
                }

                $totalAmount = array_sum(array_column($items, 'subtotal'));

                $orderData = $data;
                unset($orderData['items']);

                $orderData['total_amount'] = $totalAmount;

                $order = Order::create($orderData);

                $order->orderItems()->createMany($items);
            }
        });
    }
}
