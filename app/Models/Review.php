<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_name',
        'reviewer_email',
        'reviewer_photo',
        'reviewer_video',
        'rating',
        'comment',
        'is_visible',
        'source',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'rating'     => 'integer',
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->reviewer_name ?? 'مجهول | Anonymous';
    }
}
