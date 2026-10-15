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
                                'price' => $item->price,
                                'quantity' => $item->quantity,
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
                            <div class="lg:col-span-6">
                                <label class="mb-2 block text-sm font-semibold">商品名</label>
                                <input type="text" data-field="product_name"
                                    name="items[{{ $index }}][product_name]"
                                    value="{{ $item['product_name'] ?? '' }}" maxlength="255" required class="input w-full">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="mb-2 block text-sm font-semibold">単価（円）</label>
                                <input type="number" data-field="price" name="items[{{ $index }}][price]"
                                    value="{{ $item['price'] ?? 0 }}" step="1" required
                                    class="input w-full item-price">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="mb-2 block text-sm font-semibold">数量</label>
                                <input type="number" data-field="quantity" name="items[{{ $index }}][quantity]"
                                    value="{{ $item['quantity'] ?? 1 }}" min="1" step="1" required
                                    class="input w-full item-quantity">
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
                <div class="lg:col-span-6">
                    <label class="mb-2 block text-sm font-semibold">商品名</label>
                    <input type="text" data-field="product_name" maxlength="255" required class="input w-full">
                </div>
                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-semibold">単価（円）</label>
                    <input type="number" data-field="price" value="0" step="1" required
                        class="input w-full item-price">
                </div>
                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-semibold">数量</label>
                    <input type="number" data-field="quantity" value="1" min="1" step="1" required
                        class="input w-full item-quantity">
                </div>
            </div>

            <div class="mt-3 text-right text-sm">
                小計：
                <span class="item-subtotal font-bold">¥0</span>
            </div>
        </div>
    </template>

    <dialog id="product-select-modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">商品を選択</h3>

            <p class="mt-2 text-sm text-slate-500">
                商品を選択すると、商品名と単価が自動入力されます。
            </p>

            <div class="mt-5">
                <label for="product-select" class="mb-2 block text-sm font-semibold">
                    商品
                </label>

                <select id="product-select" class="select w-full">
                    <option value="">商品を選択してください</option>

                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                            data-price="{{ $product->price }}">
                            {{ $product->name }}（{{ formatYen($product->price) }}）
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-outline btn-neutral">
                        キャンセル
                    </button>
                </form>

                <button type="button" id="confirm-product" class="btn btn-success">
                    選択する
                </button>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>閉じる</button>
        </form>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('items-container');
            const template = document.getElementById('item-template');
            const formatter = new Intl.NumberFormat('ja-JP');

            const modal = document.getElementById('product-select-modal');
            const productSelect = document.getElementById('product-select');
            const confirmProduct = document.getElementById('confirm-product');

            // 現在、商品選択の対象になっている明細
            let targetItem = null;

            const formatYen = (amount) => {
                return `${amount < 0 ? '-' : ''}¥${formatter.format(Math.abs(amount))}`;
            };

            // 商品連携ボタンの表示を切り替える
            function updateLinkButton(item) {
                const productId = item.querySelector('[data-field="product_id"]').value;
                const button = item.querySelector('.select-product');

                if (productId) {
                    button.textContent = '商品連携済';
                    button.classList.remove('btn-outline', 'btn-neutral');
                    button.classList.add('btn-success');
                } else {
                    button.textContent = '商品を選択';
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline', 'btn-neutral');
                }
            }

            // 商品マスタとの連携を解除
            function unlinkProduct(item) {
                item.querySelector('[data-field="product_id"]').value = '';
                updateLinkButton(item);
            }

            // 商品連携時の値を記録
            function rememberProductValues(item) {
                item.dataset.linkedName =
                    item.querySelector('[data-field="product_name"]').value;

                item.dataset.linkedPrice =
                    item.querySelector('[data-field="price"]').value;
            }

            function refresh() {
                let total = 0;

                container.querySelectorAll('.order-item').forEach((item, index) => {
                    item.querySelector('.item-title').textContent = `商品 ${index + 1}`;

                    item.querySelectorAll('[data-field]').forEach(input => {
                        input.name = `items[${index}][${input.dataset.field}]`;
                    });

                    const price = Number(item.querySelector('.item-price').value) || 0;
                    const quantity = Number(item.querySelector('.item-quantity').value) || 0;
                    const subtotal = price * quantity;

                    item.querySelector('.item-subtotal').textContent = formatYen(subtotal);

                    total += subtotal;

                    updateLinkButton(item);
                });

                document.getElementById('total-amount').textContent = formatYen(total);
            }

            // 商品追加
            document.getElementById('add-item').addEventListener('click', () => {
                container.appendChild(template.content.cloneNode(true));
                refresh();
            });

            // 商品削除・商品選択
            container.addEventListener('click', event => {
                const removeButton = event.target.closest('.remove-item');

                if (removeButton) {
                    if (container.querySelectorAll('.order-item').length <= 1) {
                        alert('注文明細は最低1件必要です。');
                        return;
                    }

                    removeButton.closest('.order-item').remove();
                    refresh();
                    return;
                }

                const selectButton = event.target.closest('.select-product');

                if (selectButton) {
                    targetItem = selectButton.closest('.order-item');

                    // すでに連携済みなら、現在の商品を初期選択
                    const productId = targetItem.querySelector(
                        '[data-field="product_id"]'
                    ).value;

                    productSelect.value = productId || '';

                    modal.showModal();
                }
            });

            // モーダルで商品を確定
            confirmProduct.addEventListener('click', () => {
                if (!targetItem || !productSelect.value) {
                    alert('商品を選択してください。');
                    return;
                }

                const option = productSelect.selectedOptions[0];

                targetItem.querySelector('[data-field="product_id"]').value =
                    option.value;

                targetItem.querySelector('[data-field="product_name"]').value =
                    option.dataset.name;

                targetItem.querySelector('[data-field="price"]').value =
                    option.dataset.price;

                // 連携時の商品名・単価を記録
                rememberProductValues(targetItem);

                // 数量は変更しない
                updateLinkButton(targetItem);
                refresh();

                modal.close();
                targetItem = null;
            });

            modal.addEventListener('close', () => {
                targetItem = null;
            });

            // 金額は入力中もリアルタイムで更新
            container.addEventListener('input', refresh);

            // 商品名・単価の変更が確定したら連携解除
            container.addEventListener('change', event => {
                const input = event.target;
                const item = input.closest('.order-item');

                if (!item) return;

                const productId = item.querySelector('[data-field="product_id"]').value;
                if (!productId) return;

                if (input.dataset.field === 'product_name') {
                    if (input.value !== item.dataset.linkedName) {
                        unlinkProduct(item);
                    }
                }

                if (input.dataset.field === 'price') {
                    if (Number(input.value) !== Number(item.dataset.linkedPrice)) {
                        unlinkProduct(item);
                    }
                }
            });

            container.querySelectorAll('.order-item').forEach(item => {
                const productId = item.querySelector('[data-field="product_id"]').value;

                if (productId) {
                    rememberProductValues(item);
                }
            });

            refresh();
        });
    </script>
@endsection
