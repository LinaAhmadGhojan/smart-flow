<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft',
        'in_progress',
        'on_hold',
        'completed',
        'cancelled',
    ];

    protected $fillable = [
        'customer_id',
        'title',
        'title_ar',
        'description',
        'description_ar',
        'location',
        'maps_url',
        'status',
        'is_public',
        'qr_path',
        'completed_at',
        'capital_amount',
        'media_type',
        'media_url',
        'order',
        'is_featured',
        'images',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'order' => 'integer',
        'images' => 'array',
        'completed_at' => 'datetime',
        'capital_amount' => 'decimal:2',
    ];

    protected $appends = ['is_locked'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ProjectContact::class)->orderBy('sort_order')->orderBy('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->orderBy('sort_order')->orderBy('id');
    }

    public function publicFiles(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->where('visibility', 'public')->orderBy('sort_order')->orderBy('id');
    }

    public function privateFiles(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->where('visibility', 'private')->orderBy('sort_order')->orderBy('id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ProjectPayment::class)->orderByDesc('paid_at')->orderByDesc('id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class)->orderByDesc('spent_at')->orderByDesc('id');
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(ProjectDeliveryNote::class)->orderByDesc('delivered_at')->orderByDesc('id');
    }

    public function profitShares(): HasMany
    {
        return $this->hasMany(ProjectProfitShare::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Contract value = sum of non-cancelled invoices (or accepted quotation total as fallback). */
    public function getContractValueAttribute(): float
    {
        $invoiced = (float) $this->invoices()->where('status', '!=', 'cancelled')->sum('total');
        if ($invoiced > 0) {
            return $invoiced;
        }

        return (float) $this->quotations()
            ->whereIn('status', ['accepted', 'sent', 'draft'])
            ->orderByDesc('date')
            ->value('total') ?: 0;
    }

    public function getFinanceSummaryAttribute(): array
    {
        $contract = $this->contract_value;
        $expenses = (float) $this->expenses()->sum('amount');
        $payments = (float) $this->payments()->sum('amount');
        $profit = round($contract - $expenses, 2);

        // رأس مال المشروع = قيمة الفاتورة (العقد) — ما يحتاج إدخال يدوي
        $projectCapital = $contract;

        $shares = $this->profitShares()->get();
        $partnersCapital = (float) $shares
            ->filter(fn (ProjectProfitShare $s) => $s->isCapital())
            ->sum(fn (ProjectProfitShare $s) => (float) $s->value);

        $sharesTotal = 0.0;
        $shareRows = [];
        foreach ($shares as $share) {
            $amount = $share->calculatedAmount($profit, $projectCapital);
            $sharesTotal += $amount;
            $capitalPct = $share->capitalPercent($projectCapital);
            $shareRows[] = [
                'id' => $share->id,
                'name' => $share->name,
                'share_type' => $share->isCapital() ? 'capital' : $share->share_type,
                'value' => (float) $share->value,
                'notes' => $share->notes,
                'capital_percent' => $capitalPct,
                'calculated_amount' => $amount,
            ];
        }

        return [
            'contract_value' => round($contract, 2),
            'expenses_total' => round($expenses, 2),
            'profit' => $profit,
            'payments_total' => round($payments, 2),
            'balance_due' => round(max(0, $contract - $payments), 2),
            'capital_total' => round($projectCapital, 2),
            'partners_capital' => round($partnersCapital, 2),
            'shares_total' => round($sharesTotal, 2),
            'company_profit' => round($profit - $sharesTotal, 2),
            'shares' => $shareRows,
        ];
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getMediaUrlAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = preg_replace('#^/public/storage/#', '/storage/', $value) ?? $value;
        $value = preg_replace('#^/uploads/#', '/storage/', $value) ?? $value;

        return $value;
    }
}
