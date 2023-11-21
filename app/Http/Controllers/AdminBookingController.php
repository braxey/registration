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
    public function getAdminUserLookupPage(Request $request)
    {
        $name = $request->input('name');
        $users = User::where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%$name%")
                    ->take(25)
                    ->get();

        return view('admin-user.lookup', compact('users'));
    }

    public function getUsersUpcomingBookings($userId)
    {
        $user = User::where('id', $userId)->first();
        $appointments = $user->getUpcomingAppointments();

        return view('admin-user.user-bookings', compact('user', 'appointments'));
    }

    public function getBookingForUser($userId, $appointmentId)
    {
        $user = User::where('id', $userId)->first();
        $appointment = Appointment::where('id', $appointmentId)->first();
        $organization = Organization::findOrFail(1);
        $availableSlots = $appointment->total_slots - $appointment->slots_taken;
        $userSlots = $user->slots_booked;
        $apptUserSlots = $appointment->userSlots($user->id);

        return view('admin-user.edit-booking', compact('user', 'appointment', 'organization', 'availableSlots', 'userSlots', 'apptUserSlots'));
    }

    public function editBookingForUser(Request $request, $userId, $appointmentId)
    {
        $user = User::where('id', $userId)->first();
        $appointment = Appointment::where('id', $appointmentId)->first();
        $organization = Organization::findOrFail(1);
        $availableSlots = $appointment->total_slots - $appointment->slots_taken;
        $userSlots = $user->slots_booked;
        $apptUserSlots = $appointment->userSlots($user->id);
        $MAX_SLOTS_PER_USER = $organization->max_slots_per_user;

        $validatedData = $request->validate([
            'slots' => 'required|integer|min:0|max:'.($availableSlots+$userSlots),
        ]);
        
        // Get the number of slots requested
        $slotsRequested = $validatedData['slots'];
        
        // Check if the requested slots are not available
        if ($slotsRequested-$apptUserSlots > $availableSlots || $slotsRequested+$userSlots-$apptUserSlots > $MAX_SLOTS_PER_USER) {
            // Redirect back with an error message
            return redirect()->back();
        // If the user requests to update to 0 slots
        } else if ($slotsRequested == 0){
            // Cancel their booking
            $this->cancelBookingForUser($request, $userId, $appointmentId);
            // Redirect to appointments page
            return redirect()->route('admin-booking.user', $userId);
        }
        
        // Perform the booking logic
        // Update the appointment slots_taken count
        $appointment->slots_taken += $slotsRequested - $apptUserSlots;
        $appointment->save();

        // Update the slots booked for the user
        $user->slots_booked += $slotsRequested - $apptUserSlots;
        $user->save();

        // Update the user-appointment entry
        AppointmentUser::where('user_id', $user->id)
            ->where('appointment_id', $appointment->id)
            ->increment('slots_taken', $slotsRequested-$apptUserSlots);
    
        // Redirect to a dashboard
        return redirect()->route('admin-booking.user', $userId);
    }

    public function cancelBookingForUser(Request $request, $userId, $appointmentId)
    {
        $user = User::where('id', $userId)->first();
        $appointment = Appointment::where('id', $appointmentId)->first();
        

        // Retrieve the appointment user row
        $apptUserEntry = AppointmentUser::where('user_id', $userId)
            ->where('appointment_id', $appointmentId)
            ->first();

        // If the row is not null
        if($apptUserEntry){
            // Get slots to return
            $slotsToReturn = $apptUserEntry->slots_taken;

            // Decrement appt slots taken
            $appointment->slots_taken -= $slotsToReturn;
            $appointment->save();

            // Decrement the user slots booked (return slots back to them)
            $user->slots_booked -= $slotsToReturn;
            $user->save();

            // Remove entry from appt user table
            AppointmentUser::where('user_id', $user->id)
            ->where('appointment_id', $appointment->id)
            ->delete();
        
            // Navigate to the dashboard
            return redirect()->route('admin-booking.user', $userId);
        }else{
            // Redirect back with an error message
            return redirect()->back();
        }
    }
}
