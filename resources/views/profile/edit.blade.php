@extends('layouts.app')

@section('title', '会員情報の編集')

@section('content')
    {{-- ページタイトル --}}
    <section class="mb-8">
        <h1 class="text-3xl font-bold">会員情報の編集</h1>
        <p class="mt-3 text-base-content/70">
            お名前やご住所などの会員情報を変更できます。
        </p>
    </section>

    {{-- 会員情報編集フォーム --}}
    <section class="mx-auto max-w-2xl rounded-xl border border-base-300 bg-base-100 p-5 lg:p-8">
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
                    <label for="email" class="mb-2 block text-sm font-semibold">
                        メールアドレス <span class="text-red-600">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                        maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" required class="input w-full">
                </div>

                {{-- 郵便番号 --}}
                <div>
                    <label for="postal_code" class="mb-2 block text-sm font-semibold">
                        郵便番号
                    </label>
                    <input type="text" id="postal_code" name="postal_code"
                        value="{{ old('postal_code', $user->postal_code) }}" placeholder="例：030-0000" maxlength="{{ \App\Support\InputLimits::POSTAL_CODE }}"
                        class="input w-full">
                </div>

                {{-- 住所 --}}
                <div>
                    <label for="address" class="mb-2 block text-sm font-semibold">
                        住所
                    </label>
                    <textarea id="address" name="address" rows="3" maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}" class="textarea w-full">{{ old('address', $user->address) }}</textarea>
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
            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('home') }}" class="btn btn-outline btn-neutral">
                    キャンセル
                </a>

                <button type="submit" class="btn btn-primary">
                    変更を保存
                </button>
            </div>
        </form>
    </section>
@endsection
