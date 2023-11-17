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
    // Show all available appointments
    public function index(Request $request){
        // Retrieve the authenticated user
        $user = Auth::user();

        // Get time between
        $between = $this->getBetween([
            'start_date' => $request->input('start_date'),
            'start_time' => $request->input('start_time'),
            'end_date'   => $request->input('end_date'),
            'end_time'   => $request->input('end_time'),
        ]);

        // Retrieve all appointments
        $appointments = Appointment::orderByRaw("
                CASE
                    WHEN status = 'in progress' THEN 1
                    WHEN status = 'upcoming' THEN 2
                    WHEN status = 'completed' THEN 3
                    ELSE 3
                END
            ")
            ->orderByRaw("
                CASE WHEN status = 'completed' THEN start_time END DESC
            ")
            ->orderByRaw("
                CASE WHEN status = 'upcoming' THEN start_time END ASC
            ")
            ->orderByRaw("
                CASE WHEN status = 'in progress' THEN start_time END ASC
            ")
            ->get()->filter(function (Appointment $appointment) use ($user, $between) {
                $apptTime = Carbon::parse($appointment->start_time);
                if ($user) {
                    $allowed = $appointment->isOpen() || $user->admin;
                } else {
                    $allowed = $appointment->isOpen() && !$appointment->isWalkInOnly();
                }

                if (isset($between['start'])) {
                    $allowed = $allowed && $apptTime->gte($between['start']);
                }
                if (isset($between['end'])) {
                    $allowed = $allowed && $between['end']->gte($apptTime);
                }

                return $allowed;
            });

        // Retrieve the organization
        $organization = Organization::findOrFail(1);

        // Show appointments
        return view('appointments.index', compact('appointments', 'user', 'organization'));
    }

    // Show the admin-only guestlist
    public function guestlist(Request $request){

        $user = Auth::user();
        if(!$user->admin) abort(404);
        
        $firstName = $request->input('first_name');
        $lastName  = $request->input('last_name');
        $startDate = $request->input('start_date');
        $startTime = $request->input('start_time');
        $status = $request->input('status');

        $guests = AppointmentUser::query()
            ->join('appointments', 'appointments.id', '=', 'appointment_user.appointment_id')
            ->join('users', 'users.id', '=', 'appointment_user.user_id');

        $walkIns = WalkIn::query()
            ->leftJoin('appointments', 'walk_ins.appointment_id', '=', 'appointments.id')
            ->whereNotNull('walk_ins.appointment_id');

        // Get data from the $guests and $walkIns collections
        $guestsData = $guests->get(['appointments.start_time', 'appointments.status', 'users.first_name', 'users.last_name', 'appointment_user.slots_taken', 'appointment_user.showed_up', 'appointment_user.id']);
        $walkInsData = $walkIns->get(['start_time', 'status', 'name', 'slots', 'slots AS showed_up', 'walk_ins.id']); // For walk-ins, showed_up equals slots

        // Add an additional 'is_walk_in' field to walk-in data
        $walkInsDataWithFlag = $walkInsData->map(function ($item) {
            $item['is_walk_in'] = true;
            return $item;
        });

        // Add an additional 'is_walk_in' field to guests data
        $guestsDataWithFlag = $guestsData->map(function ($item) {
            $item['is_walk_in'] = false;
            return $item;
        });

        // Merge the data into a single collection
        $combinedData = new Collection([...$walkInsDataWithFlag, ...$guestsDataWithFlag]);

        $filteredData = $combinedData
            ->when($firstName || $lastName, function ($query) use ($firstName, $lastName) {
                return $query->filter(function ($item) use ($firstName, $lastName) {
                    if (!empty($item['name'])) {
                        $name = strtolower($item['name']);
                        $needle = rtrim(strtolower($firstName . ' ' . $lastName));
                        return strpos($name, $needle) !== false;
                    } else {
                        $first = strtolower($item['first_name']);
                        $last = strtolower($item['last_name']);
                        return $first == strtolower($firstName) || $last == strtolower($lastName);
                    }
                });
            })
            ->when($startDate || $startTime, function ($query) use ($startDate, $startTime) {
                return $query->filter(function ($item) use ($startDate, $startTime) {
                    $date = explode(' ', $item['start_time'])[0];
                    $time = explode(' ', $item['start_time'])[1];
                    if (!$startDate) {
                        return strpos($time, $startTime) !== false;
                    } else if (!$startTime) {
                        return $date == $startDate;
                    } else {
                        return strpos($time, $startTime) !== false && $date == $startDate;
                    }
                });
            })
            ->when($status, function ($query) use ($status) {
                return $query->filter(function ($item) use ($status) {
                    return $item['status'] === $status;
                });
            });

        // Combine results using UNION
        $guests = $filteredData->sortBy(function ($item) {
            $statusOrder = [
                'in progress' => 1,
                'upcoming' => 2,
                'completed' => 3,
            ];
            
            $status = $item['status'];
            $startTime = $item['start_time'];
        
            return [
                'status_order' => $statusOrder[$status],
                'completed_order' => $status === 'completed' ? -strtotime($startTime) : PHP_INT_MAX,
                'upcoming_order' => $status === 'upcoming' ? strtotime($startTime) : PHP_INT_MAX,
                'in_progress_order' => $status === 'in progress' ? strtotime($startTime) : PHP_INT_MAX,
            ];
        });


        $totalSlotsTaken = $guests->sum('slots') + $guests->sum('slots_taken');
        $totalShowedUp = $guests->sum('showed_up');


        return ($user->admin)
            ? view('appointments.guestlist', compact('guests', 'totalSlotsTaken', 'totalShowedUp'))
            : redirect()->route('appointments.index');
    }

    // Update the showed_up field for appt_user from the guestlist
    public function update_guestlist(Request $request){
        
        $user = Auth::user();
        if(!$user->admin) return redirect()->route('appointments.index');

        $request->validate([
            'guest_id' => 'required|integer',
            'showed_up' => 'required|integer|min:0',
        ]);

        $guest = AppointmentUser::findOrFail($request->input('guest_id'));
        $before = $guest->showed_up;
        $guest->showed_up = $request->input('showed_up');
        $guest->save();

        return response()->json([
            'countChange' => ($request->input('showed_up') - $before)
        ]);
    }

    // Show booking or handle request to set booking
    public function book(Request $request, $id){
        // Find the organization
        $organization = Organization::findOrFail(1);

        // Redirect to appointments if registration is closed
        if(!$organization->registration_open) return redirect()->route('appointments.index');

        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Throw 404 if passed noon on day of appointment or appt is not meant to be booked
        if(
            now() > Carbon::parse($appointment->start_time)->setTime(12, 0, 0)
            || $appointment->isWalkInOnly()
        ) {
            abort(404);
        }
        
        // Get the available slots
        $availableSlots = $appointment->total_slots - $appointment->slots_taken;

        // Retrieve the authenticated user
        $user = Auth::user();

        // Get MAX_SLOTS_PER_USER
        $MAX_SLOTS_PER_USER = $organization->max_slots_per_user;

        // Get current user slot number
        $userSlots = $user->slots_booked;
        
        // If it's a GET request, return the book view
        if ($request->isMethod('get')) {
            return view('appointments.book', compact('appointment', 'availableSlots', 'userSlots', 'organization'));
        }
        
        // If it's a POST request, handle the form submission
        // Validate the form data
        $validatedData = $request->validate([
            'slots' => 'required|integer|min:1|max:'.$availableSlots,
        ]);
        
        // Get the number of slots requested
        $slotsRequested = $validatedData['slots'];
        
        // Check if the requested slots are not available
        if ($slotsRequested > $availableSlots || $slotsRequested == 0 || $slotsRequested+$userSlots > $MAX_SLOTS_PER_USER) {
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
    
        // Redirect to a dashboard
        return redirect()->route('dashboard');
    }

    // Show booking edit or handle request to update an existing booking
    public function edit_booking(Request $request, $id){
        // Find the organization
        $organization = Organization::findOrFail(1);

        // Redirect to appointments if registration is closed
        if(!$organization->registration_open) return redirect()->route('appointments.index');

        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Throw 404 if passed noon on day of appointment or appt is not meant to be booked
        if(
            now() > Carbon::parse($appointment->start_time)->setTime(12, 0, 0)
            || $appointment->isWalkInOnly()
        ) {
            abort(404);
        }
        
        // Get the available slots
        $availableSlots = $appointment->total_slots - $appointment->slots_taken;

        // Retrieve the authenticated user
        $user = Auth::user();

        // Get MAX_SLOTS_PER_USER
        $MAX_SLOTS_PER_USER = $organization->max_slots_per_user;

        // Get current user slot number
        $userSlots = $user->slots_booked;

        // Get current number of slots taken for this appt by this user
        $apptUserSlots = AppointmentUser::where('user_id', $user->id)
            ->where('appointment_id', $appointment->id)
            ->first()
            ->slots_taken;
        
        // If it's a GET request, return the edit booking view
        if ($request->isMethod('get')) {
            return view('appointments.edit_booking', compact('appointment', 'availableSlots', 'userSlots', 'apptUserSlots', 'organization'));
        }
        // If it's a POST request, handle the form submission
        // Validate the form data
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
    
        // Redirect to a dashboard
        return redirect()->route('dashboard');
    }

    // Handle request to cancel a booking
    public function cancel_booking(Request $request, $id){
        // Find the organization
        $organization = Organization::findOrFail(1);

        // Abort if registration is closed
        if(!$organization->registration_open) abort(404);

        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Throw 404 if passed noon on day of appointment or appt is not meant to be booked
        if(
            now() > Carbon::parse($appointment->start_time)->setTime(12, 0, 0)
            || $appointment->isWalkInOnly()
        ) {
            abort(404);
        }
        
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

    private function getBetween(array $arr): array
    {
        $container = [];

        try {
            if (isset($arr['start_date'])) {
                if (isset($arr['start_time'])) {
                    $dateTimeString = $arr['start_date'] . ' ' . $arr['start_time'];
                } else {
                    $dateTimeString = $arr['start_date'] . ' 00:00';
                }
    
                $container['start'] = Carbon::parse($dateTimeString);
            }
        } catch (Exception $e) {
            unset($container['start']);
        }

        try {
            if (isset($arr['end_date'])) {
                if (isset($arr['end_time'])) {
                    $dateTimeString = $arr['end_date'] . ' ' . $arr['end_time'];
                } else {
                    $dateTimeString = $arr['end_date'] . ' 23:59';
                }
    
                $container['end'] = Carbon::parse($dateTimeString);
            }
        } catch (Exception $e) {
            unset($container['end']);
        }
        
        return $container;
    }
}
