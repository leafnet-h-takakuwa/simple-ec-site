<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\Admin\MailHistoryController as AdminMailHistoryController;
use App\Http\Controllers\Admin\FaxOrderFormController;
use App\Http\Controllers\FaxOrderFormPdfController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Middleware\EnsureEmailVerifiedForCheckout;
use App\Http\Controllers\EmailChangeController;
use App\Http\Controllers\Admin\PushSubscriptionController;
use App\Http\Controllers\Admin\B2CloudController;

/**
 * 認証不要ルート
 */
// TOPページ
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

// ご案内
Route::view('/guide/order', 'guide.order')->name('guide.order');
Route::view('/guide/shipping', 'guide.shipping')->name('guide.shipping');
Route::view('/guide/shop', 'guide.shop')->name('guide.shop');
Route::view('/legal/commerce', 'legal.commerce')->name('legal.commerce');
Route::view('/legal/privacy', 'legal.privacy')->name('legal.privacy');

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

// 注文(会員の場合はメール認証必須)
Route::middleware([EnsureEmailVerifiedForCheckout::class])->group(function () {
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
});

// FAX注文書
Route::get('/fax-order-form/pdf', [FaxOrderFormPdfController::class, 'show'])
    ->name('fax-order-form.pdf');

// お知らせ
Route::get('/notices', [NoticeController::class, 'index'])
    ->name('notices.index');
Route::get('/notices/{notice}', [NoticeController::class, 'show'])
    ->name('notices.show');

/**
 * 一般会員向け要認証ルート
 */
Route::middleware(['auth'])->group(function () {

    // 注文履歴
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])
        ->name('orders.show');
    // 会員情報
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // メールアドレス変更
    Route::post('/profile/email', [EmailChangeController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('profile.email.update');
    Route::get('/profile/email/verify/{user}/{token}', [EmailChangeController::class, 'verify'])
        ->middleware('signed')
        ->name('profile.email.verify');

    // 退会
    Route::get('/profile/withdraw', [ProfileController::class, 'withdraw'])
        ->name('profile.withdraw');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/**
 * 管理者向け要認証ルート
 */
Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ダッシュボード
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // プッシュ通知
        Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])
            ->name('push.subscribe');
        Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])
            ->name('push.unsubscribe');
        Route::post('/push/test', [PushSubscriptionController::class, 'test'])
            ->middleware('throttle:5,1')
            ->name('push.test');

        // 注文管理
        Route::get('/orders', [AdminOrderController::class, 'index'])
            ->name('orders.index');
        Route::get('/orders/create', [AdminOrderController::class, 'create'])
            ->name('orders.create');
        Route::post('/orders', [AdminOrderController::class, 'store'])
            ->name('orders.store');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])
            ->name('orders.show');
        Route::get('/orders/{order}/delivery-slip', [AdminOrderController::class, 'deliverySlip'])
            ->name('orders.deliverySlip');
        Route::get('/orders/{order}/items/edit', [AdminOrderController::class, 'editItems'])
            ->name('orders.items.edit');
        Route::put('/orders/{order}/items', [AdminOrderController::class, 'updateItems'])
            ->name('orders.items.update');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
            ->name('orders.updateStatus');
        Route::patch('/orders/{order}/memo', [AdminOrderController::class, 'updateMemo'])
            ->name('orders.updateMemo');
        Route::get('/orders/{order}/customer/edit', [AdminOrderController::class, 'editCustomer'])
            ->name('orders.customer.edit');
        Route::put('/orders/{order}/customer', [AdminOrderController::class, 'updateCustomer'])
            ->name('orders.customer.update');
        Route::patch('/orders/{order}/payment', [AdminOrderController::class, 'updatePayment'])
            ->name('orders.payment.update');
        Route::patch('/orders/{order}/fees', [AdminOrderController::class, 'updateFees'])
            ->name('orders.fees.update');
        Route::patch('/orders/{order}/b2-shippings', [AdminOrderController::class, 'updateB2Shippings'])
            ->name('orders.b2Shippings.update');
        Route::patch('/orders/{order}/shippings', [AdminOrderController::class, 'updateShippings'])
            ->name('orders.shippings.update');
        // 商品管理
        Route::get('/products', [AdminProductController::class, 'index'])
            ->name('products.index');
        Route::get('/products/create', [AdminProductController::class, 'create'])
            ->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])
            ->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])
            ->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])
            ->name('products.update');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])
            ->name('products.destroy');

        // 会員情報
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])
            ->withTrashed()
            ->name('users.show');

        // FAX注文書管理
        Route::get('/fax-order-form', [FaxOrderFormController::class, 'index'])
            ->name('fax-order-form.index');
        Route::put('/fax-order-form', [FaxOrderFormController::class, 'update'])
            ->name('fax-order-form.update');
        Route::post('/fax-order-form/preview', [FaxOrderFormPdfController::class, 'preview'])
            ->name('fax-order-form.preview');

        // メール送信履歴
        Route::get('/mail-histories', [AdminMailHistoryController::class, 'index'])
            ->name('mail-histories.index');
        Route::get('/mail-histories/{mailHistory}', [AdminMailHistoryController::class, 'show'])
            ->name('mail-histories.show');

        // お知らせ管理
        Route::resource('notices', AdminNoticeController::class)
            ->except(['show'])
            ->names('notices');

        // 顧客台帳
        Route::resource('customers', CustomerController::class)
            ->names('customers');

        // B2クラウド連携
        Route::get('/b2-cloud', [B2CloudController::class, 'index'])
            ->name('b2-cloud.index');
        Route::post('/b2-cloud/export', [B2CloudController::class, 'export'])
            ->name('b2-cloud.export');
        // CSV取り込み
        Route::get('/b2-cloud/import', [B2CloudController::class, 'import'])
            ->name('b2-cloud.import');
        Route::post('/b2-cloud/import/preview', [B2CloudController::class, 'previewImport'])
            ->name('b2-cloud.import.preview');
        Route::post('/b2-cloud/import/execute', [B2CloudController::class, 'executeImport'])
            ->name('b2-cloud.import.execute');

        // パスワード変更
        Route::view('/password', 'admin.password.edit')
            ->name('password.edit');
    });

require __DIR__ . '/auth.php';
