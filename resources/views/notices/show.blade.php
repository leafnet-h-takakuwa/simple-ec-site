@extends('layouts.app')

@section('title', $notice->title)

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8">

        {{-- パンくずリスト --}}
        <nav class="mb-5 text-sm text-base-content/60">

            <a href="{{ route('home') }}" class="link link-hover">
                TOP
            </a>
            <span class="mx-2">＞</span>
            <a href="{{ route('notices.index') }}" class="link link-hover">
                お知らせ
            </a>
            <span class="mx-2">＞</span>
            <span aria-current="page" class="text-base-content">
                {{ $notice->title }}
            </span>
        </nav>

        {{-- ページタイトル --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold leading-relaxed lg:text-3xl">
                {{ $notice->title }}
            </h1>

            <p class="mt-3 text-sm text-base-content/60">
                {{ $notice->published_at->format('Y/m/d') }}
            </p>
        </div>

        {{-- お知らせ本文 --}}
        <section class="rounded-xl border border-base-300 bg-base-100 p-5 lg:p-7">
            <div class="notice-content leading-relaxed break-words">
                {!! $notice->body !!}
            </div>
        </section>

        {{-- 一覧へ戻る --}}
        <div class="mt-8 text-center">
            <a href="{{ route('notices.index') }}" class="btn btn-outline">
                お知らせ一覧へ戻る
            </a>
        </div>
    </div>
@endsection
