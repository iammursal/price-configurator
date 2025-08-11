<?php

namespace Database\Factories;

use App\Models\AttributeOption;
use App\Models\AttributeOptionProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\AttributeOptionProduct>
 */
class AttributeOptionProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\AttributeOptionProduct>
     */
    protected $model = AttributeOptionProduct::class;

    /**
     * Get the default state for the model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_option_id' => AttributeOption::factory(),
            'product_id' => Product::factory(),
            'price_addition' => fake()->numberBetween(0, 10000), // in fils
        ];
    }
}
