<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteController extends Controller
{
    use AuthorizesRequests;

    /**
     * Website Dashboard - shows overview for a specific website
     */
    public function dashboard(Website $website): Response
    {
        $this->authorize('view', $website);

        $websites = auth()->user()->websites()
            ->withCount(['articles', 'categories'])
            ->get();

        // Get stats for this website
        $stats = [
            'totalArticles' => $website->articles()->count(),
            'articlesGrowth' => 42.4, // Would calculate from real data
            'publishedArticles' => $website->articles()->where('status', 'published')->count(),
            'draftArticles' => $website->articles()->where('status', 'draft')->count(),
            'aiUsage' => '6.5M',
            'aiUsageGrowth' => 100,
            'aiCost' => 5.5276,
            'aiEvents' => 1074,
            'adRevenue' => 0.34,
            'revenueGrowth' => 100,
            'adImpressions' => 545,
            'impressionsGrowth' => 100
        ];

        return Inertia::render('SuperAdmin/Dashboard', [
            'currentWebsite' => $website,
            'websites' => $websites,
            'stats' => $stats,
        ]);
    }

    /**
     * Display a listing of websites.
     */
    public function index(): Response
    {
        $websites = auth()->user()->websites()
            ->withCount(['articles', 'categories'])
            ->latest()
            ->get();

        return Inertia::render('SuperAdmin/Websites/Index', [
            'websites' => $websites
        ]);
    }

    /**
     * Show the form for creating a new website.
     */
    public function create(): Response
    {
        return Inertia::render('SuperAdmin/Websites/Create');
    }

    /**
     * Store a newly created website in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:websites',
            'subdomain' => 'nullable|string|max:255|unique:websites',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'favicon' => 'nullable|string',
            'domain' => 'nullable|string|unique:websites',
            'theme' => 'nullable|string',
            'social_media' => 'nullable|array',
        ], [
            'slug.unique' => 'This website URL is already taken. Please choose a different one.',
            'subdomain.unique' => 'This subdomain is already taken. Please choose a different one.',
            'domain.unique' => 'This domain is already in use by another website.',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['theme'] = $validated['theme'] ?? 'solushcooks';
        
        // Generate unique slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name']);
        }
        
        // Generate unique subdomain if not provided
        if (empty($validated['subdomain'])) {
            $validated['subdomain'] = $this->generateUniqueSubdomain($validated['slug']);
        }
        
        // Create default theme settings
        $validated['theme_settings'] = [
            'primary_color' => '#1e293b',
            'secondary_color' => '#10b981',
            'header_text' => $validated['name'],
            'newsletter_enabled' => true,
            'comments_enabled' => true,
        ];

        $website = Website::create($validated);

        return redirect()->route('superadmin.dashboard', $website)
            ->with('success', 'Website created successfully! You can now add categories and pages.');
    }

    /**
     * Display the specified website.
     */
    public function show(Website $website): Response
    {
        $this->authorize('view', $website);

        $website->load([
            'articles' => function ($query) {
                $query->latest()->take(10);
            },
            'categories' => function ($query) {
                $query->orderBy('order');
            }
        ]);

        return Inertia::render('SuperAdmin/Websites/Show', [
            'website' => $website
        ]);
    }

    /**
     * Show the form for editing the specified website.
     */
    public function edit(Website $website): Response
    {
        $this->authorize('update', $website);

        return Inertia::render('SuperAdmin/Websites/Edit', [
            'website' => $website
        ]);
    }

    /**
     * Update the specified website in storage.
     */
    public function update(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:websites,slug,' . $website->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'favicon' => 'nullable|string',
            'domain' => 'nullable|string|unique:websites,domain,' . $website->id,
            'theme' => 'nullable|string',
            'theme_settings' => 'nullable|array',
            'social_media' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $website->update($validated);

        return redirect()->route('superadmin.dashboard', $website)
            ->with('success', 'Website updated successfully!');
    }

    /**
     * Remove the specified website from storage.
     */
    public function destroy(Website $website)
    {
        $this->authorize('delete', $website);

        $website->delete();

        return redirect()->route('organization.websites.index')
            ->with('success', 'Website deleted successfully!');
    }

    /**
     * Generate a unique slug for the website.
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        // Keep checking until we find a unique slug
        while (Website::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate a unique subdomain for the website.
     */
    private function generateUniqueSubdomain(string $slug): string
    {
        $subdomain = $slug;
        $originalSubdomain = $subdomain;
        $counter = 1;

        // Keep checking until we find a unique subdomain
        while (Website::where('subdomain', $subdomain)->exists()) {
            $subdomain = $originalSubdomain . '-' . $counter;
            $counter++;
        }

        return $subdomain;
    }
}
