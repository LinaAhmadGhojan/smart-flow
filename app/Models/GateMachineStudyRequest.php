<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateMachineStudyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'site_location',
        'door_weight',
        'door_width',
        'door_height',
        'door_material',
        'has_electrical_point',
        'has_machine_wiring',
        'notes',
        'status',
    ];
}
