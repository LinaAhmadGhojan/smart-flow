<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'discount_percentage',
        'discount_price',
        'offer_description',
        'offer_description_ar',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount_percentage' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

