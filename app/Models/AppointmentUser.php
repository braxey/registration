<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Appointment;

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

    public function getShowedUp(): int
    {
        return $this->showed_up;
    }

    public function setShowedUp(int $showed)
    {
        $this->showed_up = $showed;
        $this->save();
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getAppointmentId(): int
    {
        return $this->appointment_id;
    }

    public function getUser(): ?User
    {
        return User::where('id', $this->getUserId())->first();
    }

    public function getAppointment(): ?Appointment
    {
        return Appointment::where('id', $this->getAppointmentId())->first();
    }

    public function userWasNotified(): bool
    {
        return $this->notified == true;
    }

    public function userWasNotNotified(): bool
    {
        return !$this->userWasNotified();
    }

    public function markAsNotified()
    {
        $this->notified = true;
        $this->save();
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
