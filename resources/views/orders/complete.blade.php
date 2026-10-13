@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10">

        {{-- 注文完了メッセージ --}}
        <div class="mb-10 text-center">
            <div class="mb-5 text-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold sm:text-3xl">
                ご注文ありがとうございます
            </h1>

            <p class="mt-4 text-sm leading-7 text-base-content/70">
                ご注文を受け付けました。<br>
                ご注文番号をお控えください。
            </p>
        </div>

        {{-- 注文番号 --}}
        <section class="mb-6 rounded-lg border border-base-300 bg-base-100 p-6 text-center">
            <p class="text-sm text-base-content/60">ご注文番号</p>

            <p class="mt-2 text-3xl font-bold tracking-wider">
                {{ sprintf('%08d', $order->id) }}
            </p>

            <p class="mt-3 text-xs text-base-content/60">
                お問い合わせの際は、こちらの番号をお知らせください。
            </p>
        </section>

        {{-- 注文内容 --}}
        <section class="mb-6 rounded-lg border border-base-300 bg-base-100 p-5 sm:p-7">
            <h2 class="mb-5 border-b border-base-200 pb-4 text-lg font-bold">
                ご注文内容
            </h2>

            <div class="divide-y divide-base-200">
                @foreach ($order->orderItems as $item)
                    <div class="flex items-start justify-between gap-4 py-4">
                        <div>
                            <p class="font-medium">
                                {{ $item->product_name }}
                            </p>

                            <p class="mt-1 text-sm text-base-content/60">
                                {{ number_format($item->price) }}円
                                × {{ $item->quantity }}点
                            </p>
                        </div>

                        <p class="shrink-0 font-medium">
                            {{ number_format($item->subtotal) }}円
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-base-200 pt-5">
                <span class="font-bold">合計金額</span>

                <span class="text-2xl font-bold">
                    {{ number_format($order->total_amount) }}円
                </span>
            </div>
        </section>

        {{-- お支払い方法 --}}
        <section class="mb-8 rounded-lg border border-base-300 bg-base-100 p-5 sm:p-7">
            <h2 class="mb-4 text-lg font-bold">
                お支払いについて
            </h2>

            <p class="font-medium">
                {{ $order->payment_method->label() }}
            </p>

            @if ($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
                <div class="mt-4 space-y-2 text-sm leading-7 text-base-content/70">
                    <p>
                        お振込先は、ご入力いただいたメールアドレスへ
                        別途ご案内いたします。
                    </p>
                    <p>
                        振込手数料はお客様のご負担となります。
                    </p>
                    <p>
                        ご入金を確認でき次第、商品を発送いたします。
                    </p>
                </div>
            @else
                <p class="mt-3 text-sm text-base-content/70">
                    商品お届け時に配達員へお支払いください。
                </p>
            @endif
        </section>

        {{-- 注文内容の保存案内 --}}
        <div class="mb-8 rounded-lg bg-base-200/50 p-4 text-sm text-base-content/70">
            <p>
                ※ このページにはご注文内容が記載されています。
                お問い合わせの際に必要となる場合がありますので、
                印刷またはPDFで保存していただくことをおすすめします。
            </p>
        </div>

        {{-- トップページへ --}}
        <div class="text-center">
            <a href="{{ route('home') }}" class="btn btn-primary w-full sm:w-72">
                トップページへ戻る
            </a>
        </div>
    </div>
@endsection
