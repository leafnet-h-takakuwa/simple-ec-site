@extends('layouts.admin')

@section('title', '注文明細編集')

@section('content')
    <a href="{{ route('admin.orders.show', $order->id) }}"
        class="mb-6 inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
        <x-arrow direction="left" /> 注文詳細へ戻る
    </a>

    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            注文明細の編集
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            注文番号 #{{ sprintf('%08d', $order->id) }}
            ／
            {{ $order->created_at->format('Y/m/d H:i') }}
        </p>
    </div>

    <x-admin.order-edit-warning :order="$order" />

    <form method="POST" action="{{ route('admin.orders.items.update', $order->id) }}">
        @csrf
        @method('PUT')

        <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">

            <h2 class="text-lg font-bold">注文商品</h2>

            @php
                $items = old(
                    'items',
                    $order->orderItems
                        ->map(
                            fn($item) => [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'product_name' => $item->product_name,
                                'volume' => $item->volume,
                                'price' => $item->price,
                                'quantity' => $item->quantity,
                                'tax_rate' => $item->tax_rate,
                            ],
                        )
                        ->all(),
                );
            @endphp

            <div id="items-container" class="space-y-4">
                @foreach ($items as $index => $item)
                    <div class="order-item rounded-lg border border-slate-200 p-4">
                        <input type="hidden" data-field="id" name="items[{{ $index }}][id]"
                            value="{{ $item['id'] ?? '' }}">
                        <input type="hidden" data-field="product_id" value="{{ $item['product_id'] ?? '' }}">

                        <div class="mb-3 flex items-center justify-between gap-3">
                            {{-- 左側：商品名・商品選択 --}}
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="font-semibold item-title">
                                    商品 {{ $loop->iteration }}
                                </p>
                                <button type="button" class="select-product btn btn-outline btn-neutral btn-sm">
                                    商品を選択
                                </button>
                            </div>
                            {{-- 右端：削除 --}}
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
                                    name="items[{{ $index }}][product_name]"
                                    value="{{ $item['product_name'] ?? '' }}"
                                    maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" required class="input w-full">

                                <label class="mt-3 mb-2 block text-sm font-semibold">内容量</label>
                                <input type="text" data-field="volume" value="{{ $item['volume'] ?? '' }}"
                                    maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" placeholder="例：500g"
                                    class="input w-full">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="mb-2 block text-sm font-semibold">
                                    単価（円）<span class="text-red-600">*</span>
                                </label>
                                <input type="number" data-field="price" name="items[{{ $index }}][price]"
                                    value="{{ $item['price'] ?? 0 }}" step="1" required
                                    class="input w-full item-price">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-2 block text-sm font-semibold">
                                    数量<span class="text-red-600">*</span>
                                </label>
                                <input type="number" data-field="quantity" name="items[{{ $index }}][quantity]"
                                    value="{{ $item['quantity'] ?? 1 }}" min="1" step="1" required
                                    class="input w-full item-quantity">
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

            {{-- 商品追加 --}}
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

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline btn-neutral">
                キャンセル
            </a>
            <button type="submit" class="btn btn-success">
                変更を保存
            </button>
        </div>
    </form>

    <template id="item-template">
        <div class="order-item rounded-lg border border-slate-200 p-4">
            <input type="hidden" data-field="id" value="">
            <input type="hidden" data-field="product_id" value="">

            <div class="mb-3 flex items-center justify-between gap-3">
                {{-- 左側：商品名・商品選択 --}}
                <div class="flex flex-wrap items-center gap-3">
                    <p class="font-semibold item-title"></p>

                    <button type="button" class="select-product btn btn-outline btn-neutral btn-sm">
                        商品を選択
                    </button>
                </div>

                {{-- 右端：削除 --}}
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

                    <label class="mt-3 mb-2 block text-sm font-semibold">内容量</label>
                    <input type="text" data-field="volume" maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}"
                        placeholder="例：500g" class="input w-full">
                </div>
                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-semibold">
                        単価（円）<span class="text-red-600">*</span>
                    </label>
                    <input type="number" data-field="price" value="0" step="1" required
                        class="input w-full item-price">
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-semibold">
                        数量<span class="text-red-600">*</span>
                    </label>
                    <input type="number" data-field="quantity" value="1" min="1" step="1" required
                        class="input w-full item-quantity">
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

    {{-- 商品選択モーダル --}}
    @include('admin.orders.partials.item-editor-modal')

    {{-- 商品選択・明細操作 --}}
    @include('admin.orders.partials.item-editor-script')
@endsection
