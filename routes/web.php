<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Models\Product;

// 認証不要ルート
Route::get('/', function () {
    // TODO ひとまずの表示確認、あとでコントローラーへ
    $products = Product::where('is_active', true)
        ->latest()
        ->take(3)
        ->get();
    return view('welcome', compact('products'));
})->name('home');

// 一般会員向け要認証ルート
Route::middleware(['auth'])->group(function () {

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
