@props(['subtotal', 'quantity', 'total'])

<div class="bg-gray-50 p-4 rounded-lg space-y-2">
    <div class="flex items-center justify-between text-sm text-gray-600">
        <span>Price per item</span>
        <span>{{ $subtotal->format() }}</span>
    </div>
    <div class="flex items-center justify-between text-sm text-gray-600">
        <span>Quantity</span>
        <span>{{ $quantity }}</span>
    </div>
    <div class="flex items-center justify-between border-t pt-2">
        <span class="text-lg font-medium text-gray-900">Total</span>
        <span class="text-2xl font-semibold text-gray-900">
            {{ $total->format() }}
        </span>
    </div>
</div>
