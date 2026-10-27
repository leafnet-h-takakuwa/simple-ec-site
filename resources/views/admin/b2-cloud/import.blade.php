@extends('layouts.admin')

@section('title', 'B2クラウドCSV取り込み')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.b2-cloud.index') }}"
            class="inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
            <x-arrow direction="left" /> B2クラウド連携へ戻る
        </a>

        <h1 class="mt-4 text-2xl font-bold lg:text-3xl">
            B2クラウドCSV取り込み
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            B2クラウドからダウンロードした発行結果CSVを取り込みます。
        </p>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="mb-5 text-lg font-bold">CSVファイルを選択</h2>

        <form method="POST" action="{{ route('admin.b2-cloud.import.preview') }}" enctype="multipart/form-data">
            @csrf

            <input type="file" name="csv_file" accept=".csv" class="file-input w-full" required>

            @error('csv_file')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <p class="mt-3 text-sm text-slate-500">
                ※取り込み前に内容を確認できます。この操作ではまだデータは更新されません。
            </p>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn btn-success">
                    内容を確認
                </button>
            </div>
        </form>
    </section>
@endsection
