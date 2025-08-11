<?php

namespace App\Enums;

enum DiscountComparison: string
{
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';
    case Equal = '=';
    case LessThanOrEqual = '<=';
    case LessThan = '<';
    case NotEqual = '!=';
    case NotEqualAlt = '<>';

    public function label(): string
    {
        return match ($this) {
            self::GreaterThan => __('Greater Than'),
            self::GreaterThanOrEqual => __('Greater Than or Equal'),
            self::Equal => __('Equal'),
            self::LessThanOrEqual => __('Less Than or Equal'),
            self::LessThan => __('Less Than'),
            self::NotEqual => __('Not Equal'),
            self::NotEqualAlt => __('Not Equal'),
        };
    }

    public function evaluate(int $value, int $threshold): bool
    {
        return match ($this) {
            self::GreaterThan => $value > $threshold,
            self::GreaterThanOrEqual => $value >= $threshold,
            self::Equal => $value == $threshold,
            self::LessThanOrEqual => $value <= $threshold,
            self::LessThan => $value < $threshold,
            self::NotEqual => $value != $threshold,
            self::NotEqualAlt => $value != $threshold,
        };
    }

    /**
     * Get all comparison operators for form selections.
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
