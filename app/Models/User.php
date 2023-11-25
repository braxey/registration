<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Hash;

use App\Models\Appointment;
use App\Models\AppointmentUser;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'slots_booked',
        'admin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'phone_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function setPassword(string $password)
    {
        $this->forceFill([
            'password' => Hash::make($password),
        ])->save();
    }

    public function verifyPhone()
    {
        $this->phone_verified_at = now();
        $this->save();
    }

    public function phoneVerified()
    {
        return $this->phone_verified_at !== null;
    }

    public function hasUpcomingAppointment(): bool
    {
        $apptUsers = AppointmentUser::where('user_id', $this->id)->get();

        foreach($apptUsers as $apptUser) {
            if (Appointment::where('id', $apptUser->appointment_id)->first()->getStatus() === "upcoming") {
                return true;
            }
        }
        return false;
    }

    public function getName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getCurrentNumberOfSlots(): int
    {
        return $this->slots_booked;
    }

    public function incrementSlotsBooked(int $addedSlots)
    {
        $this->slots_booked += $addedSlots;
        $this->save();
    }

    public function decrementSlotsBooked(int $removedSlots)
    {
        $this->slots_booked -= $removedSlots;
        $this->save();
    }

    public function isAdmin(): bool
    {
        return (int) $this->admin === 1;
    }

    public function getAllAppointments(): Collection
    {
        $upcomingAppointments = $this->getUpcomingAppointments();
        $completedAppointments = $this->getPastAppointments();
        return $upcomingAppointments->merge($completedAppointments);
    }

    public function getUpcomingAppointments(): Collection
    {
        $upcomingAppointmentIds = AppointmentUser::where('user_id', $this->getId())
                        ->whereHas('appointment', function ($query) {
                            $query->where('past_end', false);
                        })
                        ->pluck('appointment_id');
        
        return Appointment::whereIn('id', $upcomingAppointmentIds)
                        ->orderBy('start_time')
                        ->get();
    }

    public function getPastAppointments(): Collection
    {
        $pastAppointmentIds = AppointmentUser::where('user_id', $this->getId())
                        ->whereHas('appointment', function ($query) {
                            $query->where('past_end', true);
                        })
                        ->pluck('appointment_id');
            
        return Appointment::whereIn('id', $pastAppointmentIds)
                        ->orderByDesc('start_time')
                        ->get();
    }

    public static function fromId($id): ?User
    {
        return static::where('id', $id)->first();
    }

    public static function fromEmail(string $email): ?User
    {
        return static::where('email', $email)->first();
    }
}
