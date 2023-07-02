<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use Illuminate\Support\Facades\Auth;

const MAX_SLOTS_PER_USER = 6;

class AppointmentController extends Controller
{
    // Show all available appointments
    public function index(){
        // Retrieve all appointments
        $appointments = Appointment::all();

        // Retrieve the authenticated user
        $user = Auth::user();

        // Show appointments
        return view('appointments.index', compact('appointments', 'user'));
    }

    // Show the admin-only guestlist
    public function guestlist(){
        // Retrieve all appointments
        $appointments = Appointment::all();

        // Retrieve the authenticated user
        $user = Auth::user();

        // Retrieve all user-appointment relationships
        $apptUsers = AppointmentUser::all();

        // If the user is an admin, show guestlist
        // Else redirect to appointments
        return ($user->admin)
            ? view('appointments.guestlist', compact('appointments', 'user', 'apptUsers'))
            : redirect()->route('appointments.index');
    }

    // Show booking or handle request to set booking
    public function book(Request $request, $id){
        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Throw 404 if appointment already started
        if($appointment->start_time < now()) abort(404);
        
        // Get the available slots
        $availableSlots = $appointment->total_slots - $appointment->slots_taken;

        // Retrieve the authenticated user
        $user = Auth::user();

        // Get current user slot number
        $userSlots = $user->slots_booked;
        
        // If it's a GET request, return the book view
        if ($request->isMethod('get')) {
            return view('appointments.book', compact('appointment', 'availableSlots', 'userSlots'));
        }
        
        // If it's a POST request, handle the form submission
        // Validate the form data
        $validatedData = $request->validate([
            'slots' => 'required|integer|min:1|max:'.$availableSlots,
        ]);
        
        // Get the number of slots requested
        $slotsRequested = $validatedData['slots'];
        
        // Check if the requested slots are not available
        if ($slotsRequested > $availableSlots || $slotsRequested == 0 || $slotsRequested+$userSlots > MAX_SLOTS_PER_USER) {
            // Redirect back with an error message
            return redirect()->back();
        }
        
        // Perform the booking logic
        // Update the appointment slots_taken count
        $appointment->slots_taken += $slotsRequested;
        $appointment->save();

        // Update the user slots booked attribute
        $user->slots_booked += $slotsRequested;
        $user->save();

        // Check if the user already booked slots for this appointment
        $apptUserEntryExists = AppointmentUser::where('user_id', $user->id)
            ->where('appointment_id', $appointment->id)
            ->exists();

        // If the user has slots for this appointment
        if ($apptUserEntryExists) {
            // Update slots_taken for the user-appointment
            AppointmentUser::where('user_id', $user->id)
                ->where('appointment_id', $appointment->id)
                ->increment('slots_taken', $slotsRequested);
        // If the user does not have slots for this appointment
        } else {
            // Create a new entry for the user-appointment
            AppointmentUser::create([
                'user_id' => $user->id,
                'appointment_id' => $appointment->id,
                'slots_taken' => $slotsRequested,
            ]);
        }
    
        // Redirect to a confirmation page
        return redirect()->route('appointment.confirmation');
    }

    // Show booking edit or handle request to update an existing booking
    public function edit_booking(Request $request, $id){
        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Throw 404 if appointment already started
        if($appointment->start_time < now()) abort(404);
        
        // Get the available slots
        $availableSlots = $appointment->total_slots - $appointment->slots_taken;

        // Retrieve the authenticated user
        $user = Auth::user();

        // Get current user slot number
        $userSlots = $user->slots_booked;

        // Get current number of slots taken for this appt by this user
        $apptUserSlots = AppointmentUser::where('user_id', $user->id)
            ->where('appointment_id', $appointment->id)
            ->first()
            ->slots_taken;
        
        // If it's a GET request, return the edit booking view
        if ($request->isMethod('get')) {
            return view('appointments.edit_booking', compact('appointment', 'availableSlots', 'userSlots', 'apptUserSlots'));
        }
        
        // If it's a POST request, handle the form submission
        // Validate the form data
        $validatedData = $request->validate([
            'slots' => 'required|integer|min:0|max:'.$availableSlots,
        ]);
        
        // Get the number of slots requested
        $slotsRequested = $validatedData['slots'];
        
        // Check if the requested slots are not available
        if ($slotsRequested-$apptUserSlots > $availableSlots || $slotsRequested+$userSlots-$apptUserSlots > MAX_SLOTS_PER_USER) {
            // Redirect back with an error message
            return redirect()->back();
        // If the user requests to update to 0 slots
        }else if ($slotsRequested == 0){
            // Cancel their booking
            $this->cancel_booking($request, $appointment->id);
            // Redirect to appointments page
            return redirect()->route('appointments.index');
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
    
        // Redirect to a confirmation page
        return redirect()->route('appointment.confirmation');
    }

    // Handle request to cancel a booking
    public function cancel_booking(Request $request, $id){
        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Throw 404 if appointment already started
        if($appointment->start_time < now()) abort(404);
        
        // Get the available slots
        $availableSlots = $appointment->total_slots - $appointment->slots_taken;

        // Retrieve the authenticated user
        $user = Auth::user();

        // Retrieve the appointment user row
        $apptUserEntry = AppointmentUser::where('user_id', $user->id)
            ->where('appointment_id', $appointment->id)
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
            return redirect()->route('dashboard');
        }else{
            // Redirect back with an error message
            return redirect()->back();
        }
    }

