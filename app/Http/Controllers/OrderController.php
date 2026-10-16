<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('orderItems')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with('orderItems')
            ->findOrFail($id);

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    public function create(Request $request)
    {
        // 管理者は注文不可
        if (Auth::check() && Auth::user()->is_admin) {
            abort(403);
        }

        return view('orders.create', [
            'user' => Auth::user(),
            'order' => $request->session()->get('order', []),
        ]);
    }

    public function confirm(Request $request)
    {
        // 管理者は注文不可
        if (Auth::check() && Auth::user()->is_admin) {
            abort(403);
        }

        // カート内の商品を取得
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();

            $cart_items = $cart
                ? CartItem::with('product')
                ->where('cart_id', $cart->id)
                ->get()
                : collect();
        } else {
            $cart_items = collect();

            foreach ($request->session()->get('cart', []) as $product_id => $quantity) {
                $product = Product::find($product_id);

                if (!$product) {
                    continue;
                }

                $cart_item = new CartItem();
                $cart_item->product_id = $product->id;
                $cart_item->quantity = $quantity;
                $cart_item->setRelation('product', $product);

                $cart_items->push($cart_item);
            }
        }

        // 販売中の商品だけを対象にする
        $cart_items = $cart_items->filter(function ($cart_item) {
            return $cart_item->product
                && $cart_item->product->is_active;
        });

        // カートが空なら注文不可
        if ($cart_items->isEmpty()) {
            return redirect()->route('carts.index')
                ->with('error', 'カートに商品がありません。');
        }

        // 注文者情報・配送先情報の検証
        $validated = $request->validate([
            'orderer_name' => ['required', 'string', 'max:255'],
            'orderer_postal_code' => ['required', 'string', 'max:8'],
            'orderer_address' => ['required', 'string', 'max:255'],
            'orderer_phone' => ['required', 'string', 'max:20'],
            'orderer_email' => ['required', 'email', 'max:255'],

            'shipping_same' => ['required', 'in:0,1'],

            'shipping_name' => ['required_if:shipping_same,0', 'nullable', 'string', 'max:255'],
            'shipping_postal_code' => ['required_if:shipping_same,0', 'nullable', 'string', 'max:8'],
            'shipping_address' => ['required_if:shipping_same,0', 'nullable', 'string', 'max:255'],
            'shipping_phone' => ['required_if:shipping_same,0', 'nullable', 'string', 'max:20'],
        ]);

        // 注文者と配送先が同じ場合は注文者情報をコピー
        if ($validated['shipping_same'] === '1') {
            $validated['shipping_name'] = $validated['orderer_name'];
            $validated['shipping_postal_code'] = $validated['orderer_postal_code'];
            $validated['shipping_address'] = $validated['orderer_address'];
            $validated['shipping_phone'] = $validated['orderer_phone'];

            $payment_method = PaymentMethod::CASH_ON_DELIVERY;
        } else {
            $payment_method = PaymentMethod::BANK_TRANSFER;
        }

        // 合計金額を計算
        $total_amount = $cart_items->sum(function ($cart_item) {
            return $cart_item->product->price * $cart_item->quantity;
        });

        // 注文情報をセッションに保存
        $request->session()->put('order', [
            ...$validated,
            'payment_method' => $payment_method->value,
        ]);

        // 注文確認画面へリダイレクト
        return redirect()->route('orders.confirm.show');
    }

    public function showConfirm(Request $request)
    {
        // 管理者は注文不可
        if (Auth::check() && Auth::user()->is_admin) {
            abort(403);
        }

        // 注文情報がなければ入力画面へ
        $order = $request->session()->get('order');

        if (!$order) {
            return redirect()->route('orders.create');
        }

        // カート内の商品を取得
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();

            $cart_items = $cart
                ? CartItem::with('product')
                ->where('cart_id', $cart->id)
                ->get()
                : collect();
        } else {
            $cart_items = collect();

            foreach ($request->session()->get('cart', []) as $product_id => $quantity) {
                $product = Product::find($product_id);

                if (!$product) {
                    continue;
                }

                $cart_item = new CartItem();
                $cart_item->product_id = $product->id;
                $cart_item->quantity = $quantity;
                $cart_item->setRelation('product', $product);

                $cart_items->push($cart_item);
            }
        }

        // 販売中の商品だけを対象にする
        $cart_items = $cart_items->filter(function ($cart_item) {
            return $cart_item->product
                && $cart_item->product->is_active;
        });

        // カートが空ならカート画面へ
        if ($cart_items->isEmpty()) {
            return redirect()->route('carts.index')
                ->with('error', 'カートに商品がありません。');
        }

        // 合計金額を計算
        $total_amount = $cart_items->sum(function ($cart_item) {
            return $cart_item->product->price * $cart_item->quantity;
        });

        // 確認画面に表示した商品情報をセッションに保存
        $confirmed_items = $cart_items->map(function ($cart_item) {
            return [
                'product_id' => $cart_item->product->id,
                'price' => $cart_item->product->price,
                'quantity' => $cart_item->quantity,
            ];
        })->sortBy('product_id')->values()->all();
        $request->session()->put('confirmed_items', $confirmed_items);

        return view('orders.confirm', [
            'order' => $order,
            'payment_method' => PaymentMethod::from($order['payment_method']),
            'cart_items' => $cart_items,
            'total_amount' => $total_amount,
        ]);
    }

    public function store(Request $request)
    {
        // 管理者は注文不可
        if (Auth::check() && Auth::user()->is_admin) {
            abort(403);
        }

        // 送料・手数料への同意を確認
        $request->validate([
            'fee_agreement' => ['accepted'],
        ], [
            'fee_agreement.accepted' => '送料・手数料についての同意が必要です。',
        ]);

        // 確認画面で保存した注文情報を取得
        $order_data = $request->session()->get('order');

        if (!$order_data) {
            return redirect()->route('orders.create')
                ->with('error', '注文情報を入力してください。');
        }

        // カートの商品を取得
        $cart = null;
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();

            $cart_items = $cart
                ? CartItem::with('product')
                ->where('cart_id', $cart->id)
                ->get()
                : collect();
        } else {
            $cart_items = collect();

            foreach ($request->session()->get('cart', []) as $product_id => $quantity) {
                $product = Product::find($product_id);

                if (!$product) {
                    continue;
                }

                $cart_item = new CartItem();
                $cart_item->product_id = $product->id;
                $cart_item->quantity = $quantity;
                $cart_item->setRelation('product', $product);

                $cart_items->push($cart_item);
            }
        }

        // 販売停止・削除された商品が含まれていないか確認
        $has_unavailable_item = $cart_items->contains(function ($cart_item) {
            return !$cart_item->product
                || !$cart_item->product->is_active;
        });

        if ($has_unavailable_item) {
            $request->session()->forget('confirmed_items');

            return redirect()->route('carts.index')
                ->with('error', '購入できない商品が含まれています。カートの内容をご確認ください。');
        }

        if ($cart_items->isEmpty()) {
            return redirect()->route('carts.index')
                ->with('error', 'カートに商品がありません。');
        }

        // 合計金額を計算
        $total_amount = $cart_items->sum(function ($cart_item) {
            return $cart_item->product->price * $cart_item->quantity;
        });

        // 確認画面で表示した商品情報を取得
        $confirmed_items = $request->session()->get('confirmed_items');

        // 現在のカートの商品情報を取得
        $current_items = $cart_items->map(function ($cart_item) {
            return [
                'product_id' => $cart_item->product->id,
                'price' => $cart_item->product->price,
                'quantity' => $cart_item->quantity,
            ];
        })->sortBy('product_id')->values()->all();

        // 確認画面と注文確定時の商品情報を比較
        if ($confirmed_items === null || $confirmed_items !== $current_items) {
            $request->session()->forget('confirmed_items');

            return redirect()->route('carts.index')
                ->with('error', 'カートの商品内容または価格が変更されました。内容をご確認のうえ、もう一度お手続きください。');
        }

        try {
            // 注文・注文明細・会員カート削除をまとめて実行
            $order = DB::transaction(function () use ($order_data, $cart_items, $total_amount, $cart) {

                // 注文ヘッダー登録
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'status' => OrderStatus::RECEIVED,
                    'payment_method' => $order_data['payment_method'],
                    'cod_fee' => $order_data['payment_method'] === PaymentMethod::BANK_TRANSFER->value
                        ? 0
                        : null,
                    'total_amount' => $total_amount,
                    'orderer_name' => $order_data['orderer_name'],
                    'orderer_postal_code' => $order_data['orderer_postal_code'],
                    'orderer_address' => $order_data['orderer_address'],
                    'orderer_phone' => $order_data['orderer_phone'],
                    'orderer_email' => $order_data['orderer_email'],
                    'shipping_name' => $order_data['shipping_name'],
                    'shipping_postal_code' => $order_data['shipping_postal_code'],
                    'shipping_address' => $order_data['shipping_address'],
                    'shipping_phone' => $order_data['shipping_phone'],
                ]);

                // 注文明細登録
                foreach ($cart_items as $cart_item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cart_item->product->id,
                        'product_name' => $cart_item->product->name,
                        'price' => $cart_item->product->price,
                        'quantity' => $cart_item->quantity,
                        'subtotal' => $cart_item->product->price * $cart_item->quantity,
                    ]);
                }

                // 会員カートを空にする
                if ($cart) {
                    CartItem::where('cart_id', $cart->id)->delete();
                }

                return $order;
            });
        } catch (\Throwable $e) {
            // 詳細なエラーはログに記録
            Log::error('注文登録処理でエラーが発生しました。', [
                'exception' => $e,
            ]);

            // ユーザーには一般的なエラーメッセージを表示
            return redirect()->route('carts.index')
                ->with('error', '注文処理中にエラーが発生しました。お手数ですが、もう一度お試しください。');
        }

        // DBへの登録が成功したらセッションを整理
        $request->session()->forget(['order', 'cart', 'confirmed_items']);

        // 完了画面に表示する注文IDを保存
        $request->session()->put('completed_order_id', $order->id);

        return redirect()->route('orders.complete');
    }

    public function complete(Request $request)
    {
        // 注文完了時の注文IDを取得
        $order_id = $request->session()->get('completed_order_id');

        if (!$order_id) {
            return redirect()->route('home');
        }

        // 注文情報と注文明細を取得
        $order = Order::with('orderItems')->findOrFail($order_id);

        return view('orders.complete', [
            'order' => $order,
        ]);
    }
}
