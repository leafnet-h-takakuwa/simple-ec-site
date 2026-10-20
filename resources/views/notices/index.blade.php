@extends('layouts.app')

@section('title', 'お知らせ')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">

        {{-- パンくずリスト --}}
        <nav class="mb-5 text-sm text-base-content/60">

            <a href="{{ route('home') }}" class="link link-hover">
                TOP
            </a>
            <span class="mx-2">＞</span>
            <span aria-current="page" class="text-base-content">
                お知らせ
            </span>
        </nav>

        {{-- ページタイトル --}}
        <h1 class="mb-2 text-2xl font-bold lg:text-3xl">
            お知らせ
        </h1>

        <p class="mb-8 text-sm text-base-content/60">
            当店からの最新情報をご案内します。
        </p>

        @if ($notices->isEmpty())
            <div class="rounded-xl border border-base-300 bg-base-100 p-10 text-center">
                <p class="text-base-content/60">
                    現在、お知らせはありません。
                </p>

                <a href="{{ route('home') }}" class="btn btn-primary mt-6">
                    トップページへ戻る
                </a>
            </div>
        @else
            {{-- お知らせ一覧（PC・スマホ共通） --}}
            <div class="overflow-hidden rounded-xl border border-base-300 bg-base-100">
                <ul class="divide-y divide-base-300">
                    @foreach ($notices as $notice)
                        <li>
                            <a href="{{ route('notices.show', $notice) }}"
                                class="group flex flex-col gap-2 px-5 py-5 transition-colors hover:bg-base-200 sm:flex-row sm:items-center sm:gap-8 lg:px-7">

                                {{-- 公開日 --}}
                                <time class="shrink-0 text-sm text-base-content/60"
                                    datetime="{{ $notice->published_at->format('Y-m-d') }}">
                                    {{ $notice->published_at->format('Y/m/d') }}
                                </time>

                                {{-- タイトル --}}
                                <span class="flex-1 font-medium leading-relaxed group-hover:text-primary">
                                    {{ $notice->title }}

                                    @if ($notice->isNew())
                                        <span class="badge badge-success badge-outline badge-sm">NEW</span>
                                    @endif
                                </span>

                                {{-- 矢印 --}}
                                <x-arrow direction="right" class="hidden shrink-0 text-base-content/40 sm:block" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- ページネーション --}}
            <div class="mt-8">
                {{ $notices->links() }}
            </div>
        @endif
    </div>
@endsection
