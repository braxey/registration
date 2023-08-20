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
        $walkIns = WalkIn::all();
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

        // Strip away non-digit characters from the phone number
        $data['phone_number'] = preg_replace('/\D/', '', $data['phone_number']);

        // Validate the modified input
        $validatedData = Validator::make($data, [
            'name' => 'required',
            'phone_number' => 'required|digits:10',
            'desired_time' => 'required|date',
            'slots' => 'required|integer|min:0',
        ])->validate();

        // Create a new walk-in instance
        $walkIn = new WalkIn();
        $walkIn->name = $validatedData['name'];
        $walkIn->phone_number = $validatedData['phone_number'];
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

        // Strip away non-digit characters from the phone number
        $data['phone_number'] = preg_replace('/\D/', '', $data['phone_number']);

        // Validate the modified input
        $validatedData = Validator::make($data, [
            'name' => 'required',
            'phone_number' => 'required|digits:10',
            'desired_time' => 'required|date',
            'slots' => 'required|integer|min:0',
        ])->validate();

        // Create a new walk-in instance
        $walkIn->name = $validatedData['name'];
        $walkIn->phone_number = $validatedData['phone_number'];
        $walkIn->desired_time = $validatedData['desired_time'];
        $walkIn->slots = $validatedData['slots'];
        
        // Save the walk-in to the database
        $walkIn->save();

        // Redirect to a different page, such as the walk-in waitlist page
        return redirect()->route('walk-in.edit-form', $walkIn->id);
    }

    public function deleteWalkin(Request $request, $id)
    {
        $walkIn = WalkIn::findOrFail($id);

        // Delete the walk-in
        $walkIn->delete();

        // Redirect to appointments
        return redirect()->route('walk-in.show-waitlist');
    }
}
