<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $websites = Website::where('user_id', auth()->id())
            ->withCount(['articles', 'categories'])
            ->get();

        $users = User::with('roleRelation')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $roles = Role::orderBy('display_name')->get();

        return Inertia::render('SuperAdmin/UserManagement/Users', [
            'websites' => $websites,
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Display the specified user profile.
     */
    public function show(User $user)
    {
        $user->load('roleRelation');

        $userWebsites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        $userArticlesCount = $user->articles()->count();

        return Inertia::render('SuperAdmin/UserManagement/UserShow', [
            'user' => $user,
            'userWebsites' => $userWebsites,
            'userArticlesCount' => $userArticlesCount,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
        ]);

        return redirect()
            ->route('organization.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()
            ->route('organization.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('organization.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('organization.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
