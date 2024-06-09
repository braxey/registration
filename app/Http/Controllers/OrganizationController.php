<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

/**
 * @see OrganizationControllerTest
 */
class OrganizationController extends Controller
{
    public function getEditPage(Request $request)
    {
        $organization = Organization::find($request->route('organizationId'));
        if ($organization === null) {
            return response(null, 404);
        }

        return view('organizations.edit', [
            'organization' => $organization
        ]);
    }

    public function update(Request $request)
    {
        $organization = Organization::find($request->route('organizationId'));
        if ($organization === null) {
            return response(null, 404);
        }

        $validatedData = $request->validate([
            'org_name'           => 'required|min:1',
            'max_slots_per_user' => 'required|min:1|integer',
        ]);
        $organization->update($validatedData);

        return redirect()->route('organization.get-edit', $organization->getId());
    }

    public function toggleRegistration(Request $request)
    {
        $organization = Organization::find($request->route('organizationId'));
        if ($organization === null) {
            return response(null, 404);
        }

        $organization->toggleRegistration();

        return redirect()->route('organization.get-edit', $organization->getId());
    }
}
