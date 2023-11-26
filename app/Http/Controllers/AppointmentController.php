<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function getCreatePage(Request $request)
    {
        return view('appointments.create');
    }

    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required',
            'start_time'  => 'required|date',
            'total_slots' => 'required|integer|min:1',
        ]);

        $data = array_merge($validatedData, [
            'end_time'     => Carbon::parse($validatedData['start_time'])->addHours(1)->format('Y-m-d\TH:i'),
            'walk_in_only' => $request->input('walk-in-only') === "on"
        ]);
        
        Appointment::create($data);
        return redirect()->route('appointments.index');
    }

    public function getEditPage(Request $request)
    {
        $appointment = $request->offsetGet('appointment');
        return view('appointments.edit', [
            'appointment' => $appointment
        ]);
    }

    public function update(Request $request)
    {
        $appointment = $request->offsetGet('appointment');

        $validatedData = $request->validate([
            'description' => 'required',
            'start_time'  => 'required|date',
            'total_slots' => 'required|integer|min:0',
        ]);

        $data = array_merge($validatedData, [
            'end_time'     => Carbon::parse($validatedData['start_time'], 'EST')->addHours(1)->format('Y-m-d\TH:i'),
            'walk_in_only' => $request->input('walk-in-only') === "on"
        ]);
        
        $appointment->update($data);
        return redirect()->route('appointment.get-edit', $appointment->getId());
    }

    public function delete(Request $request)
    {
        $appointment = $request->offsetGet('appointment');

        AppointmentUser::where('appointment_id', $appointment->getId())
            ->get()
            ->map(function (AppointmentUser $booking) use ($appointment) {
                if (!$appointment->pastEnd()) {
                    $user = $booking->getUser();
                    if ($user !== null) {
                        $user->decrementSlotsBooked($booking->getSlotsTaken());
                    }
                }
                $booking->cancel();
            });
        
        $appointment->delete();
        return redirect()->route('appointments.index');
    }

    public function userDelete(User $user)
    {
        AppointmentUser::where('user_id', $user->getId())
            ->get()
            ->map(function (AppointmentUser $booking) {
                $appointment = $booking->getAppointment();
                if ($appointment !== null && !$appointment->pastEnd()) {
                    $appointment->decrementSlotsTaken($booking->getSlotsTaken());
                }
                $booking->cancel();
            });
    }
}
