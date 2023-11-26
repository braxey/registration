<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\WalkIn;
use App\Services\MailerService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Mail\NotifyEmail;

class NotifyUpcomingAppointmentsCommand extends Command
{
    protected $signature = 'notify:upcoming-appointments';

    protected $description = 'Notify users about upcoming appointments';

    protected $mailer;

    public function __construct(MailerService $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

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
                                $this->mailer->sendNotificationEmail($user->getEmail(), [
                                    'date-time' => $appointment->getParsedStartTime(),
                                    'slots'     => $booking->getSlotsTaken(),
                                    'name'      => $user->getFirstName(),
                                    'update'    => false
                                ]);
                                $booking->markAsNotified();
                            }
                        } catch (Exception $e) {
                            Log::error($e);
                        }
                    }
                });
                
                $appointment->getWalkIns()->map(function (WalkIn $walkIn) use ($appointment) {
                    if ($walkIn->wasNotNotified()) {
                        try {
                            if ($walkIn->providedEmail()) {
                                $this->mailer->sendNotificationEmail($walkIn->getEmail(), [
                                    'date-time' => $appointment->getParsedStartTime(),
                                    'slots'     => $walkIn->getNumberOfSlots(),
                                    'name'      => $walkIn->getName(),
                                    'update'    => false
                                ]);
                            }
                            $walkIn->markAsNotified();
                        } catch (Exception $e) {
                            Log::error($e);
                        }
                    }
                });
            }
        }
        
        $this->info('Notification sent successfully.');
    }
}
