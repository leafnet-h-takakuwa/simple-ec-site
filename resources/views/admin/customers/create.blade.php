@extends('layouts.admin')

@section('title', '顧客登録')

@section('content')
    {{-- タイトル --}}
    <div class="mb-8">
        <a href="{{ route('admin.customers.index') }}"
            class="inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
            <x-arrow direction="left" /> 顧客台帳へ戻る
        </a>

        <h1 class="mt-4 text-2xl font-bold lg:text-3xl">
            顧客登録
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            注文作成時に利用する顧客情報を登録します。
        </p>
    </div>

    <form method="POST" action="{{ route('admin.customers.store') }}">
        @csrf

        @include('admin.customers._form')

        {{-- 操作ボタン --}}
        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">
                キャンセル
            </a>

            <button type="submit" class="btn btn-success">
                登録する
            </button>
        </div>
    </form>
@endsection
