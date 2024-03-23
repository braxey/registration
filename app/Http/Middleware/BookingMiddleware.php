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
        $organization = Organization::find(1);
        if ($organization->registrationIsClosed()) {
            return redirect()->route('appointments.index');
        }

        $appointment = Appointment::fromId($request->route('appointmentId'));
        if ($appointment === null) {
            return response('appointment not found', 404);
        }

        if (now('EST') > $appointment->getParsedStartTime()->setTime(12, 0, 0)) {
            return response('too late to book', 401);
        }

        if ($appointment->isWalkInOnly()) {
            return response('appointment is walk-in-only', 401);
        }

        $request->offsetSet('sessionUser', Auth::user());
        $request->offsetSet('appointment', $appointment);

        if (session('booking-dry-run') === true) {
            return response('passes booking middleware', 202);
        }

        return $next($request);
    }
}
