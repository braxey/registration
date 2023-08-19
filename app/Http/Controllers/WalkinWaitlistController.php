<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WalkinWaitlistController extends Controller
{
    // edit
    // Show the admin-only guestlist
    public function guestlist(Request $request){

        $user = Auth::user();
        if(!$user->admin) abort(404);
        
        $firstName = $request->input('first_name');
        $lastName  = $request->input('last_name');
        $startDate = $request->input('start_date');
        $startTime = $request->input('start_time');
        $status = $request->input('status');

        $guests = AppointmentUser::query()
            ->when($firstName, function ($query) use ($firstName) {
                $query->whereHas('user', function ($subQuery) use ($firstName) {
                    $subQuery->where('first_name', 'LIKE', '%' . $firstName . '%');
                });
            })
            ->when($lastName, function ($query) use ($lastName) {
                $query->whereHas('user', function ($subQuery) use ($lastName) {
                    $subQuery->where('last_name', 'LIKE', '%' . $lastName . '%');
                });
            })
            ->when($startDate || $startTime, function ($query) use ($startDate, $startTime) {
                $query->whereHas('appointment', function ($subQuery) use ($startDate, $startTime) {
                    $subQuery->where('start_time', 'LIKE', '%' . $startDate . ' ' . $startTime . '%');
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->whereHas('appointment', function ($subQuery) use ($status) {
                    $subQuery->where('status', $status);
                });
            })
            ->join('appointments', 'appointments.id', '=', 'appointment_user.appointment_id')
            ->orderByRaw("
                CASE
                    WHEN status = 'in progress' THEN 1
                    WHEN status = 'upcoming' THEN 2
                    WHEN status = 'completed' THEN 3
                    ELSE 3
                END
            ")
            ->orderByRaw("
                CASE WHEN status = 'completed' THEN start_time END DESC
            ")
            ->orderByRaw("
                CASE WHEN status = 'upcoming' THEN start_time END ASC
            ")
            ->orderByRaw("
                CASE WHEN status = 'in progress' THEN start_time END ASC
            ")
            ->get();

        $totalSlotsTaken = 0;
        $totalShowedUp = 0;
        foreach($guests as $guest){
            $apptUser = AppointmentUser::where('appointment_id', $guest->appointment_id)
                                    ->where('user_id', $guest->user_id)
                                    ->first();
            $totalSlotsTaken += $apptUser->slots_taken;
            $totalShowedUp += $apptUser->showed_up;
        }

        return ($user->admin)
            ? view('appointments.walkin-waitlist', compact('guests', 'totalSlotsTaken', 'totalShowedUp'))
            : redirect()->route('appointments.index');
    }
}
