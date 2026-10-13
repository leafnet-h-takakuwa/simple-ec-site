<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Models\Product;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
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
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// カート
Route::get('/carts', [CartController::class, 'index'])
    ->name('carts.index');
Route::post('/carts', [CartController::class, 'store'])
    ->name('carts.store');
Route::patch('/carts/{product_id}', [CartController::class, 'update'])
    ->name('carts.update');
Route::delete('/carts/{product_id}', [CartController::class, 'destroy'])
    ->name('carts.destroy');

// 注文
Route::get('/orders/create', [OrderController::class, 'create'])
    ->name('orders.create');
Route::post('/orders/confirm', [OrderController::class, 'confirm'])
    ->name('orders.confirm');
Route::get('/orders/confirm', [OrderController::class, 'showConfirm'])
    ->name('orders.confirm.show');
Route::post('/orders', [OrderController::class, 'store'])
    ->name('orders.store');
Route::get('/orders/complete', [OrderController::class, 'complete'])
    ->name('orders.complete');

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
