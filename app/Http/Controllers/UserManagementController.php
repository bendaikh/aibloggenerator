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
    public function index(Request $request)
    {
        $websites = Website::where('user_id', auth()->id())
            ->withCount(['articles', 'categories'])
            ->get();

        $status = $request->get('status', 'all');
        
        $query = User::with('roleRelation')
            ->orderBy('created_at', 'desc');
        
        // Filter by status if specified
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $users = $query->paginate(20)->appends(['status' => $status]);

        $roles = Role::orderBy('display_name')->get();
        
        // Get counts for each status
        $statusCounts = [
            'all' => User::count(),
            'pending' => User::where('status', 'pending')->count(),
            'approved' => User::where('status', 'approved')->count(),
            'declined' => User::where('status', 'declined')->count(),
        ];

        return Inertia::render('SuperAdmin/UserManagement/Users', [
            'websites' => $websites,
            'users' => $users,
            'roles' => $roles,
            'currentStatus' => $status,
            'statusCounts' => $statusCounts,
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

    /**
     * Approve a pending user.
     */
    public function approve(User $user)
    {
        if ($user->status !== 'pending') {
            return redirect()
                ->route('organization.users.index')
                ->with('error', 'User is not in pending status.');
        }

        $user->update(['status' => 'approved']);

        return redirect()
            ->route('organization.users.index', ['status' => 'pending'])
            ->with('success', 'User approved successfully.');
    }

    /**
     * Decline a pending user.
     */
    public function decline(User $user)
    {
        if ($user->status !== 'pending') {
            return redirect()
                ->route('organization.users.index')
                ->with('error', 'User is not in pending status.');
        }

        $user->update(['status' => 'declined']);

        return redirect()
            ->route('organization.users.index', ['status' => 'pending'])
            ->with('success', 'User declined successfully.');
    }
}
