<?php

namespace App\Services;

use App\Models\ArticleImage;
use App\Models\ApiUsageLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AIImageService
{
    protected $client;
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        
        if (empty($user->openai_api_key)) {
            throw new \Exception('OpenAI API key not configured for this user.');
        }
        
        $this->client = \OpenAI::client($user->openai_api_key);
    }

    /**
     * Generate a single image using DALL-E 3
     * 
     * @param string $prompt The image generation prompt
     * @param string $size Image size: '1024x1024', '1792x1024', or '1024x1792'
     * @param string $quality Image quality: 'standard' or 'hd'
     * @param string $style Image style: 'natural' or 'vivid'
     * @return array ['url' => string, 'revised_prompt' => string]
     */
    public function generateImage(
        string $prompt,
        string $size = '1024x1024',
        string $quality = 'standard',
        string $style = 'natural'
    ): array {
        try {
            Log::info('AIImageService: Generating image', [
                'prompt' => Str::limit($prompt, 100),
                'size' => $size,
                'quality' => $quality,
                'style' => $style
            ]);

            $response = $this->client->images()->create([
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => $size,
                'quality' => $quality,
                'style' => $style,
                'response_format' => 'url',
            ]);

            $imageUrl = $response->data[0]->url;
            $revisedPrompt = $response->data[0]->revisedPrompt ?? $prompt;

            // Log API usage
            $cost = $this->calculateImageCost($size, $quality);
            ApiUsageLog::create([
                'user_id' => $this->user->id,
                'provider' => 'openai',
                'model' => 'dall-e-3',
                'operation' => 'image_generation',
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'estimated_cost' => $cost,
                'generation_mode' => 'ai_image',
                'metadata' => [
                    'prompt' => Str::limit($prompt, 500),
                    'size' => $size,
                    'quality' => $quality,
                    'style' => $style,
                ]
            ]);

            Log::info('AIImageService: Image generated successfully', [
                'cost' => $cost
            ]);

            return [
                'url' => $imageUrl,
                'revised_prompt' => $revisedPrompt,
                'cost' => $cost
            ];

        } catch (\Exception $e) {
            Log::error('AIImageService: Failed to generate image', [
                'error' => $e->getMessage(),
                'prompt' => Str::limit($prompt, 100)
            ]);
            throw $e;
        }
    }

    /**
     * Generate multiple images for an article (e.g., "Top 10 Homes")
     * 
     * @param array $items Array of items to generate images for
     * @param string $articleContext Context about the article for better prompts
     * @param string $size Image size
     * @param string $quality Image quality
     * @param string $style Image style
     * @return array Array of generated image data
     */
    public function generateArticleImages(
        array $items,
        string $articleContext = '',
        string $size = '1024x1024',
        string $quality = 'standard',
        string $style = 'natural'
    ): array {
        $generatedImages = [];

        foreach ($items as $index => $item) {
            try {
                // Build a detailed prompt for each item
                $prompt = $this->buildImagePrompt($item, $articleContext, $index + 1);
                
                $result = $this->generateImage($prompt, $size, $quality, $style);
                
                // Download and store the image locally
                $localPath = $this->downloadAndStoreImage($result['url'], $item['title'] ?? "item-{$index}");
                
                $generatedImages[] = [
                    'index' => $index,
                    'item' => $item,
                    'prompt' => $prompt,
                    'revised_prompt' => $result['revised_prompt'],
                    'original_url' => $result['url'],
                    'local_path' => $localPath,
                    'cost' => $result['cost']
                ];

                // Add a small delay between requests to avoid rate limiting
                if ($index < count($items) - 1) {
                    usleep(500000); // 0.5 second delay
                }

            } catch (\Exception $e) {
                Log::error("Failed to generate image for item {$index}", [
                    'item' => $item,
                    'error' => $e->getMessage()
                ]);
                
                $generatedImages[] = [
                    'index' => $index,
                    'item' => $item,
                    'error' => $e->getMessage(),
                    'local_path' => null
                ];
            }
        }

        return $generatedImages;
    }

    /**
     * Build an optimized image prompt for home decor items
     */
    protected function buildImagePrompt(array $item, string $articleContext, int $position): string
    {
        $title = $item['title'] ?? '';
        $description = $item['description'] ?? '';
        
        // Base style for home decor images
        $styleGuide = "Professional interior design photography style, high-end home decor magazine quality, " .
                      "warm natural lighting, clean composition, modern aesthetic, inviting atmosphere, " .
                      "no text or watermarks, photorealistic";

        // Build the prompt
        $prompt = "Create a stunning photograph of: {$title}. ";
        
        if (!empty($description)) {
            $prompt .= "{$description}. ";
        }
        
        if (!empty($articleContext)) {
            $prompt .= "Context: {$articleContext}. ";
        }
        
        $prompt .= $styleGuide;
        
        return $prompt;
    }

    /**
     * Download an image from URL and store it locally in public/uploads
     */
    public function downloadAndStoreImage(string $url, string $title): string
    {
        try {
            $imageContents = file_get_contents($url);
            
            if ($imageContents === false) {
                throw new \Exception('Failed to download image from URL');
            }

            // Generate unique filename
            $filename = Str::slug($title) . '-' . Str::random(8) . '.webp';
            
            // Save directly to public/uploads (not storage)
            $directory = public_path('uploads/images/ai-generated');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Try to convert to WebP for smaller file size
            $image = @imagecreatefromstring($imageContents);
            if ($image !== false) {
                $fullPath = $directory . '/' . $filename;
                imagewebp($image, $fullPath, 85);
                imagedestroy($image);
            } else {
                // Fallback: save as PNG
                $filename = Str::slug($title) . '-' . Str::random(8) . '.png';
                $fullPath = $directory . '/' . $filename;
                file_put_contents($fullPath, $imageContents);
            }

            // Return the public URL path (relative to public/)
            return '/uploads/images/ai-generated/' . $filename;

        } catch (\Exception $e) {
            Log::error('AIImageService: Failed to download and store image', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate the cost for DALL-E 3 image generation
     * 
     * Pricing as of March 2026:
     * - 1024x1024 Standard: $0.040
     * - 1024x1024 HD: $0.080
     * - 1792x1024 / 1024x1792 Standard: $0.080
     * - 1792x1024 / 1024x1792 HD: $0.120
     */
    protected function calculateImageCost(string $size, string $quality): float
    {
        $isLargeSize = in_array($size, ['1792x1024', '1024x1792']);
        $isHD = $quality === 'hd';

        if ($isLargeSize) {
            return $isHD ? 0.120 : 0.080;
        }

        return $isHD ? 0.080 : 0.040;
    }

    /**
     * Analyze article content to detect if it's a "list" article that needs multiple images
     * Returns the list items found in the content
     */
    public static function detectListArticle(string $topic, string $content = ''): array
    {
        $items = [];
        
        // Check if topic contains a number pattern like "Top 10", "Best 5", "10 Best", etc.
        if (preg_match('/\b(\d+)\s*(best|top|most|amazing|beautiful|stunning|gorgeous|incredible|favorite|popular)\b/i', $topic, $matches) ||
            preg_match('/\b(best|top|most|amazing|beautiful|stunning|gorgeous|incredible|favorite|popular)\s*(\d+)\b/i', $topic, $matches)) {
            
            $count = isset($matches[1]) && is_numeric($matches[1]) ? (int)$matches[1] : (int)$matches[2];
            
            return [
                'is_list' => true,
                'count' => $count,
                'needs_images' => true
            ];
        }

        // If content is provided, try to extract list items from H2/H3 headers
        if (!empty($content)) {
            if (preg_match_all('/<h[23][^>]*>(?:\d+[\.\):]?\s*)?([^<]+)<\/h[23]>/i', $content, $matches)) {
                foreach ($matches[1] as $title) {
                    $title = trim(strip_tags($title));
                    // Filter out generic headers
                    if (!preg_match('/^(introduction|conclusion|tips|faq|frequently|summary|overview)/i', $title)) {
                        $items[] = ['title' => $title];
                    }
                }
            }
        }

        return [
            'is_list' => count($items) >= 3,
            'count' => count($items),
            'items' => $items,
            'needs_images' => count($items) >= 3
        ];
    }

    /**
     * Generate prompts for article list items based on content analysis
     */
    public function generatePromptsForListItems(string $content, string $articleContext = ''): array
    {
        $items = [];
        
        // Extract numbered items or H2/H3 sections
        if (preg_match_all('/<h[23][^>]*>(?:\d+[\.\):]?\s*)?([^<]+)<\/h[23]>(?:\s*<p[^>]*>([^<]+)<\/p>)?/i', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                $title = trim(strip_tags($match[1]));
                $description = isset($match[2]) ? trim(strip_tags($match[2])) : '';
                
                // Filter out generic headers
                if (!preg_match('/^(introduction|conclusion|tips|faq|frequently|summary|overview|ingredients|instructions)/i', $title)) {
                    $items[] = [
                        'title' => $title,
                        'description' => Str::limit($description, 200),
                        'position' => $index + 1
                    ];
                }
            }
        }

        return $items;
    }
}
