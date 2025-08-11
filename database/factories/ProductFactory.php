<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Product>
     */
    protected $model = Product::class;

    /**
     * Get the default state for the model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->catchPhrase();

        return [
            'sku' => strtoupper(Str::random(8)),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'base_price' => fake()->numberBetween(1000, 100000), // in fils
            'is_active' => fake()->boolean(90),
        ];
    }
}
