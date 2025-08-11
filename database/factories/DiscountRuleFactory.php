<?php

namespace Database\Factories;

use App\Enums\DiscountComparison;
use App\Enums\DiscountValueType;
use App\Enums\UserType;
use App\Models\DiscountRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\DiscountRule>
 */
class DiscountRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscountRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $discountType = fake()->randomElement([DiscountValueType::Percent, DiscountValueType::Amount]);

        return [
            'name' => fake()->words(3, true),
            'discount_type' => $discountType,
            'discount_value' => $discountType === DiscountValueType::Percent
                ? fake()->randomFloat(3, 0.01, 0.50)    // 1% to 50%
                : fake()->randomFloat(3, 0.500, 20.000), // 0.500 to 20.000 KWD
            'attribute_option_id' => null,
            'comparator' => fake()->optional()->randomElement(DiscountComparison::cases()),
            'threshold' => fake()->optional()->randomFloat(3, 10.000, 200.000), // 10 to 200 KWD
            'user_type' => fake()->optional()->randomElement([UserType::Normal, UserType::Company]),
            'is_active' => fake()->boolean(80),
            'priority' => fake()->numberBetween(1, 100),
            'stop_further' => fake()->boolean(20),
            'starts_at' => fake()->optional()->dateTimeBetween('-1 month', '+1 month'),
            'ends_at' => fake()->optional()->dateTimeBetween('+1 month', '+6 months'),
        ];
    }
}
