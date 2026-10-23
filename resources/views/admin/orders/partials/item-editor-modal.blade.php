{{-- 商品選択モーダル --}}
<dialog id="product-select-modal" class="modal">
    <div class="modal-box w-11/12 max-w-3xl">
        <h3 class="text-lg font-bold" tabindex="-1" autofocus>商品を選択</h3>

        <p class="mt-2 text-sm text-slate-500">
            商品を選択すると、商品名・内容量・単価・税率が自動入力されます。
        </p>

        {{-- 検索 --}}
        <div class="mt-5">
            <label for="product-search" class="sr-only">
                商品を検索
            </label>

            <input type="search" id="product-search" placeholder="商品名で検索" class="input w-full">
        </div>

        {{-- 検索結果 --}}
        <div class="mt-5">
            <p id="product-result-count" class="mb-3 text-sm text-slate-500" aria-live="polite"></p>

            <div id="product-results"
                class="max-h-96 divide-y divide-slate-200 overflow-y-auto rounded-lg border border-slate-200">

                @forelse ($products as $product)
                    <div class="product-result p-4" data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                        data-volume="{{ $product->volume }}" data-price="{{ $product->price }}"
                        data-tax-rate="{{ $product->tax_rate }}">

                        <div class="flex items-center justify-between gap-4">
                            {{-- 商品情報 --}}
                            <div class="min-w-0 flex-1">
                                <p class="break-words font-semibold">
                                    {{ $product->name }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $product->volume }}
                                </p>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="mt-1 text-sm text-slate-600">
                                        {{ formatYen($product->price) }}
                                    </p>

                                    @if (in_array($product->id, $faxProductIds))
                                        <span class="badge badge-outline badge-success badge-sm shrink-0">
                                            FAX
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- 選択ボタン --}}
                            <button type="button" class="choose-product btn btn-outline btn-success btn-sm shrink-0">
                                選択
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-slate-500">
                        登録されている商品がありません。
                    </div>
                @endforelse
            </div>
        </div>

        <div class="modal-action">
            <button type="button" id="close-product-modal" class="btn btn-outline btn-neutral">
                キャンセル
            </button>
        </div>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button>閉じる</button>
    </form>
</dialog>
