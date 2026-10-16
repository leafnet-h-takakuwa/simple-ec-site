@extends('layouts.app')

@section('title', '注文履歴')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="mb-2 text-2xl font-bold">注文履歴</h1>
        <p class="mb-8 text-sm text-base-content/60">
            これまでのご注文を確認できます。
        </p>

        @if ($orders->isEmpty())
            <div class="rounded-xl border border-base-300 bg-base-100 p-10 text-center">
                <p class="text-base-content/60">
                    まだ注文履歴はありません。
                </p>
                <a href="{{ route('products.index') }}" class="btn btn-primary mt-6">
                    商品を見る
                </a>
            </div>
        @else
            {{-- PC --}}
            <div class="hidden overflow-x-auto rounded-xl border border-base-300 bg-base-100 md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>注文日</th>
                            <th>注文番号</th>
                            <th>注文内容</th>
                            <th class="text-right">商品合計</th>
                            <th class="text-right">支払総額</th>
                            <th>注文状況</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->created_at->format('Y/m/d') }}</td>
                                <td>#{{ sprintf('%08d', $order->id) }}</td>
                                <td>
                                    @if ($order->orderItems->isNotEmpty())
                                        {{ $order->orderItems->first()->product_name }}
                                        @if ($order->orderItems->count() > 1)
                                            ほか{{ $order->orderItems->count() - 1 }}点
                                        @endif
                                    @endif
                                </td>
                                <td class="text-right">
                                    {{ formatYen($order->total_amount) }}
                                </td>
                                <td class="text-right font-semibold">
                                    @if (
                                        $order->shipping_fee !== null &&
                                            ($order->payment_method !== \App\Enums\PaymentMethod::CASH_ON_DELIVERY || $order->cod_fee !== null))
                                        {{ formatYen($order->total_amount + $order->shipping_fee + ($order->cod_fee ?? 0)) }}
                                    @else
                                        未確定
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-outline">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline btn-sm">
                                        注文詳細を見る
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- スマホ --}}
            <div class="space-y-4 md:hidden">
                @foreach ($orders as $order)
                    <div class="rounded-xl border border-base-300 bg-base-100 p-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <span class="font-bold">注文番号 #{{ sprintf('%08d', $order->id) }}</span>
                            <span class="badge badge-outline">
                                {{ $order->status->label() }}
                            </span>
                        </div>

                        <p class="mb-3 text-sm text-base-content/60">
                            注文日：{{ $order->created_at->format('Y/m/d') }}
                        </p>

                        <div class="mb-4">
                            <p class="text-sm text-base-content/60">注文内容</p>
                            <p class="mt-1">
                                @if ($order->orderItems->isNotEmpty())
                                    {{ $order->orderItems->first()->product_name }}
                                    @if ($order->orderItems->count() > 1)
                                        ほか{{ $order->orderItems->count() - 1 }}点
                                    @endif
                                @endif
                            </p>
                        </div>

                        <div class="mb-5 space-y-3 border-t border-base-300 pt-4">

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm">商品合計</span>
                                <span>
                                    {{ formatYen($order->total_amount) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm font-bold">支払総額</span>
                                <span class="text-lg font-bold">
                                    @if (
                                        $order->shipping_fee !== null &&
                                            ($order->payment_method !== \App\Enums\PaymentMethod::CASH_ON_DELIVERY || $order->cod_fee !== null))
                                        {{ formatYen($order->total_amount + $order->shipping_fee + ($order->cod_fee ?? 0)) }}
                                    @else
                                        未確定
                                    @endif
                                </span>
                            </div>

                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline btn-sm w-full">
                                注文詳細を見る
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
