@extends('layouts.app')

@section('title', 'TOP')

@section('content')
    {{-- メインビジュアル --}}
    <section class="mb-12 overflow-hidden rounded-xl bg-base-200">

        <div class="grid items-center gap-6 p-6 md:grid-cols-2 md:p-10">

            <div class="object-contain overflow-hidden bg-base-300">
                <img src="{{ asset('images/top1.png') }}" alt="メイン写真" class="h-full w-full object-cover">
            </div>

            {{-- キャッチコピー --}}
            <div class="space-y-5">

                <h2 class="font-serif text-3xl font-semibold leading-relaxed md:text-4xl">
                    自信のある商品を<br>
                    笑顔とおもてなしで お届けします
                </h2>

                <p class="leading-relaxed text-base-content/70">
                    先代からの教え「笑顔」「品質」「おもてなし」を<br>
                    今でも心に刻み、自分たちの納得した商品を<br>
                    お客様のもとへと届けます。<br>
                    ご来店いただいたお客様へも、<br>
                    遠くからご注文いただいたお客様へも、<br>
                    私たちが自信と責任をもって<br>
                    青森の"うまい"ものお届けします。
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-neutral">
                        商品一覧を見る
                    </a>

                    <a href="{{ route('fax-order-form.pdf', ['ts' => now()->timestamp]) }}" target="_blank" rel="noopener"
                        class="btn btn-outline btn-neutral">
                        <x-icons.fax />
                        FAX注文書
                    </a>
                </div>

            </div>
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

            @forelse ($productsPopular as $product)
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
                            {{ formatYen($product->price) }}
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

    {{-- 新商品 --}}
    <section class="mb-12">

        <div class="mb-6 flex items-center justify-between border-b border-base-300 pb-3">
            <h2 class="text-2xl font-bold">
                新商品
            </h2>
            <a href="{{ route('products.index', ['sort' => 'new']) }}" class="inline-flex items-center gap-2 text-sm ">
                すべて見る <x-arrow direction="right" />
            </a>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">

            @forelse ($productsNew as $product)
                <a href="{{ route('products.show', $product->id) }}"
                    class="group relative overflow-hidden rounded-xl border border-base-300 bg-base-100 transition hover:shadow-md">

                    {{-- 商品画像 --}}
                    <div class="relative aspect-[4/3] overflow-hidden bg-base-200">
                        @if ($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                            <x-no-image />
                        @endif

                        {{-- 新着リボン --}}
                        @if ($product->isNew())
                            <x-new-ribbon />
                        @endif
                        {{-- 定番リボン --}}
                        @if ($product->is_standard)
                            <x-standard-ribbon />
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
                            {{ formatYen($product->price) }}
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

    {{-- お知らせ --}}
    <section class="mb-12">

        <div class="mb-6 flex items-center justify-between gap-4 border-b border-base-300 pb-3">
            <h2 class="text-2xl font-bold">
                お知らせ
            </h2>

            <a href="{{ route('notices.index') }}" class="inline-flex items-center gap-2 text-sm">
                すべて見る <x-arrow direction="right" />
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-base-300 bg-base-100">
            @forelse ($notices as $notice)
                <a href="{{ route('notices.show', $notice) }}"
                    class="group flex flex-col gap-2 border-b border-base-300 px-5 py-5 transition-colors last:border-b-0 hover:bg-base-200 sm:flex-row sm:items-center sm:gap-8">

                    {{-- 公開日 --}}
                    <time class="shrink-0 text-sm text-base-content/60"
                        datetime="{{ $notice->published_at->format('Y-m-d') }}">
                        {{ $notice->published_at->format('Y/m/d') }}
                    </time>

                    {{-- タイトル --}}
                    <span class="flex-1 font-medium leading-relaxed group-hover:text-primary">
                        {{ $notice->title }}

                        @if ($notice->isNew())
                            <span class="badge badge-outline badge-sm border-amber-600 text-amber-600">
                                NEW
                            </span>
                        @endif
                    </span>

                    <x-arrow direction="right" class="hidden shrink-0 text-base-content/40 sm:block" />
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-base-content/60">
                    現在、お知らせはありません。
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
            <a href="{{ route('guide.order') }}"
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
            <a href="{{ route('guide.shipping') }}"
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

            {{-- FAXでのご注文 --}}
            <a href="{{ route('fax-order-form.pdf', ['ts' => now()->timestamp]) }}" target="_blank" rel="noopener"
                class="group flex items-center gap-4 rounded-xl border border-base-300 p-6 transition hover:bg-base-200">
                <x-icons.fax class="size-10 shrink-0" />
                <div class="flex-1">
                    <h3 class="font-bold">
                        FAXでのご注文
                    </h3>
                    <p class="mt-1 text-sm text-base-content/60">
                        FAX注文書はこちらからダウンロードできます。
                    </p>
                </div>
            </a>


            {{-- パンフレット --}}
            <a href="{{ asset('documents/pamphlet.pdf') }}" target="_blank" rel="noopener noreferrer"
                class="group flex items-center gap-4 rounded-xl border border-base-300 p-6 transition hover:bg-base-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-10 shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M12 6.5c-2.5-1.8-5.4-2.2-9-1.4v13c3.6-.8 6.5-.4 9 1.4 2.5-1.8 5.4-2.2 9-1.4v-13c-3.6-.8-6.5-.4-9 1.4Z" />
                    <path d="M12 6.5v13" />
                    <path d="M5.5 9c1.5-.1 2.8.1 4 .7" />
                    <path d="M14.5 9.7c1.2-.6 2.5-.8 4-.7" />
                    <path d="M5.5 12c1.5-.1 2.8.1 4 .7" />
                    <path d="M14.5 12.7c1.2-.6 2.5-.8 4-.7" />
                </svg>

                <div class="flex-1">
                    <h3 class="font-bold">
                        髙桑商店パンフレット
                    </h3>
                    <p class="mt-1 text-sm text-base-content/60">
                        パンフレットをPDFでご覧いただけます。
                    </p>
                </div>
            </a>

        </div>
    </section>
@endsection
