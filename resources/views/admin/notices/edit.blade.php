@extends('layouts.admin')

@section('title', 'お知らせ編集')

@section('content')
    {{-- 一覧へ戻る --}}
    <a href="{{ route('admin.notices.index') }}"
        class="mb-6 inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
        <x-arrow direction="left" /> お知らせ一覧へ戻る
    </a>

    {{-- ページタイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            お知らせ編集
        </h1>
    </div>

    <form method="POST" action="{{ route('admin.notices.update', $notice) }}">
        @method('PUT')

        @include('admin.notices._form')

        {{-- 更新ボタン --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.notices.index') }}" class="btn btn-outline btn-neutral">
                キャンセル
            </a>

            <button type="submit" class="btn btn-success">
                変更を保存
            </button>
        </div>
    </form>

    {{-- 削除 --}}
    <section class="mt-10 rounded-xl border border-red-200 bg-white p-5 lg:p-7">
        <h2 class="mb-2 text-lg font-bold">お知らせの削除</h2>

        <p class="mb-5 text-sm text-slate-500">
            このお知らせを削除します。削除後は顧客ページに表示されなくなります。
        </p>

        <form method="POST" action="{{ route('admin.notices.destroy', $notice) }}"
            onsubmit="return confirm('このお知らせを削除しますか？')">
            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-outline btn-error">
                お知らせを削除
            </button>
        </form>
    </section>
@endsection
