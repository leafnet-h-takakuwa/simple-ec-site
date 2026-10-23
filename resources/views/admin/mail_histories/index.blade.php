@extends('layouts.admin')

@section('title', 'メール送信履歴')

@section('content')
    {{-- タイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">
            メール送信履歴
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            注文・お問い合わせなどのメール送信履歴を確認できます。
        </p>
    </div>



    {{-- 検索フォーム --}}
    <form action="{{ route('admin.mail-histories.index') }}" method="GET"
        class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">

        <h2 class="mb-5 text-lg font-bold">メール送信履歴検索</h2>

        {{-- PC：2列×2段 / スマホ：1列×4段 --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

            {{-- 処理日時 --}}
            <fieldset>
                <legend class="mb-2 text-sm font-semibold">
                    処理日時
                </legend>

                <div class="flex items-center gap-2">
                    <input type="date" name="date_from" aria-label="処理日時（開始）"
                        value="{{ old('date_from', $conditions['date_from'] ?? '') }}" class="input min-w-0 flex-1">

                    <span class="shrink-0 text-slate-500">〜</span>

                    <input type="date" name="date_to" aria-label="処理日時（終了）"
                        value="{{ old('date_to', $conditions['date_to'] ?? '') }}" class="input min-w-0 flex-1">
                </div>
            </fieldset>

            {{-- メール種別 --}}
            <fieldset>
                <label for="type" class="mb-2 block text-sm font-semibold">
                    メール種別
                </label>

                <select id="type" name="type" class="select w-full">
                    <option value="">すべて</option>

                    @foreach ($mailTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('type', $conditions['type'] ?? '') === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </fieldset>

            {{-- 宛先メールアドレス --}}
            <fieldset>
                <label for="address" class="mb-2 block text-sm font-semibold">
                    宛先メールアドレス
                </label>

                <input type="text" id="address" name="address" maxlength="{{ \App\Support\InputLimits::SHORT_TEXT }}"
                    value="{{ old('address', $conditions['address'] ?? '') }}" placeholder="メールアドレスの一部を入力"
                    class="input w-full">
            </fieldset>

            {{-- 送信結果 --}}
            <fieldset>
                <label for="status" class="mb-2 block text-sm font-semibold">
                    送信結果
                </label>

                <select id="status" name="status" class="select w-full">
                    <option value="">すべて</option>

                    @foreach ($mailStatuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $conditions['status'] ?? '') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </fieldset>
        </div>

        {{-- 検索ボタン --}}
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.mail-histories.index') }}" class="btn btn-outline">
                クリア
            </a>

            <button type="submit" class="btn btn-success">
                検索
            </button>
        </div>
    </form>



    {{-- 件数 --}}
    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="font-bold">
            メール送信履歴 {{ number_format($mail_histories->total()) }}件
        </p>

        <span class="text-sm text-slate-500">
            新しい順 ↓
        </span>
    </div>

    {{-- PC：テーブル --}}
    <div class="hidden overflow-x-auto rounded-xl border border-slate-200 bg-white lg:block">
        <table class="table w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th>処理日時</th>
                    <th>種別</th>
                    <th>宛先</th>
                    <th>件名</th>
                    <th>送信結果</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($mail_histories as $mail_history)
                    <tr>
                        {{-- 日時 --}}
                        <td class="whitespace-nowrap">
                            {{ $mail_history->created_at->format('Y/m/d H:i') }}
                        </td>

                        {{-- 種別 --}}
                        <td class="whitespace-nowrap">
                            {{ $mail_history->type->label() }}
                        </td>

                        {{-- 宛先 --}}
                        <td class="max-w-48 break-all">
                            {{ $mail_history->to_address }}
                        </td>

                        {{-- 件名 --}}
                        <td class="max-w-72 break-words">
                            {{ $mail_history->subject }}
                        </td>

                        {{-- 送信結果 --}}
                        <td>
                            <span class="badge badge-outline whitespace-nowrap {{ $mail_history->status->badgeClass() }}">
                                {{ $mail_history->status->label() }}
                            </span>
                        </td>

                        {{-- 詳細 --}}
                        <td class="text-center">
                            <a href="{{ route('admin.mail-histories.show', $mail_history) }}"
                                class="btn btn-outline btn-sm">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- スマホ：カード --}}
    <div class="space-y-4 lg:hidden">
        @foreach ($mail_histories as $mail_history)
            <article class="rounded-xl border border-slate-200 bg-white p-5">

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="text-sm text-slate-500">
                        {{ $mail_history->created_at->format('Y/m/d H:i') }}
                    </span>

                    <span class="badge badge-outline whitespace-nowrap {{ $mail_history->status->badgeClass() }}">
                        {{ $mail_history->status->label() }}
                    </span>
                </div>

                <p class="mt-3 text-sm text-slate-500">
                    {{ $mail_history->type->label() }}
                </p>

                <p class="mt-2 font-semibold break-words">
                    {{ $mail_history->subject }}
                </p>

                <p class="mt-3 text-sm break-all">
                    <span class="text-slate-500">宛先：</span>
                    {{ $mail_history->to_address }}
                </p>

                <div class="mt-4 flex justify-end border-t border-slate-100 pt-3">
                    <a href="{{ route('admin.mail-histories.show', $mail_history) }}" class="btn btn-outline btn-sm">
                        詳細
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    {{-- 検索結果なし --}}
    @if ($mail_histories->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">
            メール送信履歴はありません。
        </div>
    @endif

    {{-- ページネーション --}}
    <div class="mt-6">
        {{ $mail_histories->links() }}
    </div>
@endsection
