<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Display a listing of the pages.
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get all pages for the user's websites
        $pages = Page::whereHas('website', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with('website')
        ->orderBy('created_at', 'desc')
        ->get();

        // Get user's websites for the create form
        $websites = Website::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('SuperAdmin/Pages', [
            'pages' => $pages,
            'websites' => $websites
        ]);
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'website_id' => 'required|exists:websites,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
        ]);

        // Verify the website belongs to the authenticated user
        $website = Website::findOrFail($validated['website_id']);
        if ($website->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $page = Page::create($validated);

        return redirect()->route('superadmin.pages.index')
            ->with('success', 'Page created successfully!');
    }

    /**
     * Update the specified page in storage.
     */
    public function update(Request $request, Page $page)
    {
        // Verify the page's website belongs to the authenticated user
        if ($page->website->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
        ]);

        $page->update($validated);

        return redirect()->route('superadmin.pages.index')
            ->with('success', 'Page updated successfully!');
    }

    /**
     * Remove the specified page from storage.
     */
    public function destroy(Page $page)
    {
        // Verify the page's website belongs to the authenticated user
        if ($page->website->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $page->delete();

        return redirect()->route('superadmin.pages.index')
            ->with('success', 'Page deleted successfully!');
    }
}

