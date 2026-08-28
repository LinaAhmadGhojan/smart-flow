<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $fillable = [
        'number', 'date', 'client_name', 'customer_id', 'project_id', 'status', 'comments',
        'currency', 'tax_percent', 'withholding_tax_percent',
        'discount_type', 'discount_value', 'discount_amount',
        'subtotal', 'tax_amount', 'withholding_tax_amount', 'total',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'tax_percent' => 'decimal:2',
        'withholding_tax_percent' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'withholding_tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected $appends = ['invoiced_amount', 'remaining_amount'];

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getInvoicedAmountAttribute(): float
    {
        return (float) $this->invoices()->where('status', '!=', 'cancelled')->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total - $this->invoiced_amount);
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('amount');
        $discountAmount = self::computeGlobalDiscount(
            $subtotal,
            $this->discount_type,
            $this->discount_value !== null ? (float) $this->discount_value : null
        );
        $netSubtotal = max(0, $subtotal - $discountAmount);
        $tax = round($netSubtotal * ((float) $this->tax_percent / 100), 2);
        $withholding = round($netSubtotal * ((float) $this->withholding_tax_percent / 100), 2);
        $total = round($netSubtotal + $tax - $withholding, 2);

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $tax,
            'withholding_tax_amount' => $withholding,
            'total' => $total,
        ]);
    }

    public static function computeGlobalDiscount(float $subtotal, ?string $type, ?float $value): float
    {
        if (!$type || $value === null || $value <= 0 || $subtotal <= 0) {
            return 0;
        }

        if ($type === 'percent') {
            return round(min($subtotal, $subtotal * min(100, $value) / 100), 2);
        }

        if ($type === 'fixed') {
            return round(min($subtotal, $value), 2);
        }

        return 0;
    }

    /**
     * Split a document-level discount across line amounts (after item discounts).
     *
     * @param  array<int, float>  $lineAmounts
     * @return array<int, float>
     */
    public static function allocateGlobalDiscount(float $globalDiscount, array $lineAmounts): array
    {
        $count = count($lineAmounts);
        if ($count === 0 || $globalDiscount <= 0) {
            return array_fill(0, $count, 0.0);
        }

        $subtotal = array_sum($lineAmounts);
        if ($subtotal <= 0) {
            return array_fill(0, $count, 0.0);
        }

        $shares = [];
        $allocated = 0.0;
        foreach ($lineAmounts as $i => $amount) {
            $share = round($globalDiscount * ((float) $amount / $subtotal), 2);
            $shares[$i] = $share;
            $allocated += $share;
        }

        $lastIdx = $count - 1;
        $diff = round($globalDiscount - $allocated, 2);
        if ($lastIdx >= 0 && abs($diff) >= 0.01) {
            $shares[$lastIdx] = round(($shares[$lastIdx] ?? 0) + $diff, 2);
        }

        return $shares;
    }

    public function globalDiscountLabelShort(): string
    {
        if ($this->discount_type === 'percent' && (float) $this->discount_value > 0) {
            return rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.') . '%';
        }

        return '';
    }

    public function globalDiscountLabel(): string
    {
        if (!(float) $this->discount_amount) {
            return '—';
        }

        if ($this->discount_type === 'percent') {
            return rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.') . '%';
        }

        return ($this->currency ?? 'AED') . ' ' . number_format((float) $this->discount_amount, 2);
    }

    /** @return array{gross_subtotal: float, line_discount_total: float, subtotal: float, global_discount: float, net_before_tax: float} */
    public function discountBreakdown(): array
    {
        $lineDiscount = (float) $this->items()->where('is_section', false)->sum('discount_amount');
        $subtotal = (float) $this->subtotal;
        $gross = round($subtotal + $lineDiscount, 2);
        $globalDiscount = (float) ($this->discount_amount ?? 0);

        return [
            'gross_subtotal' => $gross,
            'line_discount_total' => round($lineDiscount, 2),
            'subtotal' => $subtotal,
            'global_discount' => $globalDiscount,
            'net_before_tax' => max(0, round($subtotal - $globalDiscount, 2)),
        ];
    }
}
