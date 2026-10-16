@if ($order->needsEditWarning())
    <div role="alert" class="alert alert-warning mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        </svg>

        <div>
            <p class="font-bold">注文内容の変更にご注意ください</p>
            <p class="text-sm">
                この注文はすでにお客様へ金額をご案内している可能性があります。
                金額を変更する場合は、必ずお客様へ連絡し、了承を得てください。
                銀行振込の場合は入金状況も確認してください。
            </p>
        </div>
    </div>
@endif
