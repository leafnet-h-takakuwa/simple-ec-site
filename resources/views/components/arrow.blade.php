@props([
    'direction' => 'right',
])

@php
    $rotation = match ($direction) {
        'left' => 'rotate-180',
        'up' => '-rotate-90',
        'down' => 'rotate-90',
        default => '',
    };
@endphp

<svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->class(['size-4 shrink-0', $rotation]) }} fill="none"
    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
    aria-hidden="true">
    <path d="M5 12h14" />
    <path d="m12 5 7 7-7 7" />
</svg>
