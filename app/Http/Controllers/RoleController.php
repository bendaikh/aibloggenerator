<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $websites = auth()->user()->accessibleWebsitesQuery()
            ->withCount(['articles', 'categories'])
            ->get();

        $roles = Role::withCount(['users', 'permissions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('SuperAdmin/UserManagement/Roles', [
            'websites' => $websites,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Role::create($validated);

        return redirect()
            ->route('organization.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $role->update($validated);

        return redirect()
            ->route('organization.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()
                ->route('organization.roles.index')
                ->with('error', 'Cannot delete role that has users assigned to it.');
        }

        $role->delete();

        return redirect()
            ->route('organization.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
