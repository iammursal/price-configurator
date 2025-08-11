<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Attribute
 *
 * Represents a configurable attribute for products (e.g., Delivery Method, Speed).
 *
 *
 * @property int $id
 * @property string $name
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AttributeOption[] $options
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AttributeOptionProduct[] $attributeOptionProducts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products
 */
class Attribute extends Model
{
    /** @use HasFactory<\Database\Factories\AttributeFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<int, string>
     */
    protected $fillable = [
        'name',
        'sort_order',
    ];

    /**
     * Relationship: Get the options associated with this attribute.
     */
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }

    /**
     * Relationship: Get the attribute option products associated with this attribute.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, AttributeOptionProduct::class);
    }
}
