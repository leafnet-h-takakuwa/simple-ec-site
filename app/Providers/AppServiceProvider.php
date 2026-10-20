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
        View::composer('layouts.app', function ($view) {
            $newNoticeCount = Notice::where('is_published', true)
                ->whereBetween('published_at', [
                    today()->subDays(config('shop.notices.new_days')),
                    today(),
                ])
                ->count();

            $view->with('newNoticeCount', $newNoticeCount);
        });
    }
}
