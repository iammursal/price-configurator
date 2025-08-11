<?php

namespace App\Enums;

enum UserType: string
{
    case Normal = 'normal';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::Normal => __('Individual Customer'),
            self::Company => __('Company Customer'),
        };
    }

    /**
     * Get all user types for form selections.
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
