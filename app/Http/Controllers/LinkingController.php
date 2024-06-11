<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Carbon\Carbon;

/**
 * @LinkingControllerTest
 */
class LinkingController extends Controller
{
    public function getAppointmentLinkPage(Request $request)
    {
        $between = getBetween([
            'start_date' => $request->input('start_date'),
            'start_time' => $request->input('start_time'),
            'end_date'   => $request->input('end_date'),
            'end_time'   => $request->input('end_time'),
        ]);

        $nonCompletedAppointments = Appointment::where('status', '<>', 'completed')
            ->orderBy('start_time')
            ->get()
            ->filter(function (Appointment $appointment) use ($between) {
                $appointmentTime = Carbon::parse($appointment->getStartTime());
                $allowed = true;

                if (isset($between['start'])) {
                    $allowed = $allowed && $appointmentTime->gte($between['start']);
                }
                if (isset($between['end'])) {
                    $allowed = $allowed && $between['end']->gte($appointmentTime);
                }

                return $allowed;
            });

        return view('appointments.link-walk-in', [
            'walkIn'                   => $request->offsetGet('walk-in'),
            'nonCompletedAppointments' => $nonCompletedAppointments
        ]);
    }

    public function linkAppointment(Request $request)
    {
        $walkIn = $request->offsetGet('walk-in');
        $appointment = $request->offsetGet('appointment');

        if ($walkIn->getAppointmentId() === null) {
            $appointment->addWalkIn($walkIn);
        } else if ($request->route('appointmentId') == $walkIn->getAppointmentId()) {
            return redirect()->route('walk-in.show-waitlist');
        } else {
            $currentAppointment = Appointment::fromId($walkIn->getAppointmentId());
            if ($currentAppointment !== null) {
                $currentAppointment->removeWalkIn($walkIn);
            }
            $appointment->addWalkIn($walkIn);
        }

        $walkIn->setAppointmentId($request->route('appointmentId'));
        return redirect()->route('walk-in.show-waitlist');
    }

    public function unlinkAppointment(Request $request)
    {
        $walkIn = $request->offsetGet('walk-in');
        $appointment = $request->offsetGet('appointment');

        if ($walkIn->getAppointmentId() != $request->route('appointmentId')) {
            return response(null, 400);
        }

        if ($walkIn->getAppointmentId() === null) {
            return response(null, 400);
        }

        $appointment->removeWalkIn($walkIn);
        $walkIn->setAppointmentId(null);
        return redirect()->route('walk-in.show-waitlist');
    }
}
