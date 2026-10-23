@extends('layouts.admin')

@section('title', 'メール送信履歴詳細')

@section('content')
    {{-- タイトル --}}
    <div class="mb-8">
        <a href="{{ route('admin.mail-histories.index') }}"
            class="inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
            <x-arrow direction="left" /> メール送信履歴一覧へ戻る
        </a>

        <h1 class="mt-4 text-2xl font-bold lg:text-3xl">
            メール送信履歴詳細
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            履歴ID：{{ $mail_history->id }}
        </p>
    </div>

    {{-- 送信情報 --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="mb-6 text-lg font-bold">
            送信情報
        </h2>

        <dl class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    処理日時
                </dt>
                <dd>
                    {{ $mail_history->created_at->format('Y/m/d H:i:s') }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    送信日時
                </dt>
                <dd>
                    {{ $mail_history->sent_at?->format('Y/m/d H:i:s') ?? '―' }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    メール種別
                </dt>
                <dd>
                    {{ $mail_history->type->label() }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    送信結果
                </dt>
                <dd>
                    <span class="badge badge-outline {{ $mail_history->status->badgeClass() }}">
                        {{ $mail_history->status->label() }}
                    </span>
                </dd>
            </div>
        </dl>
    </section>

    {{-- メール情報 --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="mb-6 text-lg font-bold">
            メール情報
        </h2>

        <dl class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    送信元
                </dt>
                <dd class="break-all">
                    {{ $mail_history->from_address }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    宛先
                </dt>
                <dd class="break-all">
                    {{ $mail_history->to_address }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    CC
                </dt>
                <dd class="break-all">
                    {{ $mail_history->cc_address ?: '―' }}
                </dd>
            </div>

            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    BCC
                </dt>
                <dd class="break-all">
                    {{ $mail_history->bcc_address ?: '―' }}
                </dd>
            </div>

            <div class="lg:col-span-2">
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    件名
                </dt>
                <dd class="break-words font-semibold">
                    {{ $mail_history->subject }}
                </dd>
            </div>
        </dl>
    </section>

    {{-- メール本文 --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="mb-6 text-lg font-bold">
            メール本文
        </h2>

        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 lg:p-6">
            <div class="whitespace-pre-wrap break-words text-sm leading-relaxed">{{ $mail_history->body }}</div>
        </div>
    </section>

    {{-- 送信エラー --}}
    @if ($mail_history->status === \App\Enums\MailStatus::FAILED)
        <section class="mb-8 rounded-xl border border-red-200 bg-white p-5 lg:p-7">
            <h2 class="mb-4 text-lg font-bold text-red-700">
                送信エラー
            </h2>

            <div class="rounded-lg bg-red-50 p-4 text-sm text-red-800">
                <p class="whitespace-pre-wrap break-words">{{ $mail_history->error_message ?: 'エラーの詳細は記録されていません。' }}</p>
            </div>
        </section>
    @endif

    {{-- 関連情報 --}}
    @if ($mail_history->related instanceof \App\Models\Order)
        <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
            <h2 class="mb-6 text-lg font-bold">
                関連情報
            </h2>

            <dl>
                <div>
                    <dt class="mb-2 text-sm font-semibold text-slate-500">
                        注文番号
                    </dt>
                    <dd>
                        <a href="{{ route('admin.orders.show', $mail_history->related->id) }}"
                            class="font-semibold text-green-800 hover:underline">
                            #{{ sprintf('%08d', $mail_history->related->id) }}
                        </a>
                    </dd>
                </div>
            </dl>
        </section>
    @endif
@endsection
