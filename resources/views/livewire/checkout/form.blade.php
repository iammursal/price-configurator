<div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Order Form -->
        <div class="bg-white rounded-lg shadow-sm p-6 col-span-1 lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Customer Information</h2>

            <form wire:submit="placeOrder" class="space-y-6">
                <x-ui.form.radio-group
                    label="Customer Type"
                    name="customer_type"
                    :options="['normal' => 'Individual', 'company' => 'Company']"
                    :value="$customer_type"
                    wire:model.live="customer_type"
                    required
                    :error="$errors->first('customer_type')"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.form.input
                        label="First Name"
                        name="first_name"
                        wire:model="first_name"
                        required
                        :error="$errors->first('first_name')"
                    />

                    <x-ui.form.input
                        label="Last Name"
                        name="last_name"
                        wire:model="last_name"
                        required
                        :error="$errors->first('last_name')"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.form.input
                        label="Email"
                        name="email"
                        type="email"
                        wire:model="email"
                        required
                        :error="$errors->first('email')"
                    />

                    <x-ui.form.input
                        label="Phone"
                        name="phone"
                        type="tel"
                        wire:model="phone"
                        required
                        :error="$errors->first('phone')"
                    />
                </div>

                <!-- Billing Address -->
                <h3 class="text-md font-semibold text-gray-900 mt-8 mb-4">Billing Address</h3>

                <div>
                    <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-2">
                        Address Line 1 *
                    </label>
                    <input type="text"
                           id="address_line_1"
                           wire:model="address_line_1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('address_line_1') border-red-300 @enderror">
                    @error('address_line_1')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-2">
                        Address Line 2
                    </label>
                    <input type="text"
                           id="address_line_2"
                           wire:model="address_line_2"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                            City *
                        </label>
                        <input type="text"
                               id="city"
                               wire:model="city"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('city') border-red-300 @enderror">
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-2">
                            State *
                        </label>
                        <input type="text"
                               id="state"
                               wire:model="state"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('state') border-red-300 @enderror">
                        @error('state')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Postal Code *
                        </label>
                        <input type="text"
                               id="postal_code"
                               wire:model="postal_code"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('postal_code') border-red-300 @enderror">
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6">
                    <x-ui.button
                        type="submit"
                        wire:loading.attr="disabled"
                        loading-text="Processing..."
                        size="lg"
                        class="w-full">
                        Place Order - {{ $finalTotalMoney->format() }}
                    </x-ui.button>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Order Summary</h2>

            <!-- Cart Items -->
            <div class="space-y-4 mb-6">
                @foreach($cartItems as $item)
                    <x-cart.item :item="$item" />
                @endforeach
            </div>

            <!-- Pricing Breakdown -->
            <div class="border-t border-gray-200 pt-4 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="text-gray-900">{{ $subtotalMoney->format() }}</span>
                </div>

                @if(!empty($discountCalculation['discounts']))
                    @foreach($discountCalculation['discounts'] as $discount)
                        <div class="flex items-center justify-between text-sm group" x-data="{ showTooltip: false }">
                            <div class="relative">
                                <span class="text-green-600 cursor-help border-b border-dotted border-green-400"
                                      @mouseenter="showTooltip = true"
                                      @mouseleave="showTooltip = false">
                                    {{ $discount['rule']->name }}
                                    @if($discount['rule']->isPercentageDiscount())
                                        <span class="text-xs opacity-75">({{ number_format($discount['rule']->discount_percentage, 1) }}%)</span>
                                    @else
                                        <span class="text-xs opacity-75">({{ $discount['rule']->discount_amount->format() }})</span>
                                    @endif
                                </span>

                                <!-- Tooltip -->
                                <div x-show="showTooltip"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform scale-95"
                                     x-transition:enter-end="opacity-100 transform scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 transform scale-100"
                                     x-transition:leave-end="opacity-0 transform scale-95"
                                     class="absolute bottom-full left-0 mb-2 w-96 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-10">
                                    <div class="font-mono text-xs space-y-1">
                                        <div>Applied to: <span class="text-blue-300">{{ money($discount['applied_to'])->format() }}</span></div>

                                        @if($discount['rule']->isPercentageDiscount())
                                            <div>Discount rate: <span class="text-yellow-300">{{ number_format($discount['rule']->discount_percentage, 1) }}%</span></div>
                                            <div>Calculation: <span class="text-gray-400">{{ money($discount['applied_to'])->format() }} × {{ number_format($discount['rule']->discount_value, 3) }}</span></div>
                                        @else
                                            <div>Fixed discount: <span class="text-yellow-300">{{ $discount['rule']->discount_amount->format() }}</span></div>
                                            <div>Applied: <span class="text-gray-400">{{ $discount['rule']->discount_amount->format() }}</span></div>
                                        @endif

                                        <div class="border-t border-gray-600 pt-1 mt-1">
                                            <div>Result: <span class="text-green-400 font-semibold">-{{ $discount['formatted'] }}</span></div>
                                        </div>
                                    </div>
                                    <div class="absolute top-full left-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                            <span class="text-green-600">-{{ $discount['formatted'] }}</span>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between text-sm font-medium pt-2 border-t border-gray-100">
                        <span class="text-green-600">Total Savings:</span>
                        <span class="text-green-600">-{{ $totalDiscountMoney->format() }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between text-lg font-semibold text-gray-900 pt-2 border-t border-gray-200">
                    <span>Total:</span>
                    <span>{{ $finalTotalMoney->format() }}</span>
                </div>
            </div>

            @if(!empty($discountCalculation['discounts']))
                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-2">
                            <p class="text-sm font-medium text-green-800">
                                Congratulations! You're saving {{ $totalDiscountMoney->format() }}
                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                {{ count($discountCalculation['discounts']) }} discount{{ count($discountCalculation['discounts']) > 1 ? 's' : '' }} applied sequentially
                            </p>
                        </div>
                    </div>

                    <!-- Optional: Show cascading steps -->
                    @if(isset($discountCalculation['cascading_steps']) && config('app.debug'))
                        <div class="mt-3 pt-3 border-t border-green-200">
                            <p class="text-xs font-medium text-green-800 mb-2">Discount Application Steps:</p>
                            @foreach($discountCalculation['cascading_steps'] as $step)
                                <div class="flex items-center justify-between text-xs text-green-700">
                                    <span>
                                        {{ $step['step'] }}. {{ $step['description'] }}
                                        @if($step['discount'] > 0)
                                            ({{ money($step['discount'])->format() }} off)
                                        @endif
                                    </span>
                                    <span class="font-mono">{{ money($step['amount'])->format() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
