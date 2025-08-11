<?php

namespace App\Services\Contracts;

use App\Enums\UserType;

interface DiscountServiceInterface
{
    /**
     * Calculate discounts for cart items
     */
    public function calculateDiscounts(array $cartItems, UserType $userType = UserType::Normal): array;

    /**
     * Clear discount rules cache
     */
    public function clearCache(): void;
}
