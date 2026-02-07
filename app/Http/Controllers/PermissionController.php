<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display permissions management page.
     */
    public function index()
    {
        $websites = Website::where('user_id', auth()->id())
            ->withCount(['articles', 'categories'])
            ->get();

        $permissions = Permission::orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $roles = Role::with('permissions')->get();

        return Inertia::render('SuperAdmin/UserManagement/Permissions', [
            'websites' => $websites,
            'permissions' => $permissions,
            'roles' => $roles,
        ]);
    }

    /**
     * Update role permissions.
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->syncPermissions($validated['permission_ids'] ?? []);

        return redirect()
            ->route('organization.permissions.index')
            ->with('success', 'Role permissions updated successfully.');
    }

    /**
     * Store a new permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group' => 'nullable|string|max:255',
        ]);

        Permission::create($validated);

        return redirect()
            ->route('organization.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group' => 'nullable|string|max:255',
        ]);

        $permission->update($validated);

        return redirect()
            ->route('organization.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()
            ->route('organization.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
