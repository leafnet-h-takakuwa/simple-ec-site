@extends('layouts.app')

@section('content')
    {{-- ページタイトル --}}
    <section class="mb-8">

        {{-- パンくずリスト --}}
        <nav class="mb-5 text-sm text-base-content/60">

            <a href="{{ route('home') }}" class="link link-hover">
                TOP
            </a>
            <span class="mx-2">＞</span>
            <a href="{{ route('products.index') }}" class="link link-hover">
                商品一覧
            </a>
        </nav>

        <h2 class="text-3xl font-bold">
            商品一覧
        </h2>

        <p class="mt-3 text-base-content/70">
            髙桑商店自慢のすじこ・たらこをはじめ、
            こだわりの商品をご紹介します。
        </p>

    </section>

    {{-- カテゴリ切り替え --}}
    <section class="mb-8">

        <div class="flex flex-wrap gap-2">

            <a href="{{ route('products.index') }}" @class([
                'btn btn-sm sm:btn-md',
                'btn-neutral' => $category === null,
                'btn-outline' => $category !== null,
            ])>
                すべての商品
            </a>

            @foreach ($categories as $item)
                <a href="{{ route('products.index', ['category' => $item->value]) }}" @class([
                    'btn btn-sm sm:btn-md',
                    'btn-neutral' => $category === $item->value,
                    'btn-outline' => $category !== $item->value,
                ])>
                    {{ $item->label() }}
                </a>
            @endforeach

        </div>

    </section>

    {{-- 商品一覧 --}}
    <section class="mb-12">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-base-300 pb-3">

            <h3 class="text-xl font-bold">
                @if ($category === null)
                    すべての商品
                @else
                    {{ \App\Enums\ProductCategory::from($category)->label() }}
                @endif
            </h3>

            <p class="text-sm text-base-content/60">
                全 {{ $products->total() }} 件
            </p>

        </div>

        <div class="grid grid-cols-2 gap-5 lg:grid-cols-4">

            @forelse ($products as $product)
                <a href="{{ route('products.show', ['product' => $product->id]) }}"
                    class="group flex flex-col overflow-hidden rounded-xl border border-base-300 bg-base-100 transition hover:shadow-md">

                    {{-- 商品画像 --}}
                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-base-200">

                        @if ($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                                class="h-full w-full object-cover">
                        @else
                            <x-no-image />
                        @endif
                        {{-- 新着リボン --}}
                        @if ($product->isNew())
                            <x-new-ribbon />
                        @endif
                    </div>

                    {{-- 商品情報 --}}
                    <div class="flex flex-1 flex-col gap-3 p-5">

                        <div class="flex-1 space-y-2">

                            <h4 class="text-lg font-bold group-hover:underline">
                                {{ $product->name }}
                            </h4>

                            <p class="text-sm text-base-content/60">
                                内容量：{{ $product->volume }}
                            </p>

                            @if ($product->description)
                                <p class="line-clamp-2 text-sm leading-relaxed text-base-content/70">
                                    {{ $product->description }}
                                </p>
                            @endif

                        </div>

                        <p class="text-xl font-bold">
                            ¥{{ number_format($product->price) }}
                            <span class="text-xs font-normal">
                                （税込）
                            </span>
                        </p>

                        <span class="btn btn-outline btn-neutral w-full">
                            商品詳細
                        </span>

                    </div>

                </a>

            @empty

                <div class="col-span-full rounded-xl bg-base-200 px-6 py-12 text-center">

                    <p class="text-base-content/70">
                        現在、このカテゴリの商品はありません。
                    </p>

                    <a href="{{ route('products.index') }}" class="btn btn-neutral mt-5">
                        すべての商品を見る
                    </a>

                </div>
            @endforelse

        </div>

        {{-- ページネーション --}}
        @if ($products->hasPages())
            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @endif

    </section>
@endsection
