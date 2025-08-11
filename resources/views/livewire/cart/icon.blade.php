<div class="relative" x-data="{ open: @entangle('showDropdown') }" @click.away="open = false">
    <button
        type="button"
        wire:click="toggleDropdown"
        class="relative inline-flex items-center gap-2 text-gray-700 hover:text-gray-900 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9M9 22a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" />
        </svg>
        <span class="text-sm hidden sm:inline">Cart</span>
        @if($count > 0)
            <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-primary-600 text-white">
                {{ $count }}
            </span>
        @endif
        <span class="ml-1 text-sm font-semibold hidden sm:inline">{{ $totalMoney->format() }}</span>
    </button>

    <!-- Dropdown - Fixed positioning for mobile -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 top-full mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-lg shadow-lg border border-gray-200 z-50 sm:w-80">

        @if(empty($cartItems))
            <div class="p-4 text-center text-gray-500">
                Your cart is empty
            </div>
        @else
            <!-- Cart Header -->
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Cart ({{ $count }} items)</h3>
                @if($count > 0)
                    <button
                        wire:click="clearCart"
                        wire:confirm="Are you sure you want to clear all items?"
                        class="text-xs text-red-600 hover:text-red-800">
                        Clear All
                    </button>
                @endif
            </div>

            <!-- Cart Items -->
            <div class="max-h-64 overflow-y-auto">
                @foreach($cartItems as $item)
                    <div class="px-4 py-3 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0"> 
                                <a href="{{ route('products.show', $item['product']) }}" class="text-sm font-medium text-gray-900 hover:text-primary-600">
                                    {{ $item['product']->name }}
                                </a>  
                                @if(!empty($item['selected_options']))
                                    <p class="text-xs text-gray-500 mt-1 truncate">
                                        {{ implode(', ', $item['selected_options']) }}
                                    </p>
                                @endif
                                <div class="flex items-center justify-between mt-2">
                                    <!-- Quantity Controls -->
                                    <div class="flex items-center space-x-2">
                                        <button 
                                            wire:click="updateQuantity({{ $item['index'] }}, {{ $item['qty'] - 1 }})"
                                            {{ $item['qty'] <= 1 ? 'disabled' : '' }}
                                            class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:opacity-50">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </button>
                                        <span class="text-sm text-gray-600 min-w-[20px] text-center">{{ $item['qty'] }}</span>
                                        <button 
                                            wire:click="updateQuantity({{ $item['index'] }}, {{ $item['qty'] + 1 }})"
                                            class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ money($item['amount'])->format() }}
                                    </span>
                                </div>
                            </div>
                            <button
                                wire:click="removeItem({{ $item['index'] }})" 
                                wire:confirm="Remove this item from cart?"
                                class="ml-3 text-red-600 hover:text-red-800 focus:outline-none flex-shrink-0">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Cart Footer -->
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-900">Total:</span>
                    <span class="text-lg font-bold text-gray-900">{{ $totalMoney->format() }}</span>
                </div>
                <a href="{{ route('checkout.index') }}"
                   class="block w-full px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 text-center">
                    Checkout
                </a>
            </div>
        @endif
    </div>
</div>
