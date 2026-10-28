<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MailStatus;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\MailHistory;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ステータス別の注文件数
        $received_orders = Order::where('status', OrderStatus::RECEIVED)->count();

        $awaiting_payment_orders = Order::where('status', OrderStatus::AWAITING_PAYMENT)->count();

        $preparing_orders = Order::where('status', OrderStatus::PREPARING)->count();

        // 受付から24時間以上経過
        $old_received_orders = Order::where('status', OrderStatus::RECEIVED)
            ->where('created_at', '<=', now()->subDay())
            ->count();

        // 要確認：金額未確定
        $unconfirmed_amount_orders = Order::whereIn('status', [
            OrderStatus::AWAITING_PAYMENT->value,
            OrderStatus::PREPARING->value,
        ])
            ->where(function ($query) {
                $query->whereNull('shipping_fee')
                    ->orWhere(function ($query) {
                        $query->where('payment_method', PaymentMethod::CASH_ON_DELIVERY->value)
                            ->whereNull('cod_fee');
                    });
            })
            ->count();

        // 要確認：送り状番号未登録
        // 1個口でも未登録があれば、その注文を1件として数える
        $missing_tracking_orders = Order::where('status', OrderStatus::PREPARING)
            ->where(function ($query) {
                $query->whereDoesntHave('orderShippings')
                    ->orWhereHas('orderShippings', function ($query) {
                        $query->whereNull('tracking_number')
                            ->orWhere('tracking_number', '');
                    });
            })
            ->count();

        // 要確認：直近7日間のメール送信失敗
        $failed_mails = MailHistory::where('status', MailStatus::FAILED)
            ->whereDate('created_at', '>=', now()->subDays(7)->toDateString())
            ->count();

        // 直近5件の注文（全ステータス対象）
        $latest_orders = Order::latest()->limit(5)->get();

        // 今月の販売実績
        // 発送済みへの変更履歴を基準に集計する
        $month_start = now()->startOfMonth();
        $month_end = now()->endOfMonth();

        $shipped_order_ids = OrderStatusHistory::where('to_status', OrderStatus::SHIPPED->value)
            ->whereBetween('created_at', [$month_start, $month_end])
            ->distinct()
            ->pluck('order_id');

        // 現在も発送済みの注文だけを対象にする
        $monthly_sales_orders = Order::where('status', OrderStatus::SHIPPED)
            ->whereIn('id', $shipped_order_ids)
            ->get();

        $monthly_sales_count = $monthly_sales_orders->count();
        // 今月の販売金額（商品代金 + 送料 + 代引手数料）
        $monthly_sales_amount = $monthly_sales_orders->sum(function ($order) {
            return $order->total_amount
                + $order->shipping_fee
                + ($order->cod_fee ?? 0);
        });

        // 注文経路別の販売件数
        $monthly_sales_by_source = [];

        foreach (OrderSource::cases() as $source) {
            $monthly_sales_by_source[$source->value] = $monthly_sales_orders
                ->where('order_source', $source)
                ->count();
        }

        return view('admin.dashboard', [
            'received_orders' => $received_orders,
            'awaiting_payment_orders' => $awaiting_payment_orders,
            'preparing_orders' => $preparing_orders,
            'old_received_orders' => $old_received_orders,
            'unconfirmed_amount_orders' => $unconfirmed_amount_orders,
            'missing_tracking_orders' => $missing_tracking_orders,
            'failed_mails' => $failed_mails,
            'latest_orders' => $latest_orders,
            'monthly_sales_count' => $monthly_sales_count,
            'monthly_sales_amount' => $monthly_sales_amount,
            'monthly_sales_by_source' => $monthly_sales_by_source,
        ]);
    }
}
