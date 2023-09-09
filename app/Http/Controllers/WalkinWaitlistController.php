<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\WalkIn;
use App\Models\AppointmentUser;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WalkinWaitlistController extends Controller
{
    // edit
    // Show the admin-only guestlist
    public function getWaitlist(Request $request){
        $walkIns = WalkIn::orderBy('created_at', 'desc')->get();
        return view('appointments.walkin-waitlist', compact('walkIns'));
    }

    public function getCreateWalkinForm()
    {
        return view('appointments.create-walkin');
    }

    public function createWalkin(Request $request)
    {
        // Get the input data from the request
        $data = $request->all();

        // Validate the modified input
        $validatedData = Validator::make($data, [
            'name' => 'required',
            'email' => 'required|email',
            'desired_time' => 'required|date',
            'slots' => 'required|integer|min:0',
        ])->validate();

        // Create a new walk-in instance
        $walkIn = new WalkIn();
        $walkIn->name = $validatedData['name'];
        $walkIn->email = $validatedData['email'];
        $walkIn->desired_time = $validatedData['desired_time'];
        $walkIn->slots = $validatedData['slots'];
        
        // Save the walk-in to the database
        $walkIn->save();

        // Redirect to a different page, such as the walk-in waitlist page
        $walkIns = WalkIn::all();
        return redirect(route('walk-in.show-waitlist'));
    }

    public function getEditWalkinForm(Request $request, $id)
    {
        $walkIn = WalkIn::findOrFail($id);
        return view('appointments.edit-walkin', compact('walkIn'));
    }

    public function editWalkin(Request $request, $id)
    {
        $walkIn = WalkIn::findOrFail($id);

        // Get the input data from the request
        $data = $request->all();

        // Validate the modified input
        $validatedData = Validator::make($data, [
            'name' => 'required',
            'email' => 'required|email',
            'desired_time' => 'required|date',
            'slots' => 'required|integer|min:0',
        ])->validate();

        if (
            $walkIn->slots !== $validatedData['slots']
            && $walkIn->appointment_id !== null
        ) {
            $appt = Appointment::find($walkIn->appointment_id);
            $updatedSlotsTaken = $appt->slots_taken - $walkIn->slots + $validatedData['slots'];
            if ($updatedSlotsTaken > $appt->total_slots) {
                $appt->total_slots = $updatedSlotsTaken;
            }
            $appt->slots_taken = $updatedSlotsTaken;
            $appt->save();
        }

        // Update walk-in instance
        $walkIn->name = $validatedData['name'];
        $walkIn->email = $validatedData['email'];
        $walkIn->desired_time = $validatedData['desired_time'];
        $walkIn->slots = $validatedData['slots'];
        
        // Save the walk-in
        $walkIn->save();

        // Redirect to the walk-in waitlist page
        return redirect()->route('walk-in.edit-form', $walkIn->id);
    }

    public function deleteWalkin(Request $request, $id)
    {
        $walkIn = WalkIn::findOrFail($id);

        if ($walkIn->appointment_id !== null) {
            $appt = Appointment::find($walkIn->appointment_id);
            $appt->removeWalkIn($walkIn);
        }

        // Delete the walk-in
        $walkIn->delete();

        // Redirect to appointments
        return redirect()->route('walk-in.show-waitlist');
    }

    public function getApptLinkPage(Request $request, $id)
    {
        $nonCompletedAppointments = Appointment::where('status', '<>', 'completed')
            ->orderByRaw("
                CASE
                    WHEN status = 'in progress' THEN 1
                    WHEN status = 'upcoming' THEN 2
                    ELSE 3
                END
            ")
            ->orderByRaw("
                CASE WHEN status = 'upcoming' THEN start_time END ASC
            ")
            ->orderByRaw("
                CASE WHEN status = 'in progress' THEN start_time END ASC
            ")
            ->get();
        return view('appointments.appt-walk-in-link', compact('id', 'nonCompletedAppointments'));
    }

    public function linkAppointment(Request $request, $walkInId, $apptId)
    {
        $walkIn = WalkIn::find($walkInId);
        $appt   = Appointment::find($apptId);
        if (is_null($walkIn->appointment_id)) {
            $appt->addWalkIn($walkIn);
        } else if ($apptId == $walkIn->appointment_id) {
            return redirect(route('walk-in.show-waitlist'));
        } else {
            $currAppt = Appointment::find($walkIn->appointment_id);
            $currAppt->removeWalkIn($walkIn);
            $appt->addWalkIn($walkIn);
        }
        $walkIn->appointment_id = $apptId;
        $walkIn->save();
        return redirect(route('walk-in.show-waitlist'));
    }

    public function unlinkAppointment(Request $request, $walkInId, $apptId)
    {
        $walkIn = WalkIn::find($walkInId);
        $appt   = Appointment::find($apptId);
        if (is_null($walkIn->appointment_id) || $walkIn->appointment_id != $apptId) {
            return redirect(route('walk-in.show-waitlist'));
        }

        $appt->removeWalkIn($walkIn);
        $walkIn->appointment_id = null;
        $walkIn->save();
        return redirect(route('walk-in.show-waitlist'));
    }
}
