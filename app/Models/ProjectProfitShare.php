<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProfitShare extends Model
{
    /** percent = نسبة من الربح | capital = رأس مال (حصته من الربح حسب نسبة رأس المال) */
    public const TYPES = ['percent', 'capital'];

    protected $fillable = [
        'project_id', 'name', 'share_type', 'value', 'notes', 'sort_order',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function isCapital(): bool
    {
        // legacy "fixed" treated as capital
        return in_array($this->share_type, ['capital', 'fixed'], true);
    }

    public function isPercent(): bool
    {
        return $this->share_type === 'percent';
    }

    /**
     * @param  float  $profit  Project profit (contract − expenses)
     * @param  float  $projectCapital  رأس مال المشروع الكلي (مثلاً 20000)
     */
    public function calculatedAmount(float $profit, float $projectCapital = 0): float
    {
        $profit = max(0, $profit);

        if ($this->isPercent()) {
            return round($profit * ((float) $this->value / 100), 2);
        }

        if ($this->isCapital()) {
            if ($projectCapital <= 0) {
                return 0.0;
            }

            return round($profit * ((float) $this->value / $projectCapital), 2);
        }

        return 0.0;
    }

    /** Effective % of project capital (for capital type). */
    public function capitalPercent(float $projectCapital): ?float
    {
        if (! $this->isCapital() || $projectCapital <= 0) {
            return null;
        }

        return round(((float) $this->value / $projectCapital) * 100, 2);
    }
}
