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
     * Display a listing of articles for a website.
     */
    public function index(Request $request): Response
    {
        $websiteId = $request->get('website_id');
        
        $query = Article::with(['website', 'category', 'user'])
            ->where('user_id', auth()->id());

        if ($websiteId) {
            $query->where('website_id', $websiteId);
        }

        $articles = $query->latest()->paginate(20);

        $websites = auth()->user()->websites;

        return Inertia::render('SuperAdmin/Articles/Index', [
            'articles' => $articles,
            'websites' => $websites
        ]);
    }

    /**
     * Show the form for creating a new article.
     */
    public function create(Request $request): Response
    {
        $websites = auth()->user()->websites()->with('categories')->get();
        $websiteId = $request->get('website_id');

        return Inertia::render('SuperAdmin/Articles/Create', [
            'websites' => $websites,
            'preselectedWebsite' => $websiteId
        ]);
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'website_id' => 'required|exists:websites,id',
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'meta_tags' => 'nullable|array',
            'status' => 'required|in:draft,published,scheduled',
            'generation_type' => 'required|in:manual,ai',
            'published_at' => 'nullable|date',
        ]);

        $validated['user_id'] = auth()->id();

        // Set published_at if status is published and no date is set
        if ($validated['status'] === 'published' && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $article = Article::create($validated);

        return redirect()->route('superadmin.articles.show', $article)
            ->with('success', 'Article created successfully!');
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article): Response
    {
        $this->authorize('view', $article);

        $article->load(['website', 'category', 'user']);

        return Inertia::render('SuperAdmin/Articles/Show', [
            'article' => $article
        ]);
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Article $article): Response
    {
        $this->authorize('update', $article);

        $websites = auth()->user()->websites()->with('categories')->get();
        $article->load(['website', 'category']);

        return Inertia::render('SuperAdmin/Articles/Edit', [
            'article' => $article,
            'websites' => $websites
        ]);
    }

    /**
     * Update the specified article in storage.
     */
    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $validated = $request->validate([
            'website_id' => 'required|exists:websites,id',
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'meta_tags' => 'nullable|array',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
        ]);

        $article->update($validated);

        return redirect()->route('superadmin.articles.show', $article)
            ->with('success', 'Article updated successfully!');
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        $article->delete();

        return redirect()->route('superadmin.articles.index')
            ->with('success', 'Article deleted successfully!');
    }
}

