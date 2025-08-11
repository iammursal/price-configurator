<?php

namespace Database\Seeders;

use App\Enums\DiscountComparison;
use App\Enums\DiscountValueType;
use App\Enums\UserType;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\AttributeOptionProduct;
use App\Models\DiscountRule;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Seed Attributes and Options (exactly as requested)
        $attributesData = [
            'Delivery Method' => [
                'At Home',
                'In Lab',
            ],
            'Speed' => [
                'Same Day',
                'Next Day',
            ],
            'Color' => [
                'Red',
                'Green',
                'Blue',
                'Yellow',
                'Orange',
                'Purple',
                'Black',
                'White',
            ],
            'Size' => [
                'Small',
                'Medium',
                'Large',
                'Extra Large',
            ],
        ];

        // Ensure deterministic sort orders by index and avoid duplicates on re-run
        $attributes = [];
        $optionsByAttribute = [];

        foreach ($attributesData as $attributeName => $options) {
            $attribute = Attribute::query()->updateOrCreate(
                ['name' => $attributeName],
                ['sort_order' => 0]
            );
            $attributes[$attributeName] = $attribute;

            $optionsByAttribute[$attributeName] = [];
            foreach (array_values($options) as $i => $optionName) {
                $option = AttributeOption::query()->updateOrCreate(
                    ['attribute_id' => $attribute->id, 'name' => $optionName],
                    ['sort_order' => $i]
                );
                $optionsByAttribute[$attributeName][$optionName] = $option;
            }
        }

        // 2) Seed Products with custom price range (20-200 KWD)
        // Use a callback to ensure each product gets a different random price
        $products = Product::factory()->count(10)->state(function () {
            return [
                'base_price' => fake()->numberBetween(20000, 200000), // 20-200 KWD in fils
            ];
        })->create();

        // 3) Optionally attach a sensible default selection of options to each product with a price addition
        // Price additions (in fils) tuned by attribute; adjust as needed
        $priceAdditions = [
            'Delivery Method' => [
                'At Home' => 2000, // +2.000 KD for home service
                'In Lab' => 0,
            ],
            'Speed' => [
                'Same Day' => 1500, // +1.500 KD for rush
                'Next Day' => 0,
            ],
            'Color' => [
                // No price impact for colors by default
            ],
            'Size' => [
                'Small' => 0,
                'Medium' => 250,   // +0.250 KD
                'Large' => 500,    // +0.500 KD
                'Extra Large' => 1000, // +1.000 KD
            ],
        ];

        foreach ($products as $product) {
            // Randomly select 1 or 2 attributes for this product
            $attributeNames = array_keys($optionsByAttribute);
            $numAttributes = fake()->numberBetween(1, 2); // Random 1 or 2
            $selectedAttributes = fake()->randomElements($attributeNames, $numAttributes);

            foreach ($selectedAttributes as $attrName) {
                $optionsMap = $optionsByAttribute[$attrName];

                // Create entries for ALL options of this attribute (not just one random option)
                foreach ($optionsMap as $optionName => $option) {
                    $addition = $priceAdditions[$attrName][$option->name] ?? 0;

                    // Upsert by unique (attribute_option_id, product_id)
                    AttributeOptionProduct::query()->updateOrCreate(
                        ['attribute_option_id' => $option->id, 'product_id' => $product->id],
                        ['price_addition' => $addition]
                    );
                }
            }
        }

        // 4) Seed Comprehensive Discount Rules Based on PDF Requirements

        // === ATTRIBUTE-BASED DISCOUNTS ===
        // Delivery Method Discounts
        $inLab = $optionsByAttribute['Delivery Method']['In Lab'] ?? null;
        $atHome = $optionsByAttribute['Delivery Method']['At Home'] ?? null;

        if ($inLab) {
            DiscountRule::query()->updateOrCreate(
                ['name' => 'In-Lab Discount'],
                [
                    'discount_type' => DiscountValueType::Amount,
                    'discount_value' => 2.500,      // -2.500 KD for lab pickup
                    'attribute_option_id' => $inLab->id,
                    'is_active' => true,
                    'priority' => 10,
                    'stop_further' => false,
                    'starts_at' => null,
                    'ends_at' => null,
                ]
            );
        }

        // Speed Discounts
        $nextDay = $optionsByAttribute['Speed']['Next Day'] ?? null;
        $sameDay = $optionsByAttribute['Speed']['Same Day'] ?? null;

        if ($nextDay) {
            DiscountRule::query()->updateOrCreate(
                ['name' => 'Next Day Delivery Discount'],
                [
                    'discount_type' => DiscountValueType::Percent,
                    'discount_value' => 0.07,       // 7% off for next day
                    'attribute_option_id' => $nextDay->id,
                    'is_active' => true,
                    'priority' => 15,
                    'stop_further' => false,
                    'starts_at' => null,
                    'ends_at' => null,
                ]
            );
        }

        // Color-based Promotional Discounts
        $redColor = $optionsByAttribute['Color']['Red'] ?? null;
        $blueColor = $optionsByAttribute['Color']['Blue'] ?? null;

        if ($redColor) {
            DiscountRule::query()->updateOrCreate(
                ['name' => 'Red Color Special'],
                [
                    'discount_type' => DiscountValueType::Percent,
                    'discount_value' => 0.05,       // 5% off for red items
                    'attribute_option_id' => $redColor->id,
                    'is_active' => true,
                    'priority' => 20,
                    'stop_further' => false,
                    'starts_at' => null,
                    'ends_at' => null,
                ]
            );
        }

        if ($blueColor) {
            DiscountRule::query()->updateOrCreate(
                ['name' => 'Blue Monday Special'],
                [
                    'discount_type' => DiscountValueType::Amount,
                    'discount_value' => 1.000,      // -1.000 KD for blue items
                    'attribute_option_id' => $blueColor->id,
                    'is_active' => true,
                    'priority' => 25,
                    'stop_further' => false,
                    'starts_at' => null,
                    'ends_at' => null,
                ]
            );
        }

        // Size-based Discounts
        $extraLarge = $optionsByAttribute['Size']['Extra Large'] ?? null;
        $large = $optionsByAttribute['Size']['Large'] ?? null;

        if ($extraLarge) {
            DiscountRule::query()->updateOrCreate(
                ['name' => 'Extra Large Size Discount'],
                [
                    'discount_type' => DiscountValueType::Percent,
                    'discount_value' => 0.10,       // 10% off for XL
                    'attribute_option_id' => $extraLarge->id,
                    'is_active' => true,
                    'priority' => 30,
                    'stop_further' => false,
                    'starts_at' => null,
                    'ends_at' => null,
                ]
            );
        }

        DiscountRule::query()->updateOrCreate(
            ['name' => 'Order Over 130 KWD'],
            [
                'discount_type' => DiscountValueType::Percent,
                'discount_value' => 0.10,           // 10% off
                'attribute_option_id' => null,
                'comparator' => DiscountComparison::GreaterThanOrEqual,
                'threshold' => '130.000',           // MoneyIntegerCast will convert to minor units
                'user_type' => null,
                'is_active' => true,
                'priority' => 40,
                'stop_further' => false,
                'starts_at' => null,
                'ends_at' => null,
            ]
        );

        // === USER-TYPE-BASED DISCOUNTS ===
        DiscountRule::query()->updateOrCreate(
            ['name' => 'Company Customer Discount'],
            [
                'discount_type' => DiscountValueType::Percent,
                'discount_value' => 0.15,           // 15% off for companies
                'attribute_option_id' => null,
                'comparator' => null,
                'threshold' => null,
                'user_type' => UserType::Company,
                'is_active' => true,
                'priority' => 5,                    // High priority
                'stop_further' => false,
                'starts_at' => null,
                'ends_at' => null,
            ]
        );

        DiscountRule::query()->updateOrCreate(
            ['name' => 'Individual Customer Welcome'],
            [
                'discount_type' => DiscountValueType::Amount,
                'discount_value' => 1.500,          // -1.500 KWD welcome bonus
                'attribute_option_id' => null,
                'comparator' => null,
                'threshold' => null,
                'user_type' => UserType::Normal,
                'is_active' => true,
                'priority' => 65,
                'stop_further' => false,
                'starts_at' => null,
                'ends_at' => null,
            ]
        );

        // === CONDITIONAL/COMBINATION DISCOUNTS ===
        // Same Day + At Home (Premium Service Discount)
        if ($sameDay && $atHome) {
            DiscountRule::query()->updateOrCreate(
                ['name' => 'Same Day'],
                [
                    'discount_type' => DiscountValueType::Percent,
                    'discount_value' => 0.08,       // 8% off premium service
                    'attribute_option_id' => $sameDay->id,
                    'is_active' => true,
                    'priority' => 12,
                    'stop_further' => false,
                    'starts_at' => null,
                    'ends_at' => null,
                ]
            );
        }

        // === SEASONAL/PROMOTIONAL DISCOUNTS ===
        DiscountRule::query()->updateOrCreate(
            ['name' => 'Flash Sale - Limited Time'],
            [
                'discount_type' => DiscountValueType::Percent,
                'discount_value' => 0.20,           // 20% off flash sale
                'attribute_option_id' => null,
                'comparator' => DiscountComparison::GreaterThanOrEqual,
                'threshold' => '275.000',            // MoneyIntegerCast will convert to minor units
                'user_type' => null,
                'is_active' => true,
                'priority' => 1,                    // Highest priority
                'stop_further' => true,             // Stop other discounts
                'starts_at' => now(),
                'ends_at' => now()->addDays(7),     // Valid for 7 days
            ]
        );

        // === LOYALTY/FREQUENT CUSTOMER DISCOUNTS ===
        DiscountRule::query()->updateOrCreate(
            ['name' => 'Bulk Order Discount'],
            [
                'discount_type' => DiscountValueType::Percent,
                'discount_value' => 0.12,           // 12% off bulk orders
                'attribute_option_id' => null,
                'comparator' => DiscountComparison::GreaterThanOrEqual,
                'threshold' => '350.000',           // MoneyIntegerCast will convert to minor units
                'user_type' => null,
                'is_active' => false,
                'priority' => 38,
                'stop_further' => false,
                'starts_at' => null,
                'ends_at' => null,
            ]
        );
    }
}
