<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Appointment;

class AppointmentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appointment = Appointment::find($request->route('appointmentId'));
        if ($appointment === null) {
            return response(null, 404);
        }

        $request->offsetSet('appointment', $appointment);

        return $next($request);
    }
}
