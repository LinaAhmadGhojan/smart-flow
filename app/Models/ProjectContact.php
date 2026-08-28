<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectContact extends Model
{
    protected $fillable = [
        'project_id', 'name', 'phone', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
