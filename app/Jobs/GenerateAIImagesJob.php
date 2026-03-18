<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\User;
use App\Services\AIImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class GenerateAIImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 1800; // 30 minutes - image generation can be slow in production
    public $maxExceptions = 2; // Allow some failures before marking job as failed
    public $backoff = [60, 120, 300]; // Retry after 1min, 2min, 5min

    protected int $articleId;
    protected int $userId;
    protected array $items;
    protected string $size;
    protected string $quality;
    protected string $style;

    /**
     * Create a new job instance.
     *
     * @param int $articleId The article to generate images for
     * @param int $userId The user whose API key will be used
     * @param array $items Array of items to generate images for (each with 'title' and optional 'description')
     * @param string $size Image size: '1024x1024', '1792x1024', or '1024x1792'
     * @param string $quality Image quality: 'standard' or 'hd'
     * @param string $style Image style: 'natural' or 'vivid'
     */
    public function __construct(
        int $articleId,
        int $userId,
        array $items,
        string $size = '1024x1024',
        string $quality = 'standard',
        string $style = 'natural'
    ) {
        $this->articleId = $articleId;
        $this->userId = $userId;
        $this->items = $items;
        $this->size = $size;
        $this->quality = $quality;
        $this->style = $style;
        
        // Set the queue for image jobs (separate from article jobs)
        $this->onQueue('images');
    }

    /**
     * Get the middleware the job should pass through.
     * This prevents too many image jobs from running simultaneously per user.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            // Only allow 2 concurrent image generation jobs per user to avoid OpenAI rate limits
            (new WithoutOverlapping('ai-images-user-' . $this->userId))
                ->releaseAfter(300) // Release lock after 5 minutes if stuck
                ->expireAfter(1800), // Lock expires after 30 minutes max
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('GenerateAIImagesJob: Job started - picked up by worker', [
            'article_id' => $this->articleId,
            'user_id' => $this->userId,
            'items_count' => count($this->items),
            'attempt' => $this->attempts(),
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
        ]);

        $article = Article::find($this->articleId);
        $user = User::find($this->userId);

        if (!$article || !$user) {
            Log::error('GenerateAIImagesJob: Article or user not found', [
                'article_id' => $this->articleId,
                'user_id' => $this->userId
            ]);
            return;
        }

        // Check if user has configured the selected image generation provider
        $provider = $user->image_generation_provider ?? 'openai';
        
        if ($provider === 'gemini' && empty($user->gemini_api_key)) {
            Log::error('GenerateAIImagesJob: User selected Gemini but has no API key configured');
            return;
        }
        
        if ($provider === 'ideogram' && empty($user->ideogram_api_key)) {
            Log::error('GenerateAIImagesJob: User selected Ideogram but has no API key configured');
            return;
        }
        
        if ($provider === 'openai' && empty($user->openai_api_key)) {
            Log::error('GenerateAIImagesJob: User selected OpenAI but has no API key configured');
            return;
        }

        Log::info('GenerateAIImagesJob: Using provider', [
            'provider' => $provider,
            'article_id' => $this->articleId,
            'has_ideogram_key' => !empty($user->ideogram_api_key),
            'has_openai_key' => !empty($user->openai_api_key),
            'has_gemini_key' => !empty($user->gemini_api_key),
        ]);

        try {
            Log::info('GenerateAIImagesJob: Initializing AIImageService...');
            $imageService = new AIImageService($user);
            Log::info('GenerateAIImagesJob: AIImageService initialized successfully');

            Log::info('GenerateAIImagesJob: Starting image generation', [
                'article_id' => $this->articleId,
                'items_count' => count($this->items)
            ]);

            $articleContext = "For an article titled: {$article->title}";
            
            // Separate hero image from other images
            $heroItem = null;
            $regularItems = [];
            
            foreach ($this->items as $item) {
                if (!empty($item['is_hero'])) {
                    $heroItem = $item;
                } else {
                    $regularItems[] = $item;
                }
            }
            
            $allGeneratedImages = [];
            $featuredImagePath = null;
            
            // Generate hero image first with landscape aspect ratio (better for thumbnails)
            if ($heroItem) {
                Log::info('GenerateAIImagesJob: Generating hero/featured image', [
                    'article_id' => $this->articleId,
                    'title' => $heroItem['title']
                ]);
                
                $heroImages = $imageService->generateArticleImages(
                    [$heroItem],
                    $articleContext,
                    '1792x1024', // Landscape for thumbnails
                    $this->quality,
                    $this->style
                );
                
                foreach ($heroImages as $heroImage) {
                    if (!empty($heroImage['local_path'])) {
                        $heroImage['is_hero'] = true;
                        $allGeneratedImages[] = $heroImage;
                        $featuredImagePath = $heroImage['local_path'];
                    }
                }
            }
            
            // Generate regular images for list items
            if (!empty($regularItems)) {
                Log::info('GenerateAIImagesJob: Generating list item images', [
                    'article_id' => $this->articleId,
                    'items_count' => count($regularItems)
                ]);
                
                $regularImages = $imageService->generateArticleImages(
                    $regularItems,
                    $articleContext,
                    $this->size,
                    $this->quality,
                    $this->style
                );
                
                foreach ($regularImages as $img) {
                    if (!empty($img['local_path'])) {
                        $allGeneratedImages[] = $img;
                    }
                }
            }

            // Re-verify article still exists before saving (it may have been deleted during generation)
            $article = Article::find($this->articleId);
            if (!$article) {
                Log::warning('GenerateAIImagesJob: Article was deleted during image generation, images generated but not saved', [
                    'article_id' => $this->articleId,
                    'images_generated' => count($allGeneratedImages)
                ]);
                return;
            }

            // Save generated images to database
            $savedImagesCount = 0;
            foreach ($allGeneratedImages as $position => $imageData) {
                $isHero = !empty($imageData['is_hero']);
                $imageSize = $isHero ? '1792x1024' : $this->size;
                
                ArticleImage::create([
                    'article_id' => $this->articleId,
                    'user_id' => $this->userId,
                    'title' => $imageData['item']['title'] ?? null,
                    'prompt' => $imageData['prompt'] ?? null,
                    'revised_prompt' => $imageData['revised_prompt'] ?? null,
                    'original_url' => $imageData['original_url'] ?? null,
                    'local_path' => $imageData['local_path'],
                    // Ensure stable sequential ordering across hero + content images
                    'position' => $position,
                    'size' => $imageSize,
                    'quality' => $this->quality,
                    'style' => $this->style,
                    'cost' => $imageData['cost'] ?? 0,
                    // DB enum only allows: ai, uploaded
                    'generation_type' => 'ai',
                    'metadata' => [
                        'item' => $imageData['item'],
                        'is_hero' => $isHero,
                    ]
                ]);
                $savedImagesCount++;

                Log::info('GenerateAIImagesJob: Saved image', [
                    'article_id' => $this->articleId,
                    'position' => $position,
                    'title' => $imageData['item']['title'] ?? 'N/A',
                    'is_hero' => $isHero
                ]);
            }
            
            // Set the hero image as the article's featured image (thumbnail)
            if ($featuredImagePath && empty($article->featured_image)) {
                $article->update([
                    'featured_image' => $featuredImagePath
                ]);
                
                Log::info('GenerateAIImagesJob: Set featured image for article', [
                    'article_id' => $this->articleId,
                    'featured_image' => $featuredImagePath
                ]);
            }

            Log::info('GenerateAIImagesJob: Completed', [
                'article_id' => $this->articleId,
                'images_generated' => $savedImagesCount
            ]);

        } catch (\Exception $e) {
            Log::error('GenerateAIImagesJob: Failed', [
                'article_id' => $this->articleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateAIImagesJob: Job permanently failed after all retries', [
            'article_id' => $this->articleId,
            'user_id' => $this->userId,
            'items_count' => count($this->items),
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception)
        ]);
    }
}
