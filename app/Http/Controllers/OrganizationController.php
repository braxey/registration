<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id){
        $user = Auth::user();
        $organization = Organization::findOrFail($id);

        // If it's a GET request, return the view
        if($request->isMethod('get')){
            // If the user is an admin, show the edit appointment page
            // Else, redirect to appointments
            return ($user->admin)
                ? view('organizations.edit_org', compact('organization'))
                : redirect()->route('appointments.index');
        }
        if(!$user->admin) abort(404);

        // If it's a PUT request, handle the form submission
        // Get the validated data
        $validatedData = $request->validate([
            'org_name' => 'required',
            'max_slots_per_user' => 'required|min:0',
        ]);

        // Update the appointment with the validated data
        $organization->update($validatedData);
        // Redirect to the same page
        return redirect()->route('organization.edit', $organization->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request){
        
    }

}
