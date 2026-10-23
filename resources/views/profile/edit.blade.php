@extends('layouts.app')

@section('title', '会員情報管理')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">

        {{-- パンくずリスト --}}
        <nav class="mb-5 text-sm text-base-content/60">

            <a href="{{ route('home') }}" class="link link-hover">
                TOP
            </a>
            <span class="mx-2">＞</span>
            <span aria-current="page" class="text-base-content">
                会員情報管理
            </span>
        </nav>

        {{-- ページタイトル --}}
        <h1 class="mb-2 text-2xl font-bold">会員情報の管理</h1>
        <p class="mb-8 text-sm text-base-content/60">
            会員情報やパスワードを変更できます。
        </p>

        <div id="profile-settings" class="mx-auto max-w-2xl">
            {{-- タブ --}}
            <div role="tablist" aria-label="会員設定" class="tabs tabs-box mb-5 grid grid-cols-3 gap-1">

                <button type="button" role="tab" id="profile-tab" aria-controls="profile-panel" aria-selected="true"
                    class="tab tab-active px-1 text-xs whitespace-nowrap sm:text-sm" data-tab="profile">
                    会員情報
                </button>

                <button type="button" role="tab" id="email-tab" aria-controls="email-panel" aria-selected="false"
                    tabindex="-1" class="tab px-1 text-xs whitespace-nowrap sm:text-sm" data-tab="email">
                    <span class="sm:hidden">メール変更</span>
                    <span class="hidden sm:inline">メールアドレス変更</span>
                </button>

                <button type="button" role="tab" id="password-tab" aria-controls="password-panel" aria-selected="false"
                    tabindex="-1" class="tab px-1 text-xs whitespace-nowrap sm:text-sm" data-tab="password">
                    <span class="sm:hidden">PW変更</span>
                    <span class="hidden sm:inline">パスワード変更</span>
                </button>
            </div>

            {{-- 会員情報 --}}
            <section id="profile-panel" role="tabpanel" aria-labelledby="profile-tab" data-panel="profile"
                class="rounded-xl border border-base-300 bg-base-100 p-5 lg:p-8">

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        {{-- 氏名 --}}
                        <div>
                            <label for="name" class="mb-2 block text-sm font-semibold">
                                氏名 <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" required class="input w-full">
                        </div>

                        {{-- メールアドレス --}}
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold">メールアドレス</span>

                                @if ($user->hasVerifiedEmail())
                                    <span class="badge badge-success badge-sm">
                                        認証済み
                                    </span>
                                @else
                                    <span class="badge badge-warning badge-sm">
                                        未認証
                                    </span>
                                @endif
                            </div>

                            <p class="rounded-lg bg-base-200 px-4 py-3 text-sm break-all">
                                {{ $user->email }}
                            </p>

                            <p class="mt-2 text-xs text-base-content/60">
                                メールアドレスの変更は「メール変更」タブから行えます。
                            </p>
                        </div>

                        {{-- 郵便番号 --}}
                        <div>
                            <label for="postal_code" class="mb-2 block text-sm font-semibold">
                                郵便番号
                            </label>
                            <input type="text" id="postal_code" name="postal_code"
                                value="{{ old('postal_code', $user->postal_code) }}" placeholder="例：030-0000"
                                maxlength="{{ \App\Support\InputLimits::POSTAL_CODE }}" class="input w-full">
                        </div>

                        {{-- 住所 --}}
                        <div>
                            <label for="address" class="mb-2 block text-sm font-semibold">
                                住所
                            </label>
                            <textarea id="address" name="address" rows="3" maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}"
                                class="textarea w-full">{{ old('address', $user->address) }}</textarea>
                        </div>

                        {{-- 電話番号 --}}
                        <div>
                            <label for="phone" class="mb-2 block text-sm font-semibold">
                                電話番号
                            </label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                maxlength="{{ \App\Support\InputLimits::PHONE }}" class="input w-full">
                        </div>
                    </div>

                    {{-- 操作ボタン --}}
                    <div class="mt-8 flex flex-wrap justify-end gap-3">
                        <a href="{{ route('home') }}" class="btn btn-outline btn-neutral">
                            キャンセル
                        </a>

                        <button type="submit" class="btn btn-primary">
                            変更を保存
                        </button>
                    </div>
                </form>

                {{-- 退会手続き --}}
                <div class="mt-8 border-t border-base-300 pt-6 text-right">
                    <a href="{{ route('profile.withdraw') }}"
                        class="text-sm text-base-content/60 underline underline-offset-4 hover:text-error">
                        退会手続きへ
                    </a>
                </div>

            </section>

            {{-- メールアドレス変更 --}}
            <section id="email-panel" role="tabpanel" aria-labelledby="email-tab" data-panel="email"
                class="hidden rounded-xl border border-base-300 bg-base-100 p-5 lg:p-8">

                <h2 class="text-lg font-bold">
                    メールアドレスの変更
                </h2>

                <p class="mb-6 mt-2 text-sm text-base-content/70">
                    新しいメールアドレスに確認メールを送信します。
                    認証が完了するまでは、現在のメールアドレスが使用されます。
                </p>

                @if ($user->pending_email)
                    @php
                        $isExpired =
                            !$user->pending_email_requested_at ||
                            $user->pending_email_requested_at->addMinutes(60)->isPast();
                    @endphp

                    <div class="alert {{ $isExpired ? 'alert-error' : 'alert-warning' }} mb-6">
                        <div>
                            <p class="font-semibold">
                                {{ $isExpired ? 'メールアドレス変更の有効期限切れ' : 'メールアドレスの変更確認待ち' }}
                            </p>

                            <p class="mt-1 text-sm break-all">
                                {{ $user->pending_email }}
                            </p>

                            <p class="mt-1 text-xs">
                                @if ($isExpired)
                                    確認リンクの有効期限が切れました。
                                    変更する場合は、もう一度申請してください。
                                @else
                                    確認メール内のリンクを開くと変更が確定します。
                                    再申請すると以前のリンクは無効になります。
                                @endif
                            </p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.email.update') }}">
                    @csrf

                    <div class="space-y-5">
                        {{-- 現在のメールアドレス --}}
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold">
                                    現在のメールアドレス
                                </span>

                                @if ($user->hasVerifiedEmail())
                                    <span class="badge badge-success badge-sm">認証済み</span>
                                @else
                                    <span class="badge badge-warning badge-sm">未認証</span>
                                @endif
                            </div>

                            <p class="rounded-lg bg-base-200 px-4 py-3 text-sm break-all">
                                {{ $user->email }}
                            </p>
                        </div>

                        {{-- 新しいメールアドレス --}}
                        <div>
                            <label for="new_email" class="mb-2 block text-sm font-semibold">
                                新しいメールアドレス <span class="text-red-600">*</span>
                            </label>

                            <input type="email" id="new_email" name="new_email" value="{{ old('new_email') }}"
                                maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" autocomplete="email" required
                                class="input w-full">

                            @error('new_email')
                                <p class="mt-1 text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 現在のパスワード --}}
                        <div>
                            <label for="current_password" class="mb-2 block text-sm font-semibold">
                                現在のパスワード <span class="text-red-600">*</span>
                            </label>

                            <input type="password" id="current_password" name="current_password"
                                autocomplete="current-password" required class="input w-full">

                            @error('current_password')
                                <p class="mt-1 text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            確認メールを送信
                        </button>
                    </div>
                </form>

            </section>

            {{-- パスワード変更 --}}
            <section id="password-panel" role="tabpanel" aria-labelledby="password-tab" data-panel="password"
                class="hidden rounded-xl border border-base-300 bg-base-100 p-5 lg:p-8">

                <h2 class="text-lg font-bold">パスワードの変更</h2>
                <p class="mb-6 mt-2 text-base-content/70">
                    現在のパスワードと、新しいパスワードを入力してください。
                </p>

                @livewire('settings.password')
            </section>
        </div>
    </div>
    <script>
        (() => {
            function initializeProfileTabs() {
                const container = document.getElementById('profile-settings');
                if (!container || container.dataset.initialized) return;

                container.dataset.initialized = 'true';

                const tabs = [...container.querySelectorAll('[data-tab]')];
                const panels = container.querySelectorAll('[data-panel]');

                function switchTab(name) {
                    tabs.forEach(tab => {
                        const active = tab.dataset.tab === name;
                        tab.classList.toggle('tab-active', active);
                        tab.setAttribute('aria-selected', String(active));
                        tab.tabIndex = active ? 0 : -1;
                    });

                    panels.forEach(panel => {
                        panel.classList.toggle('hidden', panel.dataset.panel !== name);
                    });
                }

                tabs.forEach((tab, index) => {
                    tab.addEventListener('click', () => {
                        switchTab(tab.dataset.tab);
                    });

                    tab.addEventListener('keydown', event => {
                        let nextIndex;

                        switch (event.key) {
                            case 'ArrowRight':
                                nextIndex = (index + 1) % tabs.length;
                                break;
                            case 'ArrowLeft':
                                nextIndex = (index - 1 + tabs.length) % tabs.length;
                                break;
                            case 'Home':
                                nextIndex = 0;
                                break;
                            case 'End':
                                nextIndex = tabs.length - 1;
                                break;
                            default:
                                return;
                        }

                        event.preventDefault();
                        switchTab(tabs[nextIndex].dataset.tab);
                        tabs[nextIndex].focus();
                    });
                });
            }

            @if ($errors->has('new_email') || $errors->has('current_password'))
                switchTab('email');
            @endif

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeProfileTabs, {
                    once: true
                });
            } else {
                initializeProfileTabs();
            }

            document.addEventListener('livewire:navigated', initializeProfileTabs);
        })();
    </script>
@endsection
