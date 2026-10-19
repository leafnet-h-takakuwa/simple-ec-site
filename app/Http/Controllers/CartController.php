<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request)
    {
        if ($request->user()?->is_admin === true) {
            abort(403);
        }

        $cart_items = $this->cartService->availableItems($request);
        return view('carts.index', [
            'cart_items' => $cart_items,
            'total' => $this->cartService->total($cart_items),
        ]);
    }


    public function store(Request $request)
    {
        $max_quantity = config('cart.max_quantity');

        // 入力値のバリデーション
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $max_quantity],
        ]);

        $product_id = $validated['product_id'];
        $quantity = $validated['quantity'];

        // 商品が販売中か確認
        $product = Product::where('id', $product_id)
            ->where('is_active', true)
            ->firstOrFail();

        if (Auth::check()) {
            // 管理者は購入不可
            if (Auth::user()->is_admin === true) {
                abort(403);
            }

            // 会員のカートを取得
            $cart = Cart::where('user_id', Auth::id())->first();

            // カートがなければ作成
            if ($cart === null) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                ]);
            }

            // 同じ商品がカートに存在するか確認
            $cart_item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product_id)
                ->first();

            if ($cart_item !== null) {
                // 既存の商品なら数量を加算
                if ($cart_item->quantity + $quantity > $max_quantity) {
                    return back()->with('error', "同じ商品は{$max_quantity}個までカートに入れられます。");
                }
                $cart_item->quantity += $quantity;
                $cart_item->save();
            } else {
                // 新しい商品なら明細を作成
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                ]);
            }
        } else {
            // ゲストのカートをセッションから取得
            $session_cart = $request->session()->get('cart', []);

            // 同じ商品が存在するか確認
            if (isset($session_cart[$product_id])) {
                // 既存の商品なら数量を加算
                if ($session_cart[$product_id] + $quantity > $max_quantity) {
                    return back()->with('error', "同じ商品は{$max_quantity}個までカートに入れられます。");
                }
                $session_cart[$product_id] += $quantity;
            } else {
                // 新しい商品なら追加
                $session_cart[$product_id] = $quantity;
            }

            // セッションに保存
            $request->session()->put('cart', $session_cart);
        }

        // カート画面へ移動
        return redirect()->route('carts.index')
            ->with('success', '商品をカートに追加しました。');
    }


    public function update(Request $request, $product_id)
    {
        // 数量のバリデーション
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . config('cart.max_quantity')],
        ]);

        $quantity = $validated['quantity'];

        if (Auth::check()) {
            // 管理者は操作禁止
            if (Auth::user()->is_admin === true) {
                abort(403);
            }

            // ログインユーザーのカートを取得
            $cart = Cart::where('user_id', Auth::id())->firstOrFail();

            // カート内の対象商品を取得
            $cart_item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product_id)
                ->firstOrFail();

            // 数量を更新
            $cart_item->quantity = $quantity;
            $cart_item->save();
        } else {
            // ゲストのカートをセッションから取得
            $session_cart = $request->session()->get('cart', []);

            // 対象商品がカートに存在するか確認
            if (!isset($session_cart[$product_id])) {
                abort(404);
            }

            // 数量を更新
            $session_cart[$product_id] = $quantity;

            // セッションに保存
            $request->session()->put('cart', $session_cart);
        }

        // カート画面へ戻る
        return redirect()->route('carts.index')
            ->with('success', 'カートの数量を変更しました。');
    }


    public function destroy(Request $request, $product_id)
    {
        if (Auth::check()) {
            // 管理者は操作禁止
            if (Auth::user()->is_admin === true) {
                abort(403);
            }

            // ログインユーザーのカートを取得
            $cart = Cart::where('user_id', Auth::id())->firstOrFail();

            // カート内の対象商品を取得
            $cart_item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product_id)
                ->firstOrFail();

            // カートから商品を削除
            $cart_item->delete();
        } else {
            // ゲストのカートをセッションから取得
            $session_cart = $request->session()->get('cart', []);

            // 対象商品が存在するか確認
            if (!isset($session_cart[$product_id])) {
                abort(404);
            }

            // 対象商品を削除
            unset($session_cart[$product_id]);

            // セッションに保存
            $request->session()->put('cart', $session_cart);
        }

        // カート画面へ戻る
        return redirect()->route('carts.index')
            ->with('success', '商品をカートから削除しました。');
    }
}
