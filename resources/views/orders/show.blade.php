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
                            </span>
                            <span class="font-semibold text-base-content">
                                {{ formatYen($item->subtotal) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 合計金額 --}}
            <div class="mt-5 flex justify-between border-t border-base-300 pt-5 text-lg font-bold">
                <span>合計金額</span>
                <span>{{ formatYen($order->total_amount) }}</span>
            </div>
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

        {{-- 一覧へ戻る --}}
        <div class="mt-8">
            <a href="{{ route('orders.index') }}" class="btn btn-outline">
                注文履歴へ戻る
            </a>
        </div>
    </div>
@endsection
