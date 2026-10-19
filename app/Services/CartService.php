<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CartService
{
    public function cart(Request $request): ?Cart
    {
        return $request->user()
            ? Cart::where('user_id', $request->user()->id)->first()
            : null;
    }

    /** 非公開・削除済み商品も判別できる形で取得し、購入可否は呼び出し元で判断する。 */
    public function items(Request $request): Collection
    {
        if ($request->user()) {
            $cart = $this->cart($request);

            return $cart ? $cart->cartItems()->with('product')->get() : collect();
        }

        $session_cart = $request->session()->get('cart', []);
        $products = Product::whereIn('id', array_keys($session_cart))->get()->keyBy('id');
        $items = collect();

        foreach ($session_cart as $product_id => $quantity) {
            $product = $products->get($product_id);
            // 従来どおり、ゲストカートの削除済み商品は読み飛ばす。
            if ($product === null) {
                continue;
            }

            $item = new CartItem(['product_id' => $product->id, 'quantity' => $quantity]);
            $item->setRelation('product', $product);
            $items->push($item);
        }

        return $items;
    }

    public function availableItems(Request $request): Collection
    {
        return $this->items($request)->filter(fn (CartItem $item) => $item->product?->is_active);
    }

    public function total(Collection $items): int
    {
        return $items->sum(fn (CartItem $item) => $item->subtotal());
    }

    /** 確認画面と注文確定時に比較する商品・価格・数量。 */
    public function snapshot(Collection $items): array
    {
        return $items->map(fn (CartItem $item) => [
            'product_id' => $item->product_id,
            'price' => $item->product->price,
            'quantity' => $item->quantity,
        ])->sortBy('product_id')->values()->all();
    }
}
