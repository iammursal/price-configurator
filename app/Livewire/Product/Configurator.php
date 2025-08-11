<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Cknow\Money\Money;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Configurator extends Component
{
    public $productAttributes;

    public Product $product;

    /** @var array<int,int> attribute_id => attribute_option_id */
    public array $selections = [];

    /** @var array<int,int> option_id => price_addition in minor units (fils) */
    public array $additionsMap = [];

    public int $quantity = 1;

    public bool $showValidationErrors = false;

    public bool $showAddedToCartToast = false;

    public function mount(): void
    {
        // Map option_id => price_addition (minor units) for this product
        $this->additionsMap = DB::table('attribute_option_product')
            ->where('product_id', $this->product->id)
            ->pluck('price_addition', 'attribute_option_id')
            ->toArray();
    }

    public function updatedSelections(): void
    {
        // Reset validation errors when user makes a selection
        $this->showValidationErrors = false;
    }

    public function updatedQuantity(): void
    {
        // Ensure quantity is at least 1
        if ($this->quantity < 1) {
            $this->quantity = 1;
        }
        // Reset validation errors when user changes quantity
        $this->showValidationErrors = false;
    }

    public function incrementQuantity(): void
    {
        $this->quantity++;
        $this->showValidationErrors = false;
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
        $this->showValidationErrors = false;
    }

    public function selectOption(int $attributeId, int $optionId): void
    {
        $this->selections[$attributeId] = $optionId;
        $this->showValidationErrors = false;
    }

    public function getSubtotalProperty(): Money
    {
        $baseAmount = (int) $this->product->base_price->getAmount();
        $additionalAmount = 0;

        foreach ($this->selections as $attributeId => $optionId) {
            if (is_numeric($optionId) && $optionId > 0) {
                $additionalAmount += $this->additionsMap[$optionId] ?? 0;
            }
        }

        return money($baseAmount + $additionalAmount);
    }

    public function getTotalProperty(): Money
    {
        $subtotal = $this->subtotal;
        $totalAmount = (int) $subtotal->getAmount() * $this->quantity;

        return money($totalAmount);
    }

    /**
     * Check if all attributes have selections
     */
    public function getAllAttributesSelected(): bool
    {
        foreach ($this->productAttributes as $attribute) {
            $selectedOptionId = $this->selections[$attribute->id] ?? null;

            if (! $selectedOptionId || ! is_numeric($selectedOptionId) || $selectedOptionId <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing attributes for display
     */
    public function getMissingAttributesProperty(): array
    {
        $missing = [];

        foreach ($this->productAttributes as $attribute) {
            $selectedOptionId = $this->selections[$attribute->id] ?? null;

            if (! $selectedOptionId || ! is_numeric($selectedOptionId) || $selectedOptionId <= 0) {
                $missing[] = $attribute->name;
            }
        }

        return $missing;
    }

    public function addToCart(): void
    {
        // Always perform validation when button is clicked
        $this->showValidationErrors = true;

        // Check if all attributes are selected
        if (! $this->getAllAttributesSelected()) {
            $missing = $this->missingAttributes;
            $message = 'Please select options for: '.implode(', ', $missing);
            $this->dispatch('notify', body: $message, type: 'error');

            return;
        }

        $cart = session('cart', ['items' => []]);

        // Create a unique key for this product configuration
        $configKey = $this->generateConfigKey($this->product->id, $this->selections);

        // Check if this exact configuration already exists
        $existingIndex = $this->findExistingCartItem($cart['items'], $configKey);

        if ($existingIndex !== null) {
            // Update existing item quantity and amount
            $cart['items'][$existingIndex]['qty'] += $this->quantity;
            $cart['items'][$existingIndex]['amount'] = $this->calculateItemTotal($cart['items'][$existingIndex]['qty']);
        } else {
            // Add new item
            $cart['items'][] = [
                'product_id' => $this->product->id,
                'qty' => $this->quantity,
                'selections' => $this->selections,
                'amount' => $this->calculateItemTotal($this->quantity),
                'config_key' => $configKey, // Add this for easy identification
            ];
        }

        session()->put('cart', $cart);

        // Reset form after successful addition
        $this->selections = [];
        $this->quantity = 1;
        $this->showValidationErrors = false;

        $this->showAddedToCartToast = true;

        // Dispatch events
        $this->dispatch('cart-updated');
        $this->dispatch('added-to-cart');

        $this->dispatch('notify', type: 'success', title: 'Added to cart', body: $this->quantity.' item(s) added');
    }

    private function generateConfigKey(int $productId, array $selections): string
    {
        // Sort selections to ensure consistent key for same configuration
        ksort($selections);

        return md5($productId.':'.json_encode($selections));
    }

    private function findExistingCartItem(array $cartItems, string $configKey): ?int
    {
        foreach ($cartItems as $index => $item) {
            if (($item['config_key'] ?? null) === $configKey) {
                return $index;
            }
        }

        return null;
    }

    private function calculateItemTotal(int $quantity): int
    {
        // Get base price as Money object
        $basePrice = $this->product->base_price; // This is a Money object

        // Convert additions to Money and sum
        $additionsTotal = collect($this->selections)
            ->map(fn ($optionId) => money($this->additionsMap[$optionId] ?? 0, $basePrice->getCurrency()))
            ->reduce(fn ($carry, $addition) => $carry ? $carry->add($addition) : $addition, null);

        // Add base price and additions
        $itemPrice = $additionsTotal ? $basePrice->add($additionsTotal) : $basePrice;

        // Multiply by quantity and return minor units
        return $itemPrice->multiply($quantity)->getAmount();
    }

    /**
     * Auto-hide the toast notification
     */
    #[On('hide-toast')]
    public function hideToast()
    {
        $this->showAddedToCartToast = false;
    }

    public function render()
    {
        return view('livewire.product.configurator');
    }
}
