<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a product with its configurable attributes/options and price additions.
     */
    public function show(Product $product)
    {
        // Ensure the product is enabled
        if (! $product->is_active) {
            abort(404);
        }

        // Load only attributes that have options associated with this product
        $attributes = Attribute::query()
            ->whereHas('options.products', function ($q) use ($product) {
                $q->where('products.id', $product->id);
            })
            ->with([
                'options' => function ($q) use ($product) {
                    $q->whereHas('products', function ($subQ) use ($product) {
                        $subQ->where('products.id', $product->id);
                    })->orderBy('sort_order');
                },
            ])
            ->orderBy('sort_order')
            ->get();

        // Fetch price additions for this product from the pivot table
        $pivotAdditions = DB::table('attribute_option_product')
            ->where('product_id', $product->id)
            ->pluck('price_addition', 'attribute_option_id'); // [option_id => fils]

        // Attach a transient price_addition attribute to each option for this product
        $attributes->each(function ($attribute) use ($pivotAdditions) {
            $attribute->options->each(function ($option) use ($pivotAdditions) {
                $option->price_addition = (float) (($pivotAdditions[$option->id] ?? 0) / 1000); // KD
            });
        });

        return view('pages.products.[slug].index', [
            'product' => $product,
            'attributes' => $attributes,
        ]);
    }
}
