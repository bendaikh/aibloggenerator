<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
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
     * Display a listing of articles for a website.
     */
    public function index(Website $website): Response
    {
        $this->authorize('view', $website);

        $articles = Article::with(['category', 'user'])
            ->where('website_id', $website->id)
            ->latest()
            ->paginate(20);

        return Inertia::render('SuperAdmin/Articles/Index', array_merge(
            $this->getCommonData($website),
            ['articles' => $articles]
        ));
    }

    /**
     * Show the form for creating a new article.
     */
    public function create(Website $website): Response
    {
        $this->authorize('view', $website);

        $website->load('categories');
        
        // Get user's AI settings
        $user = auth()->user();
        $hasApiKey = !empty($user->openai_api_key);

        return Inertia::render('SuperAdmin/Articles/Create', array_merge(
            $this->getCommonData($website),
            [
                'preselectedWebsite' => $website->id,
                'hasApiKey' => $hasApiKey,
                'defaultTone' => $user->ai_default_tone ?? 'conversational',
            ]
        ));
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:1000',
            'secondary_image' => 'nullable|string|max:1000',
            'meta_tags' => 'nullable|array',
            'status' => 'required|in:draft,published,scheduled',
            'generation_type' => 'required|in:manual,ai',
            'published_at' => 'nullable|date',
            'prep_time' => 'nullable|string|max:255',
            'cook_time' => 'nullable|string|max:255',
            'rest_time' => 'nullable|string|max:255',
            'total_time' => 'nullable|string|max:255',
        ]);

        $validated['website_id'] = $website->id;
        $validated['user_id'] = auth()->id();

        // Set published_at if status is published and no date is set
        if ($validated['status'] === 'published' && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $article = Article::create($validated);

        return redirect()->route('superadmin.articles.show', ['website' => $website->id, 'article' => $article->id])
            ->with('success', 'Article created successfully!');
    }

    /**
     * Display the specified article.
     */
    public function show(Website $website, Article $article): Response
    {
        $this->authorize('view', $article);

        $article->load(['category', 'user']);

        return Inertia::render('SuperAdmin/Articles/Show', array_merge(
            $this->getCommonData($website),
            ['article' => $article]
        ));
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Website $website, Article $article): Response
    {
        $this->authorize('update', $article);

        $website->load('categories');
        $article->load(['category']);

        return Inertia::render('SuperAdmin/Articles/Edit', array_merge(
            $this->getCommonData($website),
            ['article' => $article]
        ));
    }

    /**
     * Update the specified article in storage.
     */
    public function update(Request $request, Website $website, Article $article)
    {
        $this->authorize('update', $article);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'secondary_image' => 'nullable|string',
            'meta_tags' => 'nullable|array',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'prep_time' => 'nullable|string|max:255',
            'cook_time' => 'nullable|string|max:255',
            'rest_time' => 'nullable|string|max:255',
            'total_time' => 'nullable|string|max:255',
        ]);

        // If changing status to published and published_at is not set, set it now
        if ($validated['status'] === 'published' && !$validated['published_at'] && $article->status !== 'published') {
            $validated['published_at'] = now();
        }

        $article->update($validated);

        return redirect()->route('superadmin.articles.show', ['website' => $website->id, 'article' => $article->id])
            ->with('success', 'Article updated successfully!');
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy(Website $website, Article $article)
    {
        $this->authorize('delete', $article);

        $article->delete();

        return redirect()->route('superadmin.articles.index', ['website' => $website->id])
            ->with('success', 'Article deleted successfully!');
    }
}
