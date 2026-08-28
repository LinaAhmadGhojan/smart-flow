<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDeliveryNote extends Model
{
    protected $fillable = [
        'project_id', 'number', 'delivered_at', 'title', 'notes', 'received_by', 'delivered_by', 'items',
    ];

    protected $casts = [
        'delivered_at' => 'date:Y-m-d',
        'items' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
