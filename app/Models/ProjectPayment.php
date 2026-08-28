<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPayment extends Model
{
    public const TYPES = ['cash', 'bank', 'card', 'transfer', 'cheque', 'other'];

    protected $fillable = [
        'project_id', 'invoice_id', 'amount', 'payment_type', 'paid_at', 'receipt_path', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date:Y-m-d',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
