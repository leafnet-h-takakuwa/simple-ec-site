@extends('layouts.admin')

@section('title', '顧客台帳')

@section('content')
    {{-- タイトル --}}
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold lg:text-3xl">
                顧客台帳
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                注文作成時に利用する顧客情報を登録・管理します。<br>
                WEB会員情報とは別に管理されます。
            </p>
        </div>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-success">
            <x-plus /> 顧客を登録
        </a>
    </div>

    {{-- 検索フォーム --}}
    <form method="GET" action="{{ route('admin.customers.index') }}"
        class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">

        <label for="keyword" class="mb-2 block text-sm font-semibold">
            キーワード検索
        </label>

        <div class="flex flex-col gap-3 sm:flex-row">
            <input type="text" name="keyword" id="keyword" value="{{ $keyword }}"
                placeholder="氏名・電話番号・住所・メールアドレス・メモなど" class="input w-full flex-1">

            <div class="flex gap-2">
                <button type="submit" class="btn btn-success flex-1 sm:flex-none">
                    検索
                </button>

                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline flex-1 sm:flex-none">
                    クリア
                </a>
            </div>
        </div>
    </form>

    {{-- 件数 --}}
    <div class="mb-4 text-sm text-slate-500">
        {{ $customers->total() }}件の顧客
    </div>

    {{-- PC：テーブル --}}
    <div class="hidden overflow-x-auto rounded-xl border border-slate-200 lg:block">
        <table class="table w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th>氏名</th>
                    <th>電話番号</th>
                    <th>住所</th>
                    <th class="text-center">補足情報</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td class="font-semibold">
                            {{ $customer->name }}
                        </td>

                        <td class="whitespace-nowrap">
                            {{ $customer->phone }}
                        </td>

                        <td>
                            <p class="text-xs text-slate-500">
                                〒{{ $customer->postal_code }}
                            </p>
                            <p class="mt-1 break-words">
                                {{ $customer->address }}
                            </p>
                        </td>
                        {{-- 補足情報 --}}
                        <td class="text-center">
                            <div class="flex flex-wrap justify-center gap-1">
                                @if (filled($customer->email))
                                    <span class="badge badge-success badge-outline badge-sm">
                                        メール
                                    </span>
                                @endif

                                @if (filled($customer->memo))
                                    <span class="badge badge-outline badge-sm border-amber-600 text-amber-700">
                                        メモ
                                    </span>
                                @endif

                                @if (blank($customer->email) && blank($customer->memo))
                                    <span class="text-slate-400">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline btn-sm">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-500">
                            {{ $keyword !== '' ? '検索条件に一致する顧客はいません。' : '顧客が登録されていません。' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- スマホ：カード --}}
    <div class="space-y-4 lg:hidden">
        @forelse ($customers as $customer)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <p class="font-semibold">
                        {{ $customer->name }}
                    </p>
                </div>

                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-slate-500">
                            電話番号
                        </p>
                        <p class="mt-1">
                            {{ $customer->phone }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500">
                            住所
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            〒{{ $customer->postal_code }}
                        </p>
                        <p class="mt-1 whitespace-pre-wrap break-words">{{ $customer->address }}</p>
                    </div>
                </div>

                {{-- 補足情報 --}}
                @if (filled($customer->email) || filled($customer->memo))
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-500">
                            補足情報
                        </span>

                        @if (filled($customer->email))
                            <span class="badge badge-success badge-outline badge-sm">
                                メール
                            </span>
                        @endif

                        @if (filled($customer->memo))
                            <span class="badge badge-outline badge-sm border-amber-600 text-amber-700">
                                メモ
                            </span>
                        @endif
                    </div>
                @endif

                <div class="mt-4 flex justify-end border-t border-slate-100 pt-3">
                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline btn-sm">
                        詳細
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                {{ $keyword !== '' ? '検索条件に一致する顧客はいません。' : '顧客が登録されていません。' }}
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    <div class="mt-8">
        {{ $customers->links() }}
    </div>
@endsection
