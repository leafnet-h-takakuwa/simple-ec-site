{{-- ステータス変更内容 --}}
<div class="flex flex-wrap items-center justify-between gap-2">
    <p class="font-semibold">
        {{ $history->from_status ? \App\Enums\OrderStatus::from($history->from_status)->label() : '新規登録' }}
        →
        {{ \App\Enums\OrderStatus::from($history->to_status)->label() }}
    </p>

    <span class="text-xs text-slate-500">
        {{ $history->created_at->format('Y/m/d H:i') }}
    </span>
</div>

{{-- 連絡方法・担当者 --}}
<div class="mt-2 flex flex-wrap gap-3 text-sm text-slate-600">
    <span>
        連絡方法：
        {{ match ($history->contact_method) {
            'email' => 'メール',
            'phone' => '電話',
            'fax' => 'FAX',
            'in_person' => '対面',
            default => '連絡なし',
        } }}
    </span>

    <span>
        担当：{{ $history->user?->name ?? 'システム' }}
    </span>
</div>

{{-- 対応メモ --}}
@if (filled($history->contact_note))
    <p class="mt-3 whitespace-pre-line rounded-lg bg-slate-50 p-3 text-sm text-slate-700">{{ $history->contact_note }}
    </p>
@endif

{{-- 送信メール --}}
@if ($history->mailHistories->isNotEmpty())
    <div class="mt-3 space-y-2">
        @foreach ($history->mailHistories as $mail_history)
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ route('admin.mail-histories.show', $mail_history) }}"
                    class="inline-flex items-center gap-1 font-semibold text-green-700 hover:underline">
                    <x-icons.mail class="size-4" />
                    {{ $mail_history->type->label() }}
                </a>

                <span class="badge badge-outline badge-sm {{ $mail_history->status->badgeClass() }}">
                    {{ $mail_history->status->label() }}
                </span>
            </div>
        @endforeach
    </div>
@endif
