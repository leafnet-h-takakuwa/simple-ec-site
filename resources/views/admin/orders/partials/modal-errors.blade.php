@if (old('_modal') === $form && $errors->any())
    <div role="alert" aria-live="assertive"
        class="mb-5 max-h-40 overflow-y-auto rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="mb-2 font-semibold">入力内容を確認してください。</p>
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
