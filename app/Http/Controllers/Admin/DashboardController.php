<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 進行中の注文（受付・入金待ち・発送準備中）
        $active_orders = Order::whereIn('status', OrderStatus::activeValues())->count();

        // 設定期間内の注文
        $recent_order_days = config('shop.dashboard.recent_order_days');
        $recent_order_from = now()->subDays($recent_order_days);
        $recent_orders = Order::where('created_at', '>=', $recent_order_from)->count();

        // 全注文
        $total_orders = Order::count();

        // 発送済みの注文
        $shipped_orders = Order::where('status', OrderStatus::SHIPPED->value)->count();

        // 商品情報の最終更新
        $last_product_update = Product::max('updated_at');

        return view('admin.dashboard', [
            'active_orders' => $active_orders,
            'recent_orders' => $recent_orders,
            'recent_order_days' => $recent_order_days,
            'recent_order_from' => $recent_order_from,
            'total_orders' => $total_orders,
            'shipped_orders' => $shipped_orders,
            'last_product_update' => $last_product_update,
        ]);
    }
}
