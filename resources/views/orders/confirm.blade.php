@extends('layouts.app')

@section('title', 'ご注文内容の確認')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">

        {{-- パンくずリスト --}}
        <nav class="mb-5 text-sm text-base-content/60">
            <a href="{{ route('home') }}" class="link link-hover">TOP</a>
            <span class="mx-2">＞</span>
            <a href="{{ route('carts.index') }}" class="link link-hover">カート</a>
            <span class="mx-2">＞</span>
            <a href="{{ route('orders.create') }}" class="link link-hover">注文情報の入力</a>
            <span class="mx-2">＞</span>
            <span aria-current="page" class="text-base-content">
                注文内容の確認
            </span>
        </nav>

        {{-- ページタイトル --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold">注文内容の確認</h1>
            <p class="mt-2 text-sm text-base-content/60">
                ご注文内容をご確認のうえ、注文を確定してください。
            </p>
        </div>

        <div class="space-y-6">

            {{-- ご注文商品 --}}
            <section class="rounded-lg border border-base-300 bg-base-100 p-5 sm:p-7">
                <h2 class="mb-4 border-b border-base-200 pb-4 text-lg font-bold">
                    ご注文商品
                </h2>

                <div class="divide-y divide-base-200">
                    @foreach ($cart_items as $cart_item)
                        <div class="flex items-center gap-4 py-4">

                            {{-- 商品画像 --}}
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded bg-base-200">
                                @if ($cart_item->product->image_path)
                                    <img src="{{ asset('storage/' . $cart_item->product->image_path) }}"
                                        alt="{{ $cart_item->product->name }}" class="h-full w-full object-cover">
                                @else
                                    <x-no-image />
                                @endif
                            </div>

                            {{-- 商品名・内容量・数量 --}}
                            <div class="min-w-0 flex-1">
                                <p class="break-words font-medium">
                                    {{ $cart_item->product->name }}
                                </p>

                                <p class="mt-1 text-sm text-base-content/60">
                                    内容量：{{ $cart_item->product->volume }}
                                </p>

                                <p class="mt-2 text-sm text-base-content/60">
                                    {{ formatYen($cart_item->product->price) }}
                                    × {{ $cart_item->quantity }}
                                </p>
                            </div>

                            {{-- 小計 --}}
                            <p class="shrink-0 text-right font-semibold">
                                {{ formatYen($cart_item->subtotal()) }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- 金額内訳 --}}
                <div class="mt-3 space-y-4 border-t border-base-300 pt-5">

                    <div class="flex items-center justify-between gap-4">
                        <span class="font-bold">商品代金合計</span>
                        <span class="text-xl font-bold">
                            {{ formatYen($total_amount) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span class="text-base-content/70">送料</span>
                        <span class="font-semibold">未確定</span>
                    </div>

                    @if ($payment_method === \App\Enums\PaymentMethod::CASH_ON_DELIVERY)
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <span class="text-base-content/70">代引手数料</span>
                            <span class="font-semibold">未確定</span>
                        </div>
                    @endif


                    {{-- 送料・手数料についてのご案内 --}}
                    <div class="border-t border-base-200 pt-4 text-sm text-base-content/70">
                        <p>
                            @if ($payment_method === \App\Enums\PaymentMethod::CASH_ON_DELIVERY)
                                ※ 送料・手数料を含むお支払い総額は、ご注文受付後にご案内します。
                            @else
                                ※ 送料を含むお支払い総額は、ご注文受付後にご案内します。
                            @endif
                        </p>

                        <a href="{{ route('guide.shipping') }}" target="_blank" rel="noopener noreferrer"
                            class="link link-hover mt-2 inline-block text-primary">
                            送料・手数料について詳しく見る
                        </a>
                    </div>

                </div>
            </section>

            {{-- 注文者情報・お届け先情報 --}}
            <div class="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">

                {{-- ご注文者様の情報 --}}
                <section class="rounded-lg border border-base-300 bg-base-100 p-5 sm:p-7">
                    <h2 class="mb-5 border-b border-base-200 pb-4 text-lg font-bold">
                        ご注文者様の情報
                    </h2>

                    <dl
                        class="grid grid-cols-[100px_minmax(0,1fr)] gap-x-3 gap-y-5 text-sm sm:grid-cols-[130px_minmax(0,1fr)]">
                        <dt class="text-base-content/60">お名前</dt>
                        <dd class="break-words">{{ $order['orderer_name'] }}</dd>

                        <dt class="text-base-content/60">郵便番号</dt>
                        <dd>{{ $order['orderer_postal_code'] }}</dd>

                        <dt class="text-base-content/60">住所</dt>
                        <dd class="break-words">{{ $order['orderer_address'] }}</dd>

                        <dt class="text-base-content/60">電話番号</dt>
                        <dd>{{ $order['orderer_phone'] }}</dd>

                        <dt class="text-base-content/60">メールアドレス</dt>
                        <dd class="break-all">{{ $order['orderer_email'] ?? '未入力' }}</dd>
                    </dl>
                </section>

                {{-- お届け先の情報 --}}
                <section class="rounded-lg border border-base-300 bg-base-100 p-5 sm:p-7">
                    <h2 class="mb-5 border-b border-base-200 pb-4 text-lg font-bold">
                        お届け先の情報
                    </h2>

                    <dl
                        class="grid grid-cols-[100px_minmax(0,1fr)] gap-x-3 gap-y-5 text-sm sm:grid-cols-[130px_minmax(0,1fr)]">
                        <dt class="text-base-content/60">お名前</dt>
                        <dd class="break-words">{{ $order['shipping_name'] }}</dd>

                        <dt class="text-base-content/60">郵便番号</dt>
                        <dd>{{ $order['shipping_postal_code'] }}</dd>

                        <dt class="text-base-content/60">住所</dt>
                        <dd class="break-words">{{ $order['shipping_address'] }}</dd>

                        <dt class="text-base-content/60">電話番号</dt>
                        <dd>{{ $order['shipping_phone'] }}</dd>
                    </dl>
                </section>

            </div>

            {{-- お支払い方法 --}}
            <section class="rounded-lg border border-base-300 bg-base-200 p-5 sm:px-7">
                <p class="font-semibold">
                    お支払い方法：{{ $payment_method->label() }}
                </p>

                @if ($payment_method === \App\Enums\PaymentMethod::BANK_TRANSFER)
                    <div class="mt-3 space-y-1 text-sm text-base-content/70">
                        <p>※ 振込手数料はお客様のご負担となります。</p>
                        <p>※ ご依頼主様からのご入金確認後に商品を発送いたします。</p>
                    </div>
                @endif
            </section>

            {{-- ご注文に関するご要望 --}}
            <section class="rounded-lg border border-base-300 bg-base-100 p-5 sm:p-7">
                <h2 class="mb-5 border-b border-base-200 pb-4 text-lg font-bold">
                    ご注文に関するご要望
                </h2>

                <p class="whitespace-pre-wrap break-words text-sm">{{ $order['customer_note'] ?? '特になし' }}</p>
            </section>
        </div>

        {{-- 送料・手数料の確認 --}}
        <div class="mt-6 rounded-lg border border-base-300 bg-base-100 p-5 sm:p-6">
            <label class="flex cursor-pointer items-start gap-3">
                <input type="checkbox" name="fee_agreement" value="1" form="order_form" required
                    class="checkbox checkbox-primary mb-0">

                <span class="text-sm leading-relaxed">
                    送料
                    @if ($payment_method === \App\Enums\PaymentMethod::CASH_ON_DELIVERY)
                        ・代引手数料
                    @endif
                    が商品代金に別途加算されること、および最終的なお支払い総額が注文受付後に確定することを了承します。
                </span>
            </label>
        </div>

        {{-- 操作ボタン --}}
        <div class="mt-8 flex flex-col-reverse gap-4 sm:flex-row sm:items-center sm:justify-between">

            <a href="{{ route('orders.create') }}"
                class="text-center text-sm inline-flex items-center gap-2 hover:underline">
                <x-arrow direction="left" /> 入力内容を修正する
            </a>

            <form id="order_form" action="{{ route('orders.store') }}" method="POST" class="w-full sm:w-auto">
                @csrf

                <button id="order_submit" type="submit" class="btn btn-primary w-full sm:min-w-64">
                    注文を確定する <x-arrow direction="right" />
                </button>
            </form>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('order_form');
            const button = document.getElementById('order_submit');
            const agreement = document.querySelector('input[name="fee_agreement"]');

            let submitting = false;

            agreement.addEventListener('change', updateButton);
            updateButton();

            // 二重送信を防止
            form.addEventListener('submit', function(event) {
                if (submitting) {
                    event.preventDefault();
                    return;
                }

                submitting = true;
                button.disabled = true;
                button.textContent = '注文処理中...';
            });
        });
    </script>
@endsection
