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
                            <div class="lg:col-span-6">
                                <label class="mb-2 block text-sm font-semibold">
                                    商品名
                                </label>
                                <input type="text" data-field="product_name" value="{{ $item['product_name'] ?? '' }}"
                                    maxlength="255" required class="input w-full">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="mb-2 block text-sm font-semibold">
                                    単価（円）
                                </label>
                                <input type="number" data-field="price" value="{{ $item['price'] ?? 0 }}" step="1"
                                    required class="input item-price w-full">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="mb-2 block text-sm font-semibold">
                                    数量
                                </label>
                                <input type="number" data-field="quantity" value="{{ $item['quantity'] ?? 1 }}"
                                    min="1" step="1" required class="input item-quantity w-full">
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
                <h2 class="mb-5 text-lg font-bold">注文者情報</h2>

                <div class="space-y-4">
                    <div>
                        <label for="orderer_name" class="mb-2 block text-sm font-semibold">
                            氏名 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="orderer_name" name="orderer_name" value="{{ old('orderer_name') }}"
                            maxlength="255" required class="input w-full">
                    </div>

                    <div>
                        <label for="orderer_postal_code" class="mb-2 block text-sm font-semibold">
                            郵便番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="orderer_postal_code" name="orderer_postal_code"
                            value="{{ old('orderer_postal_code') }}" placeholder="例：030-0000" required
                            class="input w-full">
                    </div>

                    <div>
                        <label for="orderer_address" class="mb-2 block text-sm font-semibold">
                            住所 <span class="text-red-600">*</span>
                        </label>
                        <textarea id="orderer_address" name="orderer_address" rows="3" required class="textarea w-full">{{ old('orderer_address') }}</textarea>
                    </div>

                    <div>
                        <label for="orderer_phone" class="mb-2 block text-sm font-semibold">
                            電話番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="tel" id="orderer_phone" name="orderer_phone" value="{{ old('orderer_phone') }}"
                            required class="input w-full">
                    </div>

                    <div>
                        <label for="orderer_email" class="mb-2 block text-sm font-semibold">
                            メールアドレス（任意）
                        </label>
                        <input type="email" id="orderer_email" name="orderer_email"
                            value="{{ old('orderer_email') }}" class="input w-full">
                    </div>
                </div>
            </section>

            {{-- お届け先情報 --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold">お届け先情報</h2>

                    <button type="button" id="copy-orderer" class="btn btn-outline btn-neutral btn-sm">
                        注文者情報をコピー
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="shipping_name" class="mb-2 block text-sm font-semibold">
                            氏名 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="shipping_name" name="shipping_name"
                            value="{{ old('shipping_name') }}" maxlength="255" required class="input w-full">
                    </div>

                    <div>
                        <label for="shipping_postal_code" class="mb-2 block text-sm font-semibold">
                            郵便番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="shipping_postal_code" name="shipping_postal_code"
                            value="{{ old('shipping_postal_code') }}" placeholder="例：030-0000" required
                            class="input w-full">
                    </div>

                    <div>
                        <label for="shipping_address" class="mb-2 block text-sm font-semibold">
                            住所 <span class="text-red-600">*</span>
                        </label>
                        <textarea id="shipping_address" name="shipping_address" rows="3" required class="textarea w-full">{{ old('shipping_address') }}</textarea>
                    </div>

                    <div>
                        <label for="shipping_phone" class="mb-2 block text-sm font-semibold">
                            電話番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="tel" id="shipping_phone" name="shipping_phone"
                            value="{{ old('shipping_phone') }}" required class="input w-full">
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

    {{-- 新規商品テンプレート --}}
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
                <div class="lg:col-span-6">
                    <label class="mb-2 block text-sm font-semibold">商品名</label>
                    <input type="text" data-field="product_name" maxlength="255" required class="input w-full">
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-semibold">単価（円）</label>
                    <input type="number" data-field="price" value="0" step="1" required
                        class="input item-price w-full">
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-semibold">数量</label>
                    <input type="number" data-field="quantity" value="1" min="1" step="1" required
                        class="input item-quantity w-full">
                </div>
            </div>

            <div class="mt-3 text-right text-sm">
                小計：
                <span class="item-subtotal font-bold">¥0</span>
            </div>
        </div>
    </template>

    {{-- 商品選択モーダル --}}
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
                <button type="button" class="btn btn-outline btn-neutral"
                    onclick="document.getElementById('product-select-modal').close()">
                    キャンセル
                </button>

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

            // 商品番号・入力名・金額を更新
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

                rememberProductValues(targetItem);
                updateLinkButton(targetItem);
                refresh();

                modal.close();
                targetItem = null;
            });

            modal.addEventListener('close', () => {
                targetItem = null;
            });

            // 入力中も金額を更新
            container.addEventListener('input', refresh);

            // 商品名・単価の変更が確定したら商品連携を解除
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

            // バリデーションエラー後の商品連携状態を復元
            container.querySelectorAll('.order-item').forEach(item => {
                if (item.querySelector('[data-field="product_id"]').value) {
                    rememberProductValues(item);
                }
            });

            // 注文者情報をお届け先にコピー
            document.getElementById('copy-orderer').addEventListener('click', () => {
                const fields = ['name', 'postal_code', 'address', 'phone'];

                fields.forEach(field => {
                    document.getElementById(`shipping_${field}`).value =
                        document.getElementById(`orderer_${field}`).value;
                });
            });

            refresh();
        });
    </script>
@endsection
