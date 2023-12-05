<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Services\MailerService;
use App\Models\User;


class MassMailerController extends Controller
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
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
            $appointments = null;
            if ($payload['include-appointment-details']) {
                $appointments = $this->getAppointments($recipient, $payload['recipients']);
            }

            $this->mailer->sendCustomEmail(
                $recipient,
                $payload['subject'],
                $payload['message'],
                $payload['include-appointment-details'],
                $appointments
            );

            sleep(37);
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
