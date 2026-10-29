@extends('layouts.app')

@section('title', $product->name)

@section('content')
    {{-- カテゴリ名を取得 --}}
    @php
        $category = $product->category;
    @endphp

    @if ($isPreview ?? false)
        <div class="alert alert-warning mb-6">
            <span>
                プレビュー表示中です。お客様には公開されていない場合があります。
            </span>
        </div>
    @endif

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
        <span aria-current="page" class="text-base-content">
            {{ $product->name }}
        </span>
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
                <div class="relative aspect-square w-full overflow-hidden rounded-xl border border-base-300 bg-base-200">
                    @if ($product->image_path)
                        <img src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}"
                            class="h-full w-full object-contain">
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
                    {{ formatYen($product->price) }}
                    <span class="text-sm font-normal text-base-content/70">
                        （税込）
                    </span>
                </p>

                {{-- 購入操作 --}}
                @if (Auth::check() && Auth::user()->is_admin && !($isPreview ?? false))
                    {{-- 管理者には購入操作を表示しない --}}
                    <div class="rounded-lg border border-warning/40 bg-warning/10 p-4">

                        <p class="mb-3 flex items-center gap-2 text-sm font-bold text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z
                                                                           M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.8 1.8-.06-.06
                                                                           A1.7 1.7 0 0 0 16.06 18.4a1.7 1.7 0 0 0-1.56 1.1V20h-5
                                                                           v-.5a1.7 1.7 0 0 0-1.56-1.1 1.7 1.7 0 0 0-1.88.34l-.06.06
                                                                           -1.8-1.8.06-.06A1.7 1.7 0 0 0 4.6 15.06 1.7 1.7 0 0 0 3.5 13.5H3v-3h.5
                                                                           A1.7 1.7 0 0 0 4.6 8.94a1.7 1.7 0 0 0-.34-1.88L4.2 7l1.8-1.8.06.06
                                                                           A1.7 1.7 0 0 0 7.94 5.6 1.7 1.7 0 0 0 9.5 4.5V4h5v.5
                                                                           a1.7 1.7 0 0 0 1.56 1.1 1.7 1.7 0 0 0 1.88-.34L18 5.2 19.8 7l-.06.06
                                                                           A1.7 1.7 0 0 0 19.4 8.94a1.7 1.7 0 0 0 1.1 1.56h.5v3h-.5
                                                                           A1.7 1.7 0 0 0 19.4 15Z" />
                            </svg>
                            管理者用メニュー
                        </p>

                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning w-full">
                            商品を編集
                        </a>
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
                            <select name="quantity" class="select select-bordered" @disabled($isPreview ?? false)>
                                @for ($i = 1; $i <= config('cart.max_quantity'); $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-full" @disabled($isPreview ?? false)>
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
            <x-arrow direction="left" /> 商品一覧へ戻る
        </a>

    </div>
@endsection
