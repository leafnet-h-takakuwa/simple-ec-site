@extends('layouts.app')

@section('title', 'ご注文手続き')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">

        {{-- パンくずリスト --}}
        <nav class="mb-5 text-sm text-base-content/60">
            <a href="{{ route('home') }}" class="link link-hover">
                TOP
            </a>
            <span class="mx-2">＞</span>
            <a href="{{ route('carts.index') }}" class="link link-hover">
                カート
            </a>
            <span class="mx-2">＞</span>
            <span>ご注文情報の入力</span>
        </nav>

        {{-- ページタイトル --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold">ご注文情報の入力</h1>
            <p class="mt-2 text-sm text-base-content/60">
                お客様情報とお届け先をご入力ください。
            </p>
        </div>

        {{-- 注文情報入力フォーム --}}
        <form action="{{ route('orders.confirm') }}" method="POST">
            @csrf
            <div class="space-y-6">
                {{-- 注文者情報 --}}
                <section class="rounded-lg border border-base-300 bg-base-100 p-5 sm:p-7">
                    <h2 class="mb-6 border-b border-base-200 pb-4 text-lg font-bold">
                        ご注文者様の情報
                    </h2>

                    <div class="space-y-5">

                        {{-- 氏名 --}}
                        <div>
                            <label for="orderer_name" class="mb-2 block text-sm font-medium">
                                お名前 <span class="text-error">＊</span>
                            </label>

                            <input type="text" id="orderer_name" name="orderer_name"
                                value="{{ old('orderer_name', $order['orderer_name'] ?? auth()->user()->name) }}"
                                class="input w-full" maxlength="255" required>
                        </div>

                        {{-- 郵便番号 --}}
                        <div>
                            <label for="orderer_postal_code" class="mb-2 block text-sm font-medium">
                                郵便番号 <span class="text-error">＊</span>
                            </label>

                            <input type="text" id="orderer_postal_code" name="orderer_postal_code"
                                value="{{ old('orderer_postal_code', $order['orderer_postal_code'] ?? auth()->user()->postal_code) }}"
                                class="input w-full sm:max-w-xs" placeholder="123-4567" maxlength="8" required>
                        </div>

                        {{-- 住所 --}}
                        <div>
                            <label for="orderer_address" class="mb-2 block text-sm font-medium">
                                ご住所 <span class="text-error">＊</span>
                            </label>

                            <input type="text" id="orderer_address" name="orderer_address"
                                value="{{ old('orderer_address', $order['orderer_address'] ?? auth()->user()->address) }}"
                                class="input w-full" maxlength="255" required>
                        </div>

                        {{-- 電話番号 --}}
                        <div>
                            <label for="orderer_phone" class="mb-2 block text-sm font-medium">
                                電話番号 <span class="text-error">＊</span>
                            </label>
                            <input type="tel" id="orderer_phone" name="orderer_phone"
                                value="{{ old('orderer_phone', $order['orderer_phone'] ?? auth()->user()->phone) }}"
                                class="input w-full sm:max-w-sm" placeholder="090-1234-5678" maxlength="20" required>
                        </div>

                        {{-- メールアドレス --}}
                        <div>
                            <label for="orderer_email" class="mb-2 block text-sm font-medium">
                                メールアドレス
                                <span class="text-error">＊</span>
                            </label>

                            <input type="email" id="orderer_email" name="orderer_email"
                                value="{{ old('orderer_email', $order['orderer_email'] ?? auth()->user()->email) }}"
                                class="input w-full" maxlength="255" placeholder="example@example.com" required>
                        </div>
                    </div>
                </section>

                {{-- 配送先情報 --}}
                <section class="rounded-lg border border-base-300 bg-base-100 p-5 sm:p-7">
                    <h2 class="mb-6 border-b border-base-200 pb-4 text-lg font-bold">
                        お届け先の情報
                    </h2>
                    {{-- 配送先の選択 --}}
                    <div class="space-y-3">
                        <label class="flex cursor-pointer items-center gap-3">
                            <input type="radio" name="shipping_same" value="1" class="radio radio-primary"
                                @checked(old('shipping_same', $order['shipping_same'] ?? '1') == '1')>
                            <span>ご注文者様と同じ住所にお届け</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3">
                            <input type="radio" name="shipping_same" value="0" class="radio radio-primary"
                                @checked(old('shipping_same', $order['shipping_same'] ?? '1') == '0')>
                            <span>別の住所にお届け</span>
                        </label>
                    </div>

                    {{-- 別の配送先 --}}
                    <div id="shipping-fields" class="mt-6 space-y-5 border-t border-base-200 pt-6">
                        <div>
                            <label for="shipping_name" class="mb-2 block text-sm font-medium">
                                お届け先のお名前 <span class="text-error">＊</span>
                            </label>
                            <input type="text" id="shipping_name" name="shipping_name"
                                value="{{ old('shipping_name', $order['shipping_name'] ?? '') }}" class="input w-full"
                                maxlength="255">
                        </div>
                        <div>
                            <label for="shipping_postal_code" class="mb-2 block text-sm font-medium">
                                お届け先の郵便番号 <span class="text-error">＊</span>
                            </label>
                            <input type="text" id="shipping_postal_code" name="shipping_postal_code"
                                value="{{ old('shipping_postal_code', $order['shipping_postal_code'] ?? '') }}"
                                class="input w-full sm:max-w-xs" placeholder="123-4567" maxlength="8">
                        </div>
                        <div>
                            <label for="shipping_address" class="mb-2 block text-sm font-medium">
                                お届け先の住所 <span class="text-error">＊</span>
                            </label>
                            <input type="text" id="shipping_address" name="shipping_address"
                                value="{{ old('shipping_address', $order['shipping_address'] ?? '') }}"
                                class="input w-full" maxlength="255">

                        </div>
                        <div>
                            <label for="shipping_phone" class="mb-2 block text-sm font-medium">
                                お届け先の電話番号 <span class="text-error">＊</span>
                            </label>
                            <input type="tel" id="shipping_phone" name="shipping_phone"
                                value="{{ old('shipping_phone', $order['shipping_phone'] ?? '') }}"
                                class="input w-full sm:max-w-sm" placeholder="090-1234-5678" maxlength="20">
                        </div>
                    </div>
                </section>

                {{-- 支払い方法 --}}
                <section class="rounded-lg border border-base-300 bg-base-100 p-5 sm:p-7">
                    <h2 class="mb-4 text-lg font-bold">お支払い方法</h2>
                    {{-- 注文者と配送先が同じ場合 --}}
                    <div id="payment-cod">
                        <p class="font-medium">代金引換</p>
                        <p class="mt-2 text-sm text-base-content/60">
                            商品お届け時に配達員へお支払いください。
                        </p>
                    </div>

                    {{-- 注文者と配送先が異なる場合 --}}
                    <div id="payment-bank" class="hidden">
                        <p class="font-medium">銀行振込</p>
                        <div class="mt-3 space-y-2 text-sm text-base-content/70">
                            <p>
                                振込手数料はお客様のご負担となります。
                            </p>
                            <p>
                                ご依頼主様からのご入金が確認でき次第、
                                商品を発送いたします。
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            {{-- 操作ボタン --}}
            <div class="mt-8 flex flex-col-reverse gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('carts.index') }}"
                    class="text-center text-sm inline-flex items-center gap-2 hover:underline">
                    <x-arrow direction="left" /> カートへ戻る
                </a>
                <button type="submit" class="btn btn-primary w-full sm:w-auto sm:min-w-64">
                    注文内容を確認する <x-arrow direction="right" />
                </button>
            </div>
        </form>
    </div>

    {{-- 配送先入力欄の表示切り替え --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('input[name="shipping_same"]');
            const shippingFields = document.getElementById('shipping-fields');
            const inputs = shippingFields.querySelectorAll('input');
            const paymentCod = document.getElementById('payment-cod');
            const paymentBank = document.getElementById('payment-bank');

            function toggleShippingFields() {
                const selected = document.querySelector('input[name="shipping_same"]:checked');
                const isDifferent = selected && selected.value === '0';

                // 配送先入力欄の表示切り替え
                shippingFields.classList.toggle('hidden', !isDifferent);
                inputs.forEach(function(input) {
                    input.required = isDifferent;
                    input.disabled = !isDifferent;
                });

                // 支払い方法の表示切り替え
                paymentCod.classList.toggle('hidden', isDifferent);
                paymentBank.classList.toggle('hidden', !isDifferent);
            }

            radios.forEach(function(radio) {
                radio.addEventListener('change', toggleShippingFields);
            });

            toggleShippingFields();

        });
    </script>
@endsection
