<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\TaxCalculator;
use App\Support\ContactRules;
use App\Support\InputLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private TaxCalculator $taxCalculator
    ) {}

    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('orderItems')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(config('shop.pagination.orders'));

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['orderItems', 'orderShippings', 'taxSummaries'])
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

        $cart_items = $this->cartService->availableItems($request);

        // カートが空なら注文不可
        if ($cart_items->isEmpty()) {
            return redirect()->route('carts.index')
                ->with('error', 'カートに商品がありません。');
        }

        // 注文者情報・配送先情報の検証
        $validated = $request->validate([
            ...ContactRules::forPrefix('orderer_'),
            'orderer_email' => ['required', 'email', 'max:' . InputLimits::SHORT_TEXT],

            'shipping_same' => ['required', 'in:0,1'],

            ...ContactRules::forPrefix('shipping_', ['required_if:shipping_same,0', 'nullable']),
            'customer_note' => ['nullable', 'string', 'max:' . InputLimits::CUSTOMER_NOTE],
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

        // 注文情報をセッションに保存
        $request->session()->put('order', [
            ...$validated,
            'customer_note' => $validated['customer_note'] ?? null,
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

        $cart_items = $this->cartService->availableItems($request);

        // カートが空ならカート画面へ
        if ($cart_items->isEmpty()) {
            return redirect()->route('carts.index')
                ->with('error', 'カートに商品がありません。');
        }

        // 合計金額を計算
        $total_amount = $this->cartService->total($cart_items);

        // 確認画面に表示した商品情報をセッションに保存
        $confirmed_items = $this->cartService->snapshot($cart_items);
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

        $cart = $this->cartService->cart($request);
        $cart_items = $this->cartService->items($request);

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
        $total_amount = $this->cartService->total($cart_items);

        // 確認画面で表示した商品情報を取得
        $confirmed_items = $request->session()->get('confirmed_items');

        // 現在のカートの商品情報を取得
        $current_items = $this->cartService->snapshot($cart_items);

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
                    'customer_note' => $order_data['customer_note'] ?? null,
                ]);

                // 注文明細登録
                $tax_items = [];

                foreach ($cart_items as $cart_item) {
                    $subtotal = $cart_item->subtotal();
                    $tax_rate = $cart_item->product->tax_rate;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cart_item->product->id,
                        'product_name' => $cart_item->product->name,
                        'price' => $cart_item->product->price,
                        'quantity' => $cart_item->quantity,
                        'subtotal' => $subtotal,
                        'tax_rate' => $tax_rate,
                    ]);

                    // 税率別集計用
                    $tax_items[] = [
                        'subtotal' => $subtotal,
                        'tax_rate' => $tax_rate,
                    ];
                }

                // 税率別集計を計算して保存
                $tax_summaries = $this->taxCalculator->calculate(
                    $tax_items,
                    shippingFee: $order->shipping_fee ?? 0,
                    codFee: $order->cod_fee ?? 0
                );

                $order->taxSummaries()->createMany($tax_summaries);

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
