<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class BookingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Make sure registration is open.
        $organization = Organization::find(1);
        if($organization->registrationIsClosed()) {
            return redirect()->route('appointments.index');
        }

        // Make sure a booking action can be performed on this appointment.
        $appointment = Appointment::fromId($request->route('appointmentId'));
        if ($appointment === null) {
            return response(null, 404);
        }

        if(
            now('EST') > $appointment->getParsedStartTime()->setTime(12, 0, 0)
            || $appointment->isWalkInOnly()
        ) {
            return response(null, 403);
        }

        $request->offsetSet('user', Auth::user());
        $request->offsetSet('appointment', $appointment);

        return $next($request);
    }
}
