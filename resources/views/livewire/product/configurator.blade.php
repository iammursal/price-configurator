<div class="space-y-6">
    @foreach($productAttributes as $attribute)
        <x-product.attribute-selector
            :attribute="$attribute"
            :selections="$selections"
            :additionsMap="$additionsMap"
            :showValidationErrors="$showValidationErrors"
        />
    @endforeach

    <x-cart.quantity-selector :quantity="$quantity" />

    <x-ui.price-summary
        :subtotal="$this->subtotal"
        :quantity="$quantity"
        :total="$this->total"
    />

    <div>
        <x-ui.button
            wire:click="addToCart"
            wire:loading.attr="disabled"
            :loading="false"
            loading-text="Adding..."
            size="lg"
            class="w-full">
            Add {{ $quantity > 1 ? $quantity . ' Items' : 'Item' }} to Cart
        </x-ui.button>

        @if($showValidationErrors && count($this->missingAttributes) > 0)
            <p class="text-sm text-red-600 mt-2 text-center">
                Missing selections: {{ implode(', ', $this->missingAttributes) }}
            </p>
        @endif
    </div>
</div>

@push('finalScripts')
    <script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('hide-toast', () => {
            setTimeout(() => {
                Livewire.dispatch('hide-toast');
            }, 3000);
        });
    });
    </script>
@endpush
