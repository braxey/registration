<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Appointment;
use App\Models\AppointmentUser;

class User extends Authenticatable implements MustVerifyEmail
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

    public function getUpcomingAppointments()
    {
        $appointments = collect();
        $apptUsers = AppointmentUser::where('user_id', $this->id)->get();

        foreach($apptUsers as $apptUser) {
            $appt = Appointment::where('id', $apptUser->appointment_id)->first();
            if ($appt && $appt->getStatus() === "upcoming") {
                $appointments->push($appt);
            }
        }

        return $appointments;
    }

    public function getName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
