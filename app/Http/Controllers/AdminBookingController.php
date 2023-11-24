<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Organization;
use App\Models\AppointmentUser;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AdminBookingController extends Controller
{
    private Organization $organization;

    public function __construct()
    {
        $this->organization = Organization::find(1);
    }

    public function getAdminUserLookupPage(Request $request)
    {
        $name = $request->input('name');
        $users = User::where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%$name%")
                    ->take(25)
                    ->get();

        return view('admin-user.lookup', [
            'users' => $users
        ]);
    }

    public function getUsersUpcomingBookings(Request $request)
    {
        $user = User::where('id', $request->route('userId'))->first();
        if ($user === null) {
            return response(null, 404);
        }

        $appointments = $user->getUpcomingAppointments();
        return view('admin-user.user-bookings', [
            'user'         => $user,
            'appointments' => $appointments
        ]);
    }

    public function getBookingForUser(Request $request)
    {
        $user = User::where('id', $request->route('userId'))->first();
        $appointment = Appointment::where('id', $request->route('appointmentId'))->first();
        if ($user === null || $appointment === null) {
            return response(null, 404);
        }

        return view('admin-user.edit-booking', [
            'user'           => $user,
            'appointment'    => $appointment,
            'organization'   => $this->organization,
            'availableSlots' => $appointment->getAvailableSlots(),
            'userSlots'      => $user->getCurrentNumberOfSlots(),
            'apptUserSlots'  => $appointment->userSlots($user->getId())
        ]);
    }

    public function editBookingForUser(Request $request)
    {
        $user = User::where('id', $request->route('userId'))->first();
        $appointment = Appointment::where('id', $request->route('appointmentId'))->first();
        if ($user === null || $appointment === null) {
            return response(null, 404);
        }

        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        if ($booking === null) {
            return response(null, 404);
        }

        $availableSlots = $appointment->getAvailableSlots();
        $userSlots = $user->getCurrentNumberOfSlots();
        $apptUserSlots = $appointment->userSlots($user->getId());

        $validatedData = $request->validate([
            'slots' => 'required|integer|min:0|max:' . ($availableSlots + $userSlots),
        ]);
        $slotsRequested = (int) $validatedData['slots'];
        $slotChange = $slotsRequested - $apptUserSlots;
        
        if ($userSlots + $slotChange > $this->organization->getMaxSlotsPerUser()) {
            return redirect()->back();
        } else if ($slotsRequested == 0){
            $this->cancelBookingForUser($request, $user->getId(), $appointment->getId());
            return redirect()->route('admin-booking.user', $user->getId());
        }
        
        $appointment->incrementSlotsTaken($slotChange);
        $user->incrementSlotsBooked($slotChange);
        $booking->incrementSlotsTaken($slotChange);
    
        return redirect()->route('admin-booking.user', $user->getId());
    }

    public function cancelBookingForUser(Request $request)
    {
        $user = User::where('id', $request->route('userId'))->first();
        $appointment = Appointment::where('id', $request->route('appointmentId'))->first();
        if ($user === null || $appointment === null) {
            return response(null, 404);
        }

        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        if ($booking === null) {
            return response(null, 404);
        }

        $slotsToReturn = $booking->getSlotsTaken();
        $appointment->decrementSlotsTaken($slotsToReturn);
        $user->decrementSlotsBooked($slotsToReturn);

        $booking->cancel();
        return redirect()->route('admin-booking.user', $user->getId());
    }
}