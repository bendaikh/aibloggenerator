<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\Article;
use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicWebsiteController extends Controller
{
    /**
     * Display the website homepage (subdomain/custom domain).
     */
    public function showByDomain(Request $request): Response
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        return $this->renderHome($website);
    }

    /**
     * Display an article (subdomain/custom domain).
     */
    public function showArticleByDomain(Request $request, string $articleSlug): Response
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        $article = $website->articles()
            ->where('slug', $articleSlug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->with(['category', 'user', 'author'])
            ->firstOrFail();

        // Increment views
        $article->incrementViews();

        // Get related articles
        $relatedArticles = $website->publishedArticles()
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->take(3)
            ->get();

        return Inertia::render('Public/Website/Article', [
            'website' => $website,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    /**
     * Display articles in a category (subdomain/custom domain).
     */
    public function showCategoryByDomain(Request $request, string $categorySlug): Response
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        $category = $website->categories()
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $articles = $category->publishedArticles()
            ->with('category')
            ->paginate(12);

        return Inertia::render('Public/Website/Category', [
            'website' => $website,
            'category' => $category,
            'articles' => $articles,
        ]);
    }

    /**
     * Display a page (subdomain/custom domain).
     */
    public function showPageByDomain(Request $request, string $pageSlug): Response
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        $page = $website->pages()
            ->where('slug', $pageSlug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Public/Website/Page', [
            'website' => $website,
            'page' => $page,
        ]);
    }

    /**
     * Display the website homepage (legacy /site/{slug} route).
     */
    public function show(string $websiteSlug): Response
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->renderHome($website);
    }

    /**
     * Display an article (legacy route).
     */
    public function showArticle(string $websiteSlug, string $articleSlug): Response
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        $article = $website->articles()
            ->where('slug', $articleSlug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->with(['category', 'user', 'author'])
            ->firstOrFail();

        // Increment views
        $article->incrementViews();

        // Get related articles
        $relatedArticles = $website->publishedArticles()
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->take(3)
            ->get();

        return Inertia::render('Public/Website/Article', [
            'website' => $website,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    /**
     * Display articles in a category (legacy route).
     */
    public function showCategory(string $websiteSlug, string $categorySlug): Response
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        $category = $website->categories()
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $articles = $category->publishedArticles()
            ->with('category')
            ->paginate(12);

        return Inertia::render('Public/Website/Category', [
            'website' => $website,
            'category' => $category,
            'articles' => $articles,
        ]);
    }

    /**
     * Display a page (legacy route).
     */
    public function showPage(string $websiteSlug, string $pageSlug): Response
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        $page = $website->pages()
            ->where('slug', $pageSlug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Public/Website/Page', [
            'website' => $website,
            'page' => $page,
        ]);
    }

    /**
     * Render the home page for a website.
     */
    private function renderHome(Website $website): Response
    {
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)
                    ->where('show_in_menu', true)
                    ->orderBy('order');
            }
        ]);

        $latestArticles = $website->publishedArticles()
            ->with('category')
            ->take(6)
            ->get();

        $featuredArticles = $website->publishedArticles()
            ->with('category')
            ->orderByDesc('views')
            ->take(4)
            ->get();

        return Inertia::render('Public/Website/Home', [
            'website' => $website,
            'latestArticles' => $latestArticles,
            'featuredArticles' => $featuredArticles,
        ]);
    }
}

