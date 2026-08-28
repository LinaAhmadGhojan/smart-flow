<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectExpense extends Model
{
    public const PRESETS = [
        'وقود',
        'عمال',
        'كاميرات',
        'أسلاك وكابلات',
        'نقل وتوصيل',
        'مواد كهربائية',
        'أدوات',
        'إيجار معدات',
        'أخرى',
    ];

    protected $fillable = [
        'project_id', 'name', 'amount', 'spent_at', 'notes', 'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent_at' => 'date:Y-m-d',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
