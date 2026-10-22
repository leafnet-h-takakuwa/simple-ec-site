@extends('layouts.app')

@section('title', '退会手続き')

@section('content')

    <div class="mx-auto max-w-6xl px-4 py-8">

        {{-- パンくずリスト --}}
        <nav class="mb-5 text-sm text-base-content/60">

            <a href="{{ route('home') }}" class="link link-hover">
                TOP
            </a>
            <span class="mx-2">＞</span>
            <a href="{{ route('profile.edit') }}" class="link link-hover">
                会員情報の管理
            </a>
            <span class="mx-2">＞</span>
            <span aria-current="page" class="text-base-content">
                退会手続き
            </span>
        </nav>

        {{-- ページタイトル --}}
        <h1 class="mb-2 text-2xl font-bold">退会手続き</h1>
        <p class="mb-8 text-sm text-base-content/60">
            以下の内容をご確認のうえ、退会手続きを行ってください。
        </p>

        <section class="mx-auto max-w-2xl rounded-xl border border-base-300 bg-base-100 p-5 lg:p-8">
            <h2 class="text-lg font-bold">退会前にご確認ください</h2>

            <ul class="mt-4 list-disc space-y-3 pl-5">
                <li>退会後は、ログインや注文履歴の閲覧ができなくなります。</li>
                <li>受付済みの注文は、退会してもキャンセルされません。</li>
                <li>ご注文の記録は、退会後も当店で保持します。</li>
            </ul>

            <form method="POST" action="{{ route('profile.destroy') }}" class="mt-8 border-t border-base-300 pt-6">
                @csrf
                @method('DELETE')

                <div>
                    <label for="withdraw_password" class="mb-2 block text-sm font-semibold">
                        現在のパスワード <span class="text-red-600">*</span>
                    </label>

                    <p id="withdraw-password-help" class="mb-3 text-sm text-base-content/70">
                        ご本人確認のため、現在のパスワードを入力してください。
                    </p>

                    <input type="password" id="withdraw_password" name="password" autocomplete="current-password"
                        aria-describedby="withdraw-password-help" required class="input w-full">
                </div>

                <div class="mt-8 flex flex-wrap justify-end gap-3">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline btn-neutral">
                        会員情報へ戻る
                    </a>

                    <button type="submit" class="btn btn-error" onclick="return confirm('退会します。よろしいですか？')">
                        退会する
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
