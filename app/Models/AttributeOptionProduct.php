<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class Attribute
 *
 * Represents a configurable attribute for products (e.g., Delivery Method, Speed).
 *
 *
 * @property int $id
 * @property int $attribute_id
 * @property int $option_id
 * @property int $product_id
 * @property float $price_addition
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Attribute $attribute
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AttributeOption $option
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product $product
 */
class AttributeOptionProduct extends Pivot
{
    /** @use HasFactory<\Database\Factories\AttributeOptionProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<int, string>
     */
    protected $fillable = [
        'attribute_id',
        'option_id',
        'product_id',
        'price_addition',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_addition' => MoneyIntegerCast::class.':'.config('app.currency'),
        ];
    }

    /**
     * Relationship: Get the attribute that this option belongs to.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Relationship: Get the option that this attribute option belongs to.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(AttributeOption::class);
    }

    /**
     * Relationship: Products associated with this attribute with pivot data.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
