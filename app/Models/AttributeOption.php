<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class AttributeOption
 *
 * Represents an option for a given attribute (e.g., "Red" for "Color").
 *
 *
 * @property int $id
 * @property int $attribute_id
 * @property string $name
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Attribute $attribute
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AttributeOptionProduct[] $attributeOptionProducts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products
 */
class AttributeOption extends Model
{
    /** @use HasFactory<\Database\Factories\AttributeOptionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<int, string>
     */
    protected $fillable = [
        'attribute_id',
        'name',
        'sort_order',
    ];

    /**
     * Relationship: Get the attribute that owns this option.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(attribute::class);
    }

    /**
     * Relationship: Get the products that have this option.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'attribute_option_product')
            ->using(AttributeOptionProduct::class)
            ->withPivot('price_addition')
            ->withTimestamps();
    }
}
