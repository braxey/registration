<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\Organization;

class BookingController extends Controller
{
    private Organization $organization;

    private Appointment $appointment;

    private User $user;


    public function __construct()
    {
        $this->organization = Organization::find(1);
    }

    public function getBookingPage(Request $request)
    {
        $this->appointment = $request->offsetGet('appointment');
        $this->user = $request->offsetGet('sessionUser');
        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        if ($booking !== null) {
            return redirect()->route('booking.get-edit-booking', $this->appointment->getId());
        }

        return view('appointments.book', [
            'appointment'    => $this->appointment,
            'availableSlots' => $this->appointment->getAvailableSlots(),
            'userSlots'      => $this->user->getCurrentNumberOfSlots(),
            'organization'   => $this->organization
        ]);
    }

    public function book(Request $request)
    {
        $this->appointment = $request->offsetGet('appointment');
        $this->user = $request->offsetGet('sessionUser');

        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        if ($booking !== null) {
            return response(null, 401);
        }

        $availableSlots = $this->appointment->getAvailableSlots();        
        $validatedData = $request->validate([
            'slots' => 'required|integer|min:1|max:' . $availableSlots,
        ]);
        $slotsRequested = (int) $validatedData['slots'];
        
        $userSlots = $this->user->getCurrentNumberOfSlots();
        if ($slotsRequested + $userSlots > $this->organization->getMaxSlotsPerUser()) {
            return redirect()->back();
        }
        
        $this->appointment->incrementSlotsTaken($slotsRequested);
        $this->user->incrementSlotsBooked($slotsRequested);
        AppointmentUser::insertBooking($this->user->getId(), $this->appointment->getId(), $slotsRequested);

        return redirect()->route('dashboard');
    }

    public function getEditBookingPage(Request $request)
    {
        $this->appointment = $request->offsetGet('appointment');
        $this->user = $request->offsetGet('sessionUser');

        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        if ($booking === null) {
            return redirect()->route('booking.get-booking', $this->appointment->getId());
        }

        return view('appointments.edit_booking', [
            'appointment'    => $this->appointment,
            'availableSlots' => $this->appointment->getAvailableSlots(),
            'bookingSlots'   => $booking->getSlotsTaken(),
            'userSlots'      => $this->user->getCurrentNumberOfSlots(),
            'organization'   => $this->organization
        ]);
    }

    public function editBooking(Request $request)
    {
        $this->appointment = $request->offsetGet('appointment');
        $this->user = $request->offsetGet('sessionUser');

        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        if ($booking === null) {
            return response(null, 401);
        }
        
        $availableSlots = $this->appointment->getAvailableSlots();
        $userSlots = $this->user->getCurrentNumberOfSlots();
        $validatedData = $request->validate([
            'slots' => 'required|integer|min:0|max:'. ($availableSlots + $userSlots),
        ]);

        $slotsRequested = (int) $validatedData['slots'];
        $apptUserSlots = $booking->getSlotsTaken();
        $slotChange = $slotsRequested - $apptUserSlots;

        if ($slotsRequested == 0) {
            $this->cancelBooking($request);
            return redirect()->route('appointments.index');
        }
        
        if ($userSlots + $slotChange > $this->organization->getMaxSlotsPerUser()) {
            return redirect()->back();
        }
        
        $this->appointment->incrementSlotsTaken($slotChange);
        $this->user->incrementSlotsBooked($slotChange);
        $booking->incrementSlotsTaken($slotChange);
    
        return redirect()->route('dashboard');
    }

    public function cancelBooking(Request $request)
    {
        $this->appointment = $request->offsetGet('appointment');
        $this->user = $request->offsetGet('sessionUser');

        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        if ($booking === null) {
            return response(null, 404);
        }

        $slotsToReturn = $booking->getSlotsTaken();
        $this->appointment->decrementSlotsTaken($slotsToReturn);
        $this->user->decrementSlotsBooked($slotsToReturn);
        $booking->cancel();

        return redirect()->route('dashboard');
    }
}
