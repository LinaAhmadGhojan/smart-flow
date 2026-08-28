<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductGroup extends Model
{
    use HasFactory;

    protected $table = 'product_groups';

    protected $fillable = [
        'name',
        'name_ar',
        'image',
        'description',
        'description_ar',
        'sort_order',
    ];

    protected $appends = [
        'products_count',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'group_id');
    }

    public function getProductsCountAttribute(): int
    {
        if (array_key_exists('products_count', $this->attributes)) {
            return (int) $this->attributes['products_count'];
        }

        return $this->products()->count();
    }

    public function getImageAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = preg_replace('#^/public/storage/#', '/storage/', $value) ?? $value;
        $value = preg_replace('#^/uploads/#', '/storage/', $value) ?? $value;

        return $value;
    }
}
