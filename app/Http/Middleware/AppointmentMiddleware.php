<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Appointment;

/**
 * @see AppointmentMiddlewareTest
 */
class AppointmentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $appointment = Appointment::fromId($request->route('appointmentId'));
        if ($appointment === null) {
            return response('appointment not found', 404);
        }

        $request->offsetSet('appointment', $appointment);

        if (session('appointment-dry-run') === true) {
            return response('passes appointment middleware', 202);
        }

        return $next($request);
    }
}
