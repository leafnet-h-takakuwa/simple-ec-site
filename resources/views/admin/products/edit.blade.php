@extends('layouts.admin')

@section('title', '商品編集')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.products.index') }}"
            class="inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
            <x-arrow direction="left" /> 商品一覧へ戻る
        </a>

        <h1 class="mt-4 text-2xl font-bold lg:text-3xl">
            商品編集
        </h1>
    </div>

    {{-- 更新フォーム --}}
    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data"
        class="space-y-8">
        @csrf
        @method('PUT')

        {{-- 基本情報 --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-6 text-lg font-bold">基本情報</h2>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label for="name" class="mb-2 block font-semibold">
                        商品名 <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}"
                        maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" class="input w-full" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category" class="mb-2 block font-semibold">
                        分類 <span class="text-red-600">*</span>
                        <span class="text-xs font-normal text-slate-500">
                            （登録数 / 非公開数）
                        </span>
                    </label>
                    <select name="category" id="category" class="select w-full" required>
                        @foreach ($categories as $category)
                            @php
                                $count = $categoryCounts[$category->value] ?? null;
                                $total = $count?->total ?? 0;
                                $inactive = $count?->inactive ?? 0;
                            @endphp
                            <option value="{{ $category->value }}" @selected(old('category', $product->category->value) === $category->value)>
                                {{ $category->label() }}（{{ $categoryCounts[$category->value]->total ?? 0 }} /
                                {{ $categoryCounts[$category->value]->inactive ?? 0 }}）
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="volume" class="mb-2 block font-semibold">
                        内容量 <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="volume" id="volume" value="{{ old('volume', $product->volume) }}"
                        maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" class="input w-full" required>
                    @error('volume')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="mb-2 block font-semibold">
                        価格（税込） <span class="text-red-600">*</span>
                    </label>
                    <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}"
                        min="0" step="1" class="input w-full" required>
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tax_rate" class="mb-2 block font-semibold">
                        消費税率 <span class="text-red-600">*</span>
                    </label>

                    <select name="tax_rate" id="tax_rate" class="select w-full" required>
                        @foreach (config('shop.tax.rates') as $rate => $label)
                            <option value="{{ $rate }}" @selected((int) old('tax_rate', $product->tax_rate) === (int) $rate)>
                                {{ $rate }}%（{{ $label }}）
                            </option>
                        @endforeach
                    </select>

                    @error('tax_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <p class="mb-2 font-semibold">
                        公開状態 <span class="text-red-600">*</span>
                    </p>
                    <div class="flex min-h-10 items-center gap-6">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="radio" name="is_active" value="1" class="radio radio-success"
                                @checked((string) old('is_active', (int) $product->is_active) === '1')>
                            公開中
                        </label>

                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="radio" name="is_active" value="0" class="radio radio-success"
                                @checked((string) old('is_active', (int) $product->is_active) === '0')>
                            非公開
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" name="is_standard" value="1" class="checkbox checkbox-success"
                            @checked(old('is_standard', $product->is_standard ?? false))>
                        <span class="font-medium">定番商品</span>
                    </label>
                </div>
            </div>
        </section>

        {{-- 商品説明 --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">商品説明</h2>

            <textarea name="description" id="description" class="textarea min-h-40 w-full" placeholder="商品の特徴やおすすめポイントなどを入力してください">{{ old('description', $product->description) }}</textarea>

            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </section>

        {{-- 商品画像 --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">商品画像</h2>

            <p class="mb-2 text-sm font-semibold">現在の画像</p>

            <div
                class="flex h-48 w-48 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                @if ($product->image_path)
                    <img src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}"
                        class="h-full w-full object-contain">
                @else
                    <x-no-image />
                @endif
            </div>

            <div class="mt-5">
                <label for="image" class="mb-2 block font-semibold">
                    画像を変更
                </label>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                    class="file-input w-full">

                <p class="mt-2 text-sm text-slate-500">
                    変更しない場合は選択不要。JPEG・PNG・WebP、最大{{ config('shop.products.image_max_kb') }}KB。
                </p>

                @error('image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 新しい画像のプレビュー --}}
            <div id="image_preview_container" class="mt-5 hidden">
                <p class="mb-2 text-sm font-semibold">変更後のプレビュー</p>
                <div
                    class="flex h-48 w-48 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                    <img id="image_preview" alt="変更後の商品画像" class="h-full w-full object-contain">
                </div>
            </div>

            @if ($product->image_path)
                <label class="mt-5 flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="remove_image" value="1" id="remove_image"
                        class="checkbox checkbox-sm" @checked(old('remove_image') == '1')>
                    <span class="text-sm">現在の画像を削除する</span>
                </label>
                <p class="mt-1 text-xs text-slate-500">
                    新しい画像を選択した場合は、画像の差し替えを優先します。
                </p>
            @endif

            @error('remove_image')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </section>

        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline">
                キャンセル
            </a>
            <button type="submit" class="btn btn-success">
                変更を保存
            </button>
        </div>
    </form>

    {{-- 削除エリア：更新フォームとは独立 --}}
    <section class="mt-16 rounded-xl border border-red-200 bg-white p-5 lg:p-7">
        <h2 class="text-lg font-bold text-red-700">商品の削除</h2>
        <p class="mt-2 text-sm text-slate-600">
            削除した商品は、商品一覧や店舗側の商品表示から除外されます。
        </p>

        <div class="mt-5 flex justify-end">
            <button type="button" class="btn btn-error btn-outline"
                onclick="document.getElementById('delete_product_modal').showModal()">
                商品を削除
            </button>
        </div>
    </section>

    {{-- 削除確認モーダル --}}
    <dialog id="delete_product_modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold text-red-700">
                商品を削除しますか？
            </h3>

            <p class="mt-4">
                「{{ $product->name }}」を削除します。
            </p>
            <p class="mt-2 text-sm text-slate-600">
                この商品は商品一覧から表示されなくなります。
                この操作を実行しても過去の注文履歴は残ります。
            </p>

            <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}">
                @csrf
                @method('DELETE')

                <div class="modal-action">
                    <button type="button" class="btn"
                        onclick="document.getElementById('delete_product_modal').close()">
                        キャンセル
                    </button>
                    <button type="submit" class="btn btn-error">
                        削除する
                    </button>
                </div>
            </form>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>閉じる</button>
        </form>
    </dialog>

    <script>
        const imageInput = document.getElementById('image');
        const previewContainer = document.getElementById('image_preview_container');
        const previewImage = document.getElementById('image_preview');

        let previewUrl = null;

        imageInput.addEventListener('change', function() {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }

            const file = this.files[0];

            if (!file) {
                previewImage.removeAttribute('src');
                previewContainer.classList.add('hidden');
                return;
            }

            previewUrl = URL.createObjectURL(file);
            previewImage.src = previewUrl;
            previewContainer.classList.remove('hidden');
        });
    </script>
@endsection
