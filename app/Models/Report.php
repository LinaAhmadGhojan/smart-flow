<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_slot_id',
        'title',
        'content',
        'images',
        'client_name',
        'engineer_name',
        'visit_date',
        'visit_time',
        'visit_type',
        'recipient_entity',
        'site_address',
        'site_company',
        'contact_phone',
        'delivery_method',
        'delivery_notes',
        'executed_works',
        'report_notes',
        'recommendations',
    ];

    protected $casts = [
        'images' => 'array',
        'visit_date' => 'date',
    ];

    public function appointmentSlot()
    {
        return $this->belongsTo(AppointmentSlot::class);
    }
}
