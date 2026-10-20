@extends('layouts.app')

@section('title', $notice->title)

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8">

        {{-- 一覧へ戻る --}}
        <a href="{{ route('notices.index') }}"
            class="mb-6 inline-flex items-center gap-2 text-sm text-primary hover:underline">
            <x-arrow direction="left" /> お知らせ一覧へ戻る
        </a>

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
