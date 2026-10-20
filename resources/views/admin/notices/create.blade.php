@extends('layouts.admin')

@section('title', 'お知らせ登録')

@section('content')
    {{-- 一覧へ戻る --}}
    <a href="{{ route('admin.notices.index') }}"
        class="mb-6 inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
        <x-arrow direction="left" /> お知らせ一覧へ戻る
    </a>

    {{-- ページタイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            お知らせ登録
        </h1>
    </div>

    <form method="POST" action="{{ route('admin.notices.store') }}">
        @include('admin.notices._form')

        {{-- 登録ボタン --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.notices.index') }}" class="btn btn-outline btn-neutral">
                キャンセル
            </a>

            <button type="submit" class="btn btn-success">
                お知らせを登録
            </button>
        </div>
    </form>
@endsection
