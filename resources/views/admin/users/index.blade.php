@extends('layouts.admin')

@section('title', '会員管理')

@section('content')
    {{-- タイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            会員管理
        </h1>
    </div>

    {{-- 件数 --}}
    <div class="mb-4 text-sm text-slate-500">
        {{ $users->total() }}件の会員
    </div>

    {{-- PC：テーブル --}}
    <div class="hidden overflow-x-auto rounded-xl border border-slate-200 lg:block">
        <table class="table w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th>会員ID</th>
                    <th>氏名</th>
                    <th>メールアドレス</th>
                    <th>電話番号</th>
                    <th>登録日</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="text-slate-500">
                            {{ $user->id }}
                        </td>
                        <td class="font-semibold">
                            {{ $user->name }}
                        </td>
                        <td>
                            {{ $user->email }}
                        </td>
                        <td>
                            {{ $user->phone ?: '未登録' }}
                        </td>
                        <td>
                            {{ $user->created_at->format('Y/m/d') }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline btn-sm">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500">
                            会員が登録されていません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- スマホ：カード --}}
    <div class="space-y-4 lg:hidden">
        @forelse ($users as $user)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold">
                            {{ $user->name }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            会員ID：{{ $user->id }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-slate-500">
                            メールアドレス
                        </p>
                        <p class="mt-1 break-all">
                            {{ $user->email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500">
                            電話番号
                        </p>
                        <p class="mt-1">
                            {{ $user->phone ?: '未登録' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500">
                            登録日
                        </p>
                        <p class="mt-1">
                            {{ $user->created_at->format('Y/m/d') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex justify-end border-t border-slate-100 pt-3">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline btn-sm">
                        詳細
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                会員が登録されていません。
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    <div class="mt-8">
        {{ $users->links() }}
    </div>
@endsection
