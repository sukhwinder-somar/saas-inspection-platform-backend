@props([
    'variant' => 'default',
])

@php
$classes = collect([
    'rounded-lg border bg-card text-card-foreground shadow-sm',
    match($variant) {
        'default' => '',
        'outline' => 'border-border',
    }
])->implode(' ');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
