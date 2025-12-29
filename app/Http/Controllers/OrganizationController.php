<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\Article;
use App\Models\Category;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    /**
     * Organization Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        $stats = [
            'totalWebsites' => $websites->count(),
            'totalArticles' => Article::whereIn('website_id', $websites->pluck('id'))->count(),
            'totalCategories' => Category::whereIn('website_id', $websites->pluck('id'))->count(),
            'totalPages' => Page::whereIn('website_id', $websites->pluck('id'))->count(),
        ];

        return Inertia::render('Organization/Dashboard', [
            'stats' => $stats,
            'websites' => $websites,
        ]);
    }

    /**
     * Global Settings Page
     */
    public function settings()
    {
        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/Settings', [
            'settings' => [
                'openai_api_key_set' => !empty($user->openai_api_key),
                'openai_api_key_masked' => $user->openai_api_key ? 'sk-....' . substr($user->openai_api_key, -4) : null,
                'ai_model' => $user->ai_model ?? 'gpt-4o',
                'ai_default_tone' => $user->ai_default_tone ?? 'conversational',
            ],
            'websites' => $websites,
        ]);
    }

    /**
     * Update Global Settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'openai_api_key' => 'nullable|string',
            'ai_model' => 'required|in:gpt-4o,gpt-4-turbo,gpt-3.5-turbo',
            'ai_default_tone' => 'required|in:conversational,professional,casual,friendly,formal',
        ]);

        $updateData = [
            'ai_model' => $validated['ai_model'],
            'ai_default_tone' => $validated['ai_default_tone'],
        ];

        if (!empty($validated['openai_api_key'])) {
            $updateData['openai_api_key'] = $validated['openai_api_key'];
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Test AI Connection
     */
    public function testAiConnection()
    {
        $user = Auth::user();

        if (empty($user->openai_api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No API key configured'
            ]);
        }

        try {
            $client = \OpenAI::client($user->openai_api_key);
            $response = $client->models()->list();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful! API key is valid.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Websites List
     */
    public function websitesIndex()
    {
        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/Websites/Index', [
            'websites' => $websites,
        ]);
    }

    /**
     * Create Website Form
     */
    public function websitesCreate()
    {
        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/Websites/Create', [
            'websites' => $websites,
        ]);
    }

    /**
     * Store New Website
     */
    public function websitesStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255|unique:websites,subdomain',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $user = Auth::user();

        // Generate subdomain if not provided
        if (empty($validated['subdomain'])) {
            $validated['subdomain'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $originalSubdomain = $validated['subdomain'];
            $counter = 1;
            while (Website::where('subdomain', $validated['subdomain'])->exists()) {
                $validated['subdomain'] = $originalSubdomain . '-' . $counter;
                $counter++;
            }
        }

        $website = Website::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'domain' => $validated['domain'] ?? null,
            'subdomain' => $validated['subdomain'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('superadmin.dashboard', ['website' => $website->id])
            ->with('success', 'Website created successfully!');
    }

    /**
     * Edit Website Form
     */
    public function websitesEdit(Website $website)
    {
        $this->authorize('update', $website);

        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/Websites/Edit', [
            'website' => $website,
            'websites' => $websites,
        ]);
    }

    /**
     * Update Website
     */
    public function websitesUpdate(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255|unique:websites,subdomain,' . $website->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $website->update($validated);

        return redirect()->route('organization.websites.index')
            ->with('success', 'Website updated successfully!');
    }

    /**
     * Delete Website
     */
    public function websitesDestroy(Website $website)
    {
        $this->authorize('delete', $website);

        $website->delete();

        return redirect()->route('organization.websites.index')
            ->with('success', 'Website deleted successfully!');
    }
}

