<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'brand',
        'price',
        'price_number',
        'image',
        'in_stock',
        'is_visible',
        'category_id',
        'group_id',
        'features',
        'whatsapp_message',
        'description',
        'description_ar',
    ];

    protected $casts = [
        'in_stock' => 'boolean',
        'is_visible' => 'boolean',
        'features' => 'array',
        'price_number' => 'decimal:2',
    ];

    /** Visible to website visitors (not admin-only). */
    public function scopeVisibleToPublic($query)
    {
        return $query->where('is_visible', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'group_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
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
