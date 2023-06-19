<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        // Add your logic here to retrieve appointments or perform any other operations
        // Render the appointments view
        $appointments = Appointment::all();
        return view('appointments.index', ['appointments' => $appointments]);
    }

    public function book(Request $request, $id)
    {
        // Find the appointment
        $appointment = Appointment::findOrFail($id);
        
        // Get the available slots
        $availableSlots = $appointment->total_slots - $appointment->slots_taken;
        
        // If it's a GET request, return the book view
        if ($request->isMethod('get')) {
            return view('appointments.book', compact('appointment', 'availableSlots'));
        }
        
        // If it's a POST request, handle the form submission
        // Validate the form data
        $validatedData = $request->validate([
            'slots' => 'required|integer|min:1|max:'.$availableSlots,
        ]);
        
        // Get the number of slots requested
        $slotsRequested = $validatedData['slots'];
        
        // Check if the requested slots are available
        if ($slotsRequested > $availableSlots) {
            // Redirect back with an error message
            return redirect()->back()->with('error', 'The requested number of slots is not available.');
        }
        
        // Perform the booking logic
        // Update the appointment slots_taken count
        $appointment->slots_taken += $slotsRequested;
        $appointment->save();

        // Create a pivot table entry or perform any other necessary actions
        // Retrieve the authenticated user
        $user = Auth::user();
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
        return redirect()->route('appointment.confirmation')->with('success', 'Booking successful!');
    }

    public function confirmation()
    {
        // Retrieve the appointment and any other necessary data
        // Perform any additional logic or data retrieval here

        // Return the view for the appointment confirmation page
        return view('appointments.confirmation');
    }


    public function edit(Appointment $appointment)
    {
        $user = Auth::user();
        if($user->admin){
            return view('appointments.edit');
        }else{
            return redirect()->route('appointments.index');
        }        
    }

    public function update(Request $request, Appointment $appointment)
    {
        // Retrieve the authenticated user
        

        // Update appointment logic
    }
}
