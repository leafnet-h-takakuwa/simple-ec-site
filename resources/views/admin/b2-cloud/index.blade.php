@php
    use App\Enums\PaymentMethod;
@endphp

@extends('layouts.admin')

@section('title', 'B2クラウド連携')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            B2クラウド連携
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            ヤマト運輸「B2クラウド」で使用する送り状データを作成・管理する画面です。<br>
            出力する個口にチェックを入れ、CSVファイルを作成します。<br>
            「出力不可」の個口は、不足項目を確認し、注文詳細画面で必要な情報を設定してください。<br>
            送り状番号を登録済みの個口は通常表示されません。<br>
            確認する場合は「送り状番号登録済みも表示」にチェックを入れてください。
        </p>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-4 lg:p-7">

        <div class="mb-6">
            <h2 class="text-lg font-bold">発送待機一覧</h2>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                {{-- 左側：一括選択・表示切替 --}}
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" id="select-all-b2" class="btn btn-outline btn-success btn-sm">
                        出力可能なものをすべて選択
                    </button>

                    <button type="button" id="clear-all-b2" class="btn btn-outline btn-neutral btn-sm">
                        選択をすべて解除
                    </button>

                    <form method="GET" action="{{ route('admin.b2-cloud.index') }}">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox" name="show_registered" value="1"
                                class="checkbox checkbox-success checkbox-sm" @checked($showRegistered)
                                onchange="this.form.submit()">
                            <span class="text-sm">
                                送り状番号登録済みも表示
                            </span>
                        </label>
                    </form>
                </div>

                {{-- 右側：CSV出力 --}}
                <button type="button" id="open-b2-export" class="btn btn-success btn-sm ml-auto">
                    選択した個口をCSV出力
                </button>
            </div>
        </div>


        {{-- PC：テーブル表示 --}}
        <div class="hidden lg:block b2-pc-list">
            <div class="overflow-x-auto">
                <table class="table table-fixed w-full min-w-[1100px]">
                    <colgroup>
                        <col style="width: 60px"> {{-- 選択 --}}
                        <col style="width: 110px"> {{-- 出力可否 --}}
                        <col style="width: 125px"> {{-- 注文番号 --}}
                        <col style="width: 110px"> {{-- ステータス --}}
                        <col style="width: 140px"> {{-- お届け先 --}}
                        <col style="width: 100px"> {{-- 温度帯 --}}
                        <col> {{-- 品名：残りの幅 --}}
                        @if ($showRegistered)
                            <col style="width: 145px"> {{-- 送り状番号 --}}
                        @endif
                    </colgroup>

                    <thead>
                        <tr>
                            <th>選択</th>
                            <th>出力可否</th>
                            <th>注文番号</th>
                            <th>ステータス</th>
                            <th>お届け先</th>
                            <th>温度帯</th>
                            <th>品名</th>
                            @if ($showRegistered)
                                <th>送り状番号</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($shippings as $shipping)
                            @php
                                $order = $shipping->order;
                                $missing = $shipping->b2ExportMissingFields();
                                $canExport = empty($missing);
                            @endphp

                            <tr>
                                <td>
                                    <input type="checkbox" class="checkbox checkbox-success checkbox-sm b2-select"
                                        value="{{ $shipping->id }}" @disabled(!$canExport)>
                                </td>


                                <td>
                                    @if (!$canExport)
                                        {{-- 出力不可：不足項目を表示 --}}
                                        <div class="dropdown">
                                            <button type="button" tabindex="0"
                                                class="badge badge-error badge-outline badge-sm cursor-pointer whitespace-nowrap">
                                                出力不可
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m6 9 6 6 6-6" />
                                                </svg>
                                            </button>

                                            <div tabindex="0"
                                                class="dropdown-content z-50 mt-2 w-72 max-w-[80vw] rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                                                <p class="mb-3 text-sm font-bold text-slate-800">
                                                    出力できない理由
                                                </p>

                                                <ul class="space-y-2 text-sm text-red-700">
                                                    @foreach ($missing as $message)
                                                        <li class="flex items-start gap-2">
                                                            <span class="shrink-0">・</span>
                                                            <span>{{ $message }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                <p class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-500">
                                                    注文詳細画面から不足項目を設定してください。
                                                </p>
                                            </div>
                                        </div>
                                    @elseif (filled($shipping->tracking_number))
                                        {{-- 出力可能・送り状番号登録済み --}}
                                        <div class="dropdown">
                                            <button type="button" tabindex="0"
                                                class="badge badge-warning badge-outline badge-sm cursor-pointer whitespace-nowrap">
                                                出力可能
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m6 9 6 6 6-6" />
                                                </svg>
                                            </button>

                                            <div tabindex="0"
                                                class="dropdown-content z-50 mt-2 w-64 max-w-[80vw] rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                                                <p class="mb-2 text-sm font-bold text-slate-800">
                                                    送り状番号登録済み
                                                </p>

                                                <p class="break-all text-sm text-slate-700">
                                                    {{ $shipping->tracking_number }}
                                                </p>

                                                <p class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-500">
                                                    この個口は再出力できます。
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        {{-- 出力可能・送り状番号未登録 --}}
                                        <span class="badge badge-success badge-outline badge-sm whitespace-nowrap">
                                            出力可能
                                        </span>
                                    @endif
                                </td>


                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                        class="font-semibold text-green-800 hover:underline">
                                        #{{ sprintf('%08d', $order->id) }}
                                    </a>
                                </td>

                                <td>{{ $order->status->label() }}</td>

                                <td class="break-words">
                                    {{ $order->shipping_name }}
                                </td>

                                <td>
                                    {{ $shipping->cool_type?->label() ?? '未設定' }}
                                </td>

                                <td class="break-words">
                                    {{ $shipping->item_name ?: '未設定' }}
                                </td>

                                @if ($showRegistered)
                                    <td class="break-all">
                                        {{ $shipping->tracking_number ?: '—' }}
                                    </td>
                                @endif

                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $showRegistered ? 8 : 7 }}" class="py-10 text-center text-slate-500">
                                    対象の個口はありません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- スマホ・タブレット：カード表示 --}}
        <div class="space-y-4 lg:hidden b2-mobile-list">
            @forelse ($shippings as $shipping)
                @php
                    $order = $shipping->order;
                    $missing = $shipping->b2ExportMissingFields();
                    $canExport = empty($missing);
                @endphp

                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    {{-- 注文番号・選択・出力可否 --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="checkbox checkbox-success checkbox-sm b2-select"
                                value="{{ $shipping->id }}" @disabled(!$canExport)>

                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                class="font-bold text-green-800 hover:underline">
                                #{{ sprintf('%08d', $order->id) }}
                            </a>
                        </div>

                        @if (!$canExport)
                            <span class="badge badge-error badge-outline badge-sm">
                                出力不可
                            </span>
                        @elseif (filled($shipping->tracking_number))
                            <span class="badge badge-warning badge-outline badge-sm">
                                出力可能
                            </span>
                        @else
                            <span class="badge badge-success badge-outline badge-sm">
                                出力可能
                            </span>
                        @endif
                    </div>

                    {{-- お届け先・ステータス --}}
                    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500">
                        <span>{{ $order->shipping_name ?: 'お届け先未設定' }}</span>
                        <span>・</span>
                        <span>{{ $order->status->label() }}</span>
                    </div>

                    {{-- 品名・温度帯 --}}
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="break-words font-semibold">
                            {{ $shipping->item_name ?: '品名未設定' }}
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            温度帯：{{ $shipping->cool_type?->label() ?? '未設定' }}
                        </p>
                    </div>

                    {{-- 送り状番号：登録済み表示ONの場合のみ --}}
                    @if ($showRegistered)
                        <div class="mt-3 text-sm">
                            <span class="text-slate-500">送り状番号：</span>
                            <span class="font-medium break-all">
                                {{ $shipping->tracking_number ?: '未登録' }}
                            </span>
                        </div>
                    @endif

                    {{-- 不足項目：タップで展開 --}}
                    @if (!$canExport)
                        <details class="mt-4 rounded-lg bg-red-50">
                            <summary
                                class="flex cursor-pointer items-center justify-between gap-2 p-3 text-sm font-semibold text-red-700">
                                <span>
                                    不足項目を確認（{{ count($missing) }}件）
                                </span>
                                <span class="text-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="size-4 transition-transform duration-200 group-open:rotate-180"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="border-t border-red-100 px-3 py-3">
                                <ul class="space-y-2 text-sm text-red-700">
                                    @foreach ($missing as $message)
                                        <li class="flex items-start gap-2">
                                            <span class="shrink-0">・</span>
                                            <span>{{ $message }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                <p class="mt-3 text-xs text-slate-500">
                                    注文詳細画面から不足項目を設定してください。
                                </p>
                            </div>
                        </details>
                    @elseif (filled($shipping->tracking_number))
                        <details class="mt-4 rounded-lg bg-amber-50">
                            <summary
                                class="flex cursor-pointer items-center justify-between gap-2 p-3 text-sm font-semibold text-amber-800">
                                <span>送り状番号登録済み</span>
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="size-4 transition-transform duration-200 group-open:rotate-180"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="border-t border-amber-100 px-3 py-3">
                                <p class="break-all text-sm text-slate-700">
                                    {{ $shipping->tracking_number }}
                                </p>
                                <p class="mt-2 text-xs text-slate-500">
                                    この個口は再出力できます。
                                </p>
                            </div>
                        </details>
                    @endif

                </div>
            @empty
                <p class="py-10 text-center text-sm text-slate-500">
                    対象の個口はありません。
                </p>
            @endforelse
        </div>

        {{-- ページネーション --}}
        <div class="mt-6">
            {{ $shippings->links() }}
        </div>
    </section>


    {{-- CSV出力モーダル --}}
    <dialog id="b2-export-modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">B2クラウド CSV出力</h3>

            <p class="mt-2 text-sm text-slate-500">
                選択した個口をB2クラウド取込用CSVとして出力します。
            </p>

            <form id="b2-export-form" method="POST" action="{{ route('admin.b2-cloud.export') }}">
                @csrf

                <div id="b2-export-ids"></div>

                <div class="mt-5">
                    <label for="b2-shipping-date" class="mb-2 block text-sm font-semibold">
                        出荷予定日
                    </label>

                    <input type="date" id="b2-shipping-date" name="shipping_date"
                        value="{{ now()->toDateString() }}" class="input w-full" required>
                </div>

                <p class="mt-4 text-sm">
                    出力対象：
                    <span id="b2-export-count" class="font-bold">0</span> 個口
                </p>

                <div class="modal-action">
                    <button type="button" id="close-b2-export" class="btn btn-outline">
                        キャンセル
                    </button>

                    <button type="submit" class="btn btn-success">
                        CSVをダウンロード
                    </button>
                </div>
            </form>
        </div>
    </dialog>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectAllButton = document.getElementById('select-all-b2');
            const clearAllButton = document.getElementById('clear-all-b2');

            // PC・スマホのうち、現在表示されているチェックボックスを取得
            const getVisibleCheckboxes = () => {
                const container = window.matchMedia('(min-width: 1024px)').matches ?
                    document.querySelector('.b2-pc-list') :
                    document.querySelector('.b2-mobile-list');

                return container?.querySelectorAll('.b2-select:not(:disabled)') ?? [];
            };

            selectAllButton.addEventListener('click', () => {
                getVisibleCheckboxes().forEach(checkbox => {
                    checkbox.checked = true;
                });
            });

            clearAllButton.addEventListener('click', () => {
                getVisibleCheckboxes().forEach(checkbox => {
                    checkbox.checked = false;
                });
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('b2-export-modal');
            const openButton = document.getElementById('open-b2-export');
            const closeButton = document.getElementById('close-b2-export');
            const form = document.getElementById('b2-export-form');
            const idsContainer = document.getElementById('b2-export-ids');
            const count = document.getElementById('b2-export-count');

            const getSelectedIds = () => {
                const container = window.matchMedia('(min-width: 1024px)').matches ?
                    document.querySelector('.b2-pc-list') :
                    document.querySelector('.b2-mobile-list');

                return Array.from(
                    container?.querySelectorAll('.b2-select:checked:not(:disabled)') ?? []
                ).map(checkbox => checkbox.value);
            };

            openButton.addEventListener('click', () => {
                const ids = getSelectedIds();

                if (ids.length === 0) {
                    alert('出力する個口を選択してください。');
                    return;
                }

                idsContainer.replaceChildren();

                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'shipping_ids[]';
                    input.value = id;
                    idsContainer.appendChild(input);
                });

                count.textContent = ids.length;
                modal.showModal();
            });

            closeButton.addEventListener('click', () => {
                modal.close();
            });
        });
    </script>
@endsection
