<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\WalkIn;
use App\Models\AppointmentUser;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
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
        // Validate the form data
        $validatedData = $request->validate([
            'name' => 'required',
            'phone_number' => 'required|regex:/^[0-9]{10}$/',
            'desired_time' => 'required|date',
            'slots' => 'required|integer|min:0',
        ]);

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
}
