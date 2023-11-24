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
        'notified',
    ];

    // Define the relationships with other models
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function appointment(){
        return $this->belongsTo(Appointment::class);
    }

    public function incrementSlotsTaken(int $addedSlots)
    {
        $this->slots_taken += $addedSlots;
        $this->save();
    }

    public function getSlotsTaken(): int
    {
        return $this->slots_taken;
    }

    public function cancel()
    {
        static::where('user_id', $this->user_id)
            ->where('appointment_id', $this->appointment_id)
            ->delete();
    }

    public static function fromUserIdAndAppointmentId(int $userId, int $appointmentId): ?AppointmentUser
    {
        return static::where('user_id', $userId)
                    ->where('appointment_id', $appointmentId)
                    ->first();
    }

    public static function insertBooking(int $userId, int $appointmentId, int $slots)
    {
        static::create([
            'user_id'        => $userId,
            'appointment_id' => $appointmentId,
            'slots_taken'    => $slots
        ]);
    }
}
