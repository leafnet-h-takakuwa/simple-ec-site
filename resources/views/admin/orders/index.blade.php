@extends('layouts.admin')

@section('title', '注文一覧')

@section('content')
    {{-- ページタイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">注文一覧</h1>
        <p class="mt-2 text-sm text-slate-500">
            注文情報の検索・確認ができます。
        </p>
    </div>

    {{-- 検索フォーム --}}
    <form action="{{ route('admin.orders.index') }}" method="GET"
        class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">

        <h2 class="mb-5 text-lg font-bold">注文検索</h2>

        {{-- PC：2列×2段 / スマホ：1列×4段 --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

            {{-- 注文番号 --}}
            <fieldset>
                <label for="order_id" class="mb-2 block text-sm font-semibold">
                    注文番号
                </label>
                <input type="text" id="order_id" name="order_id"
                    value="{{ old('order_id', $conditions['order_id'] ?? '') }}" placeholder="例：00000001"
                    inputmode="numeric" class="input w-full">
            </fieldset>

            {{-- 注文者氏名 --}}
            <fieldset>
                <label for="orderer_name" class="mb-2 block text-sm font-semibold">
                    注文者氏名
                </label>
                <input type="text" id="orderer_name" name="orderer_name"
                    value="{{ old('orderer_name', $conditions['orderer_name'] ?? '') }}" placeholder="氏名の一部を入力"
                    class="input w-full">
            </fieldset>

            {{-- 注文日 --}}
            <fieldset>
                <legend class="mb-2 text-sm font-semibold">
                    注文日
                </legend>

                <div class="flex items-center gap-2">
                    <input type="date" name="date_from" aria-label="注文日（開始）"
                        value="{{ old('date_from', $conditions['date_from'] ?? '') }}" class="input min-w-0 flex-1">

                    <span class="shrink-0 text-slate-500">〜</span>

                    <input type="date" name="date_to" aria-label="注文日（終了）"
                        value="{{ old('date_to', $conditions['date_to'] ?? '') }}" class="input min-w-0 flex-1">
                </div>
            </fieldset>

            {{-- ステータス --}}
            <fieldset>
                <label for="status" class="mb-2 block text-sm font-semibold">
                    ステータス
                </label>

                <select id="status" name="status" class="select w-full">
                    <option value="">すべて</option>

                    <option value="active" @selected(($conditions['status'] ?? '') === 'active')>
                        対応中（発送済み以外）
                    </option>

                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $conditions['status'] ?? '') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </fieldset>
        </div>

        {{-- 検索ボタン --}}
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline">
                クリア
            </a>

            <button type="submit" class="btn btn-success">
                検索
            </button>
        </div>
    </form>

    {{-- 件数 --}}
    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="font-bold">
            注文 {{ number_format($orders->total()) }}件
        </p>
        <span class="text-sm text-slate-500">
            注文日：新しい順 ↓
        </span>
    </div>

    {{-- ページネーション --}}
    <div class="mt-6">
        {{ $orders->links() }}
    </div>

    {{-- PC：テーブル表示 --}}
    <div class="hidden overflow-x-auto rounded-xl border border-slate-200 bg-white lg:block">
        <table class="table w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th>注文日</th>
                    <th>注文番号</th>
                    <th>注文者</th>
                    <th class="text-right">合計金額</th>
                    <th>ステータス</th>
                    <th>操作</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td class="whitespace-nowrap">
                            {{ $order->created_at->format('Y/m/d') }}
                        </td>

                        <td class="font-semibold">
                            #{{ sprintf('%08d', $order->id) }}
                        </td>

                        <td>{{ $order->orderer_name }}</td>

                        <td class="whitespace-nowrap text-right font-semibold">
                            {{ formatYen($order->total_amount) }}
                        </td>

                        <td>
                            <span class="badge badge-outline whitespace-nowrap">
                                {{ $order->status->label() }}
                            </span>
                        </td>

                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline btn-sm">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- スマホ：カード表示 --}}
    <div class="space-y-4 lg:hidden">
        @foreach ($orders as $order)
            <article class="rounded-xl border border-slate-200 bg-white p-5">

                <div class="flex items-center justify-between gap-3">
                    <span class="font-bold">
                        注文番号 #{{ sprintf('%08d', $order->id) }}
                    </span>

                    <span class="text-sm text-slate-500">
                        {{ $order->created_at->format('Y/m/d') }}
                    </span>
                </div>

                <p class="mt-4 text-sm">
                    <span class="text-slate-500">注文者</span>
                    <span class="ml-3 font-semibold">
                        {{ $order->orderer_name }}
                    </span>
                </p>

                <div class="mt-4 flex items-center justify-between gap-3">
                    <span class="text-lg font-bold">
                        {{ formatYen($order->total_amount) }}
                    </span>

                    <span class="badge badge-outline">
                        {{ $order->status->label() }}
                    </span>
                </div>

                <div class="mt-4 text-right">
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline btn-sm">
                        詳細
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    {{-- 検索結果なし --}}
    @if ($orders->isEmpty())
        <div class="rounded-xl border border-slate-200 p-10 text-center text-slate-500">
            該当する注文はありません。
        </div>
    @endif
@endsection
