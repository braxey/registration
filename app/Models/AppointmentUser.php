<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentUser extends Model{
    protected $table = 'appointment_user';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'appointment_id',
        'slots_taken',
    ];

    // Define the relationships with other models
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function appointment(){
        return $this->belongsTo(Appointment::class);
    }
}
