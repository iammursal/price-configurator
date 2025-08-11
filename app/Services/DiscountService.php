<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DiscountValueType;
use App\Enums\UserType;
use App\Events\DiscountApplied;
use App\Models\DiscountRule;
use App\Services\Contracts\DiscountServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced Discount Service with caching and comprehensive error handling.
 *
 * This service calculates discount applications on cart items with support for:
 * - Attribute-based discounts (specific product options like "Red Color", "Large Size")
 * - Threshold-based discounts (minimum order value requirements)
 * - User-type-based discounts (different pricing for individual vs company customers)
 * - Priority ordering and cascading discount rules
 * - Stop-further mechanism to halt additional discount processing
 *
 * Key Features:
 * - Sequential discount processing (each discount applies to the running total)
 * - Comprehensive caching for performance optimization
 * - Robust error handling with graceful fallbacks
 * - Event-driven architecture for extensibility
 * - All calculations use minor currency units (fils) to avoid floating-point precision issues
 *
 * @author Price Configurator Team
 *
 * @version 2.0.0
 *
 * @since 1.0.0
 */
class DiscountService implements DiscountServiceInterface
{
    /** @var int Cache TTL in seconds (5 minutes) */
    private const CACHE_TTL = 300;

    /** @var string Cache key for active discount rules */
    private const CACHE_KEY = 'active_discount_rules';

