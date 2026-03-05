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
     * Generate a single image using gpt-image-1
     * 
     * @param string $prompt The image generation prompt
     * @param string $size Image size: '1024x1024', '1792x1024', or '1024x1792'
     * @param string $quality Image quality: 'standard' or 'hd' (converted to gpt-image-1 format)
     * @param string $style Image style: 'natural' or 'vivid' (ignored for gpt-image-1)
     * @return array ['url' => string, 'revised_prompt' => string]
     */
    public function generateImage(
        string $prompt,
        string $size = '1024x1024',
        string $quality = 'standard',
        string $style = 'natural'
    ): array {
        try {
            // Convert quality parameter from DALL-E format to gpt-image-1 format
            // 'standard' -> 'auto', 'hd' -> 'high'
            $gptQuality = $quality === 'hd' ? 'high' : 'auto';
            // Convert legacy DALL-E sizes to gpt-image-1 supported sizes.
            $gptSize = match ($size) {
                '1792x1024' => '1536x1024',
                '1024x1792' => '1024x1536',
                default => $size,
            };
            
            Log::info('AIImageService: Generating image', [
                'prompt' => Str::limit($prompt, 100),
                'size' => $gptSize,
                'quality' => $gptQuality,
                'model' => 'gpt-image-1'
            ]);

            // Build request parameters for gpt-image-1
            $requestParams = [
                'model' => 'gpt-image-1',
                'prompt' => $prompt,
                'n' => 1,
                'size' => $gptSize,
                'quality' => $gptQuality,
            ];

            $response = $this->client->images()->create($requestParams);

            $imageUrl = $response->data[0]->url ?? null;
            $imageBase64 = $response->data[0]->b64Json ?? $response->data[0]->b64_json ?? null;
            $revisedPrompt = $response->data[0]->revisedPrompt ?? $response->data[0]->revised_prompt ?? $prompt;

            if (!$imageUrl && !$imageBase64) {
                throw new \Exception('No image payload returned by gpt-image-1');
            }

            // Log API usage
            $cost = $this->calculateImageCost($size, $quality);
            ApiUsageLog::create([
                'user_id' => $this->user->id,
                'provider' => 'openai',
                'model' => 'gpt-image-1',
                'operation' => 'image_generation',
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'estimated_cost' => $cost,
                'generation_mode' => 'ai_image',
                'metadata' => [
                    'prompt' => Str::limit($prompt, 500),
                    'size' => $size,
                    'quality' => $gptQuality,
                ]
            ]);

            Log::info('AIImageService: Image generated successfully', [
                'cost' => $cost
            ]);

            return [
                'url' => $imageUrl,
                'b64_json' => $imageBase64,
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
                Log::info('AIImageService: Starting image generation for item', [
                    'index' => $index + 1,
                    'total' => count($items),
                    'title' => $item['title'] ?? 'N/A'
                ]);

                // Build a detailed prompt for each item
                $prompt = $this->buildImagePrompt($item, $articleContext, $index + 1);
                
                $result = $this->generateImage($prompt, $size, $quality, $style);

                Log::info('AIImageService: Image generated successfully for item', [
                    'index' => $index + 1,
                    'title' => $item['title'] ?? 'N/A'
                ]);
                
                // gpt-image-1 may return base64 image data instead of a URL.
                $localPath = null;
                if (!empty($result['b64_json'])) {
                    $localPath = $this->storeBase64Image($result['b64_json'], $item['title'] ?? "item-{$index}");
                } elseif (!empty($result['url'])) {
                    $localPath = $this->downloadAndStoreImage($result['url'], $item['title'] ?? "item-{$index}");
                } else {
                    throw new \Exception('No image data available to store.');
                }
                
                $generatedImages[] = [
                    'index' => $index,
                    'item' => $item,
                    'prompt' => $prompt,
                    'revised_prompt' => $result['revised_prompt'],
                    'original_url' => $result['url'] ?? null,
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
     * Creates highly realistic professional interior photography prompts
     */
    protected function buildImagePrompt(array $item, string $articleContext, int $position): string
    {
        $title = $item['title'] ?? '';
        $description = $item['description'] ?? '';
        $isHero = !empty($item['is_hero']);
        
        // Build the core subject
        $subject = $title;
        if (!empty($description)) {
            $subject .= ". {$description}";
        }
        
        // Construct the professional interior photography prompt
        $prompt = <<<PROMPT
Create a highly realistic professional interior design photograph illustrating: "{$subject}".

The scene must look completely natural and logical like a real home photographed by an interior design magazine.

Style: modern, elegant, minimal, stylish home decoration.

Scene requirements:
- realistic furniture placement
- balanced composition
- natural color palette
- modern decor objects
- natural lighting from large windows
- soft shadows
- real materials (wood, marble, fabric, glass, ceramic, metal)
- clean organized space
- no clutter
- livable, inviting atmosphere

Camera:
Professional real estate photography.
Camera model: Canon EOS R5 Mark II
Lens: 35mm
Aperture: f/2.8
ISO: 100
Ultra sharp focus
HDR photography
High dynamic range
Natural sunlight

Quality:
ultra realistic
photorealistic
8k resolution
magazine quality
no CGI
no 3D render
no artificial look
no text or watermarks
no people

Composition:
interior architecture photography
wide angle interior shot
perfect perspective
balanced lighting
PROMPT;

        // Add hero-specific instructions for featured images
        if ($isHero) {
            $prompt .= "\n\nThis is the hero/featured image - make it especially stunning and eye-catching, showcasing the best angle and lighting.";
        }

        return $prompt;
    }

    /**
     * Build a simplified prompt for non-home-decor themes
     * Can be extended for other themes like crochet, food, etc.
     */
    protected function buildGenericImagePrompt(array $item, string $articleContext, int $position): string
    {
        $title = $item['title'] ?? '';
        $description = $item['description'] ?? '';
        
        $styleGuide = "Professional photography style, high quality, " .
                      "natural lighting, clean composition, " .
                      "no text or watermarks, photorealistic, 8k resolution";

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
     * Store a base64-encoded image payload to public/uploads.
     */
    public function storeBase64Image(string $base64Image, string $title): string
    {
        try {
            $imageContents = base64_decode($base64Image, true);

            if ($imageContents === false) {
                throw new \Exception('Failed to decode base64 image payload');
            }

            $filename = Str::slug($title) . '-' . Str::random(8) . '.webp';
            $directory = public_path('uploads/images/ai-generated');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $image = @imagecreatefromstring($imageContents);
            if ($image !== false) {
                $fullPath = $directory . '/' . $filename;
                imagewebp($image, $fullPath, 85);
                imagedestroy($image);
            } else {
                $filename = Str::slug($title) . '-' . Str::random(8) . '.png';
                $fullPath = $directory . '/' . $filename;
                file_put_contents($fullPath, $imageContents);
            }

            return '/uploads/images/ai-generated/' . $filename;

        } catch (\Exception $e) {
            Log::error('AIImageService: Failed to store base64 image', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate the cost for gpt-image-1 image generation
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
