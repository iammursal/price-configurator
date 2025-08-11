<?php

namespace App\Enums;

enum DiscountValueType: string
{
    case Amount = 'amount';
    case Percent = 'percent';

    public function label(): string
    {
        return match ($this) {
            self::Amount => __('Fixed Amount'),
            self::Percent => __('Percentage'),
        };
    }

    /**
     * Get all discount value types for form selections.
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
