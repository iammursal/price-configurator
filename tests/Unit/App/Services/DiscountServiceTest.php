<?php

namespace Tests\Unit\App\Services;

use App\Enums\DiscountComparison;
use App\Enums\DiscountValueType;
use App\Enums\UserType;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\DiscountRule;
use App\Services\DiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_applies_discounts_sequentially_to_running_total(): void
    {
        // Arrange: two active rules ordered by priority
        DiscountRule::create([
            'name' => 'Flash Sale 20%',
            'discount_type' => DiscountValueType::Percent,
            'discount_value' => 0.20, // 20%
            'is_active' => true,
            'priority' => 1,
            'stop_further' => false,
        ]);

        DiscountRule::create([
            'name' => 'Fixed 10 KWD',
            'discount_type' => DiscountValueType::Amount,
            'discount_value' => 10.000, // KWD
            'is_active' => true,
            'priority' => 2,
            'stop_further' => false,
        ]);

        $svc = new DiscountService;

        // 100 KWD in minor units (fils)
        $cart = [
            [
                'product_id' => 1,
                'qty' => 1,
                'selections' => [],
                'amount' => 100_000,
            ],
        ];

        // Act
        $res = $svc->calculateDiscounts($cart, UserType::Normal);

        // Assert
        $this->assertSame(100_000, $res['subtotal'], 'Subtotal should be 100 KWD in minor units');
        $this->assertCount(2, $res['rules'], 'Two discounts should apply');

        // First: 20% of 100_000 => 20_000; applied_to should be original 100_000
        $this->assertSame(100_000, $res['rules'][0]['applied_to']);
        $this->assertSame(20_000, $res['rules'][0]['amount']);

        // Second: 10 KWD (10_000) applied to the new running total (80_000)
        $this->assertSame(80_000, $res['rules'][1]['applied_to']);
        $this->assertSame(10_000, $res['rules'][1]['amount']);

        // Totals
        $this->assertSame(30_000, $res['total_discount']);
        $this->assertSame(70_000, $res['final_total']);
    }

    public function test_subtotal_calculation_with_amount_field(): void
    {
        $svc = new DiscountService;

        $cart = [
            // Line with precomputed line total in 'amount' field
            [
                'product_id' => 1,
                'qty' => 1,
                'selections' => [],
                'amount' => 50_000, // 50 KWD line total
            ],
            // Another line with different amount
            [
                'product_id' => 2,
                'qty' => 3,
                'selections' => [],
                'amount' => 60_000, // 60 KWD line total (already qty-multiplied)
            ],
        ];

        $res = $svc->calculateDiscounts($cart, UserType::Normal);

        // 50_000 + 60_000 = 110_000
        $this->assertSame(110_000, $res['subtotal']);
        $this->assertSame(110_000, $res['final_total']); // no active rules in DB
        $this->assertSame(0, $res['total_discount']);
    }

    public function test_basic_subtotal_calculation()
    {
        $cartItems = [
            [
                'product_id' => 1,
                'qty' => 2,
                'amount' => 5000, // 5.000 KD in fils (line total)
                'selections' => [1 => 10],
            ],
            [
                'product_id' => 2,
                'qty' => 1,
                'amount' => 3000, // 3.000 KD in fils (line total)
                'selections' => [2 => 20],
            ],
        ];

        $service = new DiscountService;
        $result = $service->calculateDiscounts($cartItems, UserType::Normal);

        $this->assertEquals(8000, $result['subtotal']); // 8.000 KD
        $this->assertEquals(8000, $result['final_total']); // No discounts
    }

    public function test_threshold_based_percentage_discount()
    {
        // Create a discount rule for orders >= 10.000 KD (10000 fils)
        $rule = DiscountRule::factory()->create([
            'name' => 'Bulk Order Discount',
            'discount_type' => DiscountValueType::Percent,
            'discount_value' => 0.10, // 10%
            'attribute_option_id' => null,
            'comparator' => DiscountComparison::GreaterThanOrEqual,
            'threshold' => 10000, // 10.000 KD in fils - MoneyIntegerCast will handle this
            'user_type' => UserType::Normal,
            'is_active' => true,
            'priority' => 1,
            'stop_further' => false,
        ]);

        $cartItems = [
            [
                'product_id' => 1,
                'qty' => 1,
                'amount' => 12000, // 12.000 KD
                'selections' => [],
            ],
        ];

        $service = new DiscountService;
        $result = $service->calculateDiscounts($cartItems, UserType::Normal);

        $this->assertEquals(12000, $result['subtotal']);
        $this->assertEquals(1200, $result['total_discount']); // 10% of 12000
        $this->assertEquals(10800, $result['final_total']); // 12000 - 1200
        $this->assertCount(1, $result['rules']);
        $this->assertEquals($rule->id, $result['rules'][0]['rule']->id);
    }

    public function test_attribute_based_discount()
    {
        // Create the required attribute and option first
        $attribute = Attribute::create([
            'name' => 'Color',
            'description' => 'Product color',
            'sort_order' => 1,
        ]);

        $colorOption = AttributeOption::create([
            'attribute_id' => $attribute->id,
            'name' => 'Red',
            'description' => 'Red color',
            'sort_order' => 1,
        ]);

        // Create an attribute-based discount rule
        $rule = DiscountRule::factory()->create([
            'name' => 'Red Color Special',
            'discount_type' => DiscountValueType::Amount,
            'discount_value' => 2.500, // 2.500 KWD off
            'attribute_option_id' => $colorOption->id, // Use the created option ID
            'is_active' => true,
            'priority' => 1,
        ]);

        $cartItems = [
            [
                'product_id' => 1,
                'qty' => 1,
                'amount' => 10000, // 10.000 KD
                'selections' => [$attribute->id => $colorOption->id], // Red color selected
            ],
        ];

        $service = new DiscountService;
        $result = $service->calculateDiscounts($cartItems, UserType::Normal);

        $this->assertEquals(10000, $result['subtotal']);
        $this->assertEquals(2500, $result['total_discount']); // 2.500 KWD
        $this->assertEquals(7500, $result['final_total']); // 10000 - 2500
        $this->assertCount(1, $result['rules']);
        $this->assertEquals($rule->id, $result['rules'][0]['rule']->id);
    }

    public function test_user_type_based_discount()
    {
        // Create a company discount rule
        $rule = DiscountRule::factory()->create([
            'name' => 'Company Customer Discount',
            'discount_type' => DiscountValueType::Percent,
            'discount_value' => 0.15, // 15% off
            'attribute_option_id' => null, // Not attribute-based
            'comparator' => null, // Not threshold-based
            'threshold' => null, // Not threshold-based
            'user_type' => UserType::Company,
            'is_active' => true,
            'priority' => 1,
            'stop_further' => false,
        ]);

        $cartItems = [
            [
                'product_id' => 1,
                'qty' => 1,
                'amount' => 20000, // 20.000 KD
                'selections' => [],
            ],
        ];

        $service = new DiscountService;

        // Test with company user type
        $result = $service->calculateDiscounts($cartItems, UserType::Company);
        $this->assertEquals(3000, $result['total_discount']); // 15% of 20000
        $this->assertEquals(17000, $result['final_total']);
        $this->assertCount(1, $result['rules']);
        $this->assertEquals($rule->id, $result['rules'][0]['rule']->id);

        // Test with normal user type (should not apply)
        $result = $service->calculateDiscounts($cartItems, UserType::Normal);
        $this->assertEquals(0, $result['total_discount']);
        $this->assertEquals(20000, $result['final_total']);
        $this->assertCount(0, $result['rules']);
    }
}
