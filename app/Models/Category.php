<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'icon',
        'description',
        'description_ar',
    ];

    protected $appends = [
        'products_count',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getProductsCountAttribute(): int
    {
        if (array_key_exists('products_count', $this->attributes)) {
            return (int) $this->attributes['products_count'];
        }

        return $this->products()->count();
    }
}
