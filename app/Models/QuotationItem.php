<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'product_id', 'is_section', 'code', 'description', 'quantity', 'rate',
        'discount_type', 'discount_value', 'discount_amount', 'amount', 'sort_order',
    ];

    protected $casts = [
        'is_section' => 'boolean',
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /** @return array{line_subtotal: float, discount_amount: float, amount: float} */
    public static function computeAmount(float $qty, float $rate, ?string $discountType, ?float $discountValue): array
    {
        $lineSubtotal = round($qty * $rate, 2);
        $discountAmount = 0.0;

        if ($discountType === 'percent' && $discountValue > 0) {
            $discountAmount = round($lineSubtotal * min(100.0, $discountValue) / 100, 2);
        } elseif ($discountType === 'fixed' && $discountValue > 0) {
            $discountAmount = round(min($lineSubtotal, $discountValue), 2);
        }

        return [
            'line_subtotal' => $lineSubtotal,
            'discount_amount' => $discountAmount,
            'amount' => max(0, round($lineSubtotal - $discountAmount, 2)),
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function discountLabel(string $currency = 'AED'): string
    {
        if ($this->is_section || !(float) $this->discount_amount) {
            return '—';
        }
        if ($this->discount_type === 'percent') {
            $pct = rtrim(rtrim(number_format((float) $this->discount_value, 2, '.', ''), '0'), '.');

            return '-' . $pct . '%';
        }

        return '-' . $currency . ' ' . number_format((float) $this->discount_amount, 2);
    }
}
