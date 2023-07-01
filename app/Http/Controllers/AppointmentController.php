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
    public function index()
    {
        // Add your logic here to retrieve appointments or perform any other operations
        // Render the appointments view
        $appointments = Appointment::all();
        $user = Auth::user();
        return view('appointments.index', ['appointments' => $appointments, 'user' => $user]);
    }

    public function book(Request $request, $id)
    {
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
        
        // Check if the requested slots are available
        if ($slotsRequested > $availableSlots || $slotsRequested == 0 || $slotsRequested+$userSlots > MAX_SLOTS_PER_USER) {
            // Redirect back with an error message
            return redirect()->back();
        }
        
        // Perform the booking logic
        // Update the appointment slots_taken count
        $appointment->slots_taken += $slotsRequested;
        $appointment->save();

        // Create a pivot table entry or perform any other necessary actions
        $user->slots_booked += $slotsRequested;
        $user->save();

        $apptUserEntryExists = AppointmentUser::where('user_id', $user->id)
            ->where('appointment_id', $appointment->id)
            ->exists();

        if ($apptUserEntryExists) {
            // Update slots_taken
            AppointmentUser::where('user_id', $user->id)
                ->where('appointment_id', $appointment->id)
                ->increment('slots_taken', $slotsRequested);
        } else {
            // Create a new entry
            AppointmentUser::create([
                'user_id' => $user->id,
                'appointment_id' => $appointment->id,
                'slots_taken' => $slotsRequested,
            ]);
        }
    
        // Redirect to a confirmation page or any other relevant page
        return redirect()->route('appointment.confirmation');
    }

    public function edit_booking(Request $request, $id)
    {
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
        
        // If it's a GET request, return the book view
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
        
        // Check if the requested slots are available
        if ($slotsRequested-$apptUserSlots > $availableSlots || $slotsRequested+$userSlots-$apptUserSlots > MAX_SLOTS_PER_USER) {
            // Redirect back with an error message
            return redirect()->back();
        }else if ($slotsRequested == 0){
            $this->cancel_booking($request, $appointment->id);
            return redirect()->route('appointments.index');
        }
        
        // Perform the booking logic
        // Update the appointment slots_taken count
        $appointment->slots_taken += $slotsRequested - $apptUserSlots;
        $appointment->save();

        // Create a pivot table entry or perform any other necessary actions
        $user->slots_booked += $slotsRequested - $apptUserSlots;
        $user->save();

        $apptUserEntryExists = AppointmentUser::where('user_id', $user->id)
            ->where('appointment_id', $appointment->id)
            ->exists();

        if ($apptUserEntryExists) {
            // Update slots_taken
            AppointmentUser::where('user_id', $user->id)
                ->where('appointment_id', $appointment->id)
                ->increment('slots_taken', $slotsRequested-$apptUserSlots);
        }
    
        // Redirect to a confirmation page or any other relevant page
        return redirect()->route('appointment.confirmation');
    }


    public function cancel_booking(Request $request, $id)
    {
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

        if($apptUserEntry){
            // Get slots to return
            $slotsToReturn = $apptUserEntry->slots_taken;

            // Decrement appt slots taken and user slots booked
            $appointment->slots_taken -= $slotsToReturn;
            $appointment->save();
            $user->slots_booked -= $slotsToReturn;
            $user->save();

            // Remove entry from appt user table
            AppointmentUser::where('user_id', $user->id)
            ->where('appointment_id', $appointment->id)
            ->delete();
        
            // Refresh the page
            return redirect()->route('dashboard');
        }else{
            // Redirect back with an error message
            return redirect()->back();
        }
    }


    public function confirmation()
    {
        // Retrieve the appointment and any other necessary data
        // Perform any additional logic or data retrieval here

        // Return the view for the appointment confirmation page
        return view('appointments.confirmation');
    }


    public function edit(Appointment $appointment, $id)
    {
        $user = Auth::user();
        $appointment = Appointment::findOrFail($id);
        if($user->admin){
            return view('appointments.edit', ['appointment' => $appointment]);
        }else{
            return redirect()->route('appointments.index');
        }        
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'total_slots' => 'required|integer|min:0',
        ]);

        $appointment->update($validatedData);
        return redirect()->route('appointment.edit', $appointment->id);
    }

    public function create(Request $request)
    {        
        // Retrieve the authenticated user
        $user = Auth::user();

        // See if the user is an admin
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

    public function delete(Request $request, $id){
        // Retrieve the authenticated user
        $user = Auth::user();
        // See if the user is an admin
        if($user->admin == 0) abort(404);

        $appointment = Appointment::findOrFail($id);

        // reallocate slots to users who booked the appt if the end time has not passed
        $userAppointments = AppointmentUser::where('appointment_id', $appointment->id)->get();
        foreach ($userAppointments as $userAppointment) {
            $_user_id = $userAppointment->user_id;
            
            if(!$appointment->past_end){
            // Access the related user and update their slots
                $_user = User::findOrFail($_user_id);
                $_user->slots_booked = $_user->slots_booked - $userAppointment->slots_taken;
                $_user->save();
            }
        
            // Delete the user_appointment record
            AppointmentUser::where('appointment_id', $id)
                   ->where('user_id', $_user_id)
                   ->delete();
        }
        
        $appointment->delete();
        return redirect()->route('appointments.index');
    }

    // Give appointment slots back on user delete
    public function userDelete(User $user){

        // Get user appts associated with the user
        $userAppointments = AppointmentUser::where('user_id', $user->id)->get();

        foreach ($userAppointments as $userAppointment) {

            // Give slots back to user
            $_appt_id = $userAppointment->appointment_id;
            $appointment = Appointment::findOrFail($_appt_id);
            if(!$appointment->past_end){
                $appointment->slots_taken -= $userAppointment->slots_taken;
                $appointment->save();
            }
        
            // Delete the user_appointment record
            AppointmentUser::where('appointment_id', $_appt_id)
                   ->where('user_id', $user->id)
                   ->delete();
        }
    }
}
