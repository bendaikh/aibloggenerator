<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    /**
     * Toggle theme public/private status.
     */
    public function togglePublic(Theme $theme)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $theme->update([
            'is_public' => !$theme->is_public
        ]);

        return redirect()->back()->with('success', 'Theme visibility updated successfully!');
    }

    /**
     * Display all themes for superadmin management.
     */
    public function index()
    {
        // Only superadmins can access this
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $themes = Theme::withCount('websites')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Themes/Index', [
            'themes' => $themes,
        ]);
    }

    /**
     * Store a newly created theme.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:themes,slug',
            'description' => 'nullable|string',
            'show_recipe_sections' => 'boolean',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $theme = Theme::create($validated);

        return redirect()->back()->with('success', 'Theme created successfully!');
    }

    /**
     * Update the specified theme.
     */
    public function update(Request $request, Theme $theme)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:themes,slug,' . $theme->id,
            'description' => 'nullable|string',
            'show_recipe_sections' => 'boolean',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $theme->update($validated);

        return redirect()->back()->with('success', 'Theme updated successfully!');
    }

    /**
     * Remove the specified theme.
     */
    public function destroy(Theme $theme)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if theme is being used by any websites
        if ($theme->websites()->count() > 0) {
            return redirect()->back()->withErrors([
                'error' => 'Cannot delete theme that is currently in use by websites.'
            ]);
        }

        $theme->delete();

        return redirect()->back()->with('success', 'Theme deleted successfully!');
    }
}

