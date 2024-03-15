<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Constants\EmailTypes;
use App\Models\User;
use App\Models\Appointment;
use App\Services\QueueService;


class MassMailerController extends Controller
{

    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function getMassMailerPage()
    {
        return view('superuser.mass-mailer');
    }

    public function sendMassEmail(Request $request)
    {
        $payload = array_merge($request->all(), [
            'include-appointment-details' => $request->get('include-appointment-details') === 'on'
        ]);
        
        if (!in_array($payload['recipients'], ['all', 'upcoming', 'completed'])) {
            return response()->json(['message' => 'invalid recipient'], 400);
        }

        if ($payload['subject'] === null || $payload['message'] === null) {
            return response()->json(['message' => 'subject and message must be non-empty'], 400);
        }

        $recipientList = $this->getRecipientList($request->get('recipients'));
        foreach ($recipientList as $recipient) {

            $queuePayload = $payload;
            $appointments = null;
            $queuePayload['appointmentIds'] = [];
            if ($payload['include-appointment-details']) {
                $appointments = $this->getAppointments($recipient, $payload['recipients']);
                $appointments->map(function (Appointment $appointment) use (&$queuePayload) {
                    $queuePayload['appointmentIds'][] = $appointment->getId();
                });
            }
            $queuePayload['userId'] = $recipient->getId();

            $this->queueService->push($recipient->getEmail(), EmailTypes::CUSTOM, $queuePayload);
        }

        return redirect(route('mass-mailer.landing'));
    }

    private function getRecipientList(string $recipients): Collection
    {
        $users = User::all();
        switch ($recipients) {
            case 'all':
                return $users;
            case 'upcoming':
                return $users->filter(function (User $user) {
                    return $user->hasUpcomingAppointment();
                });
            case 'completed':
                return $users->filter(function (User $user) {
                    return $user->hasCompletedAppointment();
                });
            default:
                return collect([]);
        }
    }

    private function getAppointments(User $user, string $recipients): Collection
    {
        switch ($recipients) {
            case 'all':
                return $user->getAllAppointments();
            case 'upcoming':
                return $user->getUpcomingAppointments();
            case 'completed':
                return $user->getPastAppointments();
            default:
                return collect();
        }
    }
}
