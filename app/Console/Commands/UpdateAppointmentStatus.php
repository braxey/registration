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
        $now = now('EST');
        foreach ($appointments as $appointment) {
            if ($appointment->getParsedStartTime() > $now){
                if (!$appointment->isUpcoming()) {
                    $appointment->setStatus('upcoming');
                }
            } else if ($appointment->getParsedEndTime() > $now){
                if (!$appointment->isInProgress()) {
                    $appointment->setStatus('in progress');
                }
            } else if ($appointment->getParsedEndTime() < $now) { 
                if (!$appointment->pastEnd()) {
                    $appointment->setStatus('completed');
                    $bookings = $appointment->getBookings();
                    foreach ($bookings as $booking) {
                        $user = $booking->getUser();
                        if ($user !== null) {
                            $user->decrementSlotsBooked($booking->getSlotsTaken());
                        }
                    }
                }
            }
        }

        $this->info('Appointment status updated successfully.');
    }
}
