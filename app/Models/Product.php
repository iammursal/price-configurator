<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Class Product
 *
 * Represents a product with a base price and configurable options.
 *
 *
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property string $slug SEO-friendly unique identifier
 * @property string|null $description Product description
 * @property \Cknow\Money\Money $base_price Product base price as Money object
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AttributeOption[] $options
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Attribute[] $attributes
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<int, string>
     */
    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'base_price',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price' => MoneyIntegerCast::class.':'.config('app.currency'),
        ];
    }

    /**
     * Relationship: The options related to this product with pivot data (value, price addition).
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'attribute_option_product')
            ->using(AttributeOptionProduct::class)
            ->withPivot('price_addition')
            ->withTimestamps();
    }

    /**
     * Relationship: The attributes related to this product with pivot data (value, price addition).
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_options', 'id', 'attribute_id')
            ->wherePivotIn('id', function ($q) {
                $q->select('attribute_option_id')->from('attribute_option_product')->where('product_id', $this->id);
            });
    }

    public function additionsForOptionIds(array $optionIds): int
    {
        return (int) DB::table('attribute_option_product')
            ->where('product_id', $this->id)
            ->whereIn('attribute_option_id', $optionIds)
            ->sum('price_addition'); // minor units
    }
}
