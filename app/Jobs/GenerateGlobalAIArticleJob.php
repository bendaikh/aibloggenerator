<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Website;
use App\Models\Category;
use App\Models\User;
use App\Models\Author;
use App\Models\ArticleGenerationJob;
use App\Models\ApiUsageLog;
use App\Services\AIImageService;
use App\Services\PinterestDesignService;
use App\Services\RewritingService;
use App\Services\VariationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateGlobalAIArticleJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 1800; // 30 minutes - enough for 50+ websites
    public $uniqueFor = 3600; // Job is unique for 1 hour

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
    protected ?string $theme = null;

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
        ?int $variationIndex = null,
        ?string $theme = null
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
        $this->theme = $theme;
    }

    /**
     * Get the unique ID for this job to prevent duplicate processing.
     * This ensures the same job (same topic + same websites) doesn't run multiple times.
     */
    public function uniqueId(): string
    {
        // Create a unique identifier based on userId, topic, and website IDs
        $websiteIdsString = implode(',', $this->websiteIds);
        return "generate-article-{$this->userId}-{$this->topic}-{$websiteIdsString}";
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

        // CRITICAL: Check if all jobs are already completed before starting
        // This prevents re-processing if the queue worker picks up the same job again
        $allCompleted = true;
        foreach ($this->generationJobIds as $jobId) {
            $job = ArticleGenerationJob::find($jobId);
            if ($job && $job->status !== 'completed') {
                $allCompleted = false;
                break;
            }
        }

        if ($allCompleted) {
            Log::warning('All jobs are already completed, aborting to prevent duplicate processing', [
                'job_ids' => $this->generationJobIds,
                'topic' => $this->topic
            ]);
            return; // Exit early - nothing to do
        }

        // Mark all jobs as processing (only if they're still pending)
        foreach ($this->generationJobIds as $jobId) {
            $job = ArticleGenerationJob::find($jobId);
            if ($job && $job->status === 'pending') {
                $job->markAsProcessing();
            } elseif ($job && $job->status === 'completed') {
                Log::warning("Job {$jobId} is already completed, skipping processing");
            }
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
            
            // Get website theme for home-decor detection (use theme() method to get relationship, not the theme column)
            $websiteTheme = $website->theme()->first();

            $vIndex = $this->variationIndex !== null ? $this->variationIndex : $index;

            $jobId = $this->generationJobIds[$websiteId] ?? null;
            $generationJob = $jobId ? ArticleGenerationJob::find($jobId) : null;

            // Check if this website already has a completed job to avoid duplicates on retry
            if ($generationJob && $generationJob->status === 'completed') {
                Log::info("Skipping website {$websiteId} as it already has a completed article.");
                continue;
            }

            // CRITICAL: Check if an article with similar title already exists on this website
            // This prevents duplicate articles when the job is retried or runs multiple times
            $existingArticle = Article::where('website_id', $websiteId)
                ->where('title', 'LIKE', '%' . substr($this->topic, 0, 30) . '%')
                ->where('generation_mode', 'full_ai')
                ->where('created_at', '>=', now()->subMinutes(5)) // Only check recent articles (last 5 minutes)
                ->first();

            if ($existingArticle) {
                Log::info("Skipping website {$websiteId} - duplicate article detected", [
                    'existing_article_id' => $existingArticle->id,
                    'existing_title' => $existingArticle->title
                ]);
                
                // Mark the job as completed with the existing article
                if ($generationJob) {
                    $generationJob->markAsCompleted($existingArticle->id);
                }
                
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
                    // For home-decor with multi-image titles, extract URLs
                    if ($this->theme === 'home-decor' && is_array($this->featuredImages[0] ?? null) && isset($this->featuredImages[0]['url'])) {
                        // Extract just the URLs for featured/secondary images
                        $featuredImage = $this->featuredImages[$vIndex % $imageCount]['url'] ?? null;
                        if ($imageCount >= 2) {
                            $secondaryImage = $this->featuredImages[($vIndex + 1) % $imageCount]['url'] ?? null;
                        }
                    } else {
                        // Regular image handling (recipe/crochet themes)
                        $featuredImage = $this->featuredImages[$vIndex % $imageCount];
                        if ($imageCount >= 2) {
                            $secondaryImage = $this->featuredImages[($vIndex + 1) % $imageCount];
                        }
                    }
                }

                // Get default author for this website
                $defaultAuthor = $this->getDefaultAuthor($website);

                // Determine article type based on website theme
                // Home decor themed websites should use 'article' type (not recipe)
                $effectiveArticleType = $this->articleType;
                if ($websiteTheme && $websiteTheme->slug === 'home-decor') {
                    $effectiveArticleType = 'article';
                }

                // Debug: Log ingredients/instructions data before article creation
                $ingredientsToSave = $parsed['ingredients'] ?? [];
                $instructionsToSave = $parsed['instructions'] ?? [];
                $notesToSave = $parsed['notes'] ?? [];
                
                Log::info("FULL AI MODE - Creating article for website {$websiteId}", [
                    'ingredients_count' => count($ingredientsToSave),
                    'instructions_count' => count($instructionsToSave),
                    'notes_count' => count($notesToSave),
                    'ingredients_sample' => array_slice($ingredientsToSave, 0, 3),
                ]);
                
                // CRITICAL: If no ingredients, log warning
                if (empty($ingredientsToSave) && $this->articleType === 'recipe') {
                    Log::warning("FULL AI MODE - No ingredients for recipe article!", [
                        'website_id' => $websiteId,
                        'title' => $parsed['title'],
                        'parsed_keys' => array_keys($parsed),
                    ]);
                }

                // Prepare variation metadata with image sections for home decor
                $variationMetadata = [];
                if ($this->theme === 'home-decor' && !empty($this->featuredImages) && is_array($this->featuredImages[0] ?? null) && isset($this->featuredImages[0]['url'])) {
                    $variationMetadata['image_sections'] = $this->featuredImages;
                    Log::info("Storing image sections in variation_metadata", [
                        'website_id' => $websiteId,
                        'image_count' => count($this->featuredImages)
                    ]);
                }

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
                    'notes' => $notesToSave,
                    'prep_time' => $parsed['prep_time'] ?? null,
                    'cook_time' => $parsed['cook_time'] ?? null,
                    'rest_time' => $parsed['rest_time'] ?? null,
                    'total_time' => $parsed['total_time'] ?? null,
                    'ingredients' => $ingredientsToSave,
                    'instructions' => $instructionsToSave,
                    'status' => $this->autoPublish ? 'published' : 'draft',
                    'published_at' => $this->autoPublish ? now() : null,
                    'ai_generated' => true,
                    'generation_type' => 'ai',
                    'generation_mode' => 'full_ai',
                    'article_type' => $effectiveArticleType,
                    'variation_metadata' => !empty($variationMetadata) ? $variationMetadata : null,
                ]);

                if ($generationJob) {
                    $generationJob->markAsCompleted($article->id);
                }
                
                Log::info("Generated unique article for website {$websiteId}", [
                    'title' => $parsed['title'],
                    'article_type' => $effectiveArticleType,
                    'is_home_decor' => $websiteTheme?->slug === 'home-decor'
                ]);

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

                // Generate AI images for home decor articles
                $this->dispatchAIImageGenerationIfNeeded($article, $website, $user, $parsed['content'] ?? '');
                
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
        
        // Get master website theme for home-decor detection
        $masterWebsiteTheme = $masterWebsite->theme()->first();

        // WRAP ENTIRE HYBRID MODE IN TRY-CATCH to ensure jobs are marked as failed on ANY error
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
                'title' => $masterParsed['title'],
                'ingredients_count' => count($masterParsed['ingredients'] ?? []),
                'instructions_count' => count($masterParsed['instructions'] ?? []),
                'notes_count' => count($masterParsed['notes'] ?? []),
                'ingredients_sample' => array_slice($masterParsed['ingredients'] ?? [], 0, 3),
                'has_all_recipe_data' => !empty($masterParsed['ingredients']) && !empty($masterParsed['instructions']),
            ]);

            // Step 2: Create master article and variations for ALL selected websites
            // No limit - process all websites the user selected
            $websitesToProcess = $this->websiteIds;
            
            Log::info("Hybrid Mode: Processing all " . count($this->websiteIds) . " websites");
            
            foreach ($websitesToProcess as $index => $websiteId) {
                $website = Website::with(['categories', 'authors'])->find($websiteId);
                if (!$website) continue;
                
                // Get website theme for home-decor detection (use theme() method to get relationship, not the theme column)
                $websiteTheme = $website->theme()->first();

                $jobId = $this->generationJobIds[$websiteId] ?? null;
                $generationJob = $jobId ? ArticleGenerationJob::find($jobId) : null;

                if ($generationJob && $generationJob->status === 'completed') {
                    Log::info("Skipping website {$websiteId} - already completed");
                    continue;
                }

                // CRITICAL: Check if an article with similar title already exists on this website
                // This prevents duplicate articles when the job is retried or runs multiple times
                $existingArticle = Article::where('website_id', $websiteId)
                    ->where('title', 'LIKE', '%' . substr($this->topic, 0, 30) . '%')
                    ->where('generation_mode', 'hybrid_rewrite')
                    ->where('created_at', '>=', now()->subMinutes(5)) // Only check recent articles (last 5 minutes)
                    ->first();

                if ($existingArticle) {
                    Log::info("Skipping website {$websiteId} - duplicate article detected", [
                        'existing_article_id' => $existingArticle->id,
                        'existing_title' => $existingArticle->title
                    ]);
                    
                    // Mark the job as completed with the existing article
                    if ($generationJob) {
                        $generationJob->markAsCompleted($existingArticle->id);
                    }
                    
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
                        Log::info("Creating variation {$index} for website {$websiteId} - START");
                        
                        try {
                            $articleData = $variationEngine->createVariation($masterParsed, $index);
                            
                            // For home-decor with multi-image titles: shuffle the order of image sections in content
                            if ($this->theme === 'home-decor' && !empty($this->featuredImages) && is_array($this->featuredImages[0] ?? null) && isset($this->featuredImages[0]['title'])) {
                                $articleData['content'] = $this->shuffleHomeDecorImageSections($articleData['content'], $index);
                                Log::info("Shuffled image sections for home decor variation {$index}");
                            }
                            
                            Log::info("Variation {$index} created successfully for website {$websiteId}");
                        } catch (\Exception $variationError) {
                            // If variation fails, use master content as fallback
                            Log::error("Variation creation failed for website {$websiteId}, using master content as fallback", [
                                'error' => $variationError->getMessage(),
                                'index' => $index
                            ]);
                            $articleData = $masterParsed;
                        }
                        
                        // Skip uniqueness calculation to save time - it's just for logging anyway
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
                        // For home-decor with multi-image titles, extract URLs
                        if ($this->theme === 'home-decor' && is_array($this->featuredImages[0] ?? null) && isset($this->featuredImages[0]['url'])) {
                            $featuredImage = $this->featuredImages[$index % $imageCount]['url'] ?? null;
                            if ($imageCount >= 2) {
                                $secondaryImage = $this->featuredImages[($index + 1) % $imageCount]['url'] ?? null;
                            }
                        } else {
                            // Regular image handling
                            $featuredImage = $this->featuredImages[$index % $imageCount];
                            if ($imageCount >= 2) {
                                $secondaryImage = $this->featuredImages[($index + 1) % $imageCount];
                            }
                        }
                    }

                    $defaultAuthor = $this->getDefaultAuthor($website);

                    // Determine article type based on website theme
                    // Home decor themed websites should use 'article' type (not recipe)
                    $effectiveArticleType = $this->articleType;
                    if ($websiteTheme && $websiteTheme->slug === 'home-decor') {
                        $effectiveArticleType = 'article';
                    }

                    // Debug: Log ingredients/instructions data before article creation
                    $ingredientsToSave = $articleData['ingredients'] ?? [];
                    $instructionsToSave = $articleData['instructions'] ?? [];
                    $notesToSave = $articleData['notes'] ?? [];
                    
                    Log::info("HYBRID MODE - Creating article for website {$websiteId}", [
                        'is_master' => $isMaster,
                        'variation_index' => $index,
                        'article_type' => $effectiveArticleType,
                        'is_home_decor' => $websiteTheme?->slug === 'home-decor',
                        'ingredients_count' => count($ingredientsToSave),
                        'instructions_count' => count($instructionsToSave),
                        'notes_count' => count($notesToSave),
                        'ingredients_sample' => array_slice($ingredientsToSave, 0, 3),
                        'has_ingredients_key' => isset($articleData['ingredients']),
                    ]);
                    
                    // CRITICAL: If no ingredients, log warning
                    if (empty($ingredientsToSave) && $effectiveArticleType === 'recipe') {
                        Log::warning("HYBRID MODE - No ingredients for recipe article!", [
                            'website_id' => $websiteId,
                            'title' => $articleData['title'],
                            'article_data_keys' => array_keys($articleData),
                        ]);
                    }

                    // Prepare variation metadata with image sections for home decor
                    $variationMetadata = [];
                    if ($this->theme === 'home-decor' && !empty($this->featuredImages) && is_array($this->featuredImages[0] ?? null) && isset($this->featuredImages[0]['url'])) {
                        // For variations, shuffle the image order (must match the content section order)
                        $imageSections = $this->featuredImages;
                        if (!$isMaster && $index > 0) {
                            // Shuffle images for variations based on index - use same seed as shuffleHomeDecorImageSections
                            $imageSections = $this->getShuffledImageSections($index);
                        }
                        $variationMetadata['image_sections'] = $imageSections;
                        Log::info("Storing image sections in variation_metadata (Hybrid)", [
                            'website_id' => $websiteId,
                            'is_master' => $isMaster,
                            'image_count' => count($imageSections),
                            'image_order' => array_map(fn($img) => $img['title'] ?? 'no-title', $imageSections)
                        ]);
                    }
                    
                    if (!$isMaster) {
                        $variationMetadata['rewritten_locally'] = true;
                        $variationMetadata['variation_index'] = $index;
                        $variationMetadata['created_at'] = now()->toISOString();
                    }

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
                        'notes' => $notesToSave,
                        'prep_time' => $articleData['prep_time'] ?? null,
                        'cook_time' => $articleData['cook_time'] ?? null,
                        'rest_time' => $articleData['rest_time'] ?? null,
                        'total_time' => $articleData['total_time'] ?? null,
                        'ingredients' => $ingredientsToSave,
                        'instructions' => $instructionsToSave,
                        'status' => $this->autoPublish ? 'published' : 'draft',
                        'published_at' => $this->autoPublish ? now() : null,
                        'ai_generated' => true,
                        'generation_type' => 'ai',
                        'generation_mode' => 'hybrid_rewrite',
                        'article_type' => $effectiveArticleType,
                        'variation_index' => $isMaster ? null : $index,
                        'variation_metadata' => !empty($variationMetadata) ? $variationMetadata : null,
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

                    // Generate AI images for home decor articles
                    $this->dispatchAIImageGenerationIfNeeded($article, $website, $user, $articleData['content'] ?? '');

                } catch (\Exception $e) {
                    if ($generationJob) {
                        $generationJob->markAsFailed($e->getMessage());
                    }
                    Log::error("Failed to create article for website {$websiteId}: " . $e->getMessage(), [
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Continue with other websites even if one fails
                    continue;
                }
            }
            
            // FINAL CLEANUP: Ensure no jobs are left in "processing" or "pending" status
            // This catches any edge cases where jobs weren't properly updated
            $this->finalizeAllJobs('Hybrid mode completed');
            
            Log::info('Hybrid Mode: All articles processed successfully', [
                'processed_count' => count($websitesToProcess)
            ]);

        } catch (\Exception $e) {
            // CRITICAL: Ensure ALL jobs are marked as failed if master article generation fails
            Log::error('HYBRID MODE CRITICAL ERROR - Marking all jobs as failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'job_ids' => $this->generationJobIds
            ]);
            $this->failAllJobs('Master article generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function failAllJobs(string $message): void
    {
        foreach ($this->generationJobIds as $jobId) {
            $job = ArticleGenerationJob::find($jobId);
            if ($job && $job->status !== 'completed') {
                $job->markAsFailed($message);
            }
        }
    }
    
    /**
     * Finalize all jobs after processing - ensure none are left in processing/pending state.
     * Jobs that are still processing but have an article_id are marked as completed.
     * Jobs that are still processing without an article_id are marked as failed.
     */
    private function finalizeAllJobs(string $context): void
    {
        foreach ($this->generationJobIds as $websiteId => $jobId) {
            $job = ArticleGenerationJob::find($jobId);
            if (!$job) continue;
            
            // Skip if already completed or failed
            if (in_array($job->status, ['completed', 'failed'])) {
                continue;
            }
            
            // Check if this job has an associated article
            if ($job->article_id) {
                // Has article - mark as completed
                $job->markAsCompleted($job->article_id);
                Log::info("Finalized job {$jobId} as completed (had article_id)", [
                    'context' => $context,
                    'website_id' => $websiteId
                ]);
            } else {
                // No article - mark as failed
                $job->markAsFailed("Job did not complete successfully. {$context}");
                Log::warning("Finalized job {$jobId} as failed (no article_id)", [
                    'context' => $context,
                    'website_id' => $websiteId,
                    'previous_status' => $job->status
                ]);
            }
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
     * Get the variation style for a given index to ensure unique articles.
     */
    private function getVariationStyle(int $variationIndex): string
    {
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
        
        return $variationStyles[$variationIndex % count($variationStyles)];
    }

    /**
     * Detect the language of the title based on common patterns and characters.
     */
    private function detectLanguage(string $text): string
    {
        // Check for French-specific characters and common words
        $frenchPatterns = [
            '/[àâäæçéèêëïîôùûüÿœ]/ui', // French accents
            '/\b(le|la|les|un|une|des|pour|avec|dans|sur|de|du|et|est|sont|au|aux|ce|cette|ces)\b/ui', // Common French words
        ];
        
        foreach ($frenchPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return 'French';
            }
        }
        
        // Check for Spanish-specific characters
        if (preg_match('/[áéíóúñü¿¡]/ui', $text)) {
            return 'Spanish';
        }
        
        // Check for German-specific characters
        if (preg_match('/[äöüß]/ui', $text)) {
            return 'German';
        }
        
        // Check for Italian-specific characters and common words
        if (preg_match('/[àèéìíîòóùú]/ui', $text) && preg_match('/\b(il|la|lo|gli|le|dei|delle|per|con|come)\b/ui', $text)) {
            return 'Italian';
        }
        
        // Check for Portuguese-specific characters
        if (preg_match('/[ãõâêôçáéíóú]/ui', $text)) {
            return 'Portuguese';
        }
        
        // Default to English
        return 'English';
    }

    /**
     * Build the AI prompt with variation for unique articles.
     */
    private function buildPrompt(string $wordCount, Website $website, int $variationIndex): string
    {
        $keywordsText = !empty($this->keywords) ? "\n- Naturally weave in these keywords: {$this->keywords}" : '';
        
        // Detect language from the title
        $detectedLanguage = $this->detectLanguage($this->topic);
        $languageInstruction = '';
        
        if ($detectedLanguage !== 'English') {
            $languageInstruction = <<<LANGUAGE

CRITICAL LANGUAGE REQUIREMENT:
- The title is in {$detectedLanguage}, so you MUST write the ENTIRE article in {$detectedLanguage}
- ALL content including: article content, excerpt, meta_title, meta_description, tags, notes, ingredients, and instructions MUST be in {$detectedLanguage}
- Use natural, authentic {$detectedLanguage} language - not translated from English
- Write as if you are a native {$detectedLanguage} speaker

LANGUAGE;
        }
        
        // Check if website uses home-decor theme - if so, use home decor prompt regardless of article_type
        // Use theme() method to get the relationship, not the theme column (which is a string)
        $websiteTheme = $website->theme()->first();
        if ($websiteTheme && $websiteTheme->slug === 'home-decor') {
            Log::info("Building home decor prompt for website with home-decor theme", [
                'website_id' => $website->id,
                'topic' => $this->topic
            ]);
            return $this->buildHomeDecorPrompt($wordCount, $this->getVariationStyle($variationIndex), rand(1000, 9999), $variationIndex, $keywordsText);
        }
        
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
        
        // Get variation style for unique articles
        $variationStyle = $this->getVariationStyle($variationIndex);
        $randomSeed = rand(1000, 9999);
        
        // Use different prompt for article type (non-recipe)
        if ($this->articleType === 'article') {
            return $this->buildHomeDecorPrompt($wordCount, $variationStyle, $randomSeed, $variationIndex, $keywordsText);
        }
        
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
{$languageInstruction}
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
     * Build a specialized prompt for home decor / general articles (non-recipe).
     * This prompt is optimized for list articles like "Top 10 Homes" with proper H2/H3 structure for image generation.
     */
    private function buildHomeDecorPrompt(string $wordCount, string $variationStyle, int $randomSeed, int $variationIndex, string $keywordsText): string
    {
        // Detect language from the title
        $detectedLanguage = $this->detectLanguage($this->topic);
        $languageInstruction = '';
        
        if ($detectedLanguage !== 'English') {
            $languageInstruction = <<<LANGUAGE

CRITICAL LANGUAGE REQUIREMENT:
- The title is in {$detectedLanguage}, so you MUST write the ENTIRE article in {$detectedLanguage}
- ALL content including: article content, excerpt, meta_title, meta_description, tags, and notes MUST be in {$detectedLanguage}
- Use natural, authentic {$detectedLanguage} language - not translated from English
- Write as if you are a native {$detectedLanguage} speaker

LANGUAGE;
        }
        
        // Check if we have multi-image with titles (for home-decor theme)
        $hasImageTitles = !empty($this->featuredImages) && 
                         is_array($this->featuredImages[0] ?? null) && 
                         isset($this->featuredImages[0]['title']);
        
        $imageTitlesSection = '';
        if ($hasImageTitles && $this->theme === 'home-decor') {
            $imageTitlesSection = "\n\nIMAGE SECTIONS (CRITICAL - MUST INCLUDE):\n";
            $imageTitlesSection .= "The user has uploaded " . count($this->featuredImages) . " images with specific titles. You MUST create a section for EACH image with its exact title as an H2 header:\n\n";
            
            foreach ($this->featuredImages as $index => $imageData) {
                $imageTitle = $imageData['title'] ?? '';
                $imageTitlesSection .= ($index + 1) . ". <h2>{$imageTitle}</h2> - Write 3-4 detailed paragraphs about this specific design/space. Each paragraph MUST start with <strong>Title:</strong>\n";
            }
            
            $imageTitlesSection .= "\n⚠️ CRITICAL: You MUST include ALL " . count($this->featuredImages) . " image sections in order with their exact titles as H2 headers.\n";
            $imageTitlesSection .= "Structure: Introduction (2-3 paragraphs without H2) → Image Section 1 → Image Section 2 → ... → Image Section " . count($this->featuredImages) . " → Optional FAQ/Conclusion\n";
        }
        
        return <<<PROMPT
You are a professional home decor and lifestyle blog writer who creates stunning, visually-inspiring content.

Write a DETAILED, COMPREHENSIVE and COMPLETELY UNIQUE blog post about: "{$this->topic}"
{$languageInstruction}
CRITICAL TITLE RULE:
- The TITLE field below is pre-filled with the exact title the user wants. DO NOT CHANGE IT. Use it exactly as written - no additions, no modifications, no "improvements".

UNIQUENESS REQUIREMENT (Variation #{$variationIndex}, Seed: {$randomSeed}):
- {$variationStyle}
- Use different examples, metaphors, and explanations than typical articles
- Create a fresh, original perspective that stands out
{$imageTitlesSection}
⚠️ CRITICAL STRUCTURE FOR LIST ARTICLES - READ VERY CAREFULLY ⚠️
If this is a "list" article (e.g., "Top 8 Villas", "Best 15 Living Rooms", "10 Luxury Bedrooms", etc.):

IMPORTANT COUNTING RULE:
- If the title says "Top 8", you MUST have EXACTLY 8 numbered items (not 7, not 9 - exactly 8!)
- If the title says "15 Best", you MUST have EXACTLY 15 numbered items
- The Introduction paragraphs DO NOT count as one of the numbered items
- The Conclusion/FAQ sections DO NOT count as numbered items
- ONLY the numbered H2 sections (1., 2., 3., etc.) count toward the total

STRUCTURE EXAMPLE for "Top 8 Luxury Villas":
1. Introduction (2-3 paragraphs) - NO H2 header, just paragraphs at the start
2. Then EXACTLY 8 numbered H2 sections:
   - <h2>1. Villa Name One - Descriptive Subtitle</h2> (3-5 paragraphs)
   - <h2>2. Villa Name Two - Descriptive Subtitle</h2> (3-5 paragraphs)
   - <h2>3. Villa Name Three - Descriptive Subtitle</h2> (3-5 paragraphs)
   - ... continue until ...
   - <h2>8. Villa Name Eight - Descriptive Subtitle</h2> (3-5 paragraphs)
3. Optional: FAQ section, Conclusion, etc. (these don't count toward the 8)

- EACH numbered item MUST have its own <h2> header with the number, name, and a descriptive subtitle
- Each item section should have 3-5 paragraphs describing the home/space in vivid detail
- Include details like: location, architectural style, designer/architect, key features, why it's special
- This structure is CRITICAL because AI will generate images based on these numbered H2 headers
- COUNT YOUR ITEMS BEFORE SUBMITTING - if the title says 8, you need exactly 8 numbered H2 sections!

MOST CRITICAL RULE - BOLD TITLES ON ALL CONTENT (DO NOT SKIP THIS):
**EVERY SINGLE PARAGRAPH AND LIST ITEM** in the article MUST begin with a bold title. This is NON-NEGOTIABLE.

FOR PARAGRAPHS:
- Format: <p><strong>Descriptive Title Here:</strong> Then your paragraph content...</p>
- WRONG: <p>This stunning home features floor-to-ceiling windows...</p>
- RIGHT: <p><strong>Breathtaking Glass Architecture:</strong> This stunning home features floor-to-ceiling windows...</p>

FOR LIST ITEMS (VERY IMPORTANT):
- Format: <li><strong>Title Here:</strong> Then the list item content...</li>
- WRONG: <li>Natural materials like wood and stone</li>
- RIGHT: <li><strong>Natural Materials:</strong> Incorporates warm wood and natural stone throughout</li>

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
- Start with a captivating introduction (2-3 paragraphs WITHOUT an H2 header) that sets the scene and builds anticipation
- DO NOT use <h2>Introduction</h2> - just start with <p> paragraphs directly
- Then write ALL the numbered items with their H2 headers (1., 2., 3., etc.)
- Write with passion about design, architecture, and the emotional impact of beautiful spaces
- Use sensory language - describe textures, colors, light, and atmosphere
- Each home/space should feel like a mini-story with its own character

PARAGRAPH STRUCTURE (VERY IMPORTANT):
- Each paragraph should be 4-6 sentences minimum, not just 1-2 sentences.
- Use multiple paragraphs per section - don't cram everything into one paragraph.
- Add detailed descriptions, historical context, and design insights in each paragraph.
- Every major point deserves its own paragraph with full explanation.

REMINDER - BOLD TITLES ON EVERY PARAGRAPH AND LIST ITEM (MANDATORY):
- EVERY <p> tag = <p><strong>Title:</strong> content</p>
- EVERY <li> tag = <li><strong>Title:</strong> content</li>
- NO EXCEPTIONS. Check every paragraph and list item before submitting.

CONTENT DEPTH REQUIREMENTS FOR HOME DECOR ARTICLES:
- Start with introduction paragraphs (2-3 paragraphs) - NO H2 header for introduction, just <p> tags
- For EACH numbered item in the list (if title says 8, you need 8 items):
  - A numbered <h2> header like: <h2>1. Item Name - Descriptive Subtitle</h2>
  - 3-5 paragraphs with vivid descriptions
  - Design highlights and architectural features
  - What makes it unique or noteworthy
- VERIFY: Count your numbered H2 headers - they MUST match the number in the title!
- Optional: Include a FAQ section or conclusion at the end (these don't count toward the list number)

Requirements:
- Length: MINIMUM {$wordCount} words. This is a MINIMUM - feel free to write more! Be as detailed and comprehensive as possible. DO NOT stop early.
- Tone: {$this->tone} (but always authentic and personal)
- Use proper HTML formatting: <h2> for main list items/sections, <h3> for subsections, <p>, <ul>, <ol>, <strong>, <em>, <blockquote> for quotes/tips
- Make it SEO-friendly but human-first{$keywordsText}

CRITICAL OUTPUT FORMAT RULE:
- DO NOT use markdown syntax like ** or __ in your output
- Use HTML tags only: <strong> for bold, <em> for italic
- All metadata must be plain text without any markdown formatting

YOU MUST RESPOND WITH A VALID JSON OBJECT. The JSON structure must be EXACTLY as follows:

{
  "title": "{$this->topic}",
  "excerpt": "2-3 sentences teaser - plain text, no markdown",
  "meta_title": "SEO title, 50-60 characters - plain text",
  "meta_description": "SEO description, 150-160 characters - plain text",
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "prep_time": "",
  "cook_time": "",
  "rest_time": "",
  "total_time": "",
  "notes": ["Design tip 1", "Design tip 2", "Design tip 3"],
  "ingredients": [],
  "instructions": [],
  "content": "<p><strong>Welcome:</strong> Introduction paragraph 1...</p><p><strong>Overview:</strong> Introduction paragraph 2...</p><h2>1. First Item - Subtitle</h2><p><strong>Detail:</strong> Content...</p><h2>2. Second Item - Subtitle</h2><p><strong>Detail:</strong> Content...</p>..."
}

REQUIRED JSON FIELDS (ALL MUST BE PRESENT):
- "title": EXACTLY "{$this->topic}" (do not change)
- "excerpt": String, 2-3 sentences teaser (PLAIN TEXT, no HTML)
- "meta_title": String, SEO title 50-60 characters (PLAIN TEXT, no HTML)
- "meta_description": String, SEO description 150-160 characters (PLAIN TEXT, no HTML)
- "tags": Array of 5-8 relevant tag strings (PLAIN TEXT, no HTML)
- "prep_time": Empty string "" (not applicable for home decor articles)
- "cook_time": Empty string "" (not applicable for home decor articles)
- "rest_time": Empty string "" (not applicable for home decor articles)
- "total_time": Empty string "" (not applicable for home decor articles)
- "notes": Array of 3-5 design tips or insights (PLAIN TEXT, no HTML)
- "ingredients": Empty array [] (not applicable for home decor articles)
- "instructions": Empty array [] (not applicable for home decor articles)
- "content": String containing the full article in HTML format (use <strong> for bold, <em> for italic)

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
                $ingredients = array_values(array_filter(array_map([$this, 'stripHtmlAndClean'], $jsonData['ingredients'])));
                Log::info('Parsed ingredients from JSON', ['count' => count($ingredients), 'sample' => array_slice($ingredients, 0, 3)]);
            } else {
                Log::warning('No ingredients array found in JSON response', [
                    'has_ingredients_key' => isset($jsonData['ingredients']),
                    'ingredients_type' => isset($jsonData['ingredients']) ? gettype($jsonData['ingredients']) : 'not set'
                ]);
            }
            
            // Get instructions from JSON (strip HTML from each instruction)
            if (isset($jsonData['instructions']) && is_array($jsonData['instructions'])) {
                $instructions = array_values(array_filter(array_map([$this, 'stripHtmlAndClean'], $jsonData['instructions'])));
                Log::info('Parsed instructions from JSON', ['count' => count($instructions)]);
            } else {
                Log::warning('No instructions array found in JSON response', [
                    'has_instructions_key' => isset($jsonData['instructions']),
                    'instructions_type' => isset($jsonData['instructions']) ? gettype($jsonData['instructions']) : 'not set'
                ]);
            }
            
            $articleContent = $jsonData['content'] ?? '';
            
            // FALLBACK: If no ingredients in JSON, try to extract from HTML content
            if (empty($ingredients) && !empty($articleContent)) {
                Log::info('Attempting to extract ingredients from HTML content as fallback');
                $extractedIngredients = $this->extractIngredientsFromHtml($articleContent);
                if (!empty($extractedIngredients)) {
                    $ingredients = $extractedIngredients;
                    Log::info('Extracted ingredients from HTML', ['count' => count($ingredients)]);
                }
            }
            
            // FALLBACK: If no instructions in JSON, try to extract from HTML content
            if (empty($instructions) && !empty($articleContent)) {
                Log::info('Attempting to extract instructions from HTML content as fallback');
                $extractedInstructions = $this->extractInstructionsFromHtml($articleContent);
                if (!empty($extractedInstructions)) {
                    $instructions = $extractedInstructions;
                    Log::info('Extracted instructions from HTML', ['count' => count($instructions)]);
                }
            }
            
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
            'ingredients' => $ingredients,
            'instructions' => $instructions,
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
    /**
     * Extract ingredients from HTML content as a fallback.
     * Looks for <h2>Ingredients</h2> followed by a <ul> list.
     */
    private function extractIngredientsFromHtml(string $html): array
    {
        $ingredients = [];
        
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            $xpath = new \DOMXPath($dom);
            
            // Look for h2 or h3 headers containing "ingredient"
            $headers = $xpath->query('//h2|//h3');
            
            foreach ($headers as $header) {
                $headerText = strtolower(trim($header->textContent));
                
                if (strpos($headerText, 'ingredient') !== false) {
                    // Find the next UL element
                    $current = $header->nextSibling;
                    while ($current) {
                        if ($current->nodeType === XML_ELEMENT_NODE) {
                            if (strtolower($current->nodeName) === 'ul' || strtolower($current->nodeName) === 'ol') {
                                // Extract all li elements
                                $listItems = $xpath->query('.//li', $current);
                                foreach ($listItems as $li) {
                                    $text = trim($li->textContent);
                                    if (!empty($text)) {
                                        $ingredients[] = $this->stripHtmlAndClean($text);
                                    }
                                }
                                break;
                            }
                            // Stop if we hit another header
                            if (in_array(strtolower($current->nodeName), ['h1', 'h2', 'h3', 'h4'])) {
                                break;
                            }
                        }
                        $current = $current->nextSibling;
                    }
                    
                    if (!empty($ingredients)) {
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to extract ingredients from HTML', ['error' => $e->getMessage()]);
        }
        
        return $ingredients;
    }

    /**
     * Extract instructions from HTML content as a fallback.
     * Looks for <h2>Instructions</h2> followed by an <ol> list.
     */
    private function extractInstructionsFromHtml(string $html): array
    {
        $instructions = [];
        
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            $xpath = new \DOMXPath($dom);
            
            // Look for h2 or h3 headers containing "instruction", "step", or "direction"
            $headers = $xpath->query('//h2|//h3');
            
            foreach ($headers as $header) {
                $headerText = strtolower(trim($header->textContent));
                
                if (strpos($headerText, 'instruction') !== false ||
                    strpos($headerText, 'step') !== false ||
                    strpos($headerText, 'direction') !== false) {
                    
                    // Find the next OL or UL element
                    $current = $header->nextSibling;
                    while ($current) {
                        if ($current->nodeType === XML_ELEMENT_NODE) {
                            if (strtolower($current->nodeName) === 'ol' || strtolower($current->nodeName) === 'ul') {
                                // Extract all li elements
                                $listItems = $xpath->query('.//li', $current);
                                foreach ($listItems as $li) {
                                    $text = trim($li->textContent);
                                    if (!empty($text)) {
                                        // Remove leading step numbers if present
                                        $text = preg_replace('/^(?:Step\s*)?\d+[.:]\s*/i', '', $text);
                                        $instructions[] = $this->stripHtmlAndClean($text);
                                    }
                                }
                                break;
                            }
                            // Stop if we hit another header
                            if (in_array(strtolower($current->nodeName), ['h1', 'h2', 'h3', 'h4'])) {
                                break;
                            }
                        }
                        $current = $current->nextSibling;
                    }
                    
                    if (!empty($instructions)) {
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to extract instructions from HTML', ['error' => $e->getMessage()]);
        }
        
        return $instructions;
    }

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

    /**
     * Generate theme-based items when the title specifies a count but content doesn't have enough items.
     * For example, if title is "26 Luxe Home Decor" but content only has 10 H2 headers,
     * this generates 16 more items based on the "luxe home decor" theme.
     */
    private function generateThemeBasedItems(string $theme, string $styleKeywords, int $startIndex, int $endIndex, string $articleTitle): array
    {
        $items = [];
        
        // Theme-specific item variations for home decor
        $themeVariations = $this->getThemeVariations($theme);
        
        for ($i = $startIndex; $i <= $endIndex; $i++) {
            // Rotate through variations to create diverse but theme-consistent items
            $variationIndex = ($i - 1) % count($themeVariations);
            $variation = $themeVariations[$variationIndex];
            
            $items[] = [
                'title' => "{$variation} - {$theme} Design #{$i}",
                'description' => "A stunning example of {$theme} featuring {$styleKeywords}. Perfect representation of the style showcased in: {$articleTitle}",
                'position' => $i,
                'generated_from_theme' => true
            ];
        }
        
        return $items;
    }
    
    /**
     * Get theme-specific variations for generating diverse items.
     */
    private function getThemeVariations(string $theme): array
    {
        $themeLower = strtolower($theme);
        
        // Luxe/Luxury variations
        if (str_contains($themeLower, 'luxe') || str_contains($themeLower, 'luxury')) {
            return [
                'Grand Living Room with Crystal Chandelier',
                'Opulent Master Bedroom Suite',
                'Marble-Clad Luxury Bathroom',
                'Designer Kitchen with Gold Accents',
                'Elegant Formal Dining Room',
                'Lavish Home Office with Library',
                'Stunning Entrance Foyer',
                'Sophisticated Wine Cellar',
                'Glamorous Walk-in Closet',
                'Exclusive Spa-like Retreat',
                'Premium Entertainment Room',
                'Majestic Staircase Design',
                'Refined Breakfast Nook',
                'Prestigious Home Bar',
                'Sumptuous Guest Suite',
                'Elite Outdoor Living Space',
                'Magnificent Fireplace Setting',
                'Exquisite Powder Room',
                'Regal Sitting Room',
                'Distinguished Study Room',
                'Palatial Master Suite',
                'Aristocratic Drawing Room',
                'Upscale Media Room',
                'Noble Library Space',
                'Imperial Balcony Design',
                'Prestigious Conservatory',
            ];
        }
        
        // Minimalist variations
        if (str_contains($themeLower, 'minimalist') || str_contains($themeLower, 'scandinavian')) {
            return [
                'Clean-Lined Living Space',
                'Serene Minimalist Bedroom',
                'Simple Functional Kitchen',
                'Uncluttered Bathroom Design',
                'Zen-Inspired Workspace',
                'Nordic-Style Dining Area',
                'Airy Open-Plan Space',
                'Calm Meditation Corner',
                'Streamlined Entryway',
                'Peaceful Reading Nook',
            ];
        }
        
        // Bohemian variations
        if (str_contains($themeLower, 'boho') || str_contains($themeLower, 'bohemian')) {
            return [
                'Eclectic Living Room Mix',
                'Layered Textile Bedroom',
                'Artistic Kitchen Space',
                'Worldly Bathroom Design',
                'Creative Studio Space',
                'Global-Inspired Dining',
                'Cozy Reading Corner',
                'Plant-Filled Sanctuary',
                'Vintage Treasure Collection',
                'Free-Spirit Outdoor Space',
            ];
        }
        
        // Coastal variations
        if (str_contains($themeLower, 'coastal') || str_contains($themeLower, 'beach')) {
            return [
                'Breezy Living Room',
                'Seaside Bedroom Retreat',
                'Nautical Kitchen Design',
                'Ocean-Inspired Bathroom',
                'Beachy Sunroom',
                'Relaxed Dining Space',
                'Coastal Porch Design',
                'Maritime Home Office',
                'Driftwood Accent Wall',
                'Hampton-Style Elegance',
            ];
        }
        
        // Default modern home decor variations
        return [
            'Contemporary Living Room Design',
            'Modern Bedroom Sanctuary',
            'Sleek Kitchen Interior',
            'Spa-Like Bathroom',
            'Stylish Home Office',
            'Elegant Dining Space',
            'Cozy Reading Corner',
            'Sophisticated Entry',
            'Inviting Family Room',
            'Chic Outdoor Living',
            'Refined Guest Room',
            'Modern Nursery Design',
            'Trendy Teen Bedroom',
            'Functional Mudroom',
            'Beautiful Breakfast Area',
            'Impressive Home Bar',
            'Relaxing Patio Space',
            'Gorgeous Fireplace Wall',
            'Stunning Walk-in Closet',
            'Artful Gallery Wall',
            'Serene Master Bath',
            'Charming Window Seat',
            'Dramatic Accent Wall',
            'Welcoming Front Porch',
            'Sophisticated Den',
            'Peaceful Garden Room',
        ];
    }

    /**
     * Check if the website uses home-decor theme and dispatch AI image generation if needed.
     * This automatically generates images for "list" articles (e.g., "Top 10 Homes").
     * SKIPS if user has provided their own images.
     */
    private function dispatchAIImageGenerationIfNeeded(Article $article, Website $website, User $user, string $content): void
    {
        try {
            // SKIP AI image generation if user has uploaded their own images
            if (!empty($this->featuredImages)) {
                Log::info("AI Image Generation: Skipping - user provided images", [
                    'article_id' => $article->id,
                    'user_images_count' => count($this->featuredImages)
                ]);
                return;
            }
            
            // Check if website uses home-decor theme
            // Use theme() method to get the relationship, not the theme column (which is a string)
            $websiteTheme = $website->theme()->first();
            if (!$websiteTheme || $websiteTheme->slug !== 'home-decor') {
                Log::info("AI Image Generation: Website theme is not home-decor, skipping", [
                    'website_id' => $website->id,
                    'theme_slug' => $websiteTheme?->slug ?? 'none'
                ]);
                return;
            }

            Log::info("AI Image Generation: Home decor theme detected, analyzing article", [
                'website_id' => $website->id,
                'article_id' => $article->id,
                'article_type' => $this->articleType,
                'topic' => $this->topic
            ]);

            // Check if this is a "list" article that needs multiple images
            $listAnalysis = AIImageService::detectListArticle($this->topic, $content);
            
            Log::info("AI Image Generation: List analysis result", [
                'article_id' => $article->id,
                'is_list' => $listAnalysis['is_list'] ?? false,
                'needs_images' => $listAnalysis['needs_images'] ?? false,
                'count' => $listAnalysis['count'] ?? 0
            ]);

            // Extract items from content for image generation
            $imageService = new AIImageService($user);
            $items = [];
            
            // ENHANCED: Extract the number from the title first
            // For "26 Luxe Home Decor in the World" → we need exactly 26 images
            $titleCount = AIImageService::extractNumberFromTitle($this->topic);
            $theme = $listAnalysis['theme'] ?? 'modern elegant home decor';
            $styleKeywords = $listAnalysis['style_keywords'] ?? 'modern, elegant, sophisticated';
            
            Log::info("AI Image Generation: Title analysis", [
                'article_id' => $article->id,
                'title_count' => $titleCount,
                'theme' => $theme,
                'style_keywords' => $styleKeywords
            ]);
            
            if ($listAnalysis['is_list'] && $listAnalysis['needs_images']) {
                // For list articles, first try to extract from content H2 headers
                $items = $imageService->generatePromptsForListItems($content, $this->topic);
            }
            
            // ENHANCED: If title specifies a count but we don't have enough items from content,
            // generate theme-based items to reach the required count
            if ($titleCount > 0 && count($items) < $titleCount) {
                $existingCount = count($items);
                $neededCount = $titleCount - $existingCount;
                
                Log::info("AI Image Generation: Generating additional theme-based items", [
                    'article_id' => $article->id,
                    'existing_items' => $existingCount,
                    'needed_items' => $neededCount,
                    'theme' => $theme
                ]);
                
                // Generate theme-based items to fill the gap
                $themeItems = $this->generateThemeBasedItems($theme, $styleKeywords, $existingCount + 1, $titleCount, $this->topic);
                $items = array_merge($items, $themeItems);
            }
            
            // ALWAYS add a hero/featured image as the first item
            // This ensures we have a thumbnail for the article
            $heroItem = [
                'title' => $article->title,
                'description' => "Featured hero image representing {$theme}. This will be used as the article thumbnail.",
                'position' => 0,
                'is_hero' => true
            ];

            // If no list items found from content, generate items based on title count
            if (empty($items)) {
                if ($titleCount > 0) {
                    // Generate items based on the title count and theme
                    Log::info("AI Image Generation: No content items found, generating from title count", [
                        'article_id' => $article->id,
                        'title_count' => $titleCount,
                        'theme' => $theme
                    ]);
                    $items = $this->generateThemeBasedItems($theme, $styleKeywords, 1, $titleCount, $this->topic);
                } else {
                    Log::info("AI Image Generation: No list items found, generating single featured image", [
                        'article_id' => $article->id,
                        'topic' => $this->topic
                    ]);
                }
            }
            
            // Re-index positions for list items starting from 1
            foreach ($items as $index => &$item) {
                $item['position'] = $index + 1;
            }
            unset($item);
            
            // Add hero at position 0
            array_unshift($items, $heroItem);
            
            Log::info("AI Image Generation: Final items prepared", [
                'article_id' => $article->id,
                'total_items' => count($items),
                'title_count' => $titleCount
            ]);

            // Determine max images: use title count if available (+ 1 for hero), otherwise limit to 50
            $maxImages = $titleCount > 0 ? min($titleCount + 1, 51) : min(count($items), 50);
            
            $items = array_slice($items, 0, $maxImages);
            
            Log::info("AI Image Generation: Image count determined", [
                'article_id' => $article->id,
                'title_count' => $titleCount,
                'max_images' => $maxImages,
                'actual_items' => count($items)
            ]);

            Log::info("AI Image Generation: Dispatching job for home decor article", [
                'article_id' => $article->id,
                'website_id' => $website->id,
                'user_id' => $user->id,
                'items_count' => count($items),
                'estimated_cost' => count($items) * 0.04, // Standard quality cost per image
                'queue_connection' => config('queue.default'),
                'has_openai_key' => !empty($user->openai_api_key)
            ]);

            // Dispatch the image generation job with a small delay to ensure article is fully saved
            GenerateAIImagesJob::dispatch(
                $article->id,
                $user->id,
                $items,
                '1024x1024', // Standard size for article images
                'standard',  // Standard quality to control costs
                'natural'    // Natural style for home decor
            )->delay(now()->addSeconds(5)); // Small delay to ensure DB transaction is committed

        } catch (\Exception $e) {
            Log::error("AI Image Generation: Failed to dispatch job", [
                'article_id' => $article->id,
                'website_id' => $website->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the shuffled order of image sections for a given variation index
     * This ensures consistency between content section order and image_sections in variation_metadata
     */
    private function getShuffledImageSections(int $variationIndex): array
    {
        $imageSections = $this->featuredImages;
        
        if (count($imageSections) < 2) {
            return $imageSections;
        }
        
        // Extract titles to determine shuffle order
        $imageTitles = array_map(fn($img) => $img['title'] ?? '', $imageSections);
        $originalTitles = $imageTitles;
        
        // Use same seed as shuffleHomeDecorImageSections for consistency
        mt_srand($variationIndex * 54321);
        shuffle($imageTitles);
        mt_srand();
        
        // If shuffle resulted in same order, rotate by 1
        if ($imageTitles === $originalTitles) {
            $first = array_shift($imageTitles);
            $imageTitles[] = $first;
        }
        
        // Reorder imageSections based on shuffled titles
        $shuffledSections = [];
        foreach ($imageTitles as $title) {
            foreach ($imageSections as $section) {
                if (($section['title'] ?? '') === $title) {
                    $shuffledSections[] = $section;
                    break;
                }
            }
        }
        
        return $shuffledSections;
    }

    /**
     * Shuffle the order of image sections in home decor articles for variation
     * This reorders the H2 sections that correspond to uploaded images with titles
     * Uses simple regex-based approach instead of DOM parsing for reliability
     */
    private function shuffleHomeDecorImageSections(string $content, int $variationIndex): string
    {
        try {
            // Extract image section titles from the uploaded images
            $imageTitles = array_map(fn($img) => $img['title'] ?? '', $this->featuredImages);
            $imageTitles = array_filter($imageTitles);
            $imageTitles = array_values($imageTitles); // Re-index
            
            if (count($imageTitles) < 2) {
                return $content; // Need at least 2 sections to shuffle
            }
            
            // Get shuffled order (same algorithm as getShuffledImageSections)
            $originalTitles = $imageTitles;
            mt_srand($variationIndex * 54321);
            $shuffledTitles = $imageTitles;
            shuffle($shuffledTitles);
            mt_srand();
            
            // If shuffle resulted in same order, rotate by 1
            if ($shuffledTitles === $originalTitles) {
                $first = array_shift($shuffledTitles);
                $shuffledTitles[] = $first;
            }
            
            Log::info("Shuffling home decor image sections", [
                'variation_index' => $variationIndex,
                'original_order' => $originalTitles,
                'new_order' => $shuffledTitles
            ]);
            
            // Extract each section using regex
            $sections = [];
            foreach ($originalTitles as $title) {
                $escapedTitle = preg_quote($title, '/');
                // Match H2 with this title and everything until next H2 or end
                $pattern = '/(<h2[^>]*>.*?' . $escapedTitle . '.*?<\/h2>)(.*?)(?=<h2|$)/is';
                if (preg_match($pattern, $content, $matches)) {
                    $sections[$title] = $matches[1] . $matches[2];
                }
            }
            
            if (count($sections) < 2) {
                Log::warning("Could not extract enough sections for shuffling", [
                    'found_sections' => count($sections),
                    'expected' => count($originalTitles)
                ]);
                return $content;
            }
            
            // Find intro (content before first image section)
            $firstTitle = $originalTitles[0];
            $escapedFirstTitle = preg_quote($firstTitle, '/');
            $introPattern = '/^(.*?)(?=<h2[^>]*>.*?' . $escapedFirstTitle . ')/is';
            $intro = '';
            if (preg_match($introPattern, $content, $introMatch)) {
                $intro = $introMatch[1];
            }
            
            // Rebuild content with shuffled sections
            $newContent = $intro;
            foreach ($shuffledTitles as $title) {
                if (isset($sections[$title])) {
                    $newContent .= $sections[$title];
                }
            }
            
            Log::info("Successfully shuffled image sections for variation {$variationIndex}");
            return $newContent;
            
        } catch (\Exception $e) {
            Log::warning("Failed to shuffle image sections, using original content", [
                'variation_index' => $variationIndex,
                'error' => $e->getMessage()
            ]);
            return $content;
        }
    }
}
