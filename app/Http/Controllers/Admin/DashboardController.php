<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        // 進行中の注文（受付・入金待ち・発送準備中）
        $active_orders = Order::whereIn('status', [
            OrderStatus::RECEIVED->value,
            OrderStatus::AWAITING_PAYMENT->value,
            OrderStatus::PREPARING->value,
        ])->count();

        // 直近7日間の注文
        $recent_orders = Order::where('created_at', '>=', now()->subDays(7))->count();

        // 全注文
        $total_orders = Order::count();

        // 発送済みの注文
        $shipped_orders = Order::where('status', OrderStatus::SHIPPED->value)->count();

        // 商品情報の最終更新
        $last_product_update = Product::max('updated_at');

        return view('admin.dashboard', [
            'active_orders' => $active_orders,
            'recent_orders' => $recent_orders,
            'total_orders' => $total_orders,
            'shipped_orders' => $shipped_orders,
            'last_product_update' => $last_product_update,
        ]);
    }
}
