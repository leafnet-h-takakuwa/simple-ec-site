@extends('layouts.admin')

@section('title', 'ダッシュボード')

@section('content')

    {{-- ページタイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            ダッシュボード
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            注文状況と商品情報を確認できます。
        </p>
    </div>

    {{-- 注文状況 --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-5">

        {{-- 進行中の注文 --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 lg:p-6">
            <h2 class="text-sm font-bold lg:text-base">対応中の注文</h2>
            <a href="{{ route('admin.orders.index', ['status' => 'active']) }}">
                <p class="mt-4 text-3xl font-bold text-green-800 lg:text-4xl">
                    {{ number_format($active_orders) }}
                    <span class="text-sm font-normal text-slate-500">件</span>
                </p>
            </a>
            <p class="mt-3 text-xs text-slate-500">
                受付・入金待ち・発送準備中
            </p>
        </div>

        {{-- 設定期間内の注文 --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 lg:p-6">
            <h2 class="text-sm font-bold lg:text-base">直近{{ $recent_order_days }}日間の注文</h2>
            <a href="{{ route('admin.orders.index', ['date_from' => $recent_order_from->toDateString()]) }}">
                <p class="mt-4 text-3xl font-bold text-green-800 lg:text-4xl">
                    {{ number_format($recent_orders) }}
                    <span class="text-sm font-normal text-slate-500">件</span>
                </p>
            </a>
            <p class="mt-3 text-xs text-slate-500">
                過去{{ $recent_order_days }}日間の受付件数
            </p>
        </div>

        {{-- 全注文 --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 lg:p-6">
            <h2 class="text-sm font-bold lg:text-base">全注文</h2>
            <a href="{{ route('admin.orders.index') }}">
                <p class="mt-4 text-3xl font-bold text-green-800 lg:text-4xl">
                    {{ number_format($total_orders) }}
                    <span class="text-sm font-normal text-slate-500">件</span>
                </p>
            </a>
            <p class="mt-3 text-xs text-slate-500">
                これまでの累計件数
            </p>
        </div>

        {{-- 発送済み --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 lg:p-6">
            <h2 class="text-sm font-bold lg:text-base">発送済みの注文</h2>
            <a href="{{ route('admin.orders.index', ['status' => \App\Enums\OrderStatus::SHIPPED->value]) }}">
                <p class="mt-4 text-3xl font-bold text-green-800 lg:text-4xl">
                    {{ number_format($shipped_orders) }}
                    <span class="text-sm font-normal text-slate-500">件</span>
                </p>
            </a>
            <p class="mt-3 text-xs text-slate-500">
                発送済みの累計件数
            </p>
        </div>
    </div>

    {{-- 商品情報 --}}
    <section class="mt-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="text-lg font-bold lg:text-xl">
            商品情報の最終更新
        </h2>

        <p class="mt-4 text-xl font-bold text-green-800 lg:text-2xl">
            {{ $last_product_update ? \Carbon\Carbon::parse($last_product_update)->format('Y/m/d H:i') : '更新情報なし' }}
        </p>

        <p class="mt-3 text-xs text-slate-500">
            ※ 登録されている商品の最終更新日時
        </p>
    </section>

    {{-- 管理メニュー --}}
    <section class="mt-10">
        <h2 class="mb-5 text-xl font-bold">
            管理メニュー
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 p-5">
                <a href="{{ route('admin.orders.create') }}">
                    <div class="flex items-center gap-2 font-bold text-green-800">
                        注文登録 <x-arrow direction="right" />
                    </div>
                </a>
                <p class="mt-2 text-sm text-slate-500">
                    電話・FAXの注文登録
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 p-5">
                <a href="{{ route('admin.products.index') }}">
                    <div class="flex items-center gap-2 font-bold text-green-800">
                        商品管理へ <x-arrow direction="right" />
                    </div>
                </a>
                <p class="mt-2 text-sm text-slate-500">
                    商品登録・編集・公開設定
                </p>
            </div>
        </div>
    </section>
    {{-- プッシュ通知設定 --}}
    <div id="push-config" data-public-key="{{ config('webpush.vapid.public_key') }}"
        data-subscribe-url="{{ route('admin.push.subscribe') }}"
        data-unsubscribe-url="{{ route('admin.push.unsubscribe') }}" data-test-url="{{ route('admin.push.test') }}">
    </div>
    <section class="mt-10 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <div class="flex items-center gap-2">
            <h2 class="text-lg font-bold lg:text-xl">
                プッシュ通知設定
            </h2>
        </div>

        <p class="mt-3 text-sm text-slate-500">
            新しい注文が入ったとき、この端末に通知します。
            ログアウト後も通知を受け取れます。
        </p>

        <div class="mt-5 flex flex-wrap items-center gap-3">
            <span id="push-status" class="badge badge-ghost">
                確認中
            </span>

            <span id="push-status-message" class="text-sm text-slate-500">
                通知の設定状況を確認しています。
            </span>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <button type="button" id="push-enable" class="btn btn-success" disabled>
                通知を有効にする
            </button>

            <button type="button" id="push-test" class="btn btn-outline btn-neutral" hidden>
                テスト通知を送信
            </button>

            <button type="button" id="push-disable" class="btn btn-outline btn-error" hidden>
                この端末の通知を解除
            </button>
        </div>
    </section>
@endsection
