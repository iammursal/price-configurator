@props(['item', 'showRemove' => true, 'showQuantityControls' => false])

<div class="flex items-start justify-between border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
    <div class="flex-1">
        <div class="flex justify-between">
            <a wire:navigate href="{{ route('products.show', $item['product']) }}"
               class="text-sm font-medium text-gray-900 hover:text-primary-600">
                {{ $item['product']->name }}
            </a>
            @if($showRemove)
                <x-ui.icon-button
                    wire:click="removeItem({{ $item['index'] }})"
                    wire:confirm="Remove this item from cart?"
                    variant="danger"
                    icon="close"
                    size="sm"
                />
            @endif
        </div>

        @if(!empty($item['selected_options']))
            <p class="text-xs text-gray-500 mt-1">
                {{ implode(', ', $item['selected_options']) }}
            </p>
        @endif

        <div class="flex items-center justify-between mt-2">
            @if($showQuantityControls)
                <div class="flex items-center space-x-2">
                    <x-ui.icon-button
                        wire:click="updateQuantity({{ $item['index'] }}, {{ $item['qty'] - 1 }})"
                        :disabled="$item['qty'] <= 1"
                        icon="minus"
                        size="sm"
                    />
                    <span class="text-sm text-gray-600 min-w-[20px] text-center">{{ $item['qty'] }}</span>
                    <x-ui.icon-button
                        wire:click="updateQuantity({{ $item['index'] }}, {{ $item['qty'] + 1 }})"
                        icon="plus"
                        size="sm"
                    />
                </div>
            @else
                <span class="text-sm text-gray-600">Qty: {{ $item['qty'] }}</span>
            @endif

            <span class="text-sm font-medium text-gray-900">
                {{ money($item['amount'])->format() }}
            </span>
        </div>
    </div>
</div>
