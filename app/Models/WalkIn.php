<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;

class WalkIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'name',
        'slots',
        'desired_time',
        'appointment_id',
        'notified',
        'notes',
        'email',
    ];

    protected $attributes = [
        'email' => '',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNumberOfSlots(): int
    {
        return $this->slots;
    }

    public function wasNotified(): bool
    {
        return $this->notified == true;
    }

    public function wasNotNotified(): bool
    {
        return !$this->wasNotified();
    }

    public function markAsNotified()
    {
        $this->notified = true;
        $this->save();
    }

    public function providedEmail(): bool
    {
        return $this->email != '';
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAppointmentId(): ?int
    {
        return $this->appointment_id;
    }

    public function setAppointmentId(?int $id)
    {
        $this->appointment_id = $id;
        $this->save();
    }

    public function getAppointment(): ?Appointment
    {
        $appointmentId = $this->getAppointmentId();
        if ($appointmentId === null) {
            return null;
        }
        return Appointment::fromId($appointmentId);
    }

    public static function fromId($id): ?WalkIn
    {
        return static::where('id', $id)->first();
    }
}
