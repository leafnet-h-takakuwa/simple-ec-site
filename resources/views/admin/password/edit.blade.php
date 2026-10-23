@extends('layouts.admin')

@section('title', 'パスワード変更')

@section('content')
    <div class="mx-auto max-w-2xl">
        {{-- ページタイトル --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold lg:text-3xl">
                パスワード変更
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                現在のパスワードと、新しいパスワードを入力してください。
            </p>
        </div>

        {{-- パスワード変更フォーム --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-8">
            @livewire('settings.password')
        </section>
    </div>
@endsection
