<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalkIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'name',
        'slots',
        'desired_time',
        'appointment_id',
    ];
}
