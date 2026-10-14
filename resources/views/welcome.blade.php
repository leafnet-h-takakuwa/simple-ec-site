@extends('layouts.app')

@section('content')
    {{-- メインビジュアル --}}
    <section class="mb-12 overflow-hidden rounded-xl bg-base-200">

        <div class="grid items-center gap-6 p-6 md:grid-cols-2 md:p-10">

            {{-- キャッチコピー --}}
            <div class="space-y-5">

                <p class="text-sm font-semibold tracking-widest text-base-content/60">
                    髙桑商店
                </p>

                <h2 class="text-3xl font-bold leading-relaxed md:text-4xl">
                    海の恵みを、<br>
                    食卓へ。
                </h2>

                <p class="leading-relaxed text-base-content/70">
                    こだわりのすじこ・たらこをお届けします。<br>
                    毎日の食卓に、ちょっとした贅沢を。
                </p>

                <a href="{{ route('products.index') }}" class="btn btn-neutral">
                    商品一覧を見る
                </a>

            </div>

            {{-- メイン画像 --}}
            <div class="overflow-hidden rounded-lg bg-base-300">
                <img src="{{ asset('images/top.png') }}" alt="top" class="h-full w-full object-cover">
            </div>

        </div>

    </section>

    {{-- カテゴリから探す --}}
    <section class="mb-12">

        <h2 class="mb-6 border-b border-base-300 pb-3 text-2xl font-bold">
            カテゴリから探す
        </h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->value]) }}"
                    class="group overflow-hidden rounded-xl border border-base-300 bg-base-100 transition hover:shadow-md">

                    {{-- カテゴリ画像 --}}
                    <div class="overflow-hidden">
                        <img src="{{ asset('images/categories/' . $category->value . '.webp') }}"
                            alt="{{ $category->label() }}"
                            class="aspect-[400/260] w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy">
                    </div>

                    <div class="flex items-center justify-between p-4">
                        <h3 class="font-bold">
                            {{ $category->label() }}
                        </h3>
                    </div>

                </a>
            @endforeach

        </div>

    </section>

    {{-- 人気商品 --}}
    <section class="mb-12">

        <div class="mb-6 flex items-center justify-between border-b border-base-300 pb-3">
            <h2 class="text-2xl font-bold">
                人気商品
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">

            @forelse ($products as $product)
                <a href="{{ route('products.show', $product->id) }}"
                    class="group relative overflow-hidden rounded-xl border border-base-300 bg-base-100 transition hover:shadow-md">

                    {{-- ランキングバッジ --}}
                    @php
                        $rank = $loop->iteration;

                        $rankColor = match ($rank) {
                            1 => '#D4A017',
                            2 => '#94A3B8',
                            3 => '#B87333',
                            default => '#64748B',
                        };
                    @endphp

                    <div class="absolute top-0 left-4 z-10 w-14 drop-shadow-md">
                        <svg viewBox="0 0 56 90" xmlns="http://www.w3.org/2000/svg" class="w-full" role="img"
                            aria-label="{{ $rank }}位">

                            {{-- リボン本体 --}}
                            <path d="M0 0 H56 V90 L28 76 L0 90 Z" fill="{{ $rankColor }}" />

                            {{-- リボンの縁 --}}
                            <path d="M4 0 V83 L28 71 L52 83 V0" fill="none" stroke="white" stroke-opacity="0.55"
                                stroke-width="1.5" />

                            {{-- 王冠 --}}
                            <g fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">

                                <path d="M12 19 L17 32 H39 L44 19 L34 26 L28 16 L22 26 Z" />

                                <path d="M17 36 H39" />

                            </g>

                            {{-- 王冠の先端 --}}
                            <g fill="white">
                                <circle cx="12" cy="18" r="2" />
                                <circle cx="28" cy="15" r="2" />
                                <circle cx="44" cy="18" r="2" />
                            </g>

                            {{-- 順位 --}}
                            <text x="28" y="59" text-anchor="middle" font-size="27" font-weight="bold" fill="white">
                                {{ $rank }}
                            </text>
                        </svg>
                    </div>

                    {{-- 商品画像 --}}
                    <div class="aspect-[4/3] overflow-hidden bg-base-200">
                        @if ($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                            <x-no-image />
                        @endif
                    </div>

                    {{-- 商品情報 --}}
                    <div class="space-y-3 p-5">
                        <h3 class="font-bold group-hover:underline">
                            {{ $product->name }}
                        </h3>

                        <p class="text-sm text-base-content/60">
                            内容量：{{ $product->volume }}
                        </p>

                        <p class="text-xl font-bold">
                            ¥{{ number_format($product->price) }}
                            <span class="text-xs font-normal">（税込）</span>
                        </p>
                    </div>
                </a>
            @empty
                <p class="col-span-full py-8 text-center text-base-content/60">
                    現在、商品を準備中です。
                </p>
            @endforelse

        </div>

    </section>

    {{-- ご利用案内 --}}
    <section class="mb-8">

        <h2 class="mb-6 border-b border-base-300 pb-3 text-2xl font-bold">
            ご利用案内
        </h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

            {{-- ご注文方法 --}}
            <a href="#"
                class="group flex items-center gap-4 rounded-xl border border-base-300 p-6 transition hover:bg-base-200">

                <svg xmlns="http://www.w3.org/2000/svg" class="size-10 shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="3" width="14" height="18" rx="2" />
                    <path d="M9 8h6M9 12h6M9 16h4" />
                </svg>

                <div class="flex-1">
                    <h3 class="font-bold">
                        ご注文方法
                    </h3>
                    <p class="mt-1 text-sm text-base-content/60">
                        ご購入の流れをご案内します。
                    </p>
                </div>
            </a>

            {{-- 送料について --}}
            <a href="#"
                class="group flex items-center gap-4 rounded-xl border border-base-300 p-6 transition hover:bg-base-200">

                <svg xmlns="http://www.w3.org/2000/svg" class="size-10 shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h11v11H3z" />
                    <path d="M14 10h4l3 4v3h-7z" />
                    <circle cx="7.5" cy="18" r="2" />
                    <circle cx="17.5" cy="18" r="2" />
                </svg>

                <div class="flex-1">
                    <h3 class="font-bold">
                        送料について
                    </h3>
                    <p class="mt-1 text-sm text-base-content/60">
                        配送方法・送料をご確認いただけます。
                    </p>
                </div>
            </a>

        </div>

    </section>
@endsection
