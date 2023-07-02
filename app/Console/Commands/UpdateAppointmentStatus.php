<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateAppointmentStatus extends Command
{
    protected $signature = 'appointments:update-status';

    protected $description = 'Update the status of appointments';

    public function handle()
    {
        $appointments = Appointment::where('end_time', '<', now())->get();

        foreach ($appointments as $appointment) {
           if($appointment->past_end == false){ 
                // Set the appointment to past end
                $appointment->past_end = true;
                $appointment->save();

                // Set the number of available slots to 0
                $appointment->total_slots = 0;
                $appointment->save();

                // Return the slots back to users
                $appointmentUsers = AppointmentUser::where('appointment_id', $appointment->id)->get();
                foreach ($appointmentUsers as $appointmentUser) {
                    $user = User::find($appointmentUser->user_id);
                    $user->slots_booked -= $appointmentUser->slots_taken;
                    $user->save();
                }
            }
        }

        $this->info('Appointment status updated successfully.');
    }
}
