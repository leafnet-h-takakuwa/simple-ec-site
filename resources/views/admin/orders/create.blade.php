@extends('layouts.admin')

@section('title', '注文登録')

@section('content')
    {{-- 一覧へ戻る --}}
    <a href="{{ route('admin.orders.index') }}"
        class="mb-6 inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
        <x-arrow direction="left" /> 注文一覧へ戻る
    </a>

    {{-- ページタイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            注文登録
        </h1>
    </div>

    <form method="POST" action="{{ route('admin.orders.store') }}">
        @csrf

        {{-- 注文情報 --}}
        <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">注文情報</h2>

            <div class="max-w-sm">
                <label for="order_source" class="mb-2 block text-sm font-semibold">
                    注文経路 <span class="text-red-600">*</span>
                </label>

                <select id="order_source" name="order_source" class="select w-full" required>
                    @foreach ($orderSources as $source)
                        <option value="{{ $source->value }}" @selected(old('order_source', 'phone') === $source->value)>
                            {{ $source->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </section>

        {{-- 注文商品 --}}
        <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">注文商品</h2>

            @php
                $items = old('items', [
                    [
                        'product_id' => '',
                        'product_name' => '',
                        'volume' => '',
                        'price' => 0,
                        'quantity' => 1,
                    ],
                ]);
            @endphp

            <div id="items-container" class="space-y-4">
                @foreach ($items as $index => $item)
                    <div class="order-item rounded-lg border border-slate-200 p-4">
                        <input type="hidden" data-field="product_id" value="{{ $item['product_id'] ?? '' }}">

                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="item-title font-semibold">
                                    商品 {{ $loop->iteration }}
                                </p>

                                <button type="button" class="select-product btn btn-outline btn-neutral btn-sm">
                                    商品を選択
                                </button>
                            </div>

                            <button type="button" class="remove-item btn btn-outline btn-error btn-sm">
                                削除
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                            <div class="lg:col-span-5">
                                <label class="mb-2 block text-sm font-semibold">
                                    商品名<span class="text-red-600">*</span>
                                </label>
                                <input type="text" data-field="product_name" value="{{ $item['product_name'] ?? '' }}"
                                    maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" required class="input w-full">

                                <label class="mt-3 mb-2 block text-sm font-semibold">
                                    内容量
                                </label>
                                <input type="text" data-field="volume" value="{{ $item['volume'] ?? '' }}"
                                    maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" placeholder="例：500g"
                                    class="input w-full">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="mb-2 block text-sm font-semibold">
                                    単価（円）<span class="text-red-600">*</span>
                                </label>
                                <input type="number" data-field="price" value="{{ $item['price'] ?? 0 }}" step="1"
                                    required class="input item-price w-full">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-2 block text-sm font-semibold">
                                    数量<span class="text-red-600">*</span>
                                </label>
                                <input type="number" data-field="quantity" value="{{ $item['quantity'] ?? 1 }}"
                                    min="1" step="1" required class="input item-quantity w-full">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-2 block text-sm font-semibold">
                                    税率<span class="text-red-600">*</span>
                                </label>
                                <select data-field="tax_rate" class="select w-full item-tax-rate" required>
                                    @foreach (config('shop.tax.rates') as $rate => $label)
                                        <option value="{{ $rate }}" @selected((int) ($item['tax_rate'] ?? config('shop.tax.default_rate')) === $rate)>
                                            {{ $rate }}%（{{ $label }}）
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 text-right text-sm">
                            小計：
                            <span class="item-subtotal font-bold">¥0</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <button type="button" id="add-item" class="btn btn-outline btn-neutral btn-sm">
                    <x-plus /> 商品を追加
                </button>
            </div>

            <div class="mt-6 flex justify-between border-t border-slate-200 pt-5 text-lg font-bold">
                <span>合計金額</span>
                <span id="total-amount">¥0</span>
            </div>
        </section>

        {{-- 注文者情報・お届け先情報 --}}
        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- 注文者情報 --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold">注文者情報</h2>

                    <button type="button" class="select-customer btn btn-outline btn-neutral btn-sm" data-target="orderer">
                        顧客台帳から選択
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="orderer_name" class="mb-2 block text-sm font-semibold">
                            氏名 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="orderer_name" name="orderer_name" value="{{ old('orderer_name') }}"
                            maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" required class="input w-full">
                    </div>

                    <div>
                        <label for="orderer_postal_code" class="mb-2 block text-sm font-semibold">
                            郵便番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="orderer_postal_code" name="orderer_postal_code"
                            value="{{ old('orderer_postal_code') }}" placeholder="例：030-0000"
                            maxlength="{{ \App\Support\InputLimits::POSTAL_CODE }}" required class="input w-full">
                    </div>

                    <div>
                        <label for="orderer_address" class="mb-2 block text-sm font-semibold">
                            住所 <span class="text-red-600">*</span>
                        </label>
                        <textarea id="orderer_address" name="orderer_address" maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}"
                            rows="3" required class="textarea w-full">{{ old('orderer_address') }}</textarea>
                    </div>

                    <div>
                        <label for="orderer_phone" class="mb-2 block text-sm font-semibold">
                            電話番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="tel" id="orderer_phone" name="orderer_phone"
                            maxlength="{{ \App\Support\InputLimits::PHONE }}" value="{{ old('orderer_phone') }}"
                            required class="input w-full">
                    </div>

                    <div>
                        <label for="orderer_email" class="mb-2 block text-sm font-semibold">
                            メールアドレス（任意）
                        </label>
                        <input type="email" id="orderer_email" name="orderer_email"
                            maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" value="{{ old('orderer_email') }}"
                            class="input w-full">
                    </div>
                </div>
            </section>

            {{-- お届け先情報 --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold">お届け先情報</h2>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="select-customer btn btn-outline btn-neutral btn-sm"
                            data-target="shipping">
                            顧客台帳から選択
                        </button>

                        <button type="button" id="copy-orderer" class="btn btn-neutral btn-sm">
                            注文者情報をコピー
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="shipping_name" class="mb-2 block text-sm font-semibold">
                            氏名 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="shipping_name" name="shipping_name"
                            value="{{ old('shipping_name') }}" maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}"
                            required class="input w-full">
                    </div>

                    <div>
                        <label for="shipping_postal_code" class="mb-2 block text-sm font-semibold">
                            郵便番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="shipping_postal_code" name="shipping_postal_code"
                            value="{{ old('shipping_postal_code') }}" placeholder="例：030-0000"
                            maxlength="{{ \App\Support\InputLimits::POSTAL_CODE }}" required class="input w-full">
                    </div>

                    <div>
                        <label for="shipping_address" class="mb-2 block text-sm font-semibold">
                            住所 <span class="text-red-600">*</span>
                        </label>
                        <textarea id="shipping_address" name="shipping_address" maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}"
                            rows="3" required class="textarea w-full">{{ old('shipping_address') }}</textarea>
                    </div>

                    <div>
                        <label for="shipping_phone" class="mb-2 block text-sm font-semibold">
                            電話番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="tel" id="shipping_phone" name="shipping_phone"
                            maxlength="{{ \App\Support\InputLimits::PHONE }}" value="{{ old('shipping_phone') }}"
                            required class="input w-full">
                    </div>
                </div>
            </section>
        </div>

        {{-- お支払い方法 --}}
        <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">お支払い方法</h2>

            <div class="max-w-sm">
                <label for="payment_method" class="mb-2 block text-sm font-semibold">
                    お支払い方法 <span class="text-red-600">*</span>
                </label>

                <select id="payment_method" name="payment_method" class="select w-full" required>
                    <option value="">選択してください</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>
                            {{ $method->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </section>

        {{-- メモ --}}
        <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <div>
                <label for="memo" class="mb-2 block font-semibold">
                    管理メモ
                </label>
                <textarea id="memo" name="memo" rows="4" class="textarea w-full" placeholder="注文に関する連絡事項など">{{ old('memo') }}</textarea>
                <p class="mt-1 text-sm text-slate-500">
                    このメモは管理者のみ確認できます。
                </p>
            </div>
        </section>

        {{-- 登録ボタン --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-neutral">
                キャンセル
            </a>

            <button type="submit" class="btn btn-success">
                注文を登録
            </button>
        </div>
    </form>

    {{-- 商品追加テンプレート --}}
    <template id="item-template">
        <div class="order-item rounded-lg border border-slate-200 p-4">
            <input type="hidden" data-field="product_id" value="">

            <div class="mb-3 flex items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <p class="item-title font-semibold"></p>

                    <button type="button" class="select-product btn btn-outline btn-neutral btn-sm">
                        商品を選択
                    </button>
                </div>

                <button type="button" class="remove-item btn btn-outline btn-error btn-sm">
                    削除
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <label class="mb-2 block text-sm font-semibold">
                        商品名<span class="text-red-600">*</span>
                    </label>
                    <input type="text" data-field="product_name"
                        maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" required class="input w-full">

                    <label class="mt-3 mb-2 block text-sm font-semibold">
                        内容量<span class="text-red-600">*</span>
                    </label>
                    <input type="text" data-field="volume" maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}"
                        placeholder="例：500g" class="input w-full">
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-semibold">
                        単価（円）<span class="text-red-600">*</span>
                    </label>
                    <input type="number" data-field="price" value="0" step="1" required
                        class="input item-price w-full">
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-semibold">
                        数量<span class="text-red-600">*</span>
                    </label>
                    <input type="number" data-field="quantity" value="1" min="1" step="1" required
                        class="input item-quantity w-full">
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-semibold">
                        税率<span class="text-red-600">*</span>
                    </label>
                    <select data-field="tax_rate" class="select w-full item-tax-rate" required>
                        @foreach (config('shop.tax.rates') as $rate => $label)
                            <option value="{{ $rate }}" @selected($rate === config('shop.tax.default_rate'))>
                                {{ $rate }}%（{{ $label }}）
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3 text-right text-sm">
                小計：
                <span class="item-subtotal font-bold">¥0</span>
            </div>
        </div>
    </template>



    {{-- モーダル --}}
    @include('admin.orders.partials.item-editor-modal')
    @include('admin.orders.partials.customer-select-modal')

    {{-- モーダル処理 --}}
    @include('admin.orders.partials.item-editor-script')
    @include('admin.orders.partials.copy-orderer-script')
    @include('admin.orders.partials.customer-select-script')
@endsection
