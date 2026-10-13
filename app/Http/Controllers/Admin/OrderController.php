<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
