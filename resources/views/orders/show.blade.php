@extends('layouts.app')

@section('title', '注文詳細')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8">

        {{-- 一覧へ戻る --}}
        <a href="{{ route('orders.index') }}"
            class="mb-6 inline-flex items-center gap-2 text-sm text-primary hover:underline">
            <x-arrow direction="left" /> 注文履歴へ戻る
        </a>

        {{-- ページタイトル --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold lg:text-3xl">
                注文詳細
            </h1>
            <p class="mt-2 text-sm text-base-content/60">
                注文番号 #{{ sprintf('%08d', $order->id) }}
                ／
                {{ $order->created_at->format('Y/m/d H:i') }}
            </p>
        </div>

        {{-- 注文状況 --}}
        <section class="mb-8 rounded-xl border border-base-300 bg-base-100 p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">注文状況</h2>

            <div>
                <p class="mb-2 text-sm text-base-content/60">現在のステータス</p>
                <span class="badge badge-outline badge-lg">
                    {{ $order->status->label() }}
                </span>
            </div>
        </section>

        {{-- 配送情報（発送済みの場合のみ表示） --}}
        @if ($order->status === \App\Enums\OrderStatus::SHIPPED)
            <section class="mb-8 rounded-xl border border-base-300 bg-base-100 p-5 lg:p-7">
                <h2 class="mb-5 text-lg font-bold">配送情報</h2>

                <div class="mb-4 flex items-center justify-between gap-4">
                    <span class="text-sm text-base-content/60">配送会社</span>
                    <span class="font-semibold">ヤマト運輸</span>
                </div>

                @foreach ($order->orderShippings as $shipping)
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-base-300 py-4">
                        <div>
                            <p class="mb-1 text-sm text-base-content/60">
                                個口 {{ $loop->iteration }}
                            </p>

                            <p class="font-semibold">
                                {{ $shipping->tracking_number }}
                            </p>
                        </div>

                        @if ($shipping->tracking_number)
                            <a href="{{ 'https://jizen.kuronekoyamato.co.jp/jizen/servlet/crjz.b.NQ0010?id=' . urlencode($shipping->tracking_number) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 text-sm text-primary hover:underline">
                                配送状況を確認

                                <x-icons.external-link width="16" height="16" />
                            </a>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

        {{-- 注文商品 --}}
        <section class="mb-8 rounded-xl border border-base-300 bg-base-100 p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">ご注文商品</h2>

            {{-- PC：テーブル --}}
            <div class="hidden lg:block">
                <table class="table w-full">
                    <thead class="bg-base-200">
                        <tr>
                            <th>商品名</th>
                            <th class="text-right">単価</th>
                            <th class="text-right">数量</th>
                            <th class="text-right">税率</th>
                            <th class="text-right">小計</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderItems as $item)
                            <tr>
                                <td>
                                    @if ($item->product_id)
                                        <a href="{{ route('products.show', $item->product_id) }}"
                                            class="text-primary hover:underline">
                                            {{ $item->product_name }}
                                        </a>
                                    @else
                                        {{ $item->product_name }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    {{ formatYen($item->price) }}
                                </td>
                                <td class="text-right">{{ $item->quantity }}</td>
                                <td class="text-right">{{ $item->tax_rate }}%</td>
                                <td class="text-right font-semibold">
                                    {{ formatYen($item->subtotal) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- スマホ：カード --}}
            <div class="space-y-4 lg:hidden">
                @foreach ($order->orderItems as $item)
                    <div class="border-b border-base-300 pb-4 last:border-b-0">
                        <p class="font-semibold">
                            @if ($item->product_id)
                                <a href="{{ route('products.show', $item->product_id) }}"
                                    class="text-primary hover:underline">
                                    {{ $item->product_name }}
                                </a>
                            @else
                                {{ $item->product_name }}
                            @endif
                        </p>

                        <div class="mt-2 flex justify-between text-sm text-base-content/70">
                            <span>
                                {{ formatYen($item->price) }}
                                × {{ $item->quantity }}
                                （{{ $item->tax_rate }}%）
                            </span>
                            <span class="font-semibold text-base-content">
                                {{ formatYen($item->subtotal) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 金額内訳 --}}
            <div class="mt-5 space-y-4 border-t border-base-300 pt-5">

                {{-- 商品合計 --}}
                <div class="flex items-center justify-between gap-4">
                    <span>商品合計</span>
                    <span>{{ formatYen($order->total_amount) }}</span>
                </div>

                {{-- 送料 --}}
                <div class="flex items-center justify-between gap-4">
                    <span>送料</span>
                    <span>
                        {{ $order->shipping_fee !== null ? formatYen($order->shipping_fee) : '未確定' }}
                    </span>
                </div>

                {{-- 代引手数料 --}}
                @if ($order->payment_method === \App\Enums\PaymentMethod::CASH_ON_DELIVERY)
                    <div class="flex items-center justify-between gap-4">
                        <span>代引手数料</span>
                        <span>
                            {{ $order->cod_fee !== null ? formatYen($order->cod_fee) : '未確定' }}
                        </span>
                    </div>
                @endif

                {{-- 支払総額 --}}
                <div class="flex items-center justify-between gap-4 border-t border-base-300 pt-5 text-lg font-bold">
                    <span>支払総額</span>
                    <span>
                        @if (
                            $order->shipping_fee !== null &&
                                ($order->payment_method !== \App\Enums\PaymentMethod::CASH_ON_DELIVERY || $order->cod_fee !== null))
                            {{ formatYen($order->total_amount + $order->shipping_fee + ($order->cod_fee ?? 0)) }}
                        @else
                            未確定
                        @endif
                    </span>
                </div>
                {{-- 消費税内訳 --}}
                @if ($order->taxSummaries->isNotEmpty())
                    <div class="border-t border-base-300 pt-4">
                        <h3 class="mb-3 text-sm font-bold">
                            消費税内訳
                        </h3>

                        <div class="space-y-2">
                            @foreach ($order->taxSummaries->sortBy('tax_rate') as $summary)
                                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                                    <span class="text-base-content/70">
                                        {{ $summary->tax_rate }}%対象
                                        （税込 {{ formatYen($summary->tax_included_amount) }}）
                                    </span>

                                    <span class="font-semibold">
                                        内消費税 {{ formatYen($summary->tax_amount) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (
                    $order->status !== \App\Enums\OrderStatus::CANCELLED &&
                        ($order->shipping_fee === null ||
                            ($order->payment_method === \App\Enums\PaymentMethod::CASH_ON_DELIVERY && $order->cod_fee === null)))
                    <p class="text-sm text-base-content/60">
                        ※ 送料・手数料を含むお支払い総額は、ご注文受付後にご案内します。
                    </p>
                @endif
            </div>
        </section>

        {{-- ご注文に関するご要望 --}}
        <section class="mb-8 rounded-xl border border-base-300 bg-base-100 p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">ご注文に関するご要望</h2>

            @if (filled($order->customer_note))
                <p class="rounded-lg bg-base-200 px-4 py-2 break-words text-sm leading-relaxed">
                    <span class="whitespace-pre-line">{{ $order->customer_note }}</span>
                </p>
            @else
                <p class="text-sm text-base-content/60">特になし</p>
            @endif
        </section>

        {{-- 注文者情報・お届け先 --}}
        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- 注文者情報 --}}
            <section class="rounded-xl border border-base-300 bg-base-100 p-5 lg:p-7">
                <h2 class="mb-5 text-lg font-bold">ご注文者様の情報</h2>

                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-base-content/60">氏名</dt>
                        <dd class="mt-1 font-semibold">{{ $order->orderer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">郵便番号</dt>
                        <dd class="mt-1">〒{{ $order->orderer_postal_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">住所</dt>
                        <dd class="mt-1 whitespace-pre-line">{{ $order->orderer_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">電話番号</dt>
                        <dd class="mt-1">{{ $order->orderer_phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">メールアドレス</dt>
                        <dd class="mt-1 break-all">{{ $order->orderer_email ?? '未登録' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- お届け先情報 --}}
            <section class="rounded-xl border border-base-300 bg-base-100 p-5 lg:p-7">
                <h2 class="mb-5 text-lg font-bold">お届け先の情報</h2>

                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-base-content/60">氏名</dt>
                        <dd class="mt-1 font-semibold">{{ $order->shipping_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">郵便番号</dt>
                        <dd class="mt-1">〒{{ $order->shipping_postal_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">住所</dt>
                        <dd class="mt-1 whitespace-pre-line">{{ $order->shipping_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-base-content/60">電話番号</dt>
                        <dd class="mt-1">{{ $order->shipping_phone }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- お支払い方法 --}}
        <section class="mb-8 rounded-xl border border-base-300 bg-base-100 p-5 lg:p-7">
            <h2 class="mb-4 text-lg font-bold">お支払い方法</h2>
            <p class="font-semibold">
                {{ $order->payment_method->label() }}
            </p>
        </section>
    </div>
@endsection
