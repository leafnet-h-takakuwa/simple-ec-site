@extends('layouts.app')

@section('content')
    {{-- カテゴリ名を取得 --}}
    @php
        $category = $product->category;
    @endphp

    {{-- パンくずリスト --}}
    <nav class="mb-5 text-sm text-base-content/60">
        <a href="{{ route('home') }}" class="link link-hover">
            TOP
        </a>
        <span class="mx-2">＞</span>
        <a href="{{ route('products.index') }}" class="link link-hover">
            商品一覧
        </a>
        <span class="mx-2">＞</span>
        <a href="{{ route('products.index', ['category' => $category->value]) }}" class="link link-hover">
            {{ $category->label() }}
        </a>
    </nav>

    {{-- ページタイトル --}}
    <h2 class="mb-6 text-2xl font-bold sm:text-3xl">
        {{ $product->name }}
    </h2>

    {{-- 商品画像・購入エリア --}}
    <section class="mb-10">

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">

            {{-- 商品画像 --}}
            <div>

                <div class="overflow-hidden rounded-xl border border-base-300 bg-base-200">

                    {{-- 仮の商品画像 --}}
                    <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" class="aspect-square w-full"
                        role="img" aria-label="{{ $product->name }}の仮画像">

                        <rect width="400" height="400" fill="#e5e7eb" />

                        <rect x="100" y="95" width="200" height="170" rx="12" fill="none" stroke="#9ca3af"
                            stroke-width="7" />

                        <circle cx="155" cy="145" r="18" fill="#9ca3af" />

                        <path d="M115 245 L170 180 L215 220 L255 190 L285 245" fill="none" stroke="#9ca3af"
                            stroke-width="7" stroke-linejoin="round" />

                        <text x="200" y="320" text-anchor="middle" fill="#6b7280" font-size="20">
                            商品画像
                        </text>

                    </svg>

                </div>

            </div>

            {{-- 商品情報・購入エリア --}}
            <div class="flex flex-col">

                {{-- PC用の商品名 --}}
                <h3 class="mb-4 hidden text-2xl font-bold md:block">
                    {{ $product->name }}
                </h3>

                {{-- 内容量 --}}
                <p class="mb-6 text-base-content/80">
                    内容量：{{ $product->volume }}
                </p>

                {{-- 価格 --}}
                <p class="mb-7 text-3xl font-bold">
                    {{ number_format($product->price) }}円

                    <span class="text-sm font-normal text-base-content/70">
                        （税込）
                    </span>
                </p>

                {{-- 購入操作 --}}
                @if (Auth::check() && Auth::user()->is_admin)
                    {{-- 管理者には購入操作を表示しない --}}
                    <div class="rounded-lg bg-base-200 p-4">

                        <p class="text-sm text-base-content/70">
                            管理者として閲覧中です。
                            商品の購入操作はできません。
                        </p>

                    </div>
                @else
                    <form action="{{ route('carts.store') }}" method="POST">
                        <div class="mb-6 flex items-center gap-5">
                            {{-- 数量選択 --}}
                            <label for="quantity" class="font-bold">
                                数量
                            </label>

                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <select name="quantity" class="select select-bordered">
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            カートに入れる
                        </button>
                    </form>
                @endif

            </div>

        </div>

    </section>

    {{-- 商品説明 --}}
    <section class="mb-8">

        <h3 class="mb-4 border-b border-base-300 pb-3 text-xl font-bold">
            商品説明
        </h3>

        @if ($product->description)
            <p class="whitespace-pre-line leading-relaxed text-base-content/80">{{ $product->description }}</p>
        @else
            <p class="text-base-content/60">
                商品説明はありません。
            </p>
        @endif

    </section>

    {{-- 商品一覧に戻る --}}
    <div class="mb-10">

        <a href="{{ route('products.index') }}" class="btn btn-outline btn-neutral">
            ← 商品一覧へ戻る
        </a>

    </div>
@endsection
