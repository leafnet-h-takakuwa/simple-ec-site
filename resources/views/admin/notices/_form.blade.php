@csrf

{{-- お知らせ内容 --}}
<section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
    <h2 class="mb-5 text-lg font-bold">お知らせ内容</h2>

    <div class="space-y-5">
        {{-- タイトル --}}
        <div>
            <label for="title" class="mb-2 block text-sm font-semibold">
                タイトル <span class="text-red-600">*</span>
            </label>

            <input type="text" id="title" name="title" value="{{ old('title', $notice->title ?? '') }}"
                maxlength="100" required class="input w-full">

            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>


        {{-- 本文 --}}
        <div>
            <label for="notice-editor" class="mb-2 block text-sm font-semibold">
                本文 <span class="text-red-600">*</span>
            </label>

            {{-- Quillエディタ --}}
            <div class="overflow-hidden rounded-lg border border-slate-300 bg-white">
                <div id="notice-editor" class="min-h-64"></div>
            </div>

            {{-- 保存用HTML --}}
            <textarea id="body" name="body" class="hidden">{{ old('body', $notice->body ?? '') }}</textarea>

            <p class="mt-2 text-sm text-slate-500">
                太字・リンク・箇条書きを使用できます。
            </p>

            @error('body')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</section>

{{-- 公開設定 --}}
<section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
    <h2 class="mb-5 text-lg font-bold">公開設定</h2>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        {{-- 公開日 --}}
        <div>
            <label for="published_at" class="mb-2 block text-sm font-semibold">
                公開日 <span class="text-red-600">*</span>
            </label>

            <input type="date" id="published_at" name="published_at"
                value="{{ old('published_at', isset($notice) ? $notice->published_at?->format('Y-m-d') : today()->format('Y-m-d')) }}"
                required class="input w-full">

            <p class="mt-2 text-sm text-slate-500">
                未来の日付を指定すると、公開予約になります。
            </p>

            @error('published_at')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- 公開状態 --}}
        <div>
            <label for="is_published" class="mb-2 block text-sm font-semibold">
                公開状態 <span class="text-red-600">*</span>
            </label>

            <select id="is_published" name="is_published" class="select w-full" required>
                <option value="0" @selected((string) old('is_published', $notice->is_published ?? false) === '0' || old('is_published', $notice->is_published ?? false) === false)>
                    非公開
                </option>
                <option value="1" @selected((string) old('is_published', $notice->is_published ?? false) === '1')>
                    公開
                </option>
            </select>

            <p class="mt-2 text-sm text-slate-500">
                公開日が到来していても、非公開の場合は表示されません。
            </p>

            @error('is_published')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</section>
