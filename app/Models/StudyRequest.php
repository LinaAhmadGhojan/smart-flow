<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_status',
        'systems',
        'systems_other',
        'plans',
        'plan_files',
        'infrastructure_by',
        'proposed_system',
        'customer_name',
        'customer_phone',
        'project_location',
        'notes',
        'appointment_slot_id',
        'status',
    ];

    protected $casts = [
        'systems' => 'array',
        'plans' => 'array',
        'plan_files' => 'array',
    ];

    public function appointmentSlot(): BelongsTo
    {
        return $this->belongsTo(AppointmentSlot::class);
    }
}
