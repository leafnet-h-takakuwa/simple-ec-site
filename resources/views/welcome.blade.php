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
                    <span aria-hidden="true">→</span>
                </a>

            </div>

            {{-- 仮メイン画像 --}}
            <div class="overflow-hidden rounded-lg bg-base-300">
                <svg viewBox="0 0 600 400" xmlns="http://www.w3.org/2000/svg" class="h-auto w-full" role="img"
                    aria-label="メインビジュアルの仮画像">

                    <rect width="600" height="400" fill="#e5e7eb" />

                    <circle cx="300" cy="170" r="65" fill="none" stroke="#9ca3af" stroke-width="8" />

                    <path d="M245 185 Q300 110 355 185" fill="none" stroke="#9ca3af" stroke-width="8" />

                    <path d="M120 320 L245 210 L335 285 L415 225 L510 320" fill="none" stroke="#9ca3af" stroke-width="8"
                        stroke-linecap="round" stroke-linejoin="round" />

                    <text x="300" y="365" text-anchor="middle" fill="#6b7280" font-size="22">
                        メインビジュアル
                    </text>
                </svg>
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

                    {{-- 仮カテゴリ画像 --}}
                    <svg viewBox="0 0 400 260" xmlns="http://www.w3.org/2000/svg" class="w-full" role="img"
                        aria-label="{{ $category->label() }}の仮画像" <rect width="400" height="260" fill="#e5e7eb" />

                    <rect x="120" y="55" width="160" height="120" rx="12" fill="none" stroke="#9ca3af"
                        stroke-width="7" />

                    <circle cx="165" cy="95" r="15" fill="#9ca3af" />

                    <path d="M130 160 L175 115 L210 145 L240 120 L270 160" fill="none" stroke="#9ca3af" stroke-width="7"
                        stroke-linejoin="round" />

                    <text x="200" y="220" text-anchor="middle" fill="#6b7280" font-size="18">
                        {{ $category->label() }}
                    </text>

                    </svg>

                    <div class="flex items-center justify-between p-4">
                        <h3 class="font-bold">
                            {{ $category->label() }}
                        </h3>

                        <span class="transition group-hover:translate-x-1">
                            →
                        </span>
                    </div>

                </a>
            @endforeach

        </div>

    </section>

    {{-- おすすめ商品 --}}
    <section class="mb-12">

        <div class="mb-6 flex items-center justify-between border-b border-base-300 pb-3">
            <h2 class="text-2xl font-bold">
                おすすめ商品
            </h2>

            <a href="#" class="link link-hover text-sm">
                すべて見る →
            </a>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">

            @forelse ($products as $product)
                <a href="#"
                    class="group overflow-hidden rounded-xl border border-base-300 bg-base-100 transition hover:shadow-md">

                    {{-- 仮商品画像 --}}
                    <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" class="w-full" role="img"
                        aria-label="{{ $product->name }}の仮画像">

                        <rect width="400" height="300" fill="#e5e7eb" />

                        <rect x="120" y="60" width="160" height="140" rx="12" fill="none" stroke="#9ca3af"
                            stroke-width="7" />

                        <circle cx="165" cy="100" r="15" fill="#9ca3af" />

                        <path d="M130 185 L175 135 L210 165 L240 140 L270 185" fill="none" stroke="#9ca3af"
                            stroke-width="7" stroke-linejoin="round" />

                        <text x="200" y="245" text-anchor="middle" fill="#6b7280" font-size="18">
                            商品画像
                        </text>

                    </svg>

                    <div class="space-y-3 p-5">

                        <h3 class="font-bold group-hover:underline">
                            {{ $product->name }}
                        </h3>

                        <p class="text-sm text-base-content/60">
                            内容量：{{ $product->volume }}
                        </p>

                        <p class="text-xl font-bold">
                            ¥{{ number_format($product->price) }}
                            <span class="text-xs font-normal">
                                （税込）
                            </span>
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

                <span class="transition group-hover:translate-x-1">
                    →
                </span>

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

                <span class="transition group-hover:translate-x-1">
                    →
                </span>

            </a>

        </div>

    </section>
@endsection
