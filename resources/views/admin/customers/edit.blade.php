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

    {{-- 更新フォーム --}}
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

    {{-- 削除エリア：更新フォームとは独立 --}}
    <section class="mt-16 rounded-xl border border-red-200 bg-white p-5 lg:p-7">
        <h2 class="text-lg font-bold text-red-700">
            顧客の削除
        </h2>

        <p class="mt-2 text-sm text-slate-600">
            削除した顧客は、顧客台帳の一覧から表示されなくなります。
        </p>

        <div class="mt-5 flex justify-end">
            <button type="button" class="btn btn-error btn-outline"
                onclick="document.getElementById('delete_customer_modal').showModal()">
                顧客を削除
            </button>
        </div>
    </section>

    {{-- 削除確認モーダル --}}
    <dialog id="delete_customer_modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold text-red-700">
                顧客を削除しますか？
            </h3>

            <p class="mt-4">
                「{{ $customer->name }}」を削除します。
            </p>

            <p class="mt-2 text-sm text-slate-600">
                この顧客は顧客台帳の一覧から表示されなくなります。
            </p>

            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}">
                @csrf
                @method('DELETE')

                <div class="modal-action">
                    <button type="button" class="btn"
                        onclick="document.getElementById('delete_customer_modal').close()">
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
@endsection
