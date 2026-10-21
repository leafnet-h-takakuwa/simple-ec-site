@extends('layouts.admin')

@section('title', '顧客編集')

@section('content')
    {{-- タイトル --}}
    <div class="mb-8">
        <a href="{{ route('admin.customers.show', $customer) }}"
            class="inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
            <x-arrow direction="left" /> 顧客詳細へ戻る
        </a>

        <h1 class="mt-4 text-2xl font-bold lg:text-3xl">
            顧客編集
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            {{ $customer->name }} さんの登録情報を編集します。
        </p>
    </div>

    <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
        @csrf
        @method('PUT')

        @include('admin.customers._form')

        {{-- 操作ボタン --}}
        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline">
                キャンセル
            </a>

            <button type="submit" class="btn btn-success">
                更新する
            </button>
        </div>
    </form>
@endsection
