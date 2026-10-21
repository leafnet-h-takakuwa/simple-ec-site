@extends('layouts.admin')

@section('title', '商品管理')

@section('content')
    {{-- タイトル --}}
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold lg:text-3xl">
                商品管理
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                商品情報を管理します。<br>
                公開中の商品のみ表示され、定番商品に設定すると商品一覧で優先的に表示されます。
            </p>
        </div>

        <a href="{{ route('admin.products.create') }}" class="btn btn-success">
            <x-plus /> 商品を登録
        </a>
    </div>

    {{-- 検索フォーム --}}
    <form method="GET" action="{{ route('admin.products.index') }}"
        class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div>
                <label for="name" class="mb-2 block text-sm font-semibold">
                    商品名
                </label>
                <input type="text" name="name" id="name" value="{{ $conditions['name'] ?? '' }}"
                    placeholder="商品名を入力" class="input w-full">
            </div>

            <div>
                <label for="is_active" class="mb-2 block text-sm font-semibold">
                    公開状態
                </label>
                <select name="is_active" id="is_active" class="select w-full">
                    <option value="">すべて</option>
                    <option value="1" @selected(($conditions['is_active'] ?? '') === '1')>
                        公開中
                    </option>
                    <option value="0" @selected(($conditions['is_active'] ?? '') === '0')>
                        非公開
                    </option>
                </select>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline">
                クリア
            </a>
            <button type="submit" class="btn btn-success">
                検索
            </button>
        </div>
    </form>

    {{-- 検索結果 --}}
    <div class="mb-4 text-sm text-slate-500">
        {{ $products->total() }}件の商品
    </div>

    {{-- PC：テーブル --}}
    <div class="hidden overflow-x-auto rounded-xl border border-slate-200 lg:block">
        <table class="table w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th>画像</th>
                    <th>商品名</th>
                    <th>カテゴリ</th>
                    <th class="text-right">価格</th>
                    <th>公開状態</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-lg bg-slate-100">
                                @if ($product->image_path)
                                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                                        class="h-full w-full object-cover">
                                @else
                                    <x-no-image />
                                @endif
                            </div>
                        </td>
                        <td class="font-semibold">
                            <span>{{ $product->name }}</span>
                            @if ($product->is_standard)
                                <span class="ml-4 badge badge-outline badge-success badge-sm">
                                    定番
                                </span>
                            @endif
                        </td>
                        <td>
                            {{ $product->category->label() }}
                        </td>
                        <td class="text-right">
                            {{ formatYen($product->price) }}
                        </td>
                        <td>
                            @if ($product->is_active)
                                <span class="badge badge-success badge-outline">
                                    公開中
                                </span>
                            @else
                                <span class="badge badge-ghost">
                                    非公開
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline btn-sm">
                                編集
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500">
                            該当する商品がありません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- スマホ：カード --}}
    <div class="space-y-4 lg:hidden">
        @forelse ($products as $product)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex gap-4">
                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100">
                        @if ($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                                class="h-full w-full object-cover">
                        @else
                            <span class="text-xs text-slate-400">
                                No Image
                            </span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="font-semibold break-all">
                            {{ $product->name }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $product->category->label() }}
                        </p>

                        <p class="mt-2 font-bold">
                            {{ formatYen($product->price) }}
                        </p>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            @if ($product->is_active)
                                <span class="badge badge-success badge-outline">
                                    公開中
                                </span>
                            @else
                                <span class="badge badge-ghost">
                                    非公開
                                </span>
                            @endif

                            @if ($product->is_standard)
                                <span class="badge badge-success badge-outline">
                                    定番
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end border-t border-slate-100 pt-3">
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline btn-sm">
                        編集
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 p-8 text-center text-slate-500">
                該当する商品がありません。
            </div>
        @endforelse
    </div>

    {{-- ページネーション --}}
    <div class="mt-8">
        {{ $products->links() }}
    </div>
@endsection
