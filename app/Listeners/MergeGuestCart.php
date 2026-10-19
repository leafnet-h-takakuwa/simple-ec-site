<?php

namespace App\Listeners;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class MergeGuestCart
{
    /**
     * ログイン成功時にゲストカートを会員カートへ統合する
     */
    public function handle(Login $event): void
    {
        // ゲストカートを取得
        $session_cart = session()->get('cart', []);

        // ゲストカートが空なら何もしない
        if (empty($session_cart)) {
            return;
        }

        // ログインしたユーザーを取得
        $user = $event->user;

        // 管理者の場合はマージしない
        if ($user->is_admin === true) {
            return;
        }

        // DBへの保存処理
        DB::transaction(function () use ($user, $session_cart) {

            // 会員カートを取得（存在しなければ作成）
            $cart = Cart::firstOrCreate([
                'user_id' => $user->id,
            ]);

            // ゲストカートの商品を1件ずつ処理
            foreach ($session_cart as $product_id => $guest_quantity) {

                // 販売中の商品だけを対象にする
                $product = Product::where('id', $product_id)
                    ->where('is_active', true)
                    ->first();

                if ($product === null) {
                    continue;
                }

                // 会員カートに同じ商品があるか確認
                $cart_item = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $product_id)
                    ->first();

                if ($cart_item !== null) {

                    // 同じ商品があれば設定された上限まで数量を加算
                    $cart_item->quantity = min(
                        $cart_item->quantity + $guest_quantity,
                        config('cart.max_quantity')
                    );

                    $cart_item->save();
                } else {

                    // なければ新しく追加
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product_id,
                        'quantity' => min($guest_quantity, config('cart.max_quantity')),
                    ]);
                }
            }
        });

        // DBへの保存成功後、ゲストカートを削除
        session()->forget('cart');
    }
}
