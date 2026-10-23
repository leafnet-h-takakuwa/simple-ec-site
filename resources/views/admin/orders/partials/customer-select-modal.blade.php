{{-- 顧客選択モーダル --}}
<dialog id="customer-select-modal" class="modal">
    <div class="modal-box w-11/12 max-w-3xl">
        <h3 class="text-lg font-bold" tabindex="-1" autofocus>
            顧客台帳から選択
        </h3>

        <p class="mt-2 text-sm text-slate-500">
            顧客を選択すると、注文情報の入力欄に反映されます。
        </p>

        {{-- 検索・絞り込み --}}
        <div class="mt-5 space-y-3">
            <label for="customer-search" class="sr-only">
                顧客を検索
            </label>
            <input type="search" id="customer-search" placeholder="氏名・電話番号・住所などで検索" class="input w-full">

            <div class="flex flex-wrap items-center gap-6">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="checkbox" id="filter-orderer" class="checkbox checkbox-success checkbox-sm">
                    注文者
                </label>

                <label class="flex cursor-pointer items-center gap-2">
                    <input type="checkbox" id="filter-recipient" class="checkbox checkbox-success checkbox-sm">
                    配送先
                </label>
            </div>
        </div>

        {{-- 検索結果 --}}
        <div class="mt-5">
            <p id="customer-result-count" class="mb-3 text-sm text-slate-500" aria-live="polite"></p>

            <div id="customer-results"
                class="max-h-96 divide-y divide-slate-200 overflow-y-auto rounded-lg border border-slate-200">
                {{-- JavaScriptで検索結果を表示 --}}
            </div>
        </div>

        {{-- 閉じる --}}
        <div class="modal-action">
            <button type="button" id="close-customer-modal" class="btn btn-outline btn-neutral">
                キャンセル
            </button>
        </div>
    </div>

    <form method="dialog" class="modal-backdrop">
        <button>閉じる</button>
    </form>
</dialog>

{{-- 顧客データ --}}
@php
    $customerData = $customers
        ->map(
            fn($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'postal_code' => $customer->postal_code,
                'address' => $customer->address,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'memo' => $customer->memo,
                'is_orderer' => $customer->is_orderer,
                'is_recipient' => $customer->is_recipient,
            ],
        )
        ->values();
@endphp

<script type="application/json" id="customer-data">
    {!! Illuminate\Support\Js::encode($customerData) !!}
</script>
