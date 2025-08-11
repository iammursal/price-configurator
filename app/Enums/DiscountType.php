<?php

namespace App\Enums;

enum DiscountType: string
{
    case AttributeBased = 'attribute_based';
    case TotalBased = 'total_based';
    case UserTypeBased = 'user_type_based';

    public function label(): string
    {
        return match ($this) {
            self::AttributeBased => __('Attribute Based'),
            self::TotalBased => __('Total Based'),
            self::UserTypeBased => __('User Type Based'),
        };
    }
}
