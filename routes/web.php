<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Models\Product;

use App\Http\Controllers\ProductController;

use App\Enums\ProductCategory;


/**
 * 認証不要ルート
 */
// TOPページ
Route::get('/', function () {
    // TODO ひとまずの表示確認、あとでコントローラーへ
    $products = Product::where('is_active', true)
        ->latest()
        ->take(3)
        ->get();

    $categories = ProductCategory::cases();

    return view('welcome', [
        'products' => $products,
        'categories' => $categories,
    ]);
})->name('home');

// 商品一覧
Route::get('/products', [ProductController::class, 'index'])
    ->name('products.index');
// 商品詳細
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

/**
 * 一般会員向け要認証ルート
 */
Route::middleware(['auth'])->group(function () {

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
