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
use Illuminate\Support\Facades\Log;

class GenerateAIImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutes

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
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $article = Article::find($this->articleId);
        $user = User::find($this->userId);

        if (!$article || !$user) {
            Log::error('GenerateAIImagesJob: Article or user not found', [
                'article_id' => $this->articleId,
                'user_id' => $this->userId
            ]);
            return;
        }

        if (empty($user->openai_api_key)) {
            Log::error('GenerateAIImagesJob: User has no OpenAI API key');
            return;
        }

        try {
            $imageService = new AIImageService($user);

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
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
