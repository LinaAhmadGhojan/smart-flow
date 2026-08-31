<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMasterFile extends Model
{
    protected $fillable = [
        'project_master_id',
        'label',
        'path',
        'kind',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function projectMaster(): BelongsTo
    {
        return $this->belongsTo(ProjectMaster::class);
    }
}
