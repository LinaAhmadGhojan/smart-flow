<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'number', 'quotation_id', 'project_id', 'date', 'client_name', 'status', 'notes',
        'currency', 'amount', 'percent', 'tax_percent', 'tax_amount', 'total',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'amount' => 'decimal:2',
        'percent' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectPayment::class)->orderByDesc('paid_at')->orderByDesc('id');
    }
}
