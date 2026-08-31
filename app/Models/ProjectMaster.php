<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectMaster extends Model
{
    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'description_ar',
        'location',
        'maps_url',
        'media_type',
        'media_url',
        'images',
        'order',
        'is_featured',
        'is_visible',
    ];

    protected $casts = [
        'images' => 'array',
        'order' => 'integer',
        'is_featured' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(ProjectMasterFile::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeVisibleOnSite($query)
    {
        return $query->where('is_visible', true);
    }

    public function getMediaUrlAttribute(?string $value): ?string
    {
        return \App\Support\StorageUrl::toPublicUrl($value);
    }
}
