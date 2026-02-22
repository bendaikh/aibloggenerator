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

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        return $this->renderHome($website);
    }

    /**
     * Display an article (subdomain/custom domain) - for /recipes/{article} URLs.
     */
    public function showArticleByDomain(Request $request, string $articleSlug): Response
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        // Try exact match first, then try with ID pattern (e.g., slug-431)
        // This route is for recipes, so we filter by article_type = 'recipe' or null (backwards compatibility)
        $article = $website->articles()
            ->where(function ($query) use ($articleSlug) {
                $query->where('slug', $articleSlug)
                    ->orWhere('slug', 'like', $articleSlug . '-%');
            })
            ->where(function ($query) {
                $query->where('article_type', 'recipe')
                    ->orWhereNull('article_type');
            })
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

        // Load the website's theme to determine if recipe sections should be shown
        $websiteTheme = $website->theme()->first();

        return Inertia::render('Public/Website/Article', [
            'website' => $website,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'showRecipeSections' => $websiteTheme ? $websiteTheme->show_recipe_sections : true,
        ]);
    }

    /**
     * Display a regular article (subdomain/custom domain) - for /{article} URLs (at root).
     */
    public function showRegularArticleByDomain(Request $request, string $articleSlug): Response
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        // Try exact match first, then try with ID pattern (e.g., slug-431)
        // This route is for regular articles only (article_type = 'article')
        $article = $website->articles()
            ->where(function ($query) use ($articleSlug) {
                $query->where('slug', $articleSlug)
                    ->orWhere('slug', 'like', $articleSlug . '-%');
            })
            ->where('article_type', 'article')
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

        // Load the website's theme to determine if recipe sections should be shown
        $websiteTheme = $website->theme()->first();

        return Inertia::render('Public/Website/Article', [
            'website' => $website,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'showRecipeSections' => $websiteTheme ? $websiteTheme->show_recipe_sections : true,
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

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

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
     * Display all articles (subdomain/custom domain).
     */
    public function showAllArticlesByDomain(Request $request): Response
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        return $this->renderAllArticles($website);
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

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

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

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        return $this->renderHome($website);
    }

    /**
     * Display an article (legacy route) - for /recipes/{article} URLs.
     */
    public function showArticle(string $websiteSlug, string $articleSlug): Response
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        // Try exact match first, then try with ID pattern (e.g., slug-431)
        // This route is for recipes, so we filter by article_type = 'recipe' or null (backwards compatibility)
        $article = $website->articles()
            ->where(function ($query) use ($articleSlug) {
                $query->where('slug', $articleSlug)
                    ->orWhere('slug', 'like', $articleSlug . '-%');
            })
            ->where(function ($query) {
                $query->where('article_type', 'recipe')
                    ->orWhereNull('article_type');
            })
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

        // Load the website's theme to determine if recipe sections should be shown
        $websiteTheme = $website->theme()->first();

        return Inertia::render('Public/Website/Article', [
            'website' => $website,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'showRecipeSections' => $websiteTheme ? $websiteTheme->show_recipe_sections : true,
        ]);
    }

    /**
     * Display a regular article (legacy route) - for /{article} URLs (at root).
     */
    public function showRegularArticle(string $websiteSlug, string $articleSlug): Response
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        // Try exact match first, then try with ID pattern (e.g., slug-431)
        // This route is for regular articles only (article_type = 'article')
        $article = $website->articles()
            ->where(function ($query) use ($articleSlug) {
                $query->where('slug', $articleSlug)
                    ->orWhere('slug', 'like', $articleSlug . '-%');
            })
            ->where('article_type', 'article')
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

        // Load the website's theme to determine if recipe sections should be shown
        $websiteTheme = $website->theme()->first();

        return Inertia::render('Public/Website/Article', [
            'website' => $website,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'showRecipeSections' => $websiteTheme ? $websiteTheme->show_recipe_sections : true,
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

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

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
     * Display all articles (legacy route).
     */
    public function showAllArticles(string $websiteSlug): Response
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        return $this->renderAllArticles($website);
    }

    /**
     * Search articles and recipes (legacy route).
     */
    public function searchLegacy(Request $request, string $websiteSlug): Response
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        $query = $request->input('q');

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        $articles = $website->publishedArticles()
            ->with('category')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%");
                });
            })
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Website/AllArticles', [
            'website' => $website,
            'articles' => $articles,
            'searchQuery' => $query,
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

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

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
     * Search articles and recipes (subdomain/custom domain).
     */
    public function search(Request $request): Response
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        // Share website with Blade views for meta tags (Pinterest, etc.)
        view()->share('website', $website);

        $query = $request->input('q');

        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        $articles = $website->publishedArticles()
            ->with('category')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%");
                });
            })
            ->orderBy('published_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Website/AllArticles', [
            'website' => $website,
            'articles' => $articles,
            'searchQuery' => $query,
        ]);
    }

    /**
     * Render all articles page for a website.
     */
    private function renderAllArticles(Website $website): Response
    {
        // Load categories and pages for navigation
        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            }
        ]);

        $articles = $website->publishedArticles()
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return Inertia::render('Public/Website/AllArticles', [
            'website' => $website,
            'articles' => $articles,
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
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

        $latestIds = $latestArticles->pluck('id');

        $featuredArticles = $website->publishedArticles()
            ->with('category')
            ->whereNotIn('id', $latestIds)
            ->orderByDesc('views')
            ->take(5)
            ->get();

        $featuredIds = $featuredArticles->pluck('id');
        $excludedIds = $latestIds->merge($featuredIds);

        $familyFavorites = $website->publishedArticles()
            ->with('category')
            ->whereNotIn('id', $excludedIds)
            ->inRandomOrder()
            ->take(3)
            ->get();

        // If we don't have enough for family favorites after exclusion, 
        // fallback to top viewed articles that might have some overlap but 
        // prioritizing different ones than featuredArticles
        if ($familyFavorites->count() < 3) {
            $extraFavorites = $website->publishedArticles()
                ->with('category')
                ->whereNotIn('id', $excludedIds)
                ->orderByDesc('views')
                ->take(3 - $familyFavorites->count())
                ->get();
            $familyFavorites = $familyFavorites->concat($extraFavorites);
        }

        // Load the first active author for the website
        $author = $website->authors()
            ->where('is_active', true)
            ->first();

        return Inertia::render('Public/Website/Home', [
            'website' => $website,
            'latestArticles' => $latestArticles,
            'featuredArticles' => $featuredArticles,
            'familyFavorites' => $familyFavorites,
            'author' => $author,
        ]);
    }

    /**
     * Serve the ads.txt file for the website.
     */
    public function adsTxt(Request $request)
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404);
        }

        return response($website->ads_txt ?? '', 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Serve the ads.txt file for the website (legacy route).
     */
    public function adsTxtLegacy(string $websiteSlug)
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        return response($website->ads_txt ?? '', 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Generate and serve the sitemap.xml (subdomain/custom domain).
     */
    public function sitemapByDomain(Request $request)
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        return $this->generateSitemapResponse($website);
    }

    /**
     * Generate and serve the sitemap.xml (legacy route).
     */
    public function sitemap(string $websiteSlug)
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->generateSitemapResponse($website);
    }

    /**
     * Generate sitemap XML response.
     */
    private function generateSitemapResponse(Website $website)
    {
        $seoSettings = $website->seo_settings ?? [];
        
        // Check if sitemap is enabled
        if (isset($seoSettings['sitemap_enabled']) && !$seoSettings['sitemap_enabled']) {
            abort(404);
        }

        $articles = $website->publishedArticles()->get();
        $categories = $website->categories()->where('is_active', true)->get();
        $pages = $website->pages()->where('is_active', true)->get();

        $frequency = $seoSettings['sitemap_frequency'] ?? 'daily';
        $priority = $seoSettings['sitemap_priority'] ?? '0.8';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Homepage
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . htmlspecialchars($website->url) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . now()->format('Y-m-d') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>' . $frequency . '</changefreq>' . "\n";
        $xml .= '    <priority>1.0</priority>' . "\n";
        $xml .= '  </url>' . "\n";

        // Articles
        foreach ($articles as $article) {
            $articlePath = $article->article_type === 'recipe' ? 'recipes/' . $article->slug : $article->slug;
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($website->getUrlForPath($articlePath)) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . ($article->updated_at ?? $article->published_at)->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $frequency . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $priority . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Categories
        foreach ($categories as $category) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($website->getUrlForPath('category/' . $category->slug)) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . ($category->updated_at ?? now())->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Pages
        foreach ($pages as $page) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($website->getUrlForPath('page/' . $page->slug)) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . ($page->updated_at ?? now())->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.5</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate and serve the robots.txt (subdomain/custom domain).
     */
    public function robotsTxtByDomain(Request $request)
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        return $this->generateRobotsTxtResponse($website);
    }

    /**
     * Generate and serve the robots.txt (legacy route).
     */
    public function robotsTxt(string $websiteSlug)
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->generateRobotsTxtResponse($website);
    }

    /**
     * Generate and serve the AI.txt file (for AI crawler configuration).
     */
    public function aiTxtByDomain(Request $request)
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

        return $this->generateAiTxtResponse($website);
    }

    /**
     * Generate and serve the AI.txt (legacy route).
     */
    public function aiTxt(string $websiteSlug)
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->generateAiTxtResponse($website);
    }

    /**
     * Generate AI.txt response with GEO configuration.
     */
    private function generateAiTxtResponse(Website $website)
    {
        $geoSettings = $website->geo_settings ?? [];
        
        $aiTxt = "# AI.txt for {$website->name}\n";
        $aiTxt .= "# Generative Engine Optimization (GEO) Configuration\n";
        $aiTxt .= "# Updated: " . now()->toDateString() . "\n\n";
        
        $aiTxt .= "[General]\n";
        $aiTxt .= "website_name = \"{$website->name}\"\n";
        $aiTxt .= "website_url = \"{$website->url}\"\n";
        $aiTxt .= "content_type = \"blog\"\n\n";
        
        $aiTxt .= "[Attribution]\n";
        $aiTxt .= "citation_format = \"" . ($geoSettings['citation_format'] ?? 'apa') . "\"\n";
        $aiTxt .= "attribution = \"" . ($geoSettings['content_attribution'] ?? $website->name) . "\"\n";
        $aiTxt .= "author_credentials = \"" . ($geoSettings['author_credentials'] ?? '') . "\"\n\n";
        
        $aiTxt .= "[Content]\n";
        $aiTxt .= "technical_depth = \"" . ($geoSettings['technical_depth'] ?? 'intermediate') . "\"\n";
        $aiTxt .= "fact_checking = \"" . ($geoSettings['fact_checking_enabled'] ? 'enabled' : 'disabled') . "\"\n";
        $aiTxt .= "structured_data = \"" . ($geoSettings['structured_data_enhanced'] ? 'enhanced' : 'standard') . "\"\n\n";
        
        $aiTxt .= "[AI Access]\n";
        $aiTxt .= "llm_training_opt_out = \"" . ($geoSettings['llm_training_opt_out'] ? 'true' : 'false') . "\"\n";
        $aiTxt .= "api_access = \"" . ($geoSettings['api_access_enabled'] ? 'enabled' : 'disabled') . "\"\n";
        
        if (!empty($geoSettings['preferred_llms'])) {
            $aiTxt .= "preferred_models = \"" . implode(', ', $geoSettings['preferred_llms']) . "\"\n";
        }
        
        if ($geoSettings['api_access_enabled'] ?? true) {
            $aiTxt .= "api_endpoint = \"{$website->url}/api/ai/articles\"\n";
        }
        
        $aiTxt .= "\n[Expertise]\n";
        if (!empty($geoSettings['expertise_areas'])) {
            foreach ($geoSettings['expertise_areas'] as $area) {
                $aiTxt .= "area = \"{$area}\"\n";
            }
        }
        
        return response($aiTxt)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Generate robots.txt response.
     */
    private function generateRobotsTxtResponse(Website $website)
    {
        // If custom robots.txt is set, use it
        if (!empty($website->robots_txt)) {
            return response($website->robots_txt)
                ->header('Content-Type', 'text/plain');
        }

        // Generate default robots.txt based on SEO settings
        $seoSettings = $website->seo_settings ?? [];
        $geoSettings = $website->geo_settings ?? [];
        $enableIndexing = $seoSettings['enable_indexing'] ?? true;
        $sitemapEnabled = $seoSettings['sitemap_enabled'] ?? true;

        $robots = "# Robots.txt for {$website->name}\n";
        $robots .= "# Generated automatically\n\n";
        $robots .= "User-agent: *\n";

        if ($enableIndexing) {
            $robots .= "Allow: /\n";
        } else {
            $robots .= "Disallow: /\n";
        }

        // Add sitemap reference if enabled
        if ($sitemapEnabled) {
            $robots .= "\n# Sitemap\n";
            $robots .= "Sitemap: {$website->url}/sitemap.xml\n";
        }

        // Add common disallows
        $robots .= "\n# Disallow common paths\n";
        $robots .= "Disallow: /admin\n";
        $robots .= "Disallow: /api\n";
        $robots .= "Disallow: /login\n";
        $robots .= "Disallow: /register\n";

        // ========== GEO: AI CRAWLER RULES ==========
        $llmTrainingOptOut = $geoSettings['llm_training_opt_out'] ?? false;
        $apiAccessEnabled = $geoSettings['api_access_enabled'] ?? true;
        $preferredLLMs = $geoSettings['preferred_llms'] ?? ['chatgpt', 'perplexity', 'gemini', 'claude'];

        $robots .= "\n# ========================================\n";
        $robots .= "# GEO: AI Crawler Configuration\n";
        $robots .= "# ========================================\n\n";

        // Map of AI crawlers and their user agents
        $aiCrawlers = [
            'chatgpt' => ['GPTBot', 'ChatGPT-User'],
            'perplexity' => ['PerplexityBot'],
            'gemini' => ['Google-Extended', 'GoogleOther'],
            'claude' => ['ClaudeBot', 'anthropic-ai'],
            'cohere' => ['cohere-ai'],
            'meta' => ['FacebookBot', 'Meta-ExternalAgent'],
            'apple' => ['Applebot-Extended'],
            'common_crawl' => ['CCBot']
        ];

        if ($llmTrainingOptOut) {
            // Opt-out of LLM training - block all AI crawlers
            $robots .= "# LLM Training Opt-Out: Enabled\n";
            $robots .= "# Blocking AI crawlers from using content for training\n\n";
            
            foreach ($aiCrawlers as $crawlerName => $userAgents) {
                foreach ($userAgents as $userAgent) {
                    $robots .= "User-agent: {$userAgent}\n";
                    $robots .= "Disallow: /\n\n";
                }
            }
            
            // Add TDM (Text and Data Mining) reservation
            $robots .= "# TDM Reservation (EU Copyright Directive)\n";
            $robots .= "# This site reserves all rights under TDM exception\n";
        } else {
            // Allow preferred AI crawlers
            $robots .= "# AI Crawlers: Selective Access\n";
            $robots .= "# Allowing preferred AI models for GEO optimization\n\n";
            
            // Allow preferred crawlers
            foreach ($preferredLLMs as $preferred) {
                if (isset($aiCrawlers[$preferred])) {
                    foreach ($aiCrawlers[$preferred] as $userAgent) {
                        $robots .= "User-agent: {$userAgent}\n";
                        $robots .= "Allow: /\n";
                        if ($apiAccessEnabled) {
                            $robots .= "Allow: /api/ai/articles\n";
                        }
                        $robots .= "\n";
                    }
                }
            }
            
            // Block non-preferred AI crawlers
            $blockedCrawlers = array_diff(array_keys($aiCrawlers), $preferredLLMs);
            if (!empty($blockedCrawlers)) {
                $robots .= "# Blocking non-preferred AI crawlers\n";
                foreach ($blockedCrawlers as $blocked) {
                    if (isset($aiCrawlers[$blocked])) {
                        foreach ($aiCrawlers[$blocked] as $userAgent) {
                            $robots .= "User-agent: {$userAgent}\n";
                            $robots .= "Disallow: /\n\n";
                        }
                    }
                }
            }
        }

        // Add AI.txt reference
        $robots .= "\n# AI.txt for structured AI crawler information\n";
        $robots .= "# See: {$website->url}/ai.txt\n";

        return response($robots)
            ->header('Content-Type', 'text/plain');
    }
}

