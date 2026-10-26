@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">

        {{-- パンくずリスト --}}
        <nav class="mb-5 text-sm text-base-content/60">

            <a href="{{ route('home') }}" class="link link-hover">
                TOP
            </a>
            <span class="mx-2">＞</span>
            <span aria-current="page" class="text-base-content">
                カート
            </span>
        </nav>

        {{-- ページタイトル --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold">ショッピングカート</h1>
            <p class="mt-2 hidden text-sm text-base-content/60 sm:block">
                カートに入っている商品をご確認ください。
            </p>
        </div>

        @if ($cart_items->isEmpty())
            {{-- カートが空の場合 --}}
            <div class="rounded-lg border border-base-300 bg-base-100 px-6 py-16 text-center">
                <p class="text-base-content/60">カートに商品がありません。</p>

                <a href="{{ route('products.index') }}" class="btn btn-neutral mt-6">
                    商品を探す
                </a>
            </div>
        @else
            {{-- 商品一覧 --}}
            <div class="space-y-4">

                @foreach ($cart_items as $item)
                    @php
                        $product = $item->product;
                        $subtotal = $item->subtotal();
                    @endphp

                    <div class="rounded-lg border border-base-300 bg-base-100 p-4 sm:p-5">

                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center">

                            {{-- 商品画像・商品情報 --}}
                            <div class="flex min-w-0 flex-1 gap-4">

                                <a href="{{ route('products.show', $product->id) }}"
                                    class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-base-200 text-sm text-base-content/40 sm:h-32 sm:w-40">
                                    {{-- 商品画像 --}}
                                    @if ($product->image_path)
                                        <img src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}"
                                            class="h-full w-full object-cover">
                                    @else
                                        <x-no-image />
                                    @endif
                                </a>

                                <div class="min-w-0">
                                    <a href="{{ route('products.show', $product->id) }}"
                                        class="font-bold hover:underline sm:text-lg">
                                        {{ $product->name }}
                                    </a>

                                    <p class="mt-2 text-sm text-base-content/60">
                                        内容量：{{ $product->volume }}
                                    </p>

                                    <p class="mt-3 text-sm sm:text-base">
                                        <span class="hidden lg:inline">単価：</span>
                                        {{ formatYen($product->price) }}
                                    </p>
                                </div>
                            </div>

                            {{-- 数量変更・小計 --}}
                            <div
                                class="flex flex-col gap-4 border-t border-base-200 pt-4 lg:w-80 lg:shrink-0 lg:border-0 lg:pt-0">

                                <div class="flex items-end justify-between gap-3 lg:items-center">

                                    <form action="{{ route('carts.update', ['product_id' => $item->product_id]) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="mb-1 block text-sm">
                                                数量
                                            </label>

                                            <div class="flex items-center gap-2">
                                                <select name="quantity" class="select select-bordered w-20">
                                                    @for ($i = 1; $i <= config('cart.max_quantity'); $i++)
                                                        <option value="{{ $i }}" @selected($item->quantity == $i)>
                                                            {{ $i }}
                                                        </option>
                                                    @endfor
                                                </select>

                                                <button type="submit" class="btn btn-outline">
                                                    変更
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="text-right">
                                        <span class="mb-1 block text-xs text-base-content/60 lg:hidden">
                                            小計
                                        </span>

                                        <p class="text-lg font-bold sm:text-xl">
                                            {{ formatYen($subtotal) }}
                                        </p>
                                    </div>
                                </div>

                                {{-- 削除ボタン --}}
                                <div class="text-right">
                                    <button type="button" class="text-sm text-error hover:underline"
                                        onclick="document.getElementById('delete_modal_{{ $item->product_id }}').showModal()">
                                        削除
                                    </button>
                                </div>

                                {{-- 削除確認モーダル --}}
                                <dialog id="delete_modal_{{ $item->product_id }}" class="modal">
                                    <div class="modal-box">
                                        <h3 class="text-lg font-bold">
                                            商品を削除しますか？
                                        </h3>

                                        <p class="py-4">
                                            「{{ $product->name }}（{{ $product->volume }}）」をカートから削除します。
                                        </p>

                                        <div class="modal-action">
                                            {{-- キャンセル --}}
                                            <form method="dialog">
                                                <button type="submit" class="btn btn-ghost">
                                                    キャンセル
                                                </button>
                                            </form>

                                            {{-- 削除実行 --}}
                                            <form
                                                action="{{ route('carts.destroy', ['product_id' => $item->product_id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-error">
                                                    削除する
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- モーダルの背景をクリックして閉じる --}}
                                    <form method="dialog" class="modal-backdrop">
                                        <button type="submit">閉じる</button>
                                    </form>
                                </dialog>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

            {{-- 商品代金合計 --}}


            <div class="mt-6 rounded-lg border border-base-300 bg-base-200/50 p-5 sm:p-7">

                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-bold sm:text-xl">
                        商品代金合計
                    </h2>

                    <p class="text-2xl font-bold sm:text-3xl">
                        {{ formatYen($total) }}
                    </p>
                </div>

                <div class="mt-4 space-y-1 text-sm text-base-content/70">
                    <p>※ 送料は別途かかります。金額はご注文受付後にご案内します。</p>
                    <p>※ 代金引換をご利用の場合は、別途代引手数料がかかります。</p>

                    <a href="{{ route('guide.shipping') }}" target="_blank" rel="noopener noreferrer"
                        class="link link-hover mt-2 inline-block text-primary">
                        送料・手数料について詳しく見る
                    </a>
                </div>

            </div>

            {{-- 操作ボタン --}}
            <div class="mt-5 flex flex-col-reverse gap-5 sm:flex-row sm:items-center sm:justify-between">

                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center justify-center gap-2 text-sm hover:underline">
                    <x-arrow direction="left" /> お買い物を続ける
                </a>

                <a href="{{ route('orders.create') }}" class="btn btn-primary w-full sm:w-auto sm:min-w-64">
                    注文手続きへ進む <x-arrow direction="right" />
                </a>

            </div>
        @endif

    </div>
@endsection
