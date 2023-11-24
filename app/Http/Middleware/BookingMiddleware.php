<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        $appointmentId = $request->route('appointmentId');
        $appointment = Appointment::where('id', $appointmentId)->first();
        if (!$appointment) {
            return response(null, 404);
        }

        if(
            now() > Carbon::parse($appointment->getStartTime())->setTime(12, 0, 0)
            || $appointment->isWalkInOnly()
        ) {
            return response(null, 403);
        }

        $request->offsetSet('user', Auth::user());
        $request->offsetSet('appointment', $appointment);

        return $next($request);
    }
}
