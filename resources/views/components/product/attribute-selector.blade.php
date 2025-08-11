@props([
    'attribute',
    'selections' => [],
    'additionsMap' => [],
    'showValidationErrors' => false
])

<div wire:key="attribute-{{ $attribute->id }}">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ $attribute->name }} <span class="text-red-500">*</span>
    </label>

    @if($showValidationErrors && (!isset($selections[$attribute->id]) || !$selections[$attribute->id]))
        <p class="text-xs text-red-600 mb-2">Please select an option</p>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
        @foreach($attribute->options->sortBy('sort_order') as $option)
            @php
                $selected = ($selections[$attribute->id] ?? null) === $option->id;
                $additionFils = $additionsMap[$option->id] ?? 0;
                $hasError = $showValidationErrors && (!isset($selections[$attribute->id]) || !$selections[$attribute->id]);
            @endphp

            <button
                type="button"
                wire:click="$set('selections.{{ $attribute->id }}', {{ $option->id }})"
                aria-pressed="{{ $selected ? 'true' : 'false' }}"
                class="group p-3 text-left border rounded-lg transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-600 focus-visible:ring-offset-2
                    {{ $selected
                        ? 'bg-primary-600 border-primary-600 text-white shadow-sm ring-2 ring-primary-600'
                        : ($hasError
                            ? 'border-red-300 bg-red-50 hover:border-red-400 hover:bg-red-100'
                            : 'bg-white border-gray-300 hover:bg-primary-50 hover:border-primary-600 hover:text-primary-700') }}">
                <div class="text-sm font-medium {{ $selected ? 'text-white' : 'text-gray-900' }}">
                    {{ $option->name }}
                </div>
                @if($additionFils > 0)
                    <span class="ml-2 text-xs {{ $selected ? 'text-white/90' : 'text-gray-500' }}">
                        + {{ money($additionFils)->format() }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>
</div>
