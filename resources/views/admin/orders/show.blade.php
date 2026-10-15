@extends('layouts.admin')

@section('title', '注文詳細')

@section('content')
    {{-- 一覧へ戻る --}}
    <a href="{{ route('admin.orders.index') }}"
        class="mb-6 inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
        <x-arrow direction="left" /> 注文一覧へ戻る
    </a>

    {{-- ページタイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            注文詳細
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            注文番号 #{{ sprintf('%08d', $order->id) }}
            ／
            {{ $order->created_at->format('Y/m/d H:i') }}
        </p>
    </div>

    {{-- 注文状況 --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="mb-5 text-lg font-bold">注文状況</h2>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <p class="mb-2 text-sm text-slate-500">現在のステータス</p>
                <span class="badge badge-outline badge-lg">
                    {{ $order->status->label() }}
                </span>
            </div>

            <div class="flex-1">
                <label for="status" class="mb-2 block text-sm font-semibold">
                    変更先
                </label>

                <select id="status" class="select w-full">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($order->status === $status)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="button" class="btn btn-success" onclick="openStatusModal()">
                ステータスを更新
            </button>
        </div>
    </section>

    {{-- 注文商品 --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-bold">注文商品</h2>

            <a href="{{ route('admin.orders.items.edit', $order) }}" class="btn btn-outline btn-neutral btn-sm">
                明細を編集
            </a>
        </div>

        {{-- PC：テーブル --}}
        <div class="hidden lg:block">
            <table class="table w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th>商品名</th>
                        <th class="text-right">単価</th>
                        <th class="text-right">数量</th>
                        <th class="text-right">小計</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td>
                                @if ($item->product_id)
                                    <a href="{{ route('admin.products.edit', $item->product_id) }}"
                                        class="text-green-700 hover:underline">
                                        {{ $item->product_name }}
                                    </a>
                                @else
                                    {{ $item->product_name }}
                                @endif
                            </td>
                            <td class="text-right">
                                {{ formatYen($item->price) }}
                            </td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right font-semibold">
                                {{ formatYen($item->subtotal) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- スマホ：カード --}}
        <div class="space-y-4 lg:hidden">
            @foreach ($order->orderItems as $item)
                <div class="border-b border-slate-200 pb-4 last:border-b-0">
                    <p class="font-semibold">
                        @if ($item->product_id)
                            <a href="{{ route('admin.products.edit', $item->product_id) }}"
                                class="text-green-700 hover:underline">
                                {{ $item->product_name }}
                            </a>
                        @else
                            {{ $item->product_name }}
                        @endif
                    </p>
                    <div class="mt-2 flex justify-between text-sm text-slate-600">
                        <span>
                            {{ formatYen($item->price) }}
                            × {{ $item->quantity }}
                        </span>
                        <span class="font-semibold text-slate-800">
                            {{ formatYen($item->subtotal) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 flex justify-between border-t border-slate-200 pt-5 text-lg font-bold">
            <span>合計金額</span>
            <span>{{ formatYen($order->total_amount) }}</span>
        </div>
    </section>

    {{-- 注文者情報・お届け先 --}}
    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">注文者情報</h2>

            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-slate-500">氏名</dt>
                    <dd class="mt-1 font-semibold">{{ $order->orderer_name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">郵便番号</dt>
                    <dd class="mt-1">〒{{ $order->orderer_postal_code }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">住所</dt>
                    <dd class="mt-1 whitespace-pre-line">{{ $order->orderer_address }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">電話番号</dt>
                    <dd class="mt-1">{{ $order->orderer_phone }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">メールアドレス</dt>
                    <dd class="mt-1 break-all">{{ $order->orderer_email ?? '未登録' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">お届け先情報</h2>

            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-slate-500">氏名</dt>
                    <dd class="mt-1 font-semibold">{{ $order->shipping_name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">郵便番号</dt>
                    <dd class="mt-1">〒{{ $order->shipping_postal_code }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">住所</dt>
                    <dd class="mt-1 whitespace-pre-line">{{ $order->shipping_address }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">電話番号</dt>
                    <dd class="mt-1">{{ $order->shipping_phone }}</dd>
                </div>
            </dl>
        </section>
    </div>

    {{-- お支払い --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="mb-4 text-lg font-bold">お支払い方法</h2>
        <p class="font-semibold">
            {{ $order->payment_method->label() }}
        </p>
    </section>

    {{-- ステータス更新モーダル --}}
    <dialog id="status_modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">注文ステータスの更新</h3>

            <p class="mt-4">
                注文番号
                <strong>#{{ sprintf('%08d', $order->id) }}</strong>
                のステータスを変更します。
            </p>

            <div class="mt-4 rounded-lg bg-slate-50 p-4">
                <p class="text-sm text-slate-500">変更内容</p>
                <p class="mt-2 font-semibold">
                    {{ $order->status->label() }}
                    →
                    <span id="new_status_label"></span>
                </p>
            </div>

            <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" id="modal_status">

                <div class="modal-action">
                    <button type="button" class="btn" onclick="document.getElementById('status_modal').close()">
                        キャンセル
                    </button>

                    <button type="submit" class="btn btn-success">
                        更新する
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        function openStatusModal() {
            const select = document.getElementById('status');
            const selectedOption = select.options[select.selectedIndex];

            document.getElementById('modal_status').value = select.value;
            document.getElementById('new_status_label').textContent = selectedOption.text;

            document.getElementById('status_modal').showModal();
        }
    </script>
@endsection
