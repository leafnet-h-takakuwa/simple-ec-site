<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notice;
use Illuminate\Support\Facades\View;

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
        // ナビバーのお知らせ新着件数
        View::composer('layouts.app', function ($view) {
            $newNoticeCount = Notice::where('is_published', true)
                ->whereBetween('published_at', [
                    today()->subDays(config('shop.notices.new_days')),
                    today(),
                ])
                ->count();

            $view->with('newNoticeCount', $newNoticeCount);
        });

        // TCPDFの許可パス追加
        if (! defined('K_ALLOWED_PATHS')) {
            define('K_ALLOWED_PATHS', [
                public_path('images'),
                storage_path('fonts'),
            ]);
        }
    }
}
