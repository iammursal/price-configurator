@props(['quantity' => 1, 'label' => 'Quantity'])

<div class="border-t pt-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }} <span class="text-red-500">*</span>
    </label>
    <div class="flex items-center space-x-3">
        <x-ui.icon-button
            wire:click="decrementQuantity"
            :disabled="$quantity <= 1"
            icon="minus"
            size="lg"
        />

        <input type="number"
               wire:model.live="quantity"
               min="1"
               max="99"
               class="w-20 px-3 py-2 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">

        <x-ui.icon-button
            wire:click="incrementQuantity"
            icon="plus"
            size="lg"
        />
    </div>
</div>
