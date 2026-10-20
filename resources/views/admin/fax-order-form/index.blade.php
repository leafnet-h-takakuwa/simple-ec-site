@extends('layouts.admin')

@section('title', 'FAX注文書管理')

@section('content')
    {{-- ページタイトル --}}
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold lg:text-3xl">FAX注文書管理</h1>
            <p class="mt-2 text-sm text-slate-500">掲載商品とメッセージを編集します。変更は保存するまで公開されません。</p>
        </div>
        <button type="submit" form="fax-form" class="btn btn-success">変更を保存</button>
    </div>

    <form id="fax-form" method="POST" action="{{ route('admin.fax-order-form.update') }}">
        @csrf
        @method('PUT')

        {{-- スマホ・タブレット：列切り替え --}}
        <div role="tablist" aria-label="編集する列" class="tabs tabs-box mb-5 grid grid-cols-3 lg:hidden">
            <button type="button" role="tab" aria-selected="true" class="tab tab-active" data-tab="left">左列</button>
            <button type="button" role="tab" aria-selected="false" class="tab" data-tab="center">中央列</button>
            <button type="button" role="tab" aria-selected="false" class="tab" data-tab="right">右列</button>
        </div>

        {{-- PC：3列 / スマホ：選択した列のみ --}}
        <div id="fax-columns" class="grid grid-cols-1 items-start gap-5 lg:grid-cols-3">
            @foreach (['left' => '左列', 'center' => '中央列', 'right' => '右列'] as $column => $label)
                <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-5" data-column="{{ $column }}">
                    <h2 class="mb-6 text-lg font-bold">{{ $label }}</h2>

                    {{-- 掲載商品 --}}
                    <div class="mb-7">
                        <h3 class="mb-4 font-bold">掲載商品</h3>
                        <div class="space-y-3" data-list="product"></div>
                        <div class="mt-4">
                            <button type="button" class="btn btn-outline btn-neutral btn-sm" data-action="add-product">
                                <x-plus /> 商品を追加
                            </button>
                        </div>
                    </div>

                    {{-- メッセージ --}}
                    <div class="border-t border-slate-200 pt-6">
                        <h3 class="mb-3 font-bold">メッセージ</h3>
                        <p class="mb-4 text-sm text-slate-500">商品の下にある余白の中央付近へ表示します。</p>
                        <div class="space-y-3" data-list="message"></div>
                        <div class="mt-4">
                            <button type="button" class="btn btn-outline btn-neutral btn-sm" data-action="add-message">
                                <x-plus /> メッセージを追加
                            </button>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>

        {{-- 未保存の変更があるときだけ下部に注意表示 --}}
        <div id="unsaved-notice"
            class="sticky bottom-3 z-10 mt-8 hidden rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-lg"
            role="status" aria-live="polite">
            <p class="text-sm font-semibold text-amber-800">未保存の変更があります</p>
        </div>
    </form>

    {{-- 商品選択モーダル（既存の注文明細編集と同じ操作） --}}
    <dialog id="product-select-modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">商品を選択</h3>
            <p class="mt-2 text-sm text-slate-500">商品を選択すると、FAX用表示名に商品名が自動入力されます。</p>
            <div class="mt-5">
                <label for="product-select" class="mb-2 block text-sm font-semibold">商品</label>
                <select id="product-select" class="select w-full">
                    <option value="">商品を選択してください</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}（{{ formatYen($product->price) }}）</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-action">
                <button type="button" id="cancel-product" class="btn btn-outline btn-neutral">キャンセル</button>
                <button type="button" id="confirm-product" class="btn btn-success">選択する</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>閉じる</button></form>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const products =
                {{ \Illuminate\Support\Js::from(
                    $products->map(
                            fn($product) => [
                                'id' => $product->id,
                                'name' => $product->name,
                                'volume' => $product->volume,
                                'price' => $product->price,
                            ],
                        )->values(),
                ) }};

            @php
                $initialRows = old(
                    'rows',
                    $rows
                        ->flatten(1)
                        ->map(
                            fn($row) => [
                                'column' => $row->column,
                                'type' => $row->type,
                                'product_id' => $row->product_id,
                                'display_name' => $row->display_name,
                                'message' => $row->message,
                                'sort_order' => $row->sort_order,
                            ],
                        )
                        ->values()
                        ->all(),
                );
            @endphp
            const initialRows = {{ \Illuminate\Support\Js::from(array_values($initialRows)) }};

            const columns = ['left', 'center', 'right'];
            const form = document.getElementById('fax-form');
            const container = document.getElementById('fax-columns');
            const unsavedNotice = document.getElementById('unsaved-notice');
            let rows = structuredClone(initialRows);
            let dirty = {{ $errors->any() ? 'true' : 'false' }};

            if (dirty) {
                unsavedNotice.classList.remove('hidden');
            }

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, char => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                })[char]);
            }

            function markDirty() {
                dirty = true;
                unsavedNotice.classList.remove('hidden');
            }

            // 未保存の変更がある状態で画面を離れる場合、ブラウザ標準の確認を表示する。
            window.addEventListener('beforeunload', event => {
                if (!dirty) return;
                event.preventDefault();
                event.returnValue = '';
            });

            const productModal = document.getElementById('product-select-modal');
            const productSelect = document.getElementById('product-select');
            let editingProductIndex = null;

            function openProductModal(index) {
                editingProductIndex = index;
                const currentId = rows[index].product_id;
                const available = products.some(product => String(product.id) === String(currentId));
                productSelect.value = available ? String(currentId) : '';
                productModal.showModal();
            }

            productSelect.addEventListener('change', () => productSelect.setCustomValidity(''));
            document.getElementById('cancel-product').addEventListener('click', () => productModal.close());
            document.getElementById('confirm-product').addEventListener('click', () => {
                if (editingProductIndex === null || !productSelect.value) {
                    productSelect.setCustomValidity('商品を選択してください。');
                    productSelect.reportValidity();
                    return;
                }
                const product = products.find(item => String(item.id) === productSelect.value);
                rows[editingProductIndex].product_id = product.id;
                rows[editingProductIndex].display_name = product.name;
                const index = editingProductIndex;
                editingProductIndex = null;
                productModal.close();
                markDirty();
                render();
                document.getElementById(`display-name-${index}`)?.focus({
                    preventScroll: true
                });
            });
            productModal.addEventListener('close', () => {
                editingProductIndex = null;
            });

            function render() {
                columns.forEach(column => {
                    const section = container.querySelector(`[data-column="${column}"]`);
                    ['product', 'message'].forEach(type => {
                        const list = section.querySelector(`[data-list="${type}"]`);
                        const entries = rows.map((row, index) => ({
                                row,
                                index
                            }))
                            .filter(({
                                row
                            }) => row.column === column && row.type === type);

                        if (!entries.length) {
                            list.innerHTML =
                                '<p class="rounded-lg border border-dashed border-slate-200 p-5 text-center text-sm text-slate-500">まだ登録されていません。</p>';
                            return;
                        }

                        list.innerHTML = entries.map(({
                            row,
                            index
                        }, position) => {
                            const moveControls = `
                                <div class="flex items-center gap-2">
                                    <button type="button" class="btn btn-outline btn-neutral btn-sm" data-action="up" data-index="${index}" aria-label="上へ移動" ${position === 0 ? 'disabled' : ''}>↑</button>
                                    <button type="button" class="btn btn-outline btn-neutral btn-sm" data-action="down" data-index="${index}" aria-label="下へ移動" ${position === entries.length - 1 ? 'disabled' : ''}>↓</button>
                                </div>`;
                            const removeButton =
                                `<button type="button" class="btn btn-outline btn-error btn-sm" data-action="remove" data-index="${index}">削除</button>`;

                            if (type === 'product') {
                                const product = products.find(item => String(item.id) ===
                                    String(row.product_id));
                                return `
                                    <div class="rounded-lg border border-slate-200 p-4" data-row-index="${index}">
                                        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex flex-wrap items-center gap-3">
                                                <span class="font-semibold">商品 ${position + 1}</span>
                                                <button type="button" class="btn btn-outline btn-neutral btn-sm" data-action="select-product" data-index="${index}">商品を選択</button>
                                            </div>
                                            ${removeButton}
                                        </div>
                                        <p class="mb-3 break-words text-sm text-slate-600">${product ? escapeHtml(product.name) : (row.product_id ? '非公開・削除済みの商品（ID: ' + escapeHtml(row.product_id) + '）' : '商品が選択されていません')}</p>
                                        ${!product && row.product_id ? '<p class="mb-3 text-sm text-amber-700">非公開・削除済みのためPDFには掲載されません。</p>' : ''}
                                        <label class="mb-2 block text-sm font-semibold" for="display-name-${index}">注文書表示名</label>
                                        <input id="display-name-${index}" type="text" maxlength="100" class="input w-full"
                                            data-field="display_name" data-index="${index}" value="${escapeHtml(row.display_name)}"
                                            placeholder="枠に入りきらない場合は短い名称を入力">
                                        ${product ? `<p class="mt-3 text-sm text-slate-500">内容量：${escapeHtml(product.volume ?? '―')} ／ 価格：${Number(product.price).toLocaleString()}円</p>` : ''}
                                        <div class="mt-4 flex justify-end">${moveControls}</div>
                                    </div>`;
                            }

                            return `
                                <div class="rounded-lg border border-slate-200 p-4" data-row-index="${index}">
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                        <span class="font-semibold">メッセージ ${position + 1}</span>${removeButton}
                                    </div>
                                    <label class="sr-only" for="message-${index}">メッセージ ${position + 1}</label>
                                    <textarea id="message-${index}" rows="3" maxlength="255" class="textarea w-full"
                                        data-field="message" data-index="${index}" placeholder="FAX注文書に表示するメッセージ">${escapeHtml(row.message)}</textarea>
                                    <div class="mt-4 flex justify-end">${moveControls}</div>
                                </div>`;
                        }).join('');
                    });
                });
            }

            // タブ切り替え。画面内の編集データは保持する。
            function switchTab(column) {
                document.querySelectorAll('[data-tab]').forEach(tab => {
                    const active = tab.dataset.tab === column;
                    tab.classList.toggle('tab-active', active);
                    tab.setAttribute('aria-selected', String(active));
                });
                container.querySelectorAll('[data-column]').forEach(section => {
                    section.classList.toggle('max-lg:hidden', section.dataset.column !== column);
                });
            }

            document.querySelectorAll('[data-tab]').forEach(tab => {
                tab.addEventListener('click', () => switchTab(tab.dataset.tab));
            });

            container.addEventListener('click', event => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;

                const action = button.dataset.action;
                const column = button.closest('[data-column]').dataset.column;

                if (action === 'select-product') {
                    openProductModal(Number(button.dataset.index));
                    return;
                }

                let newIndex = null;
                if (action === 'add-product' || action === 'add-message') {
                    newIndex = rows.length;
                    rows.push(action === 'add-product' ? {
                        column,
                        type: 'product',
                        product_id: '',
                        display_name: '',
                        message: null
                    } : {
                        column,
                        type: 'message',
                        product_id: null,
                        display_name: null,
                        message: ''
                    });
                } else {
                    const index = Number(button.dataset.index);
                    if (action === 'remove') {
                        rows.splice(index, 1);
                    } else {
                        const row = rows[index];
                        const indices = rows.map((item, i) => ({
                                item,
                                i
                            }))
                            .filter(({
                                item
                            }) => item.column === row.column && item.type === row.type)
                            .map(({
                                i
                            }) => i);
                        const position = indices.indexOf(index);
                        const next = position + (action === 'up' ? -1 : 1);
                        if (next < 0 || next >= indices.length) return;
                        const targetIndex = indices[next];
                        [rows[index], rows[targetIndex]] = [rows[targetIndex], rows[index]];
                    }
                }
                markDirty();
                render();
            });

            container.addEventListener('input', event => {
                const field = event.target.dataset.field;
                if (!field || field === 'product_id') return;
                rows[Number(event.target.dataset.index)][field] = event.target.value;
                markDirty();
            });

            form.addEventListener('submit', () => {
                // 正規の保存時には離脱確認を表示しない。
                dirty = false;
                form.querySelectorAll('[data-generated-input]').forEach(input => input.remove());
                rows.forEach((row, index) => {
                    ['column', 'type', 'product_id', 'display_name', 'message'].forEach(field => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `rows[${index}][${field}]`;
                        input.value = row[field] ?? '';
                        input.dataset.generatedInput = 'true';
                        form.appendChild(input);
                    });
                });
                // rowsが0件の場合、Laravel側で空配列として受け取れるようにする。
                // Controllerの「present|array」に対応するため、空の場合は送信せず
                // Controller側で rows の欠落を [] に補完する必要がある。
            });

            switchTab('left');
            render();
        });
    </script>
@endsection
