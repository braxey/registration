<?php

namespace App\Console\Commands;

use App\Constants\EmailTypes;
use App\Constants\LogIdentifiers;
use App\Models\QueuedEmail;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\WalkIn;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Mail\NotifyEmail;

class NotifyUpcomingAppointmentsCommand extends Command
{
    protected $signature = 'notify:upcoming-appointments';

    protected $description = 'Notify users about upcoming appointments';

    public function handle()
    {
        $upcomingAppointments = Appointment::getUpcoming();
        
        foreach ($upcomingAppointments as $appointment) {
            if (now('EST')->gt($appointment->getParsedStartTime()->subMinutes(60))) {
                $appointment->getBookings()->map(function (AppointmentUser $booking) use ($appointment) {
                    if ($booking->userWasNotNotified()) {
                        try {
                            $user = $booking->getUser();
                            if ($user !== null) {
                                $payload = [
                                    'date-time' => $appointment->getParsedStartTime()->toIso8601String(),
                                    'slots'     => $booking->getSlotsTaken(),
                                    'name'      => $user->getFirstName(),
                                    'update'    => false,
                                ];
                                QueuedEmail::queue($user->getEmail(), EmailTypes::NOTIFICATION, $payload);
                                $booking->markAsNotified();
                            }
                        } catch (Exception $e) {
                            Log::error(LogIdentifiers::NOTIFY_COMMAND . $e->getMessage());
                        }
                    }
                });
                
                $appointment->getWalkIns()->map(function (WalkIn $walkIn) use ($appointment) {
                    if ($walkIn->wasNotNotified()) {
                        try {
                            if ($walkIn->providedEmail()) {
                                $payload = [
                                    'date-time' => $appointment->getParsedStartTime(),
                                    'slots'     => $walkIn->getNumberOfSlots(),
                                    'name'      => $walkIn->getName(),
                                    'update'    => false,
                                ];
                                QueuedEmail::queue($walkIn->getEmail(), EmailTypes::NOTIFICATION, $payload);
                            }
                            $walkIn->markAsNotified();
                        } catch (Exception $e) {
                            Log::error(LogIdentifiers::NOTIFY_COMMAND . $e->getMessage());
                        }
                    }
                });
            }
        }
        
        $this->info('Notification sent successfully.');
    }
}
