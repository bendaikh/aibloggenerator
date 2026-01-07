<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Website;
use App\Models\Category;
use App\Models\ArticleGenerationJob;
use App\Jobs\GenerateAIArticleJob;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AIArticleController extends Controller
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
     * Display the AI article generation form.
     */
    public function index(Website $website): Response
    {
        $this->authorize('view', $website);

        $user = auth()->user();
        
        $website->load('categories');

        $recentArticles = Article::where('website_id', $website->id)
            ->where('ai_generated', true)
            ->with(['category'])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('SuperAdmin/AIArticles/Generate', array_merge(
            $this->getCommonData($website),
            [
                'recentArticles' => $recentArticles,
                'hasApiKey' => !empty($user->openai_api_key),
                'defaultTone' => $user->ai_default_tone ?? 'conversational',
            ]
        ));
    }

    /**
     * Get pending/active generation jobs for current user.
     */
    public function getGenerationJobs(Request $request)
    {
        $user = auth()->user();
        
        $jobs = ArticleGenerationJob::where('user_id', $user->id)
            ->recent()
            ->with(['website', 'article'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'topic' => $job->topic,
                    'status' => $job->status,
                    'website_id' => $job->website_id,
                    'website_name' => $job->website?->name,
                    'article_id' => $job->article_id,
                    'article_title' => $job->article?->title,
                    'error_message' => $job->error_message,
                    'created_at' => $job->created_at->diffForHumans(),
                    'completed_at' => $job->completed_at?->diffForHumans(),
                ];
            });

        $activeCount = $jobs->whereIn('status', ['pending', 'processing'])->count();

        return response()->json([
            'jobs' => $jobs,
            'activeCount' => $activeCount,
        ]);
    }

    /**
     * Dismiss/clear a completed or failed job.
     */
    public function dismissJob(Request $request, $jobId)
    {
        $user = auth()->user();
        
        $job = ArticleGenerationJob::where('id', $jobId)
            ->where('user_id', $user->id)
            ->first();

        if ($job) {
            $job->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Clear all completed jobs.
     */
    public function clearCompletedJobs(Request $request)
    {
        $user = auth()->user();
        
        ArticleGenerationJob::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'failed'])
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Generate an article using AI (now with background processing option).
     */
    public function generate(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $user = auth()->user();

        // Check if user has configured OpenAI
        if (empty($user->openai_api_key)) {
            return back()->withErrors(['error' => 'Please configure your OpenAI API key in Global Settings first.']);
        }

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'topic' => 'required|string|max:255',
            'tone' => 'nullable|string|in:professional,casual,friendly,formal,conversational',
            'length' => 'nullable|string|in:short,medium,long',
            'keywords' => 'nullable|string',
            'featured_image' => 'nullable|string|max:1000',
            'auto_publish' => 'boolean',
            'auto_categorize' => 'boolean',
            'background' => 'boolean', // New option for background processing
        ]);

        // Validate category if provided
        if (!empty($validated['category_id'])) {
            $category = Category::findOrFail($validated['category_id']);
            if ($category->website_id !== $website->id) {
                abort(403, 'Category does not belong to this website.');
            }
        }

        $tone = $validated['tone'] ?? $user->ai_default_tone ?? 'conversational';
        $keywords = $validated['keywords'] ?? '';
        $backgroundProcess = $validated['background'] ?? true; // Default to background

        // If background processing is enabled (default), dispatch job
        if ($backgroundProcess) {
            // Create the generation job record
            $generationJob = ArticleGenerationJob::create([
                'user_id' => auth()->id(),
                'website_id' => $website->id,
                'topic' => $validated['topic'],
                'status' => 'pending',
            ]);

            // Dispatch the job
            GenerateAIArticleJob::dispatch(
                $generationJob->id,
                $website->id,
                auth()->id(),
                $validated['topic'],
                $tone,
                $validated['length'] ?? 'medium',
                $keywords,
                $validated['auto_publish'] ?? false,
                ($validated['auto_categorize'] ?? true) ? null : ($validated['category_id'] ?? null),
                $validated['featured_image'] ?? null
            );

            return redirect()->route('superadmin.ai-articles.index', ['website' => $website->id])
                ->with('success', 'Article generation started! Check the notification icon to track progress.');
        }

        // Synchronous processing (kept for backwards compatibility)
        return $this->generateSynchronously($request, $website, $validated);
    }

    /**
     * Synchronous article generation (original method)
     */
    private function generateSynchronously(Request $request, Website $website, array $validated)
    {
        $user = auth()->user();
        $website->load('categories');
        
        $tone = $validated['tone'] ?? $user->ai_default_tone ?? 'conversational';
        $keywords = $validated['keywords'] ?? '';

        // Determine word count based on length
        $wordCount = match($validated['length'] ?? 'medium') {
            'short' => '500-700',
            'medium' => '1000-1500',
            'long' => '2000-3000',
            default => '1000-1500'
        };

        try {
            Log::info('Starting synchronous AI article generation', ['topic' => $validated['topic']]);
            
            $apiKey = $user->openai_api_key;
            $client = \OpenAI::client($apiKey);
            $model = $user->ai_model ?? 'gpt-4o';

            // Determine category
            $category = null;
            $autoCategorize = $validated['auto_categorize'] ?? true;

            if (!empty($validated['category_id']) && !$autoCategorize) {
                $category = Category::find($validated['category_id']);
            } elseif ($website->categories->count() > 0) {
                // AI determines the best category
                $category = $this->determineBestCategory($client, $model, $website, $validated['topic']);
            }

            // Build the prompt
            $prompt = $this->buildPrompt(
                $validated['topic'],
                $wordCount,
                $tone,
                $keywords,
                $category?->name ?? 'General'
            );

            // Call OpenAI API
            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert blog writer who creates engaging, SEO-optimized content.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 4000,
                'temperature' => 0.7,
            ]);

            Log::info('OpenAI response received');

            $generatedContent = $result->choices[0]->message->content ?? '';

            if (empty($generatedContent)) {
                Log::error('Empty content received from OpenAI');
                return back()->withErrors(['error' => 'Failed to generate article content. Please try again.']);
            }

            Log::info('Parsing generated content');

            // Parse the generated content
            $parsed = $this->parseGeneratedContent($generatedContent);

            Log::info('Creating article', ['title' => $parsed['title']]);

            // Create the article
            $article = Article::create([
                'website_id' => $website->id,
                'category_id' => $category?->id,
                'user_id' => auth()->id(),
                'title' => $parsed['title'],
                'slug' => Str::slug($parsed['title']),
                'content' => $parsed['content'],
                'excerpt' => $parsed['excerpt'],
                'featured_image' => $validated['featured_image'] ?? null,
                'meta_title' => $parsed['meta_title'],
                'meta_description' => $parsed['meta_description'],
                'status' => $validated['auto_publish'] ?? false ? 'published' : 'draft',
                'published_at' => $validated['auto_publish'] ?? false ? now() : null,
                'ai_generated' => true,
                'generation_type' => 'ai',
            ]);

            Log::info('Article created successfully', ['article_id' => $article->id]);

            return redirect()->route('superadmin.articles.edit', ['website' => $website->id, 'article' => $article->id])
                ->with('success', 'AI article generated successfully! You can review and edit it before publishing.');

        } catch (\Exception $e) {
            Log::error('AI Article Generation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Error generating article: ' . $e->getMessage()]);
        }
    }

    /**
     * Determine the best category for the article topic using AI
     */
    private function determineBestCategory($client, string $model, Website $website, string $topic): ?Category
    {
        $categories = $website->categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'description' => $cat->description ?? ''
            ];
        })->toArray();

        if (empty($categories)) {
            return null;
        }

        $categoriesJson = json_encode($categories);

        $prompt = <<<PROMPT
