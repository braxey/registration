<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model{
    use HasFactory;
    protected $fillable = [
        'description',
        'start_time',
        'end_time',
        'total_slots',
    ];
}
