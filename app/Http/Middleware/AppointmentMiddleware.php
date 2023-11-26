<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Appointment;

class AppointmentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $appointment = Appointment::fromId($request->route('appointmentId'));
        if ($appointment === null) {
            return response(null, 404);
        }

        $request->offsetSet('appointment', $appointment);

        if (session('dry-run') === true) {
            return response(null, 202);
        }

        return $next($request);
    }
}
