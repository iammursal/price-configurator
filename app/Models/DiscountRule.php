<?php

namespace App\Models;

use App\Enums\DiscountComparison;
use App\Enums\DiscountMethod;
use App\Enums\DiscountType;
use App\Enums\DiscountValueType;
use App\Enums\UserType;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class DiscountRule
 *
 * Defines a discount rule which can be attribute-based, total-based, or user-type based.
 *
 *
 * @property int $id
 * @property string $name
 * @property DiscountType $discount_type Enum of discount type
 * @property DiscountMethod $discount_method Enum of discount application method
 * @property DiscountValueType $discount_value_type Enum of discount value type (amount or percent)
 * @property float $discount_value Discount value as decimal (0.05 = 5% or 1.500 = 1.500 KWD)
 * @property int|null $attribute_option_id
 * @property DiscountComparison|null $comparator
 * @property \Cknow\Money\Money|null $threshold Threshold value as Money object
 * @property UserType|null $user_type
 * @property bool $is_active
 * @property int $priority
 * @property bool $stop_further
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Product[] $products
 */
class DiscountRule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<int, string>
     */
    protected $fillable = [
        'name',
        'discount_type',
        'discount_value',
        'attribute_option_id',
        'comparator',
        'threshold',
        'user_type',
        'is_active',
        'priority',
        'stop_further',
        'starts_at',
        'ends_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'stop_further' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'discount_value' => 'decimal:3',
            'threshold' => MoneyIntegerCast::class.':'.config('app.currency'),
            'comparator' => DiscountComparison::class,
            'discount_type' => DiscountValueType::class,
            'user_type' => UserType::class,
        ];
    }

    /**
     * Get the discount value as a percentage (e.g., 0.05 returns 5.0).
     * Use this when discount_type is DiscountValueType::Percent.
     */
    public function getDiscountPercentageAttribute(): float
    {
        if ($this->discount_type === DiscountValueType::Percent) {
            return $this->discount_value * 100;
        }

        return 0.0;
    }

    /**
     * Get the discount value as a Money object.
     * Use this when discount_type is DiscountValueType::Amount.
     */
    public function getDiscountAmountAttribute(): Money
    {
        if ($this->discount_type === DiscountValueType::Amount) {
            return money($this->discount_value);
        }

        return money(0);
    }

    /**
     * Check if this discount rule applies a percentage discount.
     */
    public function isPercentageDiscount(): bool
    {
        return $this->discount_type === DiscountValueType::Percent;
    }

    /**
     * Check if this discount rule applies a fixed amount discount.
     */
    public function isAmountDiscount(): bool
    {
        return $this->discount_type === DiscountValueType::Amount;
    }
}
