<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AppointmentSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'status',
        'customer_name',
        'customer_phone',
        'customer_email',
        'engineer_name',
        'engineer_phone',
        'type',
        'location',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function studyRequest(): HasOne
    {
        return $this->hasOne(StudyRequest::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function getIsBookedAttribute(): bool
    {
        return $this->status === 'booked';
    }
}
