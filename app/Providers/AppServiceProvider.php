<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notice;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Services\CartService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ナビバーのお知らせ新着件数・カート数量
        View::composer('layouts.app', function ($view) {

            // お知らせ新着件数
            $newNoticeCount = Notice::where('is_published', true)
                ->whereBetween('published_at', [
                    today()->subDays(config('shop.notices.new_days')),
                    today(),
                ])
                ->count();

            // カート数量
            $cartCount = 0;

            // 管理者以外のみ取得
            if (!Auth::check() || !Auth::user()->is_admin) {
                $cartService = app(CartService::class);

                $cartItems = $cartService->items(request());

                $cartCount = $cartItems->sum('quantity');
            }

            $view->with([
                'newNoticeCount' => $newNoticeCount,
                'cartCount' => $cartCount,
            ]);
        });

        // ポリモーフィックリレーション
        Relation::enforceMorphMap([
            'order' => Order::class,
            'order_status_history' => OrderStatusHistory::class,
            'user' => User::class,
        ]);

        // TCPDFの許可パス追加
        if (! defined('K_ALLOWED_PATHS')) {
            define('K_ALLOWED_PATHS', [
                public_path('images'),
                storage_path('fonts'),
            ]);
        }
    }
}
