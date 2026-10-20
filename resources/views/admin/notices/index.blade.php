@extends('layouts.admin')

@section('title', 'お知らせ管理')

@section('content')
    {{-- ページタイトル --}}
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold lg:text-3xl">お知らせ管理</h1>

        <a href="{{ route('admin.notices.create') }}" class="btn btn-success">
            <x-plus /> お知らせを追加
        </a>
    </div>

    {{-- 件数 --}}
    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="font-bold">
            お知らせ {{ number_format($notices->total()) }}件
        </p>

        <span class="text-sm text-slate-500">
            公開日：新しい順 ↓
        </span>
    </div>

    {{-- ページネーション --}}
    <div class="mb-6">
        {{ $notices->links() }}
    </div>

    {{-- PC：テーブル表示 --}}
    <div class="hidden overflow-x-auto rounded-xl border border-slate-200 bg-white lg:block">
        <table class="table w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th>公開日</th>
                    <th>タイトル</th>
                    <th>公開状態</th>
                    <th>操作</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($notices as $notice)
                    @php
                        $isReserved = $notice->is_published && $notice->published_at?->gt(today());
                    @endphp

                    <tr>
                        <td class="whitespace-nowrap">
                            {{ $notice->published_at?->format('Y/m/d') ?? '未設定' }}
                        </td>

                        <td class="font-semibold">
                            {{ $notice->title }}
                        </td>

                        <td>
                            @if (!$notice->is_published)
                                <span class="badge badge-ghost">非公開</span>
                            @elseif ($isReserved)
                                <span class="badge badge-warning">公開予約</span>
                            @else
                                <span class="badge badge-success">公開中</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('admin.notices.edit', $notice) }}" class="btn btn-outline btn-sm">
                                編集
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- スマホ：カード表示 --}}
    <div class="space-y-4 lg:hidden">
        @foreach ($notices as $notice)
            @php
                $isReserved = $notice->is_published && $notice->published_at?->gt(today());
            @endphp

            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="text-sm text-slate-500">
                        {{ $notice->published_at?->format('Y/m/d') ?? '未設定' }}
                    </span>

                    @if (!$notice->is_published)
                        <span class="badge badge-ghost">非公開</span>
                    @elseif ($isReserved)
                        <span class="badge badge-warning">公開予約</span>
                    @else
                        <span class="badge badge-success">公開中</span>
                    @endif
                </div>

                <h2 class="mt-4 font-bold">
                    {{ $notice->title }}
                </h2>

                <div class="mt-4 text-right">
                    <a href="{{ route('admin.notices.edit', $notice) }}" class="btn btn-outline btn-sm">
                        編集
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    {{-- データなし --}}
    @if ($notices->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">
            お知らせはありません。
        </div>
    @endif
@endsection
