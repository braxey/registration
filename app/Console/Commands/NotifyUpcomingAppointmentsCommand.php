<?php

namespace App\Console\Commands;

use App\Constants\EmailTypes;
use App\Constants\LogIdentifiers;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\WalkIn;
use App\Models\Logger;
use App\Services\QueueService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use App\Mail\NotifyEmail;

class NotifyUpcomingAppointmentsCommand extends Command
{
    protected $signature = 'notify:upcoming-appointments';

    protected $description = 'Notify users about upcoming appointments';

    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        parent::__construct();
        $this->queueService = $queueService;
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
                                $payload = [
                                    'date-time' => $appointment->getParsedStartTime()->toIso8601String(),
                                    'slots'     => $booking->getSlotsTaken(),
                                    'name'      => $user->getFirstName(),
                                    'update'    => false,
                                ];

                                $this->queueService->push($user->getEmail(), EmailTypes::NOTIFICATION, $payload);

                                $booking->markAsNotified();
                            }
                        } catch (Exception $e) {
                            Logger::error(LogIdentifiers::NOTIFY_COMMAND, $e->getMessage());
                        }
                    }
                });
                
                $appointment->getWalkIns()->map(function (WalkIn $walkIn) use ($appointment) {
                    if ($walkIn->wasNotNotified()) {
                        try {
                            if ($walkIn->providedEmail()) {
                                $payload = [
                                    'date-time' => $appointment->getParsedStartTime()->toIso8601String(),
                                    'slots'     => $walkIn->getNumberOfSlots(),
                                    'name'      => $walkIn->getName(),
                                    'update'    => false,
                                ];

                                $this->queueService->push($walkIn->getEmail(), EmailTypes::NOTIFICATION, $payload);

                            }
                            $walkIn->markAsNotified();
                        } catch (Exception $e) {
                            Logger::error(LogIdentifiers::NOTIFY_COMMAND, $e->getMessage());
                        }
                    }
                });
            }
        }
        
        $this->info('Notification sent successfully.');
    }
}
