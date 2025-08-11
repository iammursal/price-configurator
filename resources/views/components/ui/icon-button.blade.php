@props([
    'variant' => 'ghost',
    'size' => 'md',
    'icon' => null,
    'disabled' => false
])

@php
    $baseClasses = 'inline-flex items-center justify-center transition-colors focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'ghost' => 'text-gray-600 hover:bg-gray-50 border border-gray-300',
        'danger' => 'text-red-600 hover:text-red-800 hover:bg-red-50',
        'primary' => 'text-primary-600 hover:bg-primary-50',
    ];

    $sizes = [
        'sm' => 'w-6 h-6 rounded',
        'md' => 'w-8 h-8 rounded-lg',
        'lg' => 'w-10 h-10 rounded-full',
    ];

    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<button
    type="button"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}>

    @if($icon === 'plus')
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
    @elseif($icon === 'minus')
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
        </svg>
    @elseif($icon === 'close')
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    @else
        {{ $slot }}
    @endif
</button>
