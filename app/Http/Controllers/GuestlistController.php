<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\AppointmentUser;
use App\Models\WalkIn;

/**
 * @see GuestlistControllerTest
 * TODO: Implement tests for this controller
 */
class GuestlistController extends Controller
{
    public function getGuestlist(Request $request)
    {
        $firstName = $request->input('first_name');
        $lastName  = $request->input('last_name');
        $startDate = $request->input('start_date');
        $startTime = $request->input('start_time');
        $status    = $request->input('status');

        $guests = AppointmentUser::query()
            ->join('appointments', 'appointments.id', '=', 'appointment_user.appointment_id')
            ->join('users', 'users.id', '=', 'appointment_user.user_id');

        $walkIns = WalkIn::query()
            ->leftJoin('appointments', 'walk_ins.appointment_id', '=', 'appointments.id')
            ->whereNotNull('walk_ins.appointment_id');

        // Get data from the $guests and $walkIns collections
        $guestsData = $guests->get(['appointments.start_time', 'appointments.status', 'users.first_name', 'users.last_name', 'appointment_user.slots_taken', 'appointment_user.showed_up', 'appointment_user.id']);
        $walkInsData = $walkIns->get(['start_time', 'status', 'name', 'slots', 'slots AS showed_up', 'walk_ins.id', 'walk_ins.notes']); // For walk-ins, showed_up equals slots

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
                'upcoming'    => 2,
                'completed'   => 3,
            ];
            
            $status = $item['status'];
            $startTime = $item['start_time'];
        
            return [
                'status_order'      => $statusOrder[$status],
                'completed_order'   => $status === 'completed' ? -strtotime($startTime) : PHP_INT_MAX,
                'upcoming_order'    => $status === 'upcoming' ? strtotime($startTime) : PHP_INT_MAX,
                'in_progress_order' => $status === 'in progress' ? strtotime($startTime) : PHP_INT_MAX,
            ];
        });

        $totalSlotsTaken = $guests->sum('slots') + $guests->sum('slots_taken');
        $totalShowedUp = $guests->sum('showed_up');

        return view('appointments.guestlist', compact('guests', 'totalSlotsTaken', 'totalShowedUp'));
    }

    public function updateGuestlist(Request $request)
    {
        $payload = $request->validate([
            'guest_id'  => 'required|integer',
            'showed_up' => 'required|integer|min:0',
        ]);

        $guest = AppointmentUser::find($payload['guest_id']);
        if ($guest === null) {
            return response(null, 404);
        }

        $before = $guest->getShowedUp();
        $guest->setShowedUp($payload['showed_up']);

        return response()->json([
            'countChange' => ($payload['showed_up'] - $before)
        ]);
    }
}
