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
        $appointments = Appointment::all();
        $now = now();
        foreach ($appointments as $appointment) {
            
            if($appointment->start_time > $now){
                // Appointment is upcoming
                if($appointment->status != "upcoming"){
                    $appointment->status = "upcoming";
                    $appointment->save();
                }
            }else if($appointment->end_time > $now){
                // Appointment is in progress
                if($appointment->status != "in progress"){
                    $appointment->status = "in progress";
                    $appointment->save();
                }
            }else if($appointment->end_time < $now && $appointment->past_end == false){ 
                // Set the appointment to past end
                $appointment->past_end = true;
                $appointment->save();

                // Set the status to completed
                $appointment->status = "completed";
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