Given the following article topic and available categories, determine which category is the BEST fit for this article.

Article Topic: "{$topic}"

Available Categories:
{$categoriesJson}

Respond with ONLY the category ID number that best matches the topic. Just the number, nothing else.
If none of the categories fit well, respond with the ID of the most general/closest category.
PROMPT;

        try {
            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a content categorization expert. Respond only with the category ID number.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 10,
                'temperature' => 0.1,
            ]);

            $categoryId = trim($result->choices[0]->message->content ?? '');
            $categoryId = preg_replace('/[^0-9]/', '', $categoryId);

            if (!empty($categoryId)) {
                $category = $website->categories->firstWhere('id', (int) $categoryId);
                if ($category) {
                    Log::info('AI determined best category', [
                        'topic' => $topic,
                        'category' => $category->name
                    ]);
                    return $category;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to determine category via AI', ['error' => $e->getMessage()]);
        }

        // Fallback to first category
        return $website->categories->first();
    }

    /**
     * Build the AI prompt.
     */
    private function buildPrompt(string $topic, string $wordCount, string $tone, string $keywords, string $category): string
    {
        $keywordsText = !empty($keywords) ? "\n- Naturally weave in these keywords: $keywords" : '';
        
        return <<<PROMPT
You are a professional food blogger and recipe writer who creates authentic, engaging content that reads like it was written by a passionate home cook sharing their personal experience.

Write a blog post about: "{$topic}"

CRITICAL WRITING STYLE RULES - DO NOT VIOLATE THESE:
1. DO NOT use section headers like "Introduction" or "Conclusion" - these scream AI-generated content
2. DO NOT start with generic phrases like "Are you looking for..." or "In this article, we will..."
3. DO NOT use phrases like "In conclusion", "To summarize", "Let's dive in", or "Without further ado"
4. DO NOT follow a formulaic structure - let the content flow naturally like a real blogger would write
5. DO NOT use overused AI phrases like "game-changer", "elevate", "delve into", or "embark on a journey"

HOW TO WRITE THIS (follow this closely):
- Start with a personal anecdote, a relatable moment, or jump straight into the topic with enthusiasm
- Write like you're talking to a friend who asked for your recipe/advice
- Share personal tips, failures, and lessons learned that make it authentic
- Use casual transitions between sections, not formal headers for every paragraph
- If it's a recipe, tell the story behind it before diving into ingredients
- Use descriptive, sensory language (how things smell, taste, feel)
- Include "Pro Tips" or "What You Must Know" boxes where relevant
- For recipes: organize with clear Ingredients and Instructions sections (these can have headers). ALWAYS include both Ingredients AND Instructions sections - instructions are required for recipes.
- End naturally - maybe with a call to try the recipe, a personal note, or asking readers to share their experience

Requirements:
- Length: {$wordCount} words
- Tone: {$tone} (but always authentic and personal)
- Category: {$category}{$keywordsText}
- Use proper HTML formatting: <h2> for major sections (Ingredients, Instructions, Pro Tips), <h3> for subsections, <p>, <ul>, <ol>, <strong>, <em>, <blockquote> for tips/quotes
- Make it SEO-friendly but human-first

Format your response EXACTLY as follows (no markdown code blocks, just plain text):

TITLE: [Write a specific, enticing title that promises value - not generic]

EXCERPT: [2-3 sentences that capture the essence and make readers want more - write it like a teaser, not a summary]

META_TITLE: [SEO title, 50-60 characters]

META_DESCRIPTION: [SEO description, 150-160 characters]

CONTENT:
[Full article in HTML - no ```html markers, just the HTML tags directly]
PROMPT;
    }

    /**
     * Parse the generated content.
     */
    private function parseGeneratedContent(string $content): array
    {
        $title = '';
        $excerpt = '';
        $metaTitle = '';
        $metaDescription = '';
        $articleContent = '';

        // Extract TITLE
        if (preg_match('/TITLE:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $title = trim($matches[1]);
        }

        // Extract EXCERPT
        if (preg_match('/EXCERPT:\s*(.+?)(?=\n\n|META_TITLE|$)/is', $content, $matches)) {
            $excerpt = trim($matches[1]);
        }

        // Extract META_TITLE
        if (preg_match('/META_TITLE:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaTitle = trim($matches[1]);
        }

        // Extract META_DESCRIPTION
        if (preg_match('/META_DESCRIPTION:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaDescription = trim($matches[1]);
        }

        // Extract CONTENT
        if (preg_match('/CONTENT:\s*(.+)$/is', $content, $matches)) {
            $articleContent = trim($matches[1]);
        }

        // Clean the article content - remove markdown code block markers
        $articleContent = $this->cleanContent($articleContent);

        // Fallbacks
        if (empty($title)) {
            $title = 'Untitled Article';
        }
        if (empty($excerpt)) {
            $excerpt = Str::limit(strip_tags($articleContent), 200);
        }
        if (empty($metaTitle)) {
            $metaTitle = Str::limit($title, 60);
        }
        if (empty($metaDescription)) {
            $metaDescription = Str::limit($excerpt, 160);
        }

        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'content' => $articleContent,
        ];
    }

    /**
     * Clean the generated content by removing markdown code block markers and other AI artifacts.
     */
    private function cleanContent(string $content): string
    {
        // Remove markdown code block markers (```html, ```, ```xml, etc.)
        $content = preg_replace('/^```(?:html|xml|markdown|md)?\s*\n?/i', '', $content);
        $content = preg_replace('/\n?```\s*$/i', '', $content);
        
        // Remove any remaining triple backticks in the middle of content
        $content = preg_replace('/```(?:html|xml|markdown|md)?/i', '', $content);
        
        // Remove common AI phrases that slip through
        $aiPhrases = [
            '/\b(In this article,? we will|In this blog post,? we will|Let\'s dive in|Without further ado|In conclusion,?|To summarize,?|To sum up,?|Let me explain)\b/i',
        ];
        
        foreach ($aiPhrases as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }
        
        // Clean up any double spaces or extra newlines created by removals
        $content = preg_replace('/  +/', ' ', $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return trim($content);
    }
}
