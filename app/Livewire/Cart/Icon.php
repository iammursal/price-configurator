<?php

namespace App\Livewire\Cart;

use App\Models\AttributeOption;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Livewire Cart icon with dropdown details.
 *
 * Contract for session('cart'):
 * cart = [
 *   'items' => [
 *     [
 *       'product_id'  => int,
 *       'qty'         => int,                 // quantity for this line
 *       'selections'  => [attrId => optionId],
 *       'amount'      => int,                 // LINE TOTAL in minor units (e.g., fils), not unit price
 *       'config_key'  => string|null,         // grouping key for same configuration
 *     ],
 *     ...
 *   ],
 * ]
 *
 * Notes:
 * - This component expects 'amount' to already be qty-multiplied (line total).
 * - All totals are tracked in minor units to avoid float rounding issues.
 * - UI formatting uses the money() helper with default currency to present amounts.
 */
class Icon extends Component
{
    /** Sum of item quantities across the cart (badge count). */
    public int $count = 0;

    /** Cart total in minor units (sum of line 'amount'). */
    public int $totalAmount = 0;

    /** Controls dropdown visibility (toggled by the icon button). */
    public bool $showDropdown = false;

    /**
     * Items enriched for the dropdown view:
     * [
     *   [
     *     'index'            => int,        // original array index in session cart
     *     'product'          => Product,
     *     'qty'              => int,
     *     'amount'           => int,        // line total in minor units
     *     'selected_options' => string[],   // option names for display
     *     'config_key'       => string|null,
     *   ],
     * ]
     */
    public array $cartItems = [];

    /**
     * Refresh on 'cart-updated' events dispatched from elsewhere (add/remove/clear/qty changes).
     */
    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $cart = session('cart', ['items' => []]);

        // Badge count is total quantity across lines
        $this->count = collect($cart['items'])->sum('qty');

        // Sum of line totals (minor units). 'amount' is expected as line total.
        $this->totalAmount = (int) collect($cart['items'])->sum('amount');

        // Load detailed items for the dropdown (product + selected option names)
        $this->loadCartItems($cart['items']);
    }

    /**
     * Map raw cart lines to view-friendly structures with Product and option names.
     * Note: This performs 1 query per product and per option; for larger carts,
     * consider eager loading or caching if needed.
     */
    protected function loadCartItems(array $items): void
    {
        $this->cartItems = [];

        // Batch resolve products and options to avoid N+1
        $productIds = collect($items)->pluck('product_id')->unique()->all();
        $optionIds = collect($items)->pluck('selections')->flatMap(fn ($s) => array_values($s ?? []))->unique()->all();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $options = AttributeOption::whereIn('id', $optionIds)->get()->keyBy('id');

        foreach ($items as $index => $item) {
            $product = $products[$item['product_id']] ?? null;
            if (! $product) {
                continue;
            }

            $selectedOptions = [];
            foreach ($item['selections'] as $optionId) {
                if (isset($options[$optionId])) {
                    $selectedOptions[] = $options[$optionId]->name;
                }
            }

            $this->cartItems[] = [
                'index' => $index,
                'product' => $product,
                'qty' => $item['qty'],
                'amount' => $item['amount'],
                'selected_options' => $selectedOptions,
                'config_key' => $item['config_key'] ?? null,
            ];
        }
    }

    /** Toggle dropdown visibility. */
    public function toggleDropdown(): void
    {
        $this->showDropdown = ! $this->showDropdown;
    }

    /**
     * Remove a line by its index and notify listeners/UI.
     * Uses array_splice to keep indexes contiguous.
     */
    public function removeItem(int $index): void
    {
        $cart = session('cart', ['items' => []]);

        if (isset($cart['items'][$index])) {
            array_splice($cart['items'], $index, 1);
            session()->put('cart', $cart);

            $this->refreshCart();
            $this->dispatch('cart-updated');
            $this->dispatch('notify', body: 'Item removed from cart.');
        }
    }

    /**
     * Update quantity for a line. Recomputes the line total ('amount') in minor units.
     * Assumes:
     * - $product->base_price is a Money object (cknown/laravel-money).
     * - Any option-based price additions are returned in minor units by getPriceAddition()
     *   when available; otherwise additions default to 0 (no change).
     */
    public function updateQuantity(int $index, int $newQty): void
    {
        if ($newQty <= 0) {
            $this->removeItem($index);

            return;
        }

        $cart = session('cart', ['items' => []]);

        if (isset($cart['items'][$index])) {
            $line = &$cart['items'][$index];
            $line['qty'] = $newQty;

            $product = Product::find($line['product_id']);
            if ($product) {
                $baseMinor = (int) $product->base_price->getAmount();
                $selectionIds = array_values($line['selections'] ?? []);
                $additionsMinor = $product->additionsForOptionIds($selectionIds);
                $unitMinor = $baseMinor + (int) $additionsMinor;
                $line['amount'] = $unitMinor * $newQty; // line total in minor units
            }

            session()->put('cart', $cart);
            $this->refreshCart();
            $this->dispatch('cart-updated');
        }
    }

    /** Clear the entire cart and notify UI. */
    public function clearCart(): void
    {
        session()->forget('cart');
        $this->refreshCart();
        $this->dispatch('cart-updated');
        $this->dispatch('notify', body: 'Cart cleared.');
    }

    /** Initialize state on first mount. */
    public function mount(): void
    {
        $this->refreshCart();
    }

    /** Render the icon + dropdown with formatted total using the money() helper. */
    public function render()
    {
        $totalMoney = money($this->totalAmount);

        return view('livewire.cart.icon', compact('totalMoney'));
    }
}
