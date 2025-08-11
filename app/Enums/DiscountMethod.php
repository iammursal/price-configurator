<?php

namespace App\Enums;

enum DiscountMethod: string
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => __('Percentage'),
            self::FixedAmount => __('Fixed Amount'),
        };
    }
}