    // Show confirmation page
    public function confirmation(){
        // TODO: customize by type of confirmation (initial booking vs update booking)

        // Return the view for the appointment confirmation page
        return view('appointments.confirmation');
    }

    // Show the edit update appointment or handle update form submission
    public function edit(Request $request, $id){
        $user = Auth::user();
        $appointment = Appointment::findOrFail($id);

        // If it's a GET request, return the view
        if($request->isMethod('get')){
            // If the user is an admin, show the edit appointment page
            // Else, redirect to appointments
            return ($user->admin)
                ? view('appointments.edit', compact('appointment'))
                : redirect()->route('appointments.index');
        }

        // If it's a PUT request, handle the form submission
        // Get the validated data
        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'total_slots' => 'required|integer|min:0',
        ]);

        // Update the appointment with the validated data
        $appointment->update($validatedData);
        // Redirect to the same page
        return redirect()->route('appointment.edit', $appointment->id);
    }

    // Show create form or handle create form submission
    public function create(Request $request){        
        // Retrieve the authenticated user
        $user = Auth::user();

        // See if the user is not an admin
        if($user->admin == 0) abort(404);

        // If it's a GET request, return the create view
        if ($request->isMethod('get')) {
            return view('appointments.create');
        }
        
        // Validate the form data
        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'total_slots' => 'required|integer|min:1',
        ]);

        // Create a new appointment instance
        $appointment = new Appointment();
        $appointment->title = $validatedData['title'];
        $appointment->description = $validatedData['description'];
        $appointment->start_time = $validatedData['start_time'];
        $appointment->end_time = $validatedData['end_time'];
        $appointment->total_slots = $validatedData['total_slots'];
        
        // Save the appointment to the database
        $appointment->save();

        // Redirect to a different page, such as the appointment index page
        return redirect()->route('appointments.index');
    }

    // Handle request to delete appointment
    public function delete(Request $request, $id){

        // Retrieve the authenticated user
        $user = Auth::user();

        // See if the user is not an admin
        if($user->admin == 0) abort(404);

        // Retrieve the appointment to be deleted
        $appointment = Appointment::findOrFail($id);

        // Reallocate slots to users who booked the appt if the end time has not passed

        // Retrieve all user-appointment relationships for the appt being deleted
        $userAppointments = AppointmentUser::where('appointment_id', $appointment->id)->get();
        // Handle each individually
        foreach ($userAppointments as $userAppointment) {
            // Retrive a user's ID from the user-appt entry
            $_user_id = $userAppointment->user_id;
            
            // If the appointment is past end
            if(!$appointment->past_end){
            // Give the user the necessary amount of slots back
                $_user = User::findOrFail($_user_id);
                $_user->slots_booked = $_user->slots_booked - $userAppointment->slots_taken;
                $_user->save();
            }
        
            // Delete the user-appointment record
            AppointmentUser::where('appointment_id', $id)
                   ->where('user_id', $_user_id)
                   ->delete();
        }
        
        // Delete the appointment
        $appointment->delete();

        // Redirect to appointments
        return redirect()->route('appointments.index');
    }

    // Give appointment slots back on user delete
    public function userDelete(User $user){

        // Get user appts associated with the user
        $userAppointments = AppointmentUser::where('user_id', $user->id)->get();
        // Handle each individually
        foreach ($userAppointments as $userAppointment) {
            // Retrive a appointment's ID from the user-appt entry
            $_appt_id = $userAppointment->appointment_id;

            // If the appointment is past end
            $appointment = Appointment::findOrFail($_appt_id);
            // Give the appointment the necessary amount of slots back
            if(!$appointment->past_end){
                $appointment->slots_taken -= $userAppointment->slots_taken;
                $appointment->save();
            }
        
            // Delete the user-appointment record
            AppointmentUser::where('appointment_id', $_appt_id)
                   ->where('user_id', $user->id)
                   ->delete();
        }
    }
}
