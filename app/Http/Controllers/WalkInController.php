<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\WalkIn;
use Carbon\Carbon;


class WalkInController extends Controller
{
    public function getWaitlist(Request $request)
    {
        return view('appointments.walkin-waitlist', [
            'walkIns' => WalkIn::orderBy('created_at', 'desc')->get()
        ]);
    }

    public function getCreateWalkinPage()
    {
        return view('appointments.create-walkin');
    }

    public function createWalkin(Request $request)
    {
        $validatedData = $request->validate([
            'name'         => 'required',
            'desired_time' => 'required|date',
            'slots'        => 'required|integer|min:1',
            'notes'        => 'nullable|string'
        ]);

        $email = $request->get('email');
        if ($email !== null) {
            $validatedData = array_merge($validatedData,
                $request->validate([
                    'email' => 'required|email',
                ])
            );
        }

        WalkIn::create($validatedData);
        return redirect()->route('walk-in.show-waitlist');
    }

    public function getEditWalkinPage(Request $request)
    {
        return view('appointments.edit-walkin', [
            'walkIn' => $request->offsetGet('walk-in')
        ]);
    }

    public function updateWalkin(Request $request)
    {
        $walkIn = $request->offsetGet('walk-in');

        $validatedData = $request->validate([
            'name'         => 'required',
            'desired_time' => 'required|date',
            'slots'        => 'required|integer|min:1',
            'notes'        => 'nullable|string',
        ]);

        $email = $request->get('email');
        if ($email !== null) {
            $validatedData = array_merge($validatedData,
                $request->validate([
                    'email' => 'required|email',
                ])
            );
        } else {
            $validatedData = array_merge($validatedData, [
                'email' => ''
            ]);
        }

        $appointment = $walkIn->getAppointment();
        if (
            $walkIn->getNumberOfSlots() != $validatedData['slots']
            && $appointment !== null
        ) {
            $updatedSlotsTaken = $appointment->getSlotsTaken() - $walkIn->getNumberOfSlots() + $validatedData['slots'];
            if ($updatedSlotsTaken > $appointment->getTotalSlots()) {
                $appointment->setTotalSlots($updatedSlotsTaken);
            }
            $appointment->setSlotsTaken($updatedSlotsTaken);
        }

        $walkIn->update($validatedData);
        return redirect()->route('walk-in.get-edit', $walkIn->getId());
    }

    public function deleteWalkin(Request $request)
    {
        $walkIn = $request->offsetGet('walk-in');
        $appointment = $walkIn->getAppointment();

        if ($appointment !== null) {
            $appointment->removeWalkIn($walkIn);
        }

        $walkIn->delete();
        return redirect()->route('walk-in.show-waitlist');
    }
}
