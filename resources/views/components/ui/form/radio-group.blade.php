@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'value' => null,
    'required' => false,
    'error' => null
])

<div>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="grid grid-cols-2 gap-4">
        @foreach($options as $optionValue => $optionLabel)
            <label class="flex items-center p-3 border rounded-lg cursor-pointer {{ $value === $optionValue ? 'border-primary-500 bg-primary-50' : 'border-gray-300' }}">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $optionValue }}"
                    {{ $value === $optionValue ? 'checked' : '' }}
                    {{ $attributes->only(['wire:model', 'wire:model.live']) }}
                    class="sr-only"
                >
                <div class="flex items-center">
                    <div class="w-4 h-4 border-2 rounded-full mr-3 {{ $value === $optionValue ? 'border-primary-500 bg-primary-500' : 'border-gray-300' }}">
                        @if($value === $optionValue)
                            <div class="w-2 h-2 bg-white rounded-full mx-auto mt-0.5"></div>
                        @endif
                    </div>
                    <span class="text-sm font-medium text-gray-700">{{ $optionLabel }}</span>
                </div>
            </label>
        @endforeach
    </div>

    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
