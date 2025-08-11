<?php

namespace App\Services;

use App\Events\CartItemAdded;
use App\Events\CartItemRemoved;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function addItem(Product $product, array $selections, int $quantity): void
    {
        if (! $this->validateSelections($product, $selections)) {
            throw new \InvalidArgumentException('Invalid product configuration');
        }

        $cart = $this->getCart();
        $configKey = $this->generateConfigKey($product->id, $selections);
        $amount = $this->calculateItemTotal($product, $selections, $quantity);

        $existingIndex = $this->findExistingItem($cart['items'], $configKey);

        if ($existingIndex !== null) {
            $cart['items'][$existingIndex]['qty'] += $quantity;
            $cart['items'][$existingIndex]['amount'] = $this->calculateItemTotal(
                $product,
                $selections,
                $cart['items'][$existingIndex]['qty']
            );
        } else {
            $cart['items'][] = [
                'product_id' => $product->id,
                'qty' => $quantity,
                'selections' => $selections,
                'amount' => $amount,
                'config_key' => $configKey,
                'created_at' => now()->toISOString(),
            ];
        }

        $this->saveCart($cart);
        event(new CartItemAdded($product, $selections, $quantity));
    }

    public function removeItem(int $index): void
    {
        $cart = $this->getCart();

        if (isset($cart['items'][$index])) {
            $removedItem = $cart['items'][$index];
            array_splice($cart['items'], $index, 1);
            $this->saveCart($cart);

            event(new CartItemRemoved($removedItem));
        }
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($index);

            return;
        }

        $cart = $this->getCart();

        if (isset($cart['items'][$index])) {
            $item = $cart['items'][$index];
            $product = Product::find($item['product_id']);

            if ($product) {
                $cart['items'][$index]['qty'] = $quantity;
                $cart['items'][$index]['amount'] = $this->calculateItemTotal(
                    $product,
                    $item['selections'],
                    $quantity
                );
                $this->saveCart($cart);
            }
        }
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function getCart(): array
    {
        return Session::get(self::SESSION_KEY, ['items' => []]);
    }

    public function getItemCount(): int
    {
        $cart = $this->getCart();

        return collect($cart['items'])->sum('qty');
    }

    public function getTotal(): int
    {
        $cart = $this->getCart();

        return collect($cart['items'])->sum('amount');
    }

    public function validateIntegrity(): bool
    {
        $cart = $this->getCart();

        foreach ($cart['items'] as $item) {
            if (! $this->validateCartItem($item)) {
                Log::warning('Cart integrity check failed', ['item' => $item]);

                return false;
            }
        }

        return true;
    }

    private function validateCartItem(array $item): bool
    {
        // Check required fields
        if (! isset($item['product_id'], $item['qty'], $item['amount'], $item['selections'])) {
            return false;
        }

        // Validate product exists
        $product = Product::find($item['product_id']);
        if (! $product) {
            return false;
        }

        // Validate selections
        if (! $this->validateSelections($product, $item['selections'])) {
            return false;
        }

        // Validate amount calculation
        $expectedAmount = $this->calculateItemTotal($product, $item['selections'], $item['qty']);
        if (abs($expectedAmount - $item['amount']) > 1) { // Allow 1 fils tolerance
            return false;
        }

        return true;
    }

    private function validateSelections(Product $product, array $selections): bool
    {
        // Get valid option IDs for this product
        $validOptionIds = $product->options()->pluck('attribute_options.id')->toArray();

        foreach ($selections as $attributeId => $optionId) {
            if (! in_array($optionId, $validOptionIds, true)) {
                return false;
            }
        }

        return true;
    }

    private function calculateItemTotal(Product $product, array $selections, int $quantity): int
    {
        $basePrice = $product->base_price->getAmount();
        $additions = $product->additionsForOptionIds(array_values($selections));

        return ($basePrice + $additions) * $quantity;
    }

    private function generateConfigKey(int $productId, array $selections): string
    {
        ksort($selections);

        return md5($productId.':'.serialize($selections));
    }

    private function findExistingItem(array $items, string $configKey): ?int
    {
        foreach ($items as $index => $item) {
            if (($item['config_key'] ?? '') === $configKey) {
                return $index;
            }
        }

        return null;
    }

    private function saveCart(array $cart): void
    {
        Session::put(self::SESSION_KEY, $cart);
    }
}