    /**
     * Calculate and apply all qualifying discounts to cart items.
     *
     * This method processes discounts in priority order, applying each qualifying
     * discount to the running total (cascading effect). Each discount is evaluated
     * against the current cart state and customer type.
     *
     * @param  array<int, array<string, mixed>>  $cartItems  Cart items with structure:
     *                                                       [
     *                                                       'product_id' => int,
     *                                                       'qty' => int,
     *                                                       'amount' => int (line total in minor units),
     *                                                       'selections' => array<int, int> (attribute_id => option_id)
     *                                                       ]
     * @param  UserType  $userType  Customer type affecting discount eligibility
     * @return array<string, mixed> Discount calculation result:
     *                              [
     *                              'rules' => array<int, array<string, mixed>>, // Applied discount rules
     *                              'discounts' => array<int, array<string, mixed>>, // Alias for 'rules'
     *                              'total_discount' => int, // Total discount amount in minor units
     *                              'subtotal' => int, // Original subtotal in minor units
     *                              'final_total' => int, // Final total after discounts in minor units
     *                              'cascading_steps' => array<int, array<string, mixed>>, // Step-by-step breakdown
     *                              'error' => string|null // Error message if calculation failed
     *                              ]
     */
    public function calculateDiscounts(array $cartItems, UserType $userType = UserType::Normal): array
    {
        try {
            // Calculate initial totals and gather required data
            $originalSubtotal = $this->calculateSubtotal($cartItems);
            $currentTotal = $originalSubtotal;
            $selectedOptionIds = $this->getSelectedOptionIds($cartItems);
            $rules = $this->activeRules();

            $applied = [];

            // Process each discount rule in priority order
            foreach ($rules as $rule) {
                // Stop processing if cart total is already zero or negative
                if ($currentTotal <= 0) {
                    break;
                }

                // Check if this rule qualifies for the current cart state
                if (! $this->qualifies($rule, $currentTotal, $userType, $selectedOptionIds)) {
                    continue;
                }

                // Calculate the discount amount for this rule
                $amount = $this->discountAmount($rule, $currentTotal);

                // Skip rules that produce no discount (shouldn't happen but safety check)
                if ($amount <= 0) {
                    Log::warning('Discount rule produced zero amount', [
                        'rule_id' => $rule->id,
                        'rule_name' => $rule->name,
                        'current_total' => $currentTotal,
                        'rule_type' => $rule->discount_type->value,
                        'rule_value' => $rule->discount_value,
                    ]);

                    continue;
                }

                // Ensure we never discount more than the remaining total
                $amount = min($amount, $currentTotal);

                // Record this discount application
                $applied[] = $this->makeEntry($rule, $amount, $currentTotal);

                // Apply the discount to running total (cascading effect)
                $currentTotal -= $amount;

                // Dispatch event for tracking/analytics
                event(new DiscountApplied($rule, $amount, $currentTotal));

                // Stop processing further rules if this rule has stop_further flag
                if ($rule->stop_further) {
                    Log::info('Discount processing stopped due to stop_further rule', [
                        'rule_id' => $rule->id,
                        'rule_name' => $rule->name,
                    ]);
                    break;
                }
            }

            return $this->buildResponse($applied, $originalSubtotal, $currentTotal);

        } catch (\Exception $e) {
            // Log the error with full context for debugging
            Log::error('Discount calculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cart_items' => $cartItems,
                'user_type' => $userType->value,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return safe fallback to prevent application crashes
            $fallbackSubtotal = $this->calculateSubtotal($cartItems);

            return [
                'rules' => [],
                'discounts' => [],
                'total_discount' => 0,
                'subtotal' => $fallbackSubtotal,
                'final_total' => $fallbackSubtotal,
                'cascading_steps' => [],
                'error' => 'Discount calculation failed. Please try again.',
            ];
        }
    }

    /**
     * Retrieve all active discount rules with caching.
     *
     * This method fetches discount rules that are:
     * - Currently active (is_active = true)
     * - Within their validity period (starts_at/ends_at)
     * - Ordered by priority (lower numbers = higher priority)
     *
     * Results are cached for performance optimization.
     *
     * @return Collection<int, DiscountRule> Active discount rules ordered by priority
     */
    private function activeRules(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            Log::debug('Fetching active discount rules from database');

            return DiscountRule::where('is_active', true)
                // Rule is either always active (no start date) OR has started
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                // Rule is either always active (no end date) OR has not ended
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                // Order by priority (lower = higher priority), then by ID for consistency
                ->orderBy('priority')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * Clear the discount rules cache.
     *
     * This should be called when discount rules are modified to ensure
     * the latest rules are used in calculations.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Log::info('Discount rules cache cleared');
    }

    /**
     * Check if a discount rule qualifies for the current cart state.
     *
     * A rule qualifies if all of its conditions are met:
     * - Attribute condition: Required option is selected (for attribute-based rules)
     * - Threshold condition: Cart total meets comparison criteria (for threshold-based rules)
     * - User type condition: Customer type matches requirement (for user-type-based rules)
     *
     * @param  DiscountRule  $rule  The discount rule to evaluate
     * @param  int  $currentTotal  Current cart total in minor units
     * @param  UserType  $userType  Customer type
     * @param  array<int>  $selectedOptionIds  All selected attribute option IDs across cart items
     * @return bool True if the rule qualifies, false otherwise
     */
    private function qualifies(DiscountRule $rule, int $currentTotal, UserType $userType, array $selectedOptionIds): bool
    {
        // Attribute-based rule: must have the required option selected
        if ($rule->attribute_option_id !== null && ! in_array($rule->attribute_option_id, $selectedOptionIds, true)) {
            Log::debug('Rule disqualified: required attribute option not selected', [
                'rule_id' => $rule->id,
                'required_option_id' => $rule->attribute_option_id,
                'selected_option_ids' => $selectedOptionIds,
            ]);

            return false;
        }

        // Threshold-based rule: current total must meet comparison criteria
        if ($rule->threshold !== null && $rule->comparator !== null) {
            $thresholdMinor = (int) $rule->threshold->getAmount();

            if (! $rule->comparator->evaluate($currentTotal, $thresholdMinor)) {
                Log::debug('Rule disqualified: threshold not met', [
                    'rule_id' => $rule->id,
                    'current_total' => $currentTotal,
                    'threshold' => $thresholdMinor,
                    'comparator' => $rule->comparator->value,
                ]);

                return false;
            }
        }

        // User-type-based rule: customer type must match
        if ($rule->user_type !== null && $rule->user_type !== $userType) {
            Log::debug('Rule disqualified: user type mismatch', [
                'rule_id' => $rule->id,
                'required_user_type' => $rule->user_type->value,
                'actual_user_type' => $userType->value,
            ]);

            return false;
        }

        Log::debug('Rule qualified for application', [
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
        ]);

        return true;
    }

    /**
     * Calculate the discount amount based on rule type and current total.
     *
     * Supports two discount types:
     * - Percentage discounts: applies percentage to current total
     * - Fixed amount discounts: applies fixed amount (but never more than current total)
     *
     * @param  DiscountRule  $rule  The discount rule to apply
     * @param  int  $currentTotal  Current cart total in minor units
     * @return int Discount amount in minor units
     */
    private function discountAmount(DiscountRule $rule, int $currentTotal): int
    {
        if ($rule->discount_type === DiscountValueType::Percent) {
            // For percentage discounts, rule->discount_value is a decimal (e.g., 0.20 for 20%)
            $discountAmount = (int) round($currentTotal * (float) $rule->discount_value);

            Log::debug('Calculated percentage discount', [
                'rule_id' => $rule->id,
                'percentage' => $rule->discount_value * 100,
                'applied_to' => $currentTotal,
                'discount_amount' => $discountAmount,
            ]);

            return $discountAmount;
        }

        if ($rule->discount_type === DiscountValueType::Amount) {
            // For fixed amount discounts, convert from major currency units to minor units
            $minor = (int) money((string) $rule->discount_value)->getAmount();
            $discountAmount = min($minor, $currentTotal); // Never discount more than available

            Log::debug('Calculated fixed amount discount', [
                'rule_id' => $rule->id,
                'fixed_amount' => $minor,
                'applied_to' => $currentTotal,
                'discount_amount' => $discountAmount,
            ]);

            return $discountAmount;
        }

        // Unknown discount type - should not happen
        Log::warning('Unknown discount type encountered', [
            'rule_id' => $rule->id,
            'discount_type' => $rule->discount_type,
        ]);

        return 0;
    }

    /**
     * Create a discount entry for the response array.
     *
     * @param  DiscountRule  $rule  The discount rule that was applied
     * @param  int  $amount  The discount amount in minor units
     * @param  int  $appliedTo  The total the discount was applied to
     * @return array<string, mixed> Discount entry
     */
    private function makeEntry(DiscountRule $rule, int $amount, int $appliedTo): array
    {
        return [
            'rule' => $rule,
            'amount' => $amount,
            'applied_to' => $appliedTo,
        ];
    }

    /**
     * Build the final response array with all discount calculations.
     *
     * Includes:
     * - Applied discount rules with details
     * - Totals and subtotals
     * - Cascading steps for debugging/UX display
     *
     * @param  array<int, array<string, mixed>>  $appliedDiscounts  Applied discount entries
     * @param  int  $originalSubtotal  Original cart subtotal before discounts
     * @param  int  $currentTotal  Final total after all discounts
     * @return array<string, mixed> Complete discount calculation result
     */
    private function buildResponse(array $appliedDiscounts, int $originalSubtotal, int $currentTotal): array
    {
        $totalDiscount = array_sum(array_column($appliedDiscounts, 'amount'));

        Log::info('Discount calculation completed', [
            'original_subtotal' => $originalSubtotal,
            'total_discount' => $totalDiscount,
            'final_total' => $currentTotal,
            'rules_applied' => count($appliedDiscounts),
        ]);

        return [
            'rules' => $appliedDiscounts,
            'discounts' => $appliedDiscounts, // Alias for backward compatibility
            'total_discount' => $totalDiscount,
            'subtotal' => $originalSubtotal,
            'final_total' => max(0, $currentTotal), // Ensure never negative
            'cascading_steps' => $this->buildCascadingSteps($appliedDiscounts, $originalSubtotal),
        ];
    }

    /**
     * Build step-by-step discount application for debugging or UX display.
     *
     * Shows how each discount was applied sequentially to create a cascading effect.
     * Useful for displaying discount breakdown to customers or debugging discount logic.
     *
     * @param  array<int, array<string, mixed>>  $appliedDiscounts  Applied discount entries
     * @param  int  $originalSubtotal  Original cart subtotal
     * @return array<int, array<string, mixed>> Step-by-step breakdown
     */
    private function buildCascadingSteps(array $appliedDiscounts, int $originalSubtotal): array
    {
        $steps = [];
        $running = $originalSubtotal;

        // Initial state (before any discounts)
        $steps[] = [
            'step' => 0,
            'description' => 'Original Subtotal',
            'amount' => $running,
            'discount' => 0,
        ];

        // Each discount application step
        foreach ($appliedDiscounts as $i => $discount) {
            $running -= $discount['amount'];

            // Add helpful label based on rule type
            $label = $discount['rule']->attribute_option_id ? '(Attribute-based)' : '(Order-level)';

            $steps[] = [
                'step' => $i + 1,
                'description' => "After: {$discount['rule']->name} {$label}",
                'amount' => $running,
                'discount' => $discount['amount'],
                'applied_to' => $discount['applied_to'],
            ];
        }

        return $steps;
    }

    /**
     * Calculate cart subtotal from line item amounts.
     *
     * Expects each cart item to have an 'amount' field containing the line total
     * (quantity × unit price + option additions) in minor currency units.
     *
     * @param  array<int, array<string, mixed>>  $cartItems  Cart items with 'amount' field
     * @return int Total cart amount in minor units
     */
    private function calculateSubtotal(array $cartItems): int
    {
        $total = 0;

        foreach ($cartItems as $item) {
            // Each item should have a pre-calculated line total
            $lineTotal = $item['amount'] ?? 0;
            $total += $lineTotal;
        }

        Log::debug('Cart subtotal calculated', [
            'item_count' => count($cartItems),
            'subtotal' => $total,
        ]);

        return $total;
    }

    /**
     * Extract all selected attribute option IDs from cart items.
     *
     * Used for determining eligibility of attribute-based discount rules.
     * Flattens all selections across all cart items into a single array.
     *
     * @param  array<int, array<string, mixed>>  $cartItems  Cart items with 'selections' field
     * @return array<int> Unique array of selected attribute option IDs
     */
    private function getSelectedOptionIds(array $cartItems): array
    {
        $optionIds = [];

        foreach ($cartItems as $item) {
            if (isset($item['selections']) && is_array($item['selections'])) {
                // selections format: [attribute_id => option_id]
                // We only need the option IDs for discount rule matching
                $optionIds = array_merge($optionIds, array_values($item['selections']));
            }
        }

        $uniqueOptionIds = array_unique($optionIds);

        Log::debug('Selected option IDs extracted', [
            'total_selections' => count($optionIds),
            'unique_selections' => count($uniqueOptionIds),
            'option_ids' => $uniqueOptionIds,
        ]);

        return $uniqueOptionIds;
    }
}
