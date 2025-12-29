<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get common data for views (websites list and current website)
     */
    private function getCommonData(Website $website): array
    {
        return [
            'currentWebsite' => $website,
            'websites' => auth()->user()->websites()
                ->withCount(['articles', 'categories'])
                ->get(),
        ];
    }

    /**
     * Display a listing of the pages.
     */
    public function index(Website $website): Response
    {
        $this->authorize('view', $website);
        
        // Get all pages for this website
        $pages = Page::where('website_id', $website->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('SuperAdmin/Pages', array_merge(
            $this->getCommonData($website),
            ['pages' => $pages]
        ));
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request, Website $website)
    {
        $this->authorize('view', $website);

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

        $validated['website_id'] = $website->id;

        Page::create($validated);

        return redirect()->route('superadmin.pages.index', ['website' => $website->id])
            ->with('success', 'Page created successfully!');
    }

    /**
     * Update the specified page in storage.
     */
    public function update(Request $request, Website $website, Page $page)
    {
        // Verify the page belongs to this website
        if ($page->website_id !== $website->id) {
            abort(403, 'Page does not belong to this website.');
        }

        $this->authorize('view', $website);

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

        return redirect()->route('superadmin.pages.index', ['website' => $website->id])
            ->with('success', 'Page updated successfully!');
    }

    /**
     * Remove the specified page from storage.
     */
    public function destroy(Website $website, Page $page)
    {
        // Verify the page belongs to this website
        if ($page->website_id !== $website->id) {
            abort(403, 'Page does not belong to this website.');
        }

        $this->authorize('view', $website);

        $page->delete();

        return redirect()->route('superadmin.pages.index', ['website' => $website->id])
            ->with('success', 'Page deleted successfully!');
    }
}
