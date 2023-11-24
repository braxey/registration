<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\WalkIn;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;


class AppointmentController extends Controller
{
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
        if(!$user->admin) abort(404);

        // If it's a PUT request, handle the form submission
        // Get the validated data
        $validatedData = $request->validate([
            'description' => 'required',
            'start_time' => 'required|date',
            'total_slots' => 'required|integer|min:0',
        ]);

        // Update the appointment with the validated data
        $appointment->description = $validatedData['description'];
        $appointment->start_time = $validatedData['start_time'];
        $appointment->end_time = Carbon::parse($validatedData['start_time'])->addHours(1)->format('Y-m-d\TH:i');
        $appointment->total_slots = $validatedData['total_slots'];
        $appointment->walk_in_only = $request->input('walk-in-only') === "on";
        
        // Save the appointment to the database
        $appointment->save();

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
            'description' => 'required',
            'start_time' => 'required|date',
            'total_slots' => 'required|integer|min:1',
        ]);

        // Create a new appointment instance
        $appointment = new Appointment();
        $appointment->description = $validatedData['description'];
        $appointment->start_time = $validatedData['start_time'];
        $appointment->end_time = Carbon::parse($validatedData['start_time'])->addHours(1)->format('Y-m-d\TH:i');
        $appointment->total_slots = $validatedData['total_slots'];
        $appointment->walk_in_only = $request->input('walk-in-only') === "on";
        
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
