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
    </div>
    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-500">
        <span>
            注文番号 #{{ sprintf('%08d', $order->id) }}
            ／
            {{ $order->created_at->format('Y/m/d H:i') }}
        </span>

        <span class="badge badge-ghost badge-sm">
            {{ $order->order_source->label() }}
        </span>
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

    {{-- お客様情報 --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">

        {{-- 見出し・編集ボタン --}}
        <div class="mb-5 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold">お客様情報</h2>

            <a href="{{ route('admin.orders.customer.edit', $order) }}" class="btn btn-outline btn-neutral btn-sm">
                お客様情報を編集
            </a>
        </div>

        {{-- 注文者情報・お届け先情報 --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- 注文者情報 --}}
            <div class="rounded-xl border border-slate-200 bg-slate-30 p-5">
                <h3 class="mb-5 font-bold">注文者情報</h3>

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
            </div>

            {{-- お届け先情報 --}}
            <div class="rounded-xl border border-slate-200 bg-slate-30 p-5">
                <h3 class="mb-5 font-bold">お届け先情報</h3>

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
            </div>

        </div>
    </section>

    {{-- お支払い --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold">お支払い方法</h2>

            <button type="button" class="btn btn-outline btn-neutral btn-sm"
                onclick="document.getElementById('payment_modal').showModal()">
                変更
            </button>
        </div>

        <p class="font-semibold">{{ $order->payment_method->label() }}</p>
    </section>

    {{-- メモ --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold">管理メモ</h2>
            <span id="memo-status" class="text-xs text-slate-500"></span>
        </div>

        <textarea id="memo" name="memo" rows="4" class="textarea w-full" placeholder="注文に関する連絡事項など">{{ $order->memo }}</textarea>

        <p class="mt-1 text-sm text-slate-500">
            このメモは管理者のみ確認できます。
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

    {{-- 支払い方法変更モーダル --}}
    <dialog id="payment_modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">お支払い方法の変更</h3>

            <p class="mt-2 text-sm text-slate-500">
                変更するお支払い方法を選択してください。
            </p>

            <form method="POST" action="{{ route('admin.orders.payment.update', $order) }}">
                @csrf
                @method('PATCH')

                <div class="mt-6">
                    <label for="payment_method" class="mb-2 block text-sm font-semibold">
                        お支払い方法
                    </label>

                    <select id="payment_method" name="payment_method" class="select w-full" required>
                        @foreach (\App\Enums\PaymentMethod::cases() as $method)
                            <option value="{{ $method->value }}" @selected(old('payment_method', $order->payment_method->value) === $method->value)>
                                {{ $method->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn btn-outline btn-neutral"
                        onclick="document.getElementById('payment_modal').close()">
                        キャンセル
                    </button>

                    <button type="submit" class="btn btn-success">
                        変更を保存
                    </button>
                </div>
            </form>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>閉じる</button>
        </form>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const memo = document.getElementById('memo');
            const status = document.getElementById('memo-status');

            let savedValue = memo.value;

            memo.addEventListener('blur', async () => {
                if (memo.value === savedValue) return;

                const valueToSave = memo.value;

                status.textContent = '保存中...';
                status.className = 'text-xs text-slate-500';

                try {
                    const response = await fetch(
                        @json(route('admin.orders.updateMemo', $order)), {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                            body: JSON.stringify({
                                memo: valueToSave,
                            }),
                        }
                    );

                    if (!response.ok) {
                        throw new Error('保存に失敗しました');
                    }

                    savedValue = valueToSave;
                    status.textContent = '保存しました';
                    status.className = 'text-xs text-green-700';

                } catch (error) {
                    status.textContent = '保存に失敗しました';
                    status.className = 'text-xs text-red-600';
                }
            });
        });

        function openStatusModal() {
            const select = document.getElementById('status');
            const selectedOption = select.options[select.selectedIndex];

            document.getElementById('modal_status').value = select.value;
            document.getElementById('new_status_label').textContent = selectedOption.text;

            document.getElementById('status_modal').showModal();
        }
    </script>
@endsection
