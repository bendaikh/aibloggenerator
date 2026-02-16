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
     * Display an article (subdomain/custom domain) - for /recipes/{article} URLs.
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

        return Inertia::render('Public/Website/Article', [
            'website' => $website,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
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
     * Display all articles (subdomain/custom domain).
     */
    public function showAllArticlesByDomain(Request $request): Response
    {
        $website = $request->get('website');
        
        if (!$website) {
            abort(404, 'Website not found');
        }

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
     * Display an article (legacy route) - for /recipes/{article} URLs.
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

        return Inertia::render('Public/Website/Article', [
            'website' => $website,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
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
     * Display all articles (legacy route).
     */
    public function showAllArticles(string $websiteSlug): Response
    {
        $website = Website::where('slug', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

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

        return response($robots)
            ->header('Content-Type', 'text/plain');
    }
}

