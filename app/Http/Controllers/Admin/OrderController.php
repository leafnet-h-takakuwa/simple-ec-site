<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // 検索条件を取得・検証
        $conditions = $request->validate([
            'order_id' => ['nullable', 'integer', 'min:1'],
            'orderer_name' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    ...array_column(OrderStatus::cases(), 'value'),
                ]),
            ],
        ]);

        $query = Order::query();

        // 注文番号（完全一致）
        if (!empty($conditions['order_id'])) {
            $query->where('id', $conditions['order_id']);
        }

        // 注文者氏名（部分一致）
        if (!empty($conditions['orderer_name'])) {
            $query->where(
                'orderer_name',
                'ilike',
                '%' . $conditions['orderer_name'] . '%'
            );
        }

        // 注文日（開始日を含む）
        if (!empty($conditions['date_from'])) {
            $query->whereDate('created_at', '>=', $conditions['date_from']);
        }

        // 注文日（終了日を含む）
        if (!empty($conditions['date_to'])) {
            $query->whereDate('created_at', '<=', $conditions['date_to']);
        }

        // ステータス
        if (!empty($conditions['status'])) {
            if ($conditions['status'] === 'active') {
                $query->whereIn('status', [
                    OrderStatus::RECEIVED->value,
                    OrderStatus::AWAITING_PAYMENT->value,
                    OrderStatus::PREPARING->value,
                ]);
            } else {
                $query->where('status', $conditions['status']);
            }
        }

        // 新しい順に10件ずつ取得
        $orders = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'conditions' => $conditions,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function show($id)
    {
        $order = Order::with('orderItems')->findOrFail($id);

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function editItems($id)
    {
        // 注文情報と注文明細を取得
        $order = Order::with('orderItems')->findOrFail($id);

        // 商品情報取得
        $products = Product::orderBy('name')
            ->get(['id', 'name', 'price']);

        return view('admin.orders.edit-items', [
            'order' => $order,
            'products' => $products,
        ]);
    }


    public function updateItems(Request $request, $id)
    {
        // 入力値の検証
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'distinct'],
            'items.*.product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // 注文情報を取得
        $order = Order::findOrFail($id);

        DB::transaction(function () use ($order, $validated) {
            // 同じ注文への同時更新を防ぐ
            $order = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 現在の注文明細を取得
            $existingItems = $order->orderItems()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // フォームから送信された既存明細ID
            $submittedIds = collect($validated['items'])
                ->pluck('id')
                ->filter(fn($id) => $id !== null)
                ->map(fn($id) => (int) $id);

            // 別の注文の明細IDが含まれていないか確認
            if ($submittedIds->diff($existingItems->keys())->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'items' => '更新対象の注文明細が正しくありません。',
                ]);
            }

            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                // 小計をサーバー側で計算
                $subtotal = $item['price'] * $item['quantity'];

                // 合計金額を加算
                $totalAmount += $subtotal;

                $data = [
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ];

                if (!empty($item['id'])) {
                    // 既存明細を更新
                    $existingItems->get((int) $item['id'])->update($data);
                } else {
                    // 新規明細を追加
                    $order->orderItems()->create($data);
                }
            }

            if ($totalAmount < 0) {
                throw ValidationException::withMessages([
                    'items' => '注文合計金額がマイナスになるような変更はできません。',
                ]);
            }

            // 画面から削除された既存明細を削除
            $deleteIds = $existingItems->keys()->diff($submittedIds);

            if ($deleteIds->isNotEmpty()) {
                $order->orderItems()
                    ->whereIn('id', $deleteIds)
                    ->delete();
            }

            // 注文合計を更新
            $order->total_amount = $totalAmount;
            $order->save();
        });


        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', '注文明細を更新しました。');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        // 注文情報を取得
        $order = Order::findOrFail($id);

        // 変更がない場合
        if ($order->status->value === $validated['status']) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', '注文ステータスに変更はありません。');
        }

        // ステータスを変更して保存
        $order->status = OrderStatus::from($validated['status']);
        $order->save();

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', '注文ステータスを更新しました。');
    }
}
