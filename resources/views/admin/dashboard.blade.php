@extends('layouts.admin')

@section('title', 'ダッシュボード')

@section('content')
    {{-- ページタイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            ダッシュボード
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            注文状況と販売実績を確認できます。
        </p>
    </div>

    {{-- ステータス別の注文件数 --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:gap-5">
        @foreach ([['label' => '受付', 'count' => $received_orders, 'status' => \App\Enums\OrderStatus::RECEIVED->value, 'description' => '対応待ちの注文'], ['label' => '入金待ち', 'count' => $awaiting_payment_orders, 'status' => \App\Enums\OrderStatus::AWAITING_PAYMENT->value, 'description' => '入金確認待ち'], ['label' => '発送準備中', 'count' => $preparing_orders, 'status' => \App\Enums\OrderStatus::PREPARING->value, 'description' => '発送待ちの注文']] as $card)
            <a href="{{ route('admin.orders.index', ['status' => $card['status']]) }}"
                class="block rounded-xl border border-slate-200 bg-white p-5 transition hover:border-green-300 hover:shadow-sm lg:p-6">
                <h2 class="text-sm font-bold lg:text-base">
                    {{ $card['label'] }}
                </h2>
                <p class="mt-4 text-3xl font-bold text-green-800 lg:text-4xl">
                    {{ number_format($card['count']) }}
                    <span class="text-sm font-normal text-slate-500">件</span>
                </p>
                <p class="mt-3 text-xs text-slate-500">
                    {{ $card['description'] }}
                </p>
            </a>
        @endforeach
    </div>

    {{-- 要確認 --}}
    <section class="mt-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <div class="mb-5 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-amber-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v4m0 4h.01M10.29 3.86 1.82 18.5A2 2 0 0 0 3.55 21h16.9a2 2 0 0 0 1.73-2.5L13.71 3.86a2 2 0 0 0-3.42 0Z" />
            </svg>
            <h2 class="text-lg font-bold lg:text-xl">要確認</h2>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach ([
            [
                'label' => '受付から24時間以上経過',
                'count' => $old_received_orders,
                'url' => route('admin.orders.index', ['attention' => 'old_received']),
            ],
            [
                'label' => '金額未確定の注文',
                'count' => $unconfirmed_amount_orders,
                'url' => route('admin.orders.index', ['attention' => 'unconfirmed_amount']),
            ],
            [
                'label' => '送り状番号未登録',
                'count' => $missing_tracking_orders,
                'url' => route('admin.orders.index', ['attention' => 'missing_tracking']),
            ],
            [
                'label' => 'メール送信失敗（直近7日）',
                'count' => $failed_mails,
                'url' => route('admin.mail-histories.index', [
                    'status' => \App\Enums\MailStatus::FAILED->value,
                    'date_from' => now()->subDays(7)->toDateString(),
                ]),
            ],
        ] as $item)
                <a href="{{ $item['url'] }}"
                    class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-4 py-3 transition hover:bg-slate-100">
                    <span class="text-sm font-medium text-green-800">
                        {{ $item['label'] }}
                    </span>

                    <span class="badge {{ $item['count'] > 0 ? 'badge-warning' : 'badge-ghost' }} shrink-0">
                        {{ number_format($item['count']) }}件
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- 直近5件の注文 --}}
    <section class="mt-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold lg:text-xl">
                直近5件の注文
            </h2>
            <a href="{{ route('admin.orders.index') }}"
                class="inline-flex items-center gap-1 text-sm font-medium text-green-800 hover:underline">
                注文一覧を見る
                <x-arrow direction="right" />
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>注文番号</th>
                        <th>注文日時</th>
                        <th>注文者</th>
                        <th>注文経路</th>
                        <th>ステータス</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latest_orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}"
                                    class="font-semibold text-green-800 hover:underline">
                                    #{{ sprintf('%08d', $order->id) }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap text-sm">
                                {{ $order->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td class="text-sm">
                                {{ $order->orderer_name }}
                            </td>
                            <td class="whitespace-nowrap text-sm">
                                {{ $order->order_source?->label() ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="badge badge-ghost">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-sm text-slate-500">
                                注文はまだありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- 今月の販売実績 --}}
    <section class="mt-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold lg:text-xl">
                今月の販売実績
            </h2>
            <span class="badge badge-ghost">
                {{ now()->format('Y年n月') }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <p class="text-sm text-slate-500">販売件数</p>
                <p class="mt-2 text-3xl font-bold text-green-800">
                    {{ number_format($monthly_sales_count) }}
                    <span class="text-sm font-normal text-slate-500">件</span>
                </p>
            </div>
            <div>
                <p class="text-sm text-slate-500">販売金額</p>
                <p class="mt-2 text-3xl font-bold text-green-800">
                    ¥{{ number_format($monthly_sales_amount) }}
                </p>
            </div>
        </div>

        <div class="my-6 border-t border-slate-200"></div>

        <h3 class="mb-3 text-sm font-bold">
            注文経路別の販売件数
        </h3>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach (\App\Enums\OrderSource::cases() as $source)
                <div class="rounded-lg bg-slate-50 px-4 py-3 text-center">
                    <p class="text-sm text-slate-500">
                        {{ $source->label() }}
                    </p>
                    <p class="mt-2 text-lg font-bold">
                        {{ number_format($monthly_sales_by_source[$source->value] ?? 0) }}
                        <span class="text-xs font-normal text-slate-500">件</span>
                    </p>
                </div>
            @endforeach
        </div>

        <p class="mt-5 text-xs text-slate-500">
            ※ 発送済みの注文を対象に、発送済みへの変更日時を基準に集計しています。
            販売金額には送料・代引手数料を含みます。
        </p>
    </section>

    {{-- 管理メニュー --}}
    <section class="mt-10">
        <h2 class="mb-5 text-xl font-bold">
            管理メニュー
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <a href="{{ route('admin.orders.create') }}"
                class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-green-300 hover:shadow-sm">
                <div class="flex items-center gap-2 font-bold text-green-800">
                    注文登録 <x-arrow direction="right" />
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    電話・FAXなどの注文を登録
                </p>
            </a>

            <a href="{{ route('admin.products.create') }}"
                class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-green-300 hover:shadow-sm">
                <div class="flex items-center gap-2 font-bold text-green-800">
                    商品登録 <x-arrow direction="right" />
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    新しい商品を追加
                </p>
            </a>

            {{-- お知らせ登録のルート名は実際の定義に合わせて調整 --}}
            <a href="{{ route('admin.notices.create') }}"
                class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-green-300 hover:shadow-sm">
                <div class="flex items-center gap-2 font-bold text-green-800">
                    お知らせ登録 <x-arrow direction="right" />
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    新しいお知らせを掲載
                </p>
            </a>
        </div>
    </section>

    {{-- プッシュ通知設定 --}}
    <div id="push-config" data-public-key="{{ config('webpush.vapid.public_key') }}"
        data-subscribe-url="{{ route('admin.push.subscribe') }}"
        data-unsubscribe-url="{{ route('admin.push.unsubscribe') }}" data-test-url="{{ route('admin.push.test') }}">
    </div>

    <section class="mt-10 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="text-lg font-bold lg:text-xl">
            プッシュ通知設定
        </h2>

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
