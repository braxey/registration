<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\Appointment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private Organization $organization;

    public function __construct()
    {
        $this->organization = Organization::find(1);
    }

    public function getDashboard()
    {
        $user = Auth::user();
        
        $allAppointments = $user->getAllAppointments();
        $pastAppointments = $user->getPastAppointments();
        $upcomingAppointments = $user->getUpcomingAppointments();
        
        return view('dashboard', [
            'allAppointments'      => $allAppointments,
            'pastAppointments'     => $pastAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'organization'         => $this->organization
        ]);
    }

    public function showAllAppointments(Request $request)
    {
        $user = Auth::user();

        $between = getBetween([
            'start_date' => $request->input('start_date'),
            'start_time' => $request->input('start_time'),
            'end_date'   => $request->input('end_date'),
            'end_time'   => $request->input('end_time'),
        ]);

        $inProgressAppointments = Appointment::where('status', 'in progress')
            ->orderBy('start_time')
            ->get();

        $upcomingAppointments = Appointment::where('status', 'upcoming')
            ->orderBy('start_time')
            ->get();

        $completedAppointments = Appointment::where('status', 'completed')
            ->orderBy('start_time', 'desc')
            ->get();

        $allAppointments = $inProgressAppointments
            ->merge($upcomingAppointments)
            ->merge($completedAppointments)
            ->filter(function (Appointment $appointment) use ($user, $between) {
                $apptTime = Carbon::parse($appointment->getStartTime());
                if ($user !== null) {
                    $allowed = ($appointment->isOpen() && !$appointment->isWalkInOnly()) || $user->isAdmin();
                } else {
                    $allowed = $appointment->isOpen() && !$appointment->isWalkInOnly();
                }

                if (isset($between['start'])) {
                    $allowed = $allowed && $apptTime->gte($between['start']);
                }
                if (isset($between['end'])) {
                    $allowed = $allowed && $between['end']->gte($apptTime);
                }

                return $allowed;
            });

        if ($upcomingAppointments->count() > 0) {
            $min = $upcomingAppointments->first()->getStartDate();
            $max = $upcomingAppointments->last()->getStartDate();
        }

        return view('appointments.index', [
            'appointments' => $allAppointments,
            'user'         => $user,
            'organization' => $this->organization,
            'min'          => $min ?? '',
            'max'          => $max ?? ''
        ]);
    }
}
