@props([
    'label' => '商品画像なし',
])

<svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class(['h-full w-full']) }} role="img"
    aria-label="{{ $label }}">

    {{-- 背景 --}}
    <rect width="400" height="400" fill="#f1f5f9" />

    {{-- 装飾 --}}
    <circle cx="200" cy="177" r="94" fill="#e2e8f0" />

    {{-- 画像アイコン --}}
    <rect x="133" y="118" width="134" height="112" rx="12" fill="#ffffff" stroke="#94a3b8"
        stroke-width="6" />

    <circle cx="173" cy="154" r="12" fill="#94a3b8" />

    <path d="M145 211 L180 174 L207 196 L232 168 L255 211" fill="none" stroke="#94a3b8" stroke-width="6"
        stroke-linecap="round" stroke-linejoin="round" />

    {{-- テキスト --}}
    <text x="200" y="294" text-anchor="middle" fill="#64748b" font-size="19" font-weight="500" font-family="sans-serif">
        {{ $label }}
    </text>
</svg>
