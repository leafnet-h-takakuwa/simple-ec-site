<svg
    {{ $attributes->merge([
        'xmlns' => 'http://www.w3.org/2000/svg',
        'fill' => 'none',
        'viewBox' => '0 0 24 24',
        'stroke' => 'currentColor',
        'stroke-width' => '1.8',
        'stroke-linecap' => 'round',
        'stroke-linejoin' => 'round',
    ]) }}>
    {{-- 台帳本体 --}}
    <rect x="5" y="2.5" width="16" height="19" rx="2" />

    {{-- 左側の綴じ具 --}}
    <path d="M5 7H3M5 12H3M5 17H3" />

    {{-- 顧客プロフィール --}}
    <circle cx="13" cy="9" r="2" />
    <path d="M9.5 16a3.5 3.5 0 0 1 7 0" />
</svg>
