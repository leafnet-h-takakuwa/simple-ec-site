@extends('layouts.admin')

@section('title', '商品登録')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.products.index') }}"
            class="inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
            <x-arrow direction="left" /> 商品一覧へ戻る
        </a>

        <h1 class="mt-4 text-2xl font-bold lg:text-3xl">
            商品登録
        </h1>
    </div>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- 基本情報 --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-6 text-lg font-bold">基本情報</h2>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label for="name" class="mb-2 block font-semibold">
                        商品名 <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" maxlength="255"
                        class="input w-full" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category" class="mb-2 block font-semibold">
                        分類 <span class="text-red-600">*</span>
                    </label>
                    <select name="category" id="category" class="select w-full" required>
                        <option value="">選択してください</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->value }}" @selected(old('category') === $category->value)>
                                {{ $category->label() }}
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
                    <input type="text" name="volume" id="volume" value="{{ old('volume') }}" maxlength="255"
                        placeholder="例：500g" class="input w-full" required>
                    @error('volume')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="mb-2 block font-semibold">
                        価格（税込） <span class="text-red-600">*</span>
                    </label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" min="0"
                        step="1" class="input w-full" required>
                    @error('price')
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
                                @checked(old('is_active', '1') === '1')>
                            公開中
                        </label>

                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="radio" name="is_active" value="0" class="radio radio-success"
                                @checked(old('is_active', '1') === '0')>
                            非公開
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- 商品説明 --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">商品説明</h2>

            <textarea name="description" id="description" class="textarea min-h-40 w-full" placeholder="商品の特徴やおすすめポイントなどを入力してください">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </section>

        {{-- 商品画像 --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-5 text-lg font-bold">商品画像</h2>

            {{-- 画像プレビュー --}}
            <div id="image_preview_container" class="mt-5 hidden">
                <p class="mb-2 text-sm font-semibold">プレビュー</p>

                <div
                    class="flex h-48 w-48 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                    <img id="image_preview" src="" alt="商品画像プレビュー" class="h-full w-full object-contain">
                </div>
            </div>

            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                class="file-input w-full">

            <p class="mt-2 text-sm text-slate-500">
                JPEG・PNG・WebP形式、最大5MB。画像の登録は任意です。
            </p>

            @error('image')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </section>

        {{-- 操作ボタン --}}
        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline">
                キャンセル
            </a>

            <button type="submit" class="btn btn-success">
                商品を登録
            </button>
        </div>
    </form>

    <script>
        const imageInput = document.getElementById('image');
        const previewContainer = document.getElementById('image_preview_container');
        const previewImage = document.getElementById('image_preview');

        let previewUrl = null;

        imageInput.addEventListener('change', function() {
            // 前回のプレビューURLを解放
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }

            const file = this.files[0];

            // ファイルが選択されていない場合
            if (!file) {
                previewImage.removeAttribute('src');
                previewContainer.classList.add('hidden');
                return;
            }

            // 選択した画像をプレビュー表示
            previewUrl = URL.createObjectURL(file);
            previewImage.src = previewUrl;
            previewContainer.classList.remove('hidden');
        });
    </script>
@endsection
