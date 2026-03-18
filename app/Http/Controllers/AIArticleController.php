<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Website;
use App\Models\Category;
use App\Models\ArticleGenerationJob;
use App\Jobs\GenerateAIArticleJob;
use App\Jobs\GenerateAIImagesJob;
use App\Services\AIImageService;
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
            'secondary_image' => 'nullable|string|max:1000',
            'auto_publish' => 'boolean',
            'auto_categorize' => 'boolean',
            'background' => 'boolean', // New option for background processing
            'article_type' => 'nullable|in:recipe,article', // Article type: recipe (with /recipes/ URL) or article (root URL)
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
                $validated['featured_image'] ?? null,
                $validated['secondary_image'] ?? null,
                $validated['article_type'] ?? 'recipe'
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

        // Determine word count based on length (increased for more comprehensive articles)
        $wordCount = match($validated['length'] ?? 'medium') {
            'short' => '1000-1500',
            'medium' => '2000-3000',
            'long' => '4000-5000',
            default => '2000-3000'
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

            // Call OpenAI API with increased max_tokens for comprehensive articles
            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert blog writer who creates engaging, SEO-optimized, comprehensive content. You write detailed articles with well-organized paragraphs and in-depth coverage of topics.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 8000,
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
                'secondary_image' => $validated['secondary_image'] ?? null,
                'meta_title' => $parsed['meta_title'],
                'meta_description' => $parsed['meta_description'],
                'status' => $validated['auto_publish'] ?? false ? 'published' : 'draft',
                'published_at' => $validated['auto_publish'] ?? false ? now() : null,
                'ai_generated' => true,
                'generation_type' => 'ai',
                'article_type' => $validated['article_type'] ?? 'recipe',
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

Write a DETAILED, COMPREHENSIVE blog post about: "{$topic}"

MOST CRITICAL RULE - BOLD TITLES ON ALL CONTENT (DO NOT SKIP THIS):
**EVERY SINGLE PARAGRAPH AND LIST ITEM** in the article MUST begin with a bold title. This is NON-NEGOTIABLE.

FOR PARAGRAPHS:
- Format: <p><strong>Descriptive Title Here:</strong> Then your paragraph content...</p>
- WRONG: <p>Journeying into the world of Korean cuisine...</p>
- RIGHT: <p><strong>A Gateway to Korean Flavors:</strong> Journeying into the world of Korean cuisine...</p>

FOR LIST ITEMS (VERY IMPORTANT):
- Format: <li><strong>Title Here:</strong> Then the list item content...</li>
- WRONG: <li>All-in-one comfort meal with protein, potatoes, and cheese</li>
- RIGHT: <li><strong>Complete Comfort Meal:</strong> All-in-one comfort meal with protein, potatoes, and cheese</li>
- WRONG: <li>Savory ranch seasoning with juicy chicken</li>
- RIGHT: <li><strong>Savory Ranch Flavor:</strong> Delicious ranch seasoning perfectly coats the juicy chicken</li>

EVERY <p> and <li> tag MUST start with <strong>Title:</strong>
- NO paragraph or list item should EVER start without a bold title
- If I see ANY paragraph or list item without a bold title, the article is REJECTED

CRITICAL WRITING STYLE RULES - DO NOT VIOLATE THESE:
1. Use DESCRIPTIVE, ENGAGING headers (<h2> and <h3>) to organize your content. Avoid generic ones like "Introduction" or "Conclusion". Instead, use something like "The Secret to Perfect Chickpeas" or "Why This Salad is a Weeknight Hero".
2. DO NOT start with generic phrases like "Are you looking for..." or "In this article, we will..."
3. DO NOT use phrases like "In conclusion", "To summarize", "Let's dive in", or "Without further ado"
4. DO NOT follow a formulaic structure - let the content flow naturally like a real blogger would write
5. DO NOT use overused AI phrases like "game-changer", "elevate", "delve into", or "embark on a journey"
6. REMEMBER: Every <p> AND <li> tag MUST have <strong>Title:</strong> at the start!

HOW TO WRITE THIS (follow this closely):
- Start with a LONG, ENGAGING personal story or anecdote (at least 5-7 detailed paragraphs). Talk about why you love this dish, when you first had it, or a funny kitchen fail related to it. Make readers feel like they're sitting in your kitchen hearing the story.
- Write like you're talking to a friend who asked for your recipe/advice. Be warm, enthusiastic, and VERY thorough.
- Share personal tips, failures, and lessons learned that make it authentic.

PARAGRAPH STRUCTURE (VERY IMPORTANT):
- Each paragraph should be 4-6 sentences minimum, not just 1-2 sentences.
- Use multiple paragraphs per section - don't cram everything into one paragraph.
- Add detailed explanations, examples, and context in each paragraph.
- Every major point deserves its own paragraph with full explanation.

REMINDER - BOLD TITLES ON EVERY PARAGRAPH AND LIST ITEM (MANDATORY):
- EVERY <p> tag = <p><strong>Title:</strong> content</p>
- EVERY <li> tag = <li><strong>Title:</strong> content</li>
- NO EXCEPTIONS. Check every paragraph and list item before submitting.

CONTENT DEPTH REQUIREMENTS:
- Include a section on "Why This Recipe Works" with at least 3 paragraphs explaining the science/technique behind the dish.
- Include a "Tips for Success" section with at least 4-5 detailed tips, each explained in its own paragraph.
- Include a section on variations (e.g., "How to Make it Vegan", "Add a Spicy Kick", "Make it Gluten-Free") with detailed explanations for each variation.
- Include a "Common Mistakes to Avoid" section with at least 3 mistakes and how to fix them.
- Include a "Serving Suggestions" section with pairing ideas, side dishes, and presentation tips.
- Include a "Storage and Reheating" section with detailed instructions.
- Include a "Frequently Asked Questions" section with at least 5 Q&As.

FOR RECIPE CONTENT - INSTRUCTIONS SECTION IS MANDATORY:
- Ingredients section: List items with quantities (e.g., "2 cups flour", "1 lb lamb").
- You MUST include an <h2>Instructions</h2> section - this is NON-NEGOTIABLE
- Instructions section: Use <ol> with step-by-step COOKING ACTIONS starting with verbs (e.g., "1. Preheat oven to 350°F", "2. Sauté onions until golden")
- Include at least 8-12 detailed instruction steps with explanations for WHY each step matters
- CRITICAL: Instructions must be ACTION STEPS (preheat, mix, chop, sauté, bake, simmer, serve) - NOT ingredient descriptions!
- THE ARTICLE WILL BE REJECTED IF THERE IS NO INSTRUCTIONS SECTION
- End naturally with a "Final Thoughts" section (but don't call it "Conclusion") that encourages readers to try it and share their results - make this at least 2-3 paragraphs.

Requirements:
- Length: MINIMUM {$wordCount} words. This is a MINIMUM - feel free to write more! Be as detailed and comprehensive as possible. DO NOT stop early.
- Tone: {$tone} (but always authentic and personal)
- Category: {$category}{$keywordsText}
- Use proper HTML formatting: <h2> for major sections, <h3> for subsections, <p>, <ul>, <ol>, <strong>, <em>, <blockquote> for tips/quotes
- Make it SEO-friendly but human-first

CRITICAL OUTPUT FORMAT RULE:
- DO NOT use markdown syntax like ** or __ in your output
- Use HTML tags only: <strong> for bold, <em> for italic
- All metadata must be plain text without any markdown formatting

Format your response EXACTLY as follows (no markdown code blocks, just plain text):

TITLE: [Write a specific, enticing title - plain text, no markdown]

EXCERPT: [2-3 sentences teaser - plain text, no markdown]

META_TITLE: [SEO title, 50-60 characters - plain text]

META_DESCRIPTION: [SEO description, 150-160 characters - plain text]

CONTENT:
[Full article in HTML - use <strong> tags for bold, NOT ** markdown]
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
        
        // Remove markdown bold markers (**text** -> text) but preserve HTML <strong> tags
        $content = preg_replace('/\*\*([^*]+)\*\*/', '$1', $content);
        // Remove any standalone ** markers that might be left over
        $content = preg_replace('/\*\*/', '', $content);
        
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

    /**
     * Generate AI images for an article (for home decor and similar themes).
     * This can generate images based on article sections/list items.
     */
    public function generateImages(Request $request, Website $website, Article $article)
    {
        $this->authorize('update', $website);

        $user = auth()->user();
        
        // Check the user's selected image generation provider
        $provider = $user->image_generation_provider ?? 'openai';
        
        if ($provider === 'gemini') {
            if (empty($user->gemini_api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API key not configured. Please add your Gemini API key in settings.'
                ], 400);
            }
        } elseif ($provider === 'ideogram') {
            if (empty($user->ideogram_api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ideogram API key not configured. Please add your Ideogram API key in settings.'
                ], 400);
            }
        } else {
            if (empty($user->openai_api_key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI API key not configured. Please add your API key in settings.'
                ], 400);
            }
        }

        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.title' => 'required|string|max:500',
            'items.*.description' => 'nullable|string|max:1000',
            'size' => 'nullable|string|in:1024x1024,1792x1024,1024x1792',
            'quality' => 'nullable|string|in:standard,hd',
            'style' => 'nullable|string|in:natural,vivid',
            'auto_detect' => 'nullable|boolean',
        ]);

        $items = $validated['items'] ?? [];
        $size = $validated['size'] ?? '1024x1024';
        $quality = $validated['quality'] ?? 'standard';
        $style = $validated['style'] ?? 'natural';
        $autoDetect = $validated['auto_detect'] ?? true;

        // Auto-detect items from article content if not provided
        if (empty($items) && $autoDetect) {
            $items = $this->extractListItemsFromContent($article->content, $article->title);
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No items found to generate images for. Please provide items or ensure the article has list sections.'
            ], 400);
        }

        // Dispatch the job to generate images asynchronously
        GenerateAIImagesJob::dispatch(
            $article->id,
            $user->id,
            $items,
            $size,
            $quality,
            $style
        );

        $itemsCount = count($items);
        $providerName = match($provider) {
            'gemini' => 'Google Gemini',
            'ideogram' => 'Ideogram',
            default => 'OpenAI DALL-E'
        };
        
        return response()->json([
            'success' => true,
            'message' => "Image generation started for {$itemsCount} items using {$providerName}. Images will appear in the article once generated.",
            'items_count' => $itemsCount,
            'provider' => $provider,
            'estimated_cost' => $this->estimateImageCost($itemsCount, $size, $quality)
        ]);
    }

    /**
     * Extract list items from article content for image generation.
     */
    private function extractListItemsFromContent(string $content, string $articleTitle): array
    {
        $items = [];

        // First check if this is a "list" type article (e.g., "Top 10 Homes")
        $listInfo = AIImageService::detectListArticle($articleTitle, $content);
        
        if (!$listInfo['is_list']) {
            return [];
        }

        // Extract H2/H3 sections that could be list items
        if (preg_match_all('/<h([23])[^>]*>(?:\d+[\.\):]?\s*)?([^<]+)<\/h\1>(?:\s*<p[^>]*>([^<]+)<\/p>)?/i', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                $title = trim(strip_tags($match[2]));
                $description = isset($match[3]) ? trim(strip_tags($match[3])) : '';
                
                // Filter out generic headers
                if (!preg_match('/^(introduction|conclusion|tips|faq|frequently|summary|overview|ingredients|instructions)/i', $title)) {
                    $items[] = [
                        'title' => $title,
                        'description' => Str::limit($description, 200),
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Estimate the cost of generating images.
     */
    private function estimateImageCost(int $count, string $size, string $quality): float
    {
        $isLargeSize = in_array($size, ['1792x1024', '1024x1792']);
        $isHD = $quality === 'hd';

        $costPerImage = 0.040; // Base: 1024x1024 standard
        
        if ($isLargeSize && $isHD) {
            $costPerImage = 0.120;
        } elseif ($isLargeSize || $isHD) {
            $costPerImage = 0.080;
        }

        return round($count * $costPerImage, 2);
    }

    /**
     * Get AI-generated images for an article.
     */
    public function getArticleImages(Website $website, Article $article)
    {
        $this->authorize('view', $website);

        $images = $article->articleImages()->orderBy('position')->get();

        return response()->json([
            'success' => true,
            'images' => $images
        ]);
    }

    /**
     * Delete an AI-generated image.
     */
    public function deleteArticleImage(Website $website, Article $article, $imageId)
    {
        $this->authorize('update', $website);

        $image = $article->articleImages()->findOrFail($imageId);
        
        // Delete the file from storage
        $filePath = str_replace('/storage/', '', $image->local_path);
        if (\Storage::disk('public')->exists($filePath)) {
            \Storage::disk('public')->delete($filePath);
        }

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }
}
