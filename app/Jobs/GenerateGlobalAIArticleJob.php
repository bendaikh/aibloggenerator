<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Website;
use App\Models\Category;
use App\Models\User;
use App\Models\Author;
use App\Models\ArticleGenerationJob;
use App\Models\ApiUsageLog;
use App\Services\PinterestDesignService;
use App\Services\RewritingService;
use App\Services\VariationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateGlobalAIArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutes

    protected array $generationJobIds = []; // website_id => generation_job_id
    protected array $websiteIds = [];
    protected int $userId = 0;
    protected string $topic = '';
    protected string $tone = '';
    protected string $length = '';
    protected string $keywords = '';
    protected string $ingredients = '';
    protected bool $autoPublish = false;
    protected array $featuredImages = [];
    protected string $articleType = 'recipe';
    protected ?int $variationIndex = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        array $generationJobIds,
        array $websiteIds,
        int $userId,
        string $topic,
        string $tone = 'conversational',
        string $length = 'medium',
        string $keywords = '',
        string $ingredients = '',
        bool $autoPublish = false,
        array $featuredImages = [],
        string $articleType = 'recipe',
        ?int $variationIndex = null
    ) {
        $this->generationJobIds = $generationJobIds;
        $this->websiteIds = $websiteIds;
        $this->userId = $userId;
        $this->topic = $topic;
        $this->tone = $tone;
        $this->length = $length;
        $this->keywords = $keywords;
        $this->ingredients = $ingredients;
        $this->autoPublish = $autoPublish;
        $this->featuredImages = $featuredImages;
        $this->articleType = $articleType;
        $this->variationIndex = $variationIndex;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user || empty($user->openai_api_key)) {
            $this->failAllJobs('User or AI configuration not found.');
            return;
        }

        // Mark all jobs as processing
        foreach ($this->generationJobIds as $jobId) {
            $job = ArticleGenerationJob::find($jobId);
            if ($job) $job->markAsProcessing();
        }

        // Determine generation mode
        $generationMode = $user->article_generation_mode ?? 'full_ai';
        $maxVariations = $user->max_variations ?? 5;
        $websiteCount = count($this->websiteIds);

        Log::info('Starting global background AI article generation', [
            'topic' => $this->topic, 
            'websites' => $websiteCount,
            'mode' => $generationMode,
            'max_variations' => $maxVariations
        ]);

        // Determine word count based on length (increased for more comprehensive articles)
        $wordCount = match($this->length) {
            'short' => '1000-1500',
            'medium' => '2000-3000',
            'long' => '4000-5000',
            default => '2000-3000'
        };

        try {
            if ($generationMode === 'hybrid_rewrite' && $websiteCount >= 1) {
                // HYBRID MODE: Generate 1 master article + rewrite for variations
                // Note: Even with 1 website, we use hybrid mode to demonstrate the feature
                $this->handleHybridMode($user, $wordCount, $maxVariations);
            } else {
                // FULL AI MODE: Generate unique AI article for each website
                $this->handleFullAIMode($user, $wordCount);
            }

            Log::info('Global background AI articles created successfully');

        } catch (\Exception $e) {
            $this->failAllJobs($e->getMessage());
            Log::error('GenerateGlobalAIArticleJob Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle FULL AI mode - generate unique AI content for each website
     */
    private function handleFullAIMode(User $user, string $wordCount): void
    {
        $apiKey = $user->openai_api_key;
        $client = \OpenAI::client($apiKey);
        $model = $user->ai_model ?? 'gpt-4o';
        $imageCount = count($this->featuredImages);

        // Generate UNIQUE article for EACH website
        foreach ($this->websiteIds as $index => $websiteId) {
            $website = Website::with(['categories', 'authors'])->find($websiteId);
            if (!$website) continue;

            $vIndex = $this->variationIndex !== null ? $this->variationIndex : $index;

            $jobId = $this->generationJobIds[$websiteId] ?? null;
            $generationJob = $jobId ? ArticleGenerationJob::find($jobId) : null;

            // Check if this website already has a completed job to avoid duplicates on retry
            if ($generationJob && $generationJob->status === 'completed') {
                Log::info("Skipping website {$websiteId} as it already has a completed article.");
                continue;
            }

            try {
                // Build unique prompt for this website
                $prompt = $this->buildPrompt($wordCount, $website, $vIndex);
                
                // Call OpenAI API for THIS specific website (unique content) with increased max_tokens
                // Using JSON mode for structured, reliable output
                $result = $client->chat()->create([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert blog writer who creates engaging, SEO-optimized, comprehensive content. Each article you write must be completely unique and different from others on the same topic. You write detailed articles with well-organized paragraphs and in-depth coverage. You MUST respond with valid JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 8000,
                    'temperature' => 0.9, // Higher temperature for more variation
                    'response_format' => ['type' => 'json_object'],
                ]);

                $generatedContent = $result->choices[0]->message->content ?? '';
                
                // Log API usage and cost
                $usage = $result->usage ?? null;
                if ($usage) {
                    ApiUsageLog::logUsage(
                        userId: $this->userId,
                        provider: 'openai',
                        model: $model,
                        operation: 'article_generation',
                        promptTokens: $usage->promptTokens ?? 0,
                        completionTokens: $usage->completionTokens ?? 0,
                        generationMode: 'full_ai',
                        articleId: null, // Article not created yet
                        metadata: [
                            'topic' => $this->topic,
                            'website_id' => $websiteId,
                            'variation_index' => $vIndex,
                        ]
                    );
                    
                    Log::info("API Usage logged for website {$websiteId}", [
                        'tokens' => $usage->totalTokens ?? 0,
                        'estimated_cost' => ApiUsageLog::calculateCost($model, $usage->promptTokens ?? 0, $usage->completionTokens ?? 0)
                    ]);
                }

                // Check if job still exists before continuing (user might have cancelled)
                $generationJob = ArticleGenerationJob::find($jobId);
                if (!$generationJob) {
                    Log::info("GenerateGlobalAIArticleJob: Generation job {$jobId} was deleted/cancelled during processing");
                    continue;
                }

                if (empty($generatedContent)) {
                    if ($generationJob) {
                        $generationJob->markAsFailed('Empty content received from OpenAI');
                    }
                    continue;
                }

                // Parse the generated content
                $parsed = $this->parseGeneratedContent($generatedContent);

                // Per-site category determination
                $category = null;
                if ($website->categories->count() > 0) {
                    $category = $this->determineBestCategory($client, $model, $website);
                }

                // Assign image sequentially
                $featuredImage = null;
                $secondaryImage = null;
                if ($imageCount > 0) {
                    $featuredImage = $this->featuredImages[$vIndex % $imageCount];
                    // If there are at least 2 images, use the next one as secondary
                    if ($imageCount >= 2) {
                        $secondaryImage = $this->featuredImages[($vIndex + 1) % $imageCount];
                    }
                }

                // Get default author for this website
                $defaultAuthor = $this->getDefaultAuthor($website);

                // Create the article
                $article = Article::create([
                    'website_id' => $website->id,
                    'category_id' => $category?->id,
                    'user_id' => $this->userId,
                    'author_id' => $defaultAuthor?->id,
                    'title' => $parsed['title'],
                    'slug' => Str::slug($parsed['title']) . '-' . rand(100, 999),
                    'content' => $parsed['content'],
                    'excerpt' => $parsed['excerpt'],
                    'featured_image' => $featuredImage,
                    'secondary_image' => $secondaryImage,
                    'meta_title' => $parsed['meta_title'],
                    'meta_description' => $parsed['meta_description'],
                    'meta_tags' => $parsed['meta_tags'] ?? [],
                    'notes' => $parsed['notes'] ?? [],
                    'prep_time' => $parsed['prep_time'] ?? null,
                    'cook_time' => $parsed['cook_time'] ?? null,
                    'rest_time' => $parsed['rest_time'] ?? null,
                    'total_time' => $parsed['total_time'] ?? null,
                    'status' => $this->autoPublish ? 'published' : 'draft',
                    'published_at' => $this->autoPublish ? now() : null,
                    'ai_generated' => true,
                    'generation_type' => 'ai',
                    'generation_mode' => 'full_ai',
                    'article_type' => $this->articleType,
                ]);

                if ($generationJob) {
                    $generationJob->markAsCompleted($article->id);
                }
                
                Log::info("Generated unique article for website {$websiteId}", ['title' => $parsed['title']]);

                // Generate Pinterest pin if article has images (marked as missing design for manual review)
                if ($article->featured_image) {
                    try {
                        PinterestDesignService::createFromArticle($article, null, null, 'simple_center', 'missing_design', true);
                        Log::info("Pinterest pin created (missing_design) for article on website {$websiteId}", ['article_id' => $article->id]);
                    } catch (\Exception $e) {
                        Log::warning("Failed to create Pinterest pin for website {$websiteId}", [
                            'article_id' => $article->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
            } catch (\Exception $e) {
                if ($generationJob) {
                    $generationJob->markAsFailed($e->getMessage());
                }
                Log::error("Failed to generate article for website {$websiteId}: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle HYBRID mode - generate 1 master article via AI, then rewrite locally for variations
     */
    private function handleHybridMode(User $user, string $wordCount, int $maxVariations): void
    {
        $apiKey = $user->openai_api_key;
        $client = \OpenAI::client($apiKey);
        $model = $user->ai_model ?? 'gpt-4o';
        $imageCount = count($this->featuredImages);
        
        // Initialize services
        $rewritingService = new RewritingService();
        $variationEngine = new VariationEngine($rewritingService);

        // Step 1: Generate the MASTER article using AI (only once!)
        Log::info('Hybrid Mode: Generating master article via AI');
        
        $firstWebsiteId = $this->websiteIds[0];
        $masterWebsite = Website::with(['categories', 'authors'])->find($firstWebsiteId);
        
        if (!$masterWebsite) {
            $this->failAllJobs('Master website not found');
            return;
        }

        try {
            // Build prompt for master article
            $prompt = $this->buildPrompt($wordCount, $masterWebsite, 0);
            
            // Generate master article via AI
            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert blog writer who creates engaging, SEO-optimized, comprehensive content. You write detailed articles with well-organized paragraphs and in-depth coverage. You MUST respond with valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 8000,
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'],
            ]);

            $masterContent = $result->choices[0]->message->content ?? '';
            
            // Log API usage and cost for master article (HYBRID MODE - only 1 API call!)
            $usage = $result->usage ?? null;
            if ($usage) {
                ApiUsageLog::logUsage(
                    userId: $this->userId,
                    provider: 'openai',
                    model: $model,
                    operation: 'article_generation',
                    promptTokens: $usage->promptTokens ?? 0,
                    completionTokens: $usage->completionTokens ?? 0,
                    generationMode: 'hybrid_rewrite',
                    articleId: null,
                    metadata: [
                        'topic' => $this->topic,
                        'is_master_article' => true,
                        'target_websites' => count($this->websiteIds),
                        'note' => 'Master article - variations created locally without additional API costs'
                    ]
                );
                
                Log::info("API Usage logged for HYBRID MODE master article", [
                    'tokens' => $usage->totalTokens ?? 0,
                    'estimated_cost' => ApiUsageLog::calculateCost($model, $usage->promptTokens ?? 0, $usage->completionTokens ?? 0),
                    'websites_count' => count($this->websiteIds),
                    'savings' => 'Only 1 API call for ' . count($this->websiteIds) . ' articles!'
                ]);
            }
            
            if (empty($masterContent)) {
                $this->failAllJobs('Empty master content received from OpenAI');
                return;
            }

            // Parse master content
            $masterParsed = $this->parseGeneratedContent($masterContent);
            
            Log::info('Hybrid Mode: Master article generated successfully', [
                'title' => $masterParsed['title']
            ]);

            // Step 2: Create master article and variations
            $variationCount = min($maxVariations, count($this->websiteIds));
            
            foreach ($this->websiteIds as $index => $websiteId) {
                $website = Website::with(['categories', 'authors'])->find($websiteId);
                if (!$website) continue;

                $jobId = $this->generationJobIds[$websiteId] ?? null;
                $generationJob = $jobId ? ArticleGenerationJob::find($jobId) : null;

                if ($generationJob && $generationJob->status === 'completed') {
                    Log::info("Skipping website {$websiteId} - already completed");
                    continue;
                }

                try {
                    $isMaster = ($index === 0);
                    $articleData = null;
                    $masterArticleId = null;

                    if ($isMaster) {
                        // First website gets the master article (original AI content)
                        $articleData = $masterParsed;
                        Log::info("Creating master article for website {$websiteId}");
                    } else {
                        // Subsequent websites get locally rewritten variations
                        Log::info("Creating variation {$index} for website {$websiteId}");
                        $articleData = $variationEngine->createVariation($masterParsed, $index);
                        
                        // Calculate uniqueness score
                        $uniquenessScore = $variationEngine->calculateUniquenessScore($articleData, $masterParsed);
                        Log::info("Variation uniqueness score: {$uniquenessScore}%");
                    }

                    // Determine category
                    $category = null;
                    if ($website->categories->count() > 0) {
                        $category = $this->determineBestCategory($client, $model, $website);
                    }

                    // Assign images
                    $featuredImage = null;
                    $secondaryImage = null;
                    if ($imageCount > 0) {
                        $featuredImage = $this->featuredImages[$index % $imageCount];
                        if ($imageCount >= 2) {
                            $secondaryImage = $this->featuredImages[($index + 1) % $imageCount];
                        }
                    }

                    $defaultAuthor = $this->getDefaultAuthor($website);

                    // Create article
                    $article = Article::create([
                        'website_id' => $website->id,
                        'category_id' => $category?->id,
                        'user_id' => $this->userId,
                        'author_id' => $defaultAuthor?->id,
                        'master_article_id' => $isMaster ? null : null, // Will be set after master is created
                        'title' => $articleData['title'],
                        'slug' => Str::slug($articleData['title']) . '-' . rand(100, 999),
                        'content' => $articleData['content'],
                        'excerpt' => $articleData['excerpt'],
                        'featured_image' => $featuredImage,
                        'secondary_image' => $secondaryImage,
                        'meta_title' => $articleData['meta_title'],
                        'meta_description' => $articleData['meta_description'],
                        'meta_tags' => $articleData['meta_tags'] ?? [],
                        'notes' => $articleData['notes'] ?? [],
                        'prep_time' => $articleData['prep_time'] ?? null,
                        'cook_time' => $articleData['cook_time'] ?? null,
                        'rest_time' => $articleData['rest_time'] ?? null,
                        'total_time' => $articleData['total_time'] ?? null,
                        'status' => $this->autoPublish ? 'published' : 'draft',
                        'published_at' => $this->autoPublish ? now() : null,
                        'ai_generated' => true,
                        'generation_type' => 'ai',
                        'generation_mode' => 'hybrid_rewrite',
                        'article_type' => $this->articleType,
                        'variation_index' => $isMaster ? null : $index,
                        'variation_metadata' => $isMaster ? null : [
                            'rewritten_locally' => true,
                            'variation_index' => $index,
                            'created_at' => now()->toISOString(),
                        ],
                    ]);

                    // Link variations to master
                    if ($isMaster) {
                        $masterArticleId = $article->id;
                        Log::info("Master article created with ID: {$masterArticleId}");
                    } else {
                        // Update variation to link to master
                        // We need to find the master article for this batch
                        $masterArticle = Article::where('user_id', $this->userId)
                            ->where('generation_mode', 'hybrid_rewrite')
                            ->whereNull('master_article_id')
                            ->where('title', 'LIKE', '%' . substr($this->topic, 0, 20) . '%')
                            ->latest()
                            ->first();
                        
                        if ($masterArticle) {
                            $article->update(['master_article_id' => $masterArticle->id]);
                            Log::info("Linked variation to master article {$masterArticle->id}");
                        }
                    }

                    if ($generationJob) {
                        $generationJob->markAsCompleted($article->id);
                    }

                    Log::info("Created article for website {$websiteId}", [
                        'is_master' => $isMaster,
                        'title' => $article->title
                    ]);

                    // Generate Pinterest pin
                    if ($article->featured_image) {
                        try {
                            PinterestDesignService::createFromArticle($article, null, null, 'simple_center', 'missing_design', true);
                        } catch (\Exception $e) {
                            Log::warning("Failed to create Pinterest pin: " . $e->getMessage());
                        }
                    }

                } catch (\Exception $e) {
                    if ($generationJob) {
                        $generationJob->markAsFailed($e->getMessage());
                    }
                    Log::error("Failed to create article for website {$websiteId}: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            $this->failAllJobs('Master article generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function failAllJobs(string $message): void
    {
        foreach ($this->generationJobIds as $jobId) {
            $job = ArticleGenerationJob::find($jobId);
            if ($job) $job->markAsFailed($message);
        }
    }

    /**
     * Get the default author for a website (first active author).
     */
    private function getDefaultAuthor(Website $website): ?Author
    {
        // Get the first active author for this website
        return $website->authors
            ->where('is_active', true)
            ->first();
    }

    /**
     * Determine the best category for the article topic using LOCAL keyword matching.
     * This is much faster than making an additional API call (~0ms vs ~5-10 seconds).
     */
    private function determineBestCategory($client, string $model, Website $website): ?Category
    {
        $categories = $website->categories;

        if ($categories->isEmpty()) {
            return null;
        }

        // Prepare topic words for matching (lowercase, remove common words)
        $topicWords = $this->extractKeywords($this->topic);
        $keywordWords = !empty($this->keywords) ? $this->extractKeywords($this->keywords) : [];
        $allSearchWords = array_unique(array_merge($topicWords, $keywordWords));

        $bestMatch = null;
        $bestScore = 0;

        foreach ($categories as $category) {
            $categoryWords = $this->extractKeywords($category->name . ' ' . ($category->description ?? ''));
            
            // Calculate match score
            $score = 0;
            foreach ($allSearchWords as $word) {
                foreach ($categoryWords as $catWord) {
                    // Exact match
                    if ($word === $catWord) {
                        $score += 3;
                    }
                    // Partial match (word contains or is contained)
                    elseif (strlen($word) >= 3 && strlen($catWord) >= 3) {
                        if (str_contains($catWord, $word) || str_contains($word, $catWord)) {
                            $score += 1;
                        }
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $category;
            }
        }

        // Return best match, or first category if no good match found
        return $bestMatch ?? $categories->first();
    }

    /**
     * Extract keywords from a string for category matching.
     */
    private function extractKeywords(string $text): array
    {
        // Convert to lowercase and extract words
        $text = strtolower($text);
        
        // Remove common stop words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'what', 'which', 'who', 'whom', 'when', 'where', 'why', 'how', 'all', 'each', 'every', 'both', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just', 'recipe', 'recipes', 'best', 'easy', 'homemade', 'delicious', 'simple', 'quick', 'make', 'how'];
        
        // Extract words (letters only, min 2 chars)
        preg_match_all('/[a-z]{2,}/', $text, $matches);
        $words = $matches[0] ?? [];
        
        // Remove stop words
        $words = array_diff($words, $stopWords);
        
        return array_values(array_unique($words));
    }

    /**
     * Build the AI prompt with variation for unique articles.
     */
    private function buildPrompt(string $wordCount, Website $website, int $variationIndex): string
    {
        $keywordsText = !empty($this->keywords) ? "\n- Naturally weave in these keywords: {$this->keywords}" : '';
        
        // Handle ingredients: if provided by user, tell AI to use them; otherwise AI generates
        $ingredientsText = '';
        if (!empty($this->ingredients)) {
            $ingredientsText = <<<INGREDIENTS_INSTRUCTION

IMPORTANT - INGREDIENTS ARE PROVIDED BY USER:
- The user has provided these ingredients: {$this->ingredients}
- Use ONLY these ingredients in the "ingredients" JSON array (split by comma/newline)
- You MUST include detailed "instructions" array with ACTUAL COOKING STEPS

INSTRUCTIONS MUST BE COOKING ACTIONS, FOR EXAMPLE:
- "Preheat your oven to 350°F (175°C)"
- "In a large bowl, combine the dry ingredients"
- "Heat oil in a pan over medium heat"
- "Add the onions and sauté until translucent"

DO NOT repeat ingredient names as instructions. Instructions are VERBS/ACTIONS (preheat, mix, chop, sauté, bake, stir, simmer, serve).
INGREDIENTS_INSTRUCTION;
        } else {
            $ingredientsText = <<<INGREDIENTS_INSTRUCTION

FOR RECIPE CONTENT:
- You MUST generate a comprehensive "ingredients" array with exact quantities (e.g., ["2 cups flour", "1 lb chicken breast", "1 tsp salt"])
- You MUST include an "instructions" array with step-by-step COOKING ACTIONS
- These MUST be different! Ingredients = WHAT you need. Instructions = HOW to cook (action verbs).
INGREDIENTS_INSTRUCTION;
        }
        
        // Add variation instructions to ensure unique articles
        $variationStyles = [
            "Focus on beginner-friendly tips and simple explanations.",
            "Take an expert perspective with advanced techniques and insider knowledge.",
            "Use a storytelling approach with personal anecdotes and experiences.",
            "Focus on quick tips and time-saving hacks.",
            "Take a health-conscious and nutritional perspective.",
            "Focus on budget-friendly options and cost-saving ideas.",
            "Emphasize traditional methods and classic approaches.",
            "Take a modern, trendy perspective with current innovations.",
            "Focus on family-friendly adaptations and kid-approved variations.",
            "Take an international perspective, comparing different regional approaches.",
        ];
        
        $variationStyle = $variationStyles[$variationIndex % count($variationStyles)];
        $randomSeed = rand(1000, 9999);
        
        $ingredientsPrompt = "";
        if ($this->articleType === 'recipe') {
            $ingredientsPrompt = "⚠️ CRITICAL FOR RECIPES - INGREDIENTS ARE ABSOLUTELY MANDATORY ⚠️\n";
            $ingredientsPrompt .= "YOUR RECIPE WILL BE REJECTED IF THE \"ingredients\" ARRAY IS EMPTY!\n\n";
            if (!empty($this->ingredients)) {
                $ingredientsPrompt .= "- The \"ingredients\" array MUST contain ONLY these ingredients: {$this->ingredients}\n";
                $ingredientsPrompt .= "- Split them properly into the array format.\n";
            } else {
                $ingredientsPrompt .= "- The \"ingredients\" array MUST contain 8-15 ingredients with EXACT quantities (e.g., \"2 cups flour\", \"1 lb chicken breast\", \"3 cloves garlic, minced\").\n";
                $ingredientsPrompt .= "- NEVER leave the ingredients array empty - this is the MOST IMPORTANT part of a recipe!\n";
            }
            $ingredientsPrompt .= "- The \"instructions\" array MUST contain 8-12 detailed cooking steps.\n";
            $ingredientsPrompt .= "- Each instruction MUST be a PLAIN TEXT cooking step starting with an ACTION VERB (Preheat, Mix, Add, Stir, Bake, etc.).\n";
            $ingredientsPrompt .= "- DO NOT include HTML tags inside ingredients or instructions arrays - they should be PLAIN TEXT strings.\n";
            $ingredientsPrompt .= "- DO NOT include ingredients or instructions in the \"content\" HTML - they go ONLY in their own arrays.\n";
        }

        return <<<PROMPT
You are a professional blog writer who creates authentic, engaging content.

Write a DETAILED, COMPREHENSIVE and COMPLETELY UNIQUE blog post about: "{$this->topic}"

CRITICAL TITLE RULE:
- The TITLE field below is pre-filled with the exact title the user wants. DO NOT CHANGE IT. Use it exactly as written - no additions, no modifications, no "improvements".

UNIQUENESS REQUIREMENT (Variation #{$variationIndex}, Seed: {$randomSeed}):
- {$variationStyle}
- Use different examples, metaphors, and explanations than typical articles
- Create a fresh, original perspective that stands out

{$ingredientsPrompt}

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
1. Use DESCRIPTIVE, ENGAGING headers (<h2> and <h3>) to organize your content. Avoid generic ones like "Introduction" or "Conclusion". Instead, use creative headers that fit the topic.
2. DO NOT start with generic phrases like "Are you looking for..." or "In this article, we will..."
3. DO NOT use phrases like "In conclusion", "To summarize", "Let's dive in", or "Without further ado"
4. DO NOT follow a formulaic structure
5. DO NOT use overused AI phrases like "game-changer", "elevate", "delve into", or "embark on a journey"
6. REMEMBER: Every <p> AND <li> tag MUST have <strong>Title:</strong> at the start!

HOW TO WRITE THIS (follow this closely):
- Start with a LONG, ENGAGING introduction (at least 5-7 detailed paragraphs). Use storytelling, personal anecdotes, or historical context to draw the reader in. Make readers feel connected to your story.
- Write like you're talking to a friend. Be warm, enthusiastic, and VERY thorough.

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
- Include a section on "Why This Works" or "The Science Behind It" with at least 3 paragraphs explaining the technique or reasoning.
- Include a "Tips for Success" section with at least 4-5 detailed tips, each explained in its own paragraph.
- Include a section on variations with detailed explanations for each option.
- Include a "Common Mistakes to Avoid" section with at least 3 mistakes and how to fix them.
- Include a "Serving Suggestions" or "How to Use" section with detailed ideas.
- Include a "Storage Tips" or "Making Ahead" section with detailed instructions.
- Include a "Frequently Asked Questions" section with at least 5 Q&As.
- End naturally with a final section that encourages reader engagement - make this at least 2-3 paragraphs.

Requirements:
- Length: MINIMUM {$wordCount} words. This is a MINIMUM - feel free to write more! Be as detailed and comprehensive as possible. DO NOT stop early.
- Tone: {$this->tone} (but always authentic and personal)
- Use proper HTML formatting: <h2> for major sections, <h3> for subsections, <p>, <ul>, <ol>, <strong>, <em>, <blockquote> for tips/quotes
- Make it SEO-friendly but human-first{$keywordsText}

CRITICAL FOR RECIPES - JSON ARRAYS ARE MANDATORY:
- The "ingredients" JSON array MUST contain all ingredients with exact quantities
- The "instructions" JSON array MUST contain 8-12 step-by-step cooking directions
- Each instruction step must START WITH AN ACTION VERB: Preheat, Mix, Chop, Sauté, Bake, Stir, Add, Pour, Heat, Season, Serve, etc.
- WRONG instruction: "Meat: Traditionally lamb is used" (this is an ingredient description, NOT an instruction)
- RIGHT instruction: "Season the lamb with salt and pepper, then sear in a hot pan for 3 minutes per side"
- THE JSON RESPONSE WILL BE REJECTED IF "ingredients" OR "instructions" ARRAYS ARE EMPTY

CRITICAL OUTPUT FORMAT RULE:
- DO NOT use markdown syntax like ** or __ in your output
- Use HTML tags only: <strong> for bold, <em> for italic
- Times, notes, and all metadata must be plain text without any markdown formatting

YOU MUST RESPOND WITH A VALID JSON OBJECT. The JSON structure must be EXACTLY as follows:

{
  "title": "{$this->topic}",
  "excerpt": "2-3 sentences teaser - plain text, no markdown",
  "meta_title": "SEO title, 50-60 characters - plain text",
  "meta_description": "SEO description, 150-160 characters - plain text",
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "prep_time": "10 mins",
  "cook_time": "25 mins",
  "rest_time": "5 mins",
  "total_time": "40 mins",
  "notes": ["Pro tip 1", "Pro tip 2", "Pro tip 3"],
  "ingredients": ["1 cup flour", "2 eggs", "1 tsp salt"],
  "instructions": ["Step 1: Preheat oven to 350°F", "Step 2: Mix dry ingredients", "Step 3: Add wet ingredients"],
  "content": "<h2>Introduction</h2><p><strong>Opening Title:</strong> Your engaging introduction...</p>..."
}

REQUIRED JSON FIELDS (ALL MUST BE PRESENT):
- "title": EXACTLY "{$this->topic}" (do not change)
- "excerpt": String, 2-3 sentences teaser (PLAIN TEXT, no HTML)
- "meta_title": String, SEO title 50-60 characters (PLAIN TEXT, no HTML)
- "meta_description": String, SEO description 150-160 characters (PLAIN TEXT, no HTML)
- "tags": Array of 5-8 relevant tag strings (PLAIN TEXT, no HTML)
- "prep_time": String, e.g. "10 mins" (PLAIN TEXT, empty string if not applicable)
- "cook_time": String, e.g. "25 mins" (PLAIN TEXT, empty string if not applicable)
- "rest_time": String, e.g. "5 mins" (PLAIN TEXT, empty string if not applicable)
- "total_time": String, e.g. "40 mins" (PLAIN TEXT, empty string if not applicable)
- "notes": Array of 3-5 pro tip strings (PLAIN TEXT, no HTML)
- "ingredients": ⚠️ MANDATORY ARRAY - must contain 8-15 ingredient strings with exact quantities (PLAIN TEXT ONLY, e.g. ["2 cups flour", "1 lb chicken breast", "3 cloves garlic, minced"])
- "instructions": ⚠️ MANDATORY ARRAY - must contain 8-12 cooking step strings (PLAIN TEXT ONLY, each starting with action verb, e.g. ["Preheat oven to 350°F.", "Mix the dry ingredients in a bowl."])
- "content": String containing the full article in HTML format (use <strong> for bold, <em> for italic)

⚠️ CRITICAL FOR RECIPES - READ CAREFULLY ⚠️
- The "ingredients" array is MANDATORY and MUST contain 8-15 ingredients with exact quantities
- The "instructions" array is MANDATORY and MUST contain 8-12 step-by-step cooking directions
- Each instruction MUST start with an ACTION VERB: Preheat, Mix, Chop, Sauté, Bake, Stir, Add, Pour, Heat, Season, Serve, etc.
- BOTH "ingredients" AND "instructions" arrays MUST be PLAIN TEXT - NO HTML TAGS inside them!
- Example ingredients: ["2 cups all-purpose flour", "1 lb ground beef", "3 cloves garlic, minced", "1/2 cup olive oil"]
- Example instructions: ["Preheat the oven to 375°F.", "In a large bowl, combine flour and salt.", "Heat oil in a skillet over medium heat."]
- IF EITHER ARRAY IS EMPTY, THE RECIPE WILL BE REJECTED!

IMPORTANT: Return ONLY the JSON object, no additional text before or after.
PROMPT;
    }

    /**
     * Parse the generated content from JSON response.
     */
    private function parseGeneratedContent(string $content): array
    {
        // Default values
        $title = $this->topic;
        $excerpt = '';
        $metaTitle = '';
        $metaDescription = '';
        $metaTags = [];
        $notes = [];
        $prepTime = '';
        $cookTime = '';
        $restTime = '';
        $totalTime = '';
        $articleContent = '';
        $ingredients = [];
        $instructions = [];

        // Try to parse JSON response
        $jsonData = json_decode($content, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            // Successfully parsed JSON
            Log::info('Successfully parsed JSON response from AI');
            
            // ALWAYS use the original topic as the title - never let AI change it
            $title = $this->topic;
            
            // Use stripHtmlAndClean for plain text fields to remove any HTML elements
            $excerpt = $this->stripHtmlAndClean($jsonData['excerpt'] ?? '');
            $metaTitle = $this->stripHtmlAndClean($jsonData['meta_title'] ?? '');
            $metaDescription = $this->stripHtmlAndClean($jsonData['meta_description'] ?? '');
            
            // Handle tags - could be array or comma-separated string (strip HTML from each tag)
            if (isset($jsonData['tags'])) {
                if (is_array($jsonData['tags'])) {
                    $metaTags = array_slice(array_filter(array_map([$this, 'stripHtmlAndClean'], $jsonData['tags'])), 0, 10);
                } else {
                    $tagsArray = array_map('trim', explode(',', $jsonData['tags']));
                    $metaTags = array_slice(array_filter(array_map([$this, 'stripHtmlAndClean'], $tagsArray)), 0, 10);
                }
            }
            
            // Strip HTML from time fields
            $prepTime = $this->stripHtmlAndClean($jsonData['prep_time'] ?? '');
            $cookTime = $this->stripHtmlAndClean($jsonData['cook_time'] ?? '');
            $restTime = $this->stripHtmlAndClean($jsonData['rest_time'] ?? '');
            $totalTime = $this->stripHtmlAndClean($jsonData['total_time'] ?? '');
            
            // Handle notes - could be array or newline-separated string (strip HTML from each note)
            if (isset($jsonData['notes'])) {
                if (is_array($jsonData['notes'])) {
                    $notes = array_slice(array_filter(array_map([$this, 'stripHtmlAndClean'], $jsonData['notes'])), 0, 10);
                } else {
                    $notesArray = array_filter(array_map('trim', explode("\n", $jsonData['notes'])));
                    $notes = array_slice(array_filter(array_map([$this, 'stripHtmlAndClean'], $notesArray)), 0, 10);
                }
            }
            
            // Get ingredients from JSON (strip HTML from each ingredient)
            if (isset($jsonData['ingredients']) && is_array($jsonData['ingredients'])) {
                $ingredients = array_filter(array_map([$this, 'stripHtmlAndClean'], $jsonData['ingredients']));
            }
            
            // Get instructions from JSON (strip HTML from each instruction)
            if (isset($jsonData['instructions']) && is_array($jsonData['instructions'])) {
                $instructions = array_filter(array_map([$this, 'stripHtmlAndClean'], $jsonData['instructions']));
            }
            
            $articleContent = $jsonData['content'] ?? '';
            
        } else {
            // Fallback: try to parse as the old text format if JSON parsing fails
            Log::warning('JSON parsing failed, falling back to text parsing', [
                'error' => json_last_error_msg(),
                'content_preview' => substr($content, 0, 500)
            ]);
            
            // Use legacy regex parsing as fallback
            return $this->parseGeneratedContentLegacy($content);
        }

        $articleContent = $this->cleanContent($articleContent);

        // Build ingredients and instructions HTML sections from JSON data
        $articleContent = $this->buildRecipeSectionsFromJson($articleContent, $ingredients, $instructions);

        // Fallback for empty fields
        if (empty($excerpt)) $excerpt = Str::limit(strip_tags($articleContent), 200);
        if (empty($metaTitle)) $metaTitle = Str::limit($title, 60);
        if (empty($metaDescription)) $metaDescription = Str::limit($excerpt, 160);

        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_tags' => $metaTags,
            'notes' => $notes,
            'prep_time' => $prepTime,
            'cook_time' => $cookTime,
            'rest_time' => $restTime,
            'total_time' => $totalTime,
            'content' => $articleContent,
        ];
    }

    /**
     * Build recipe sections (ingredients and instructions) from JSON arrays.
     */
    private function buildRecipeSectionsFromJson(string $content, array $ingredients, array $instructions): string
    {
        // If user provided ingredients, use those instead of AI-generated ones
        if (!empty($this->ingredients)) {
            $ingredients = array_map('trim', preg_split('/[,\n]+/', $this->ingredients));
            $ingredients = array_filter($ingredients);
        }
        
        // Remove any existing ingredients section from content (we'll add our own)
        $content = $this->removeExistingIngredientsSection($content);
        
        // Remove any existing instructions section from content if we have JSON instructions
        if (!empty($instructions)) {
            $content = $this->removeExistingInstructionsSection($content);
        }
        
        // Build ingredients HTML
        $ingredientsHtml = '';
        if (!empty($ingredients)) {
            $ingredientsHtml = "\n\n<h2>Ingredients</h2>\n<ul>\n";
            foreach ($ingredients as $ingredient) {
                $ingredient = trim($ingredient);
                if (!empty($ingredient)) {
                    $ingredientsHtml .= "    <li>" . htmlspecialchars($ingredient) . "</li>\n";
                }
            }
            $ingredientsHtml .= "</ul>";
        }
        
        // Build instructions HTML
        $instructionsHtml = '';
        if (!empty($instructions)) {
            $instructionsHtml = "\n\n<h2>Instructions</h2>\n<ol>\n";
            foreach ($instructions as $index => $instruction) {
                $instruction = trim($instruction);
                if (!empty($instruction)) {
                    // Remove leading step numbers if present (e.g., "1. ", "Step 1: ")
                    $instruction = preg_replace('/^(?:Step\s*)?\d+[.:]\s*/i', '', $instruction);
                    $instructionsHtml .= "    <li><strong>Step " . ($index + 1) . ":</strong> " . htmlspecialchars($instruction) . "</li>\n";
                }
            }
            $instructionsHtml .= "</ol>";
        }
        
        // Find the best position to insert ingredients and instructions
        // Look for where the main content sections end (before FAQ, Tips, etc.)
        $insertPosition = $this->findInsertPositionForRecipeSections($content);
        
        if ($insertPosition !== false) {
            return substr($content, 0, $insertPosition) . $ingredientsHtml . $instructionsHtml . substr($content, $insertPosition);
        }
        
        // Default: append at the end
        return $content . $ingredientsHtml . $instructionsHtml;
    }

    /**
     * Remove existing ingredients section from content.
     */
    private function removeExistingIngredientsSection(string $content): string
    {
        // Use DOM parser to safely remove ingredients section
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $xpath = new \DOMXPath($dom);
        $headers = $xpath->query('//h2|//h3');
        $elementsToRemove = [];
        
        foreach ($headers as $header) {
            $headerText = strtolower(trim($header->textContent));
            
            if ((strpos($headerText, 'ingredient') !== false) && 
                (strpos($headerText, 'instruction') === false) &&
                (strpos($headerText, 'step') === false) &&
                (strpos($headerText, 'direction') === false)) {
                
                $elementsToRemove[] = $header;
                
                $sibling = $header->nextSibling;
                while ($sibling) {
                    if ($sibling->nodeType === XML_TEXT_NODE) {
                        $sibling = $sibling->nextSibling;
                        continue;
                    }
                    
                    if ($sibling->nodeName === 'ul' || $sibling->nodeName === 'ol') {
                        $elementsToRemove[] = $sibling;
                        break;
                    }
                    
                    if ($sibling->nodeName === 'h2' || $sibling->nodeName === 'h3') {
                        break;
                    }
                    
                    $sibling = $sibling->nextSibling;
                }
            }
        }
        
        foreach ($elementsToRemove as $element) {
            if ($element->parentNode) {
                $element->parentNode->removeChild($element);
            }
        }
        
        $wrapper = $dom->getElementsByTagName('div')->item(0);
        $cleanedContent = '';
        if ($wrapper) {
            foreach ($wrapper->childNodes as $child) {
                $cleanedContent .= $dom->saveHTML($child);
            }
        }
        
        return preg_replace('/\n{3,}/', "\n\n", trim($cleanedContent));
    }

    /**
     * Remove existing instructions section from content.
     */
    private function removeExistingInstructionsSection(string $content): string
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $xpath = new \DOMXPath($dom);
        $headers = $xpath->query('//h2|//h3');
        $elementsToRemove = [];
        
        foreach ($headers as $header) {
            $headerText = strtolower(trim($header->textContent));
            
            if ((strpos($headerText, 'instruction') !== false) || 
                (strpos($headerText, 'direction') !== false) ||
                (strpos($headerText, 'how to make') !== false) ||
                (strpos($headerText, 'method') !== false && strpos($headerText, 'cooking method') !== false)) {
                
                $elementsToRemove[] = $header;
                
                $sibling = $header->nextSibling;
                while ($sibling) {
                    if ($sibling->nodeType === XML_TEXT_NODE) {
                        $sibling = $sibling->nextSibling;
                        continue;
                    }
                    
                    if ($sibling->nodeName === 'ul' || $sibling->nodeName === 'ol') {
                        $elementsToRemove[] = $sibling;
                        break;
                    }
                    
                    if ($sibling->nodeName === 'h2' || $sibling->nodeName === 'h3') {
                        break;
                    }
                    
                    $sibling = $sibling->nextSibling;
                }
            }
        }
        
        foreach ($elementsToRemove as $element) {
            if ($element->parentNode) {
                $element->parentNode->removeChild($element);
            }
        }
        
        $wrapper = $dom->getElementsByTagName('div')->item(0);
        $cleanedContent = '';
        if ($wrapper) {
            foreach ($wrapper->childNodes as $child) {
                $cleanedContent .= $dom->saveHTML($child);
            }
        }
        
        return preg_replace('/\n{3,}/', "\n\n", trim($cleanedContent));
    }

    /**
     * Find the best position to insert recipe sections (ingredients and instructions).
     */
    private function findInsertPositionForRecipeSections(string $content): int|false
    {
        // Look for typical sections that should come AFTER ingredients/instructions
        $sectionsAfterRecipe = [
            'tips',
            'faq',
            'frequently asked',
            'common mistakes',
            'storage',
            'serving',
            'variations',
            'notes',
        ];
        
        // Find the earliest occurrence of these sections
        $earliestPosition = false;
        
        foreach ($sectionsAfterRecipe as $section) {
            if (preg_match('/<h[23][^>]*>[^<]*' . preg_quote($section, '/') . '[^<]*<\/h[23]>/i', $content, $matches, \PREG_OFFSET_CAPTURE)) {
                $position = $matches[0][1];
                if ($earliestPosition === false || $position < $earliestPosition) {
                    $earliestPosition = $position;
                }
            }
        }
        
        return $earliestPosition;
    }

    /**
     * Legacy parsing method for backwards compatibility when JSON parsing fails.
     */
    private function parseGeneratedContentLegacy(string $content): array
    {
        $title = $this->topic;
        $excerpt = '';
        $metaTitle = '';
        $metaDescription = '';
        $metaTags = [];
        $notes = [];
        $prepTime = '';
        $cookTime = '';
        $restTime = '';
        $totalTime = '';
        $articleContent = '';

        if (preg_match('/EXCERPT:\s*(.+?)(?=\n\n|META_TITLE|$)/is', $content, $matches)) {
            $excerpt = $this->stripHtmlAndClean(trim($matches[1]));
        }
        if (preg_match('/META_TITLE:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaTitle = $this->stripHtmlAndClean(trim($matches[1]));
        }
        if (preg_match('/META_DESCRIPTION:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaDescription = $this->stripHtmlAndClean(trim($matches[1]));
        }
        if (preg_match('/TAGS:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $tagsString = trim($matches[1]);
            $tagsArray = array_map('trim', explode(',', $tagsString));
            $metaTags = array_slice(array_filter(array_map([$this, 'stripHtmlAndClean'], $tagsArray)), 0, 10);
        }
        if (preg_match('/PREP_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $prepTime = $this->stripHtmlAndClean(trim($matches[1]));
        }
        if (preg_match('/COOK_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $cookTime = $this->stripHtmlAndClean(trim($matches[1]));
        }
        if (preg_match('/REST_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $restTime = $this->stripHtmlAndClean(trim($matches[1]));
        }
        if (preg_match('/TOTAL_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $totalTime = $this->stripHtmlAndClean(trim($matches[1]));
        }
        if (preg_match('/NOTES:\s*(.+?)(?=\n\n|CONTENT:|$)/is', $content, $matches)) {
            $notesString = trim($matches[1]);
            $notesArray = array_filter(array_map('trim', explode("\n", $notesString)));
            $notes = array_slice(array_filter(array_map([$this, 'stripHtmlAndClean'], $notesArray)), 0, 10);
        }
        if (preg_match('/CONTENT:\s*(.+)$/is', $content, $matches)) {
            $articleContent = trim($matches[1]);
        }

        $articleContent = $this->cleanContent($articleContent);

        if (!empty($this->ingredients)) {
            $articleContent = $this->ensureIngredientsAtEnd($articleContent);
        }

        if (empty($excerpt)) $excerpt = Str::limit(strip_tags($articleContent), 200);
        if (empty($metaTitle)) $metaTitle = Str::limit($title, 60);
        if (empty($metaDescription)) $metaDescription = Str::limit($excerpt, 160);

        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_tags' => $metaTags,
            'notes' => $notes,
            'prep_time' => $prepTime,
            'cook_time' => $cookTime,
            'rest_time' => $restTime,
            'total_time' => $totalTime,
            'content' => $articleContent,
        ];
    }

    /**
     * Ensure ingredients section appears at the end of the article.
     * This function will REMOVE any existing ingredients section and add it at the very end.
     * IMPORTANT: This method carefully preserves the Instructions section!
     */
    private function ensureIngredientsAtEnd(string $content): string
    {
        // Create a DOM parser to safely manipulate HTML without breaking other sections
        $dom = new \DOMDocument();
        // Suppress warnings for malformed HTML
        @$dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $xpath = new \DOMXPath($dom);
        
        // Find and remove ONLY the Ingredients section (not Instructions!)
        // Look for h2 or h3 headers that contain "Ingredient" but NOT "Instruction"
        $headers = $xpath->query('//h2|//h3');
        $elementsToRemove = [];
        
        foreach ($headers as $header) {
            $headerText = strtolower(trim($header->textContent));
            
            // Only target ingredients headers, NOT instructions
            if ((strpos($headerText, 'ingredient') !== false) && 
                (strpos($headerText, 'instruction') === false) &&
                (strpos($headerText, 'step') === false) &&
                (strpos($headerText, 'direction') === false)) {
                
                $elementsToRemove[] = $header;
                
                // Also remove the list that follows the header
                $sibling = $header->nextSibling;
                while ($sibling) {
                    // Skip text nodes
                    if ($sibling->nodeType === XML_TEXT_NODE) {
                        $sibling = $sibling->nextSibling;
                        continue;
                    }
                    
                    // If we hit a ul or ol, mark it for removal
                    if ($sibling->nodeName === 'ul' || $sibling->nodeName === 'ol') {
                        $elementsToRemove[] = $sibling;
                        break;
                    }
                    
                    // If we hit another header, stop
                    if ($sibling->nodeName === 'h2' || $sibling->nodeName === 'h3') {
                        break;
                    }
                    
                    $sibling = $sibling->nextSibling;
                }
            }
        }
        
        // Remove the marked elements
        foreach ($elementsToRemove as $element) {
            if ($element->parentNode) {
                $element->parentNode->removeChild($element);
            }
        }
        
        // Get the cleaned content
        $wrapper = $dom->getElementsByTagName('div')->item(0);
        $cleanedContent = '';
        if ($wrapper) {
            foreach ($wrapper->childNodes as $child) {
                $cleanedContent .= $dom->saveHTML($child);
            }
        }
        
        // Clean up any extra whitespace from removal
        $cleanedContent = preg_replace('/\n{3,}/', "\n\n", trim($cleanedContent));
        
        // Now append ingredients at the very end (BEFORE any existing Instructions section)
        $ingredientsList = array_map('trim', preg_split('/[,\n]+/', $this->ingredients));
        $ingredientsHtml = "\n\n<h2>Ingredients</h2>\n<ul>\n";
        foreach ($ingredientsList as $ingredient) {
            $ingredient = trim($ingredient);
            if (!empty($ingredient)) {
                $ingredientsHtml .= "    <li>" . htmlspecialchars($ingredient) . "</li>\n";
            }
        }
        $ingredientsHtml .= "</ul>";
        
        // Check if there's an Instructions section, and insert ingredients before it
        if (preg_match('/<h[23][^>]*>\s*(?:Instructions?|Steps?|Directions?)\s*<\/h[23]>/i', $cleanedContent, $matches, PREG_OFFSET_MATCH)) {
            $insertPosition = $matches[0][1];
            return substr($cleanedContent, 0, $insertPosition) . $ingredientsHtml . "\n\n" . substr($cleanedContent, $insertPosition);
        }
        
        // If no instructions section found, just append at the end
        return $cleanedContent . $ingredientsHtml;
    }

    /**
     * Clean the generated content.
     */
    private function cleanContent(string $content): string
    {
        $content = preg_replace('/^```(?:html|xml|markdown|md)?\s*\n?/i', '', $content);
        $content = preg_replace('/\n?```\s*$/i', '', $content);
        $content = preg_replace('/```(?:html|xml|markdown|md)?/i', '', $content);
        
        // Decode HTML entities that might have been escaped by AI
        // This converts &lt;p&gt; back to <p>, etc.
        // We need to be careful to only decode specific entities that should be HTML tags
        $content = $this->decodeEscapedHtmlTags($content);
        
        // Remove markdown bold markers (**text** -> text) but preserve HTML <strong> tags
        $content = preg_replace('/\*\*([^*]+)\*\*/', '$1', $content);
        // Remove any standalone ** markers that might be left over
        $content = preg_replace('/\*\*/', '', $content);
        
        $aiPhrases = [
            '/\b(In this article,? we will|In this blog post,? we will|Let\'s dive in|Without further ado|In conclusion,?|To summarize,?|To sum up,?|Let me explain)\b/i',
        ];
        
        foreach ($aiPhrases as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }
        
        $content = preg_replace('/  +/', ' ', $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return trim($content);
    }
    
    /**
     * Decode HTML entities for valid HTML tags that were escaped by AI.
     * This fixes cases where AI returns &lt;p&gt; instead of <p>.
     */
    private function decodeEscapedHtmlTags(string $content): string
    {
        // Fix escaped forward slashes in closing tags (from JSON encoding)
        // This converts <\/strong> to </strong>, <\/p> to </p>, etc.
        $content = preg_replace('/<\\\\\/([a-zA-Z0-9]+)>/u', '</$1>', $content);
        
        // Also fix double-escaped versions: <\\/tag> or <\\\/tag>
        $content = preg_replace('/<\\\\+\/([a-zA-Z0-9]+)>/u', '</$1>', $content);
        
        // List of valid HTML tags we want to decode
        $validTags = [
            'p', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'strong', 'em', 'b', 'i', 'u',
            'blockquote', 'div', 'span', 'a', 'img',
            'table', 'tr', 'td', 'th', 'thead', 'tbody',
        ];
        
        foreach ($validTags as $tag) {
            // Decode opening tags: &lt;p&gt; -> <p> and &lt;p ...&gt; -> <p ...>
            $content = preg_replace(
                '/&lt;(' . preg_quote($tag, '/') . ')(\s[^&]*)?&gt;/i',
                '<$1$2>',
                $content
            );
            
            // Decode closing tags: &lt;/p&gt; -> </p>
            $content = preg_replace(
                '/&lt;\/(' . preg_quote($tag, '/') . ')&gt;/i',
                '</$1>',
                $content
            );
        }
        
        // Also handle self-closing tags like &lt;br/&gt; or &lt;br /&gt;
        $content = preg_replace('/&lt;(br|hr|img)(\s[^&]*)?\s*\/?&gt;/i', '<$1$2>', $content);
        
        // Handle cases where quotes in attributes are also escaped
        // &lt;a href=&quot;...&quot;&gt; -> <a href="...">
        $content = str_replace('&quot;', '"', $content);
        $content = str_replace('&#039;', "'", $content);
        $content = str_replace('&apos;', "'", $content);
        
        return $content;
    }
    
    /**
     * Clean markdown markers from a string value.
     */
    private function cleanMarkdown(string $value): string
    {
        // Remove markdown bold markers (**text** -> text)
        $value = preg_replace('/\*\*([^*]+)\*\*/', '$1', $value);
        // Remove any standalone ** markers
        $value = preg_replace('/\*\*/', '', $value);
        return trim($value);
    }
    
    /**
     * Strip HTML tags and clean up a plain text value.
     * Use this for fields that should NOT contain HTML (excerpt, meta fields, notes, etc.)
     */
    private function stripHtmlAndClean(string $value): string
    {
        // First decode any HTML entities
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove all HTML tags
        $value = strip_tags($value);
        
        // Remove markdown bold markers (**text** -> text)
        $value = preg_replace('/\*\*([^*]+)\*\*/', '$1', $value);
        // Remove any standalone ** markers
        $value = preg_replace('/\*\*/', '', $value);
        
        // Clean up multiple spaces and newlines
        $value = preg_replace('/\s+/', ' ', $value);
        
        return trim($value);
    }
}
