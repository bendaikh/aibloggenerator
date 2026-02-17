<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Website;
use Illuminate\Http\Request;

class AiArticleController extends Controller
{
    /**
     * Get all articles for a website in AI-friendly format
     */
    public function index(Request $request, $websiteSlug)
    {
        $website = Website::where('slug', $websiteSlug)
            ->orWhere('domain', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Check if API access is enabled
        $geoSettings = $website->geo_settings ?? [];
        if (!($geoSettings['api_access_enabled'] ?? true)) {
            return response()->json([
                'error' => 'API access is disabled for this website'
            ], 403);
        }

        $articles = Article::where('website_id', $website->id)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'website' => [
                'name' => $website->name,
                'url' => $website->url,
                'description' => $geoSettings['ai_summary'] ?? $website->description,
                'expertise_areas' => $geoSettings['expertise_areas'] ?? [],
                'citation_format' => $geoSettings['citation_format'] ?? 'apa',
                'content_attribution' => $geoSettings['content_attribution'] ?? $website->name,
            ],
            'articles' => $articles->map(function ($article) use ($geoSettings) {
                return $this->formatArticleForAI($article, $geoSettings);
            })
        ]);
    }

    /**
     * Get a single article in AI-friendly format
     */
    public function show(Request $request, $websiteSlug, $articleSlug)
    {
        $website = Website::where('slug', $websiteSlug)
            ->orWhere('domain', $websiteSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // Check if API access is enabled
        $geoSettings = $website->geo_settings ?? [];
        if (!($geoSettings['api_access_enabled'] ?? true)) {
            return response()->json([
                'error' => 'API access is disabled for this website'
            ], 403);
        }

        $article = Article::where('website_id', $website->id)
            ->where('slug', $articleSlug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return response()->json([
            'website' => [
                'name' => $website->name,
                'url' => $website->url,
                'citation_format' => $geoSettings['citation_format'] ?? 'apa',
                'content_attribution' => $geoSettings['content_attribution'] ?? $website->name,
            ],
            'article' => $this->formatArticleForAI($article, $geoSettings)
        ]);
    }

    /**
     * Format article data for AI consumption
     */
    private function formatArticleForAI($article, $geoSettings)
    {
        $data = [
            'title' => $article->title,
            'url' => $article->url,
            'excerpt' => $article->excerpt,
            'content' => strip_tags($article->content),
            'published_at' => $article->published_at,
            'updated_at' => $article->updated_at,
            'author' => [
                'name' => $article->author->name ?? $article->user->name ?? 'Admin',
                'credentials' => $geoSettings['author_credentials'] ?? null,
            ],
            'category' => $article->category ? [
                'name' => $article->category->name,
                'slug' => $article->category->slug,
            ] : null,
            'tags' => $article->meta_tags ?? [],
        ];

        // Add GEO metadata
        if (!empty($geoSettings)) {
            $data['geo_metadata'] = [
                'key_facts' => $geoSettings['key_facts'] ?? [],
                'conversational_queries' => $geoSettings['conversational_queries'] ?? [],
                'entity_definitions' => $geoSettings['entity_definitions'] ?? [],
                'technical_depth' => $geoSettings['technical_depth'] ?? 'intermediate',
                'fact_checked' => $geoSettings['fact_checking_enabled'] ?? false,
            ];
        }

        // Add recipe-specific data if it's a recipe
        if ($article->article_type === 'recipe') {
            $data['recipe'] = [
                'prep_time' => $article->prep_time,
                'cook_time' => $article->cook_time,
                'total_time' => $article->total_time,
                'servings' => $article->servings,
                'ingredients' => $article->ingredients,
                'instructions' => $article->instructions,
            ];
        }

        return $data;
    }
}
