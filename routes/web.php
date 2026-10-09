<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// 認証不要ルート
Route::get('/', function () {
    return view('welcome');
})->name('home');

// 一般会員向け要認証ルート
Route::middleware(['auth'])->group(function () {

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
