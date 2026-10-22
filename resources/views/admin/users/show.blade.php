@extends('layouts.admin')

@section('title', '会員詳細')

@section('content')
    {{-- タイトル --}}
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
            <x-arrow direction="left" /> 会員一覧へ戻る
        </a>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold lg:text-3xl">
                会員詳細
            </h1>

            @if ($user->trashed())
                <span class="badge badge-ghost whitespace-nowrap">退会済み</span>
            @endif
        </div>

        <p class="mt-2 text-sm text-slate-500">
            会員ID：{{ $user->id }}
        </p>
    </div>

    {{-- 会員情報 --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="mb-6 text-lg font-bold">
            登録情報
        </h2>

        <dl class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    氏名
                </dt>
                <dd class="break-words">
                    {{ $user->name }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    メールアドレス
                </dt>
                <dd class="break-all">
                    {{ $user->email }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    郵便番号
                </dt>
                <dd>
                    {{ $user->postal_code ?: '未登録' }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    電話番号
                </dt>
                <dd>
                    {{ $user->phone ?: '未登録' }}
                </dd>
            </div>

            <div class="lg:col-span-2">
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    住所
                </dt>
                <dd class="whitespace-pre-wrap break-words">{{ $user->address ?: '未登録' }}</dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    登録日
                </dt>
                <dd>
                    {{ $user->created_at->format('Y/m/d H:i') }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    最終更新日
                </dt>
                <dd>
                    {{ $user->updated_at->format('Y/m/d H:i') }}
                </dd>
            </div>
            @if ($user->trashed())
                <div>
                    <dt class="mb-2 text-sm font-semibold text-slate-500">
                        退会日時
                    </dt>
                    <dd>
                        {{ $user->deleted_at->format('Y/m/d H:i') }}
                    </dd>
                </div>
            @endif
        </dl>
    </section>

    {{-- 注文履歴 --}}
    <section>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold">
                注文履歴
            </h2>

            <span class="text-sm text-slate-500">
                {{ $orders->total() }}件の注文
            </span>
        </div>

        {{-- PC：テーブル --}}
        <div class="hidden overflow-x-auto rounded-xl border border-slate-200 lg:block">
            <table class="table w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th>注文番号</th>
                        <th>注文日</th>
                        <th class="text-right">合計金額</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="font-semibold">
                                #{{ sprintf('%08d', $order->id) }}
                            </td>
                            <td>
                                {{ $order->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td class="text-right font-semibold">
                                {{ formatYen($order->total_amount) }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline btn-sm">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-500">
                                注文履歴はありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- スマホ：カード --}}
        <div class="space-y-4 lg:hidden">
            @forelse ($orders as $order)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="font-semibold">
                            注文 #{{ sprintf('%08d', $order->id) }}
                        </p>

                        <p class="text-xs text-slate-500">
                            {{ $order->created_at->format('Y/m/d H:i') }}
                        </p>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs text-slate-500">
                            合計金額
                        </p>
                        <p class="mt-1 text-lg font-bold">
                            {{ formatYen($order->total_amount) }}
                        </p>
                    </div>

                    <div class="mt-4 flex justify-end border-t border-slate-100 pt-3">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline btn-sm">
                            詳細
                        </a>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                    注文履歴はありません。
                </div>
            @endforelse
        </div>

        {{-- ページネーション --}}
        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    </section>
@endsection
