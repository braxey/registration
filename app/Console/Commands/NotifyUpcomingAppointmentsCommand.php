<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use Twilio\Rest\Client;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class NotifyUpcomingAppointmentsCommand extends Command
{
    protected $signature = 'notify:upcoming-appointments';

    protected $description = 'Notify users about upcoming appointments';

    public function handle(){
        $upcomingAppointments = Appointment::where('status', 'upcoming')->get(); 
        $anHourAgo = now()->subMinutes(60);

        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_PHONE_NUMBER");

        $client = new Client($account_sid, $auth_token);
        
        foreach ($upcomingAppointments as $appointment) {

            if ($appointment->start_time > $anHourAgo) {
                $apptUsers = AppointmentUser::where('appointment_id', $appointment->id)->get();
                $formattedStart = Carbon::parse($appointment->start_time, 'EST')->format('g:i A');

                foreach ($apptUsers as $apptUser) {
                    if ($apptUser->notified == false) {
                        // Send notification to user via text
                        $user = User::find($apptUser->user_id);
                        if ($user) {
                            $message = "You have an appointment that begins at ".$formattedStart.".";
                    
                            try {
                                $client->messages->create($user->phone_number, [
                                    'from' => $twilio_number, 
                                    'body' => $message]);
                    
                                dump('SMS Sent Successfully.');
                    
                            } catch (Exception $e) {
                                dump("Error: ". $e->getMessage());
                            }

                            // Update notified status
                            $apptUser->notified = true;
                            $apptUser->save();
                        }
                    }
                }
            }
        }
        
        $this->info('Notification sent successfully.');
    }
}
