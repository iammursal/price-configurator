<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartItemAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Product $product,
        public array $selections,
        public int $quantity
    ) {}
}
