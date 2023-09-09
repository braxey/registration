<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use App\Mail\NotifyEmail;

class NotifyUpcomingAppointmentsCommand extends Command
{
    protected $signature = 'notify:upcoming-appointments';

    protected $description = 'Notify users about upcoming appointments';

    public function handle(){
        $upcomingAppointments = Appointment::where('status', 'upcoming')->get();
        
        foreach ($upcomingAppointments as $appointment) {
            if (now()->gt(Carbon::parse($appointment->start_time)->subMinutes(60))) {
                $apptUsers = AppointmentUser::where('appointment_id', $appointment->id)->get();
                $formattedStart = Carbon::parse($appointment->start_time, 'EST');

                foreach ($apptUsers as $apptUser) {
                    if ($apptUser->notified == false) {
                        // Send notification to user via email
                        try {
                            $user = User::find($apptUser->user_id);
                            if ($user && !is_null($user->email_verified_at)) {
                                Mail::to($user->email)->send(new NotifyEmail($formattedStart, $apptUser->slots_taken));
                                AppointmentUser::where('user_id', $user->id)
                                                    ->where('appointment_id', $appointment->id)
                                                    ->update(['notified' => true]);
                            }
                        } catch (\Exception $e) {
                            \Log::error($e);
                        }
                    }
                }
            }
        }
        
        $this->info('Notification sent successfully.');
    }
}
