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
    protected string $provider;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->provider = $user->image_generation_provider ?? 'openai';
        
        // Initialize the appropriate client based on provider
        if ($this->provider === 'gemini') {
            if (empty($user->gemini_api_key)) {
                throw new \Exception('Gemini API key not configured for this user.');
            }
            // Gemini uses HTTP client (Guzzle)
            $this->client = new \GuzzleHttp\Client();
        } elseif ($this->provider === 'ideogram') {
            if (empty($user->ideogram_api_key)) {
                throw new \Exception('Ideogram API key not configured for this user.');
            }
            // Ideogram uses HTTP client (Guzzle)
            $this->client = new \GuzzleHttp\Client();
        } else {
            // Default to OpenAI
            if (empty($user->openai_api_key)) {
                throw new \Exception('OpenAI API key not configured for this user.');
            }
            $this->client = \OpenAI::client($user->openai_api_key);
        }
    }

    /**
     * Generate a single image using the configured provider (OpenAI, Gemini, or Ideogram)
     * 
     * @param string $prompt The image generation prompt
     * @param string $size Image size: '1024x1024', '1792x1024', or '1024x1792'
     * @param string $quality Image quality: 'standard' or 'hd'
     * @param string $style Image style: 'natural' or 'vivid'
     * @return array ['url' => string, 'revised_prompt' => string, 'b64_json' => string|null]
     */
    public function generateImage(
        string $prompt,
        string $size = '1024x1024',
        string $quality = 'standard',
        string $style = 'natural'
    ): array {
        if ($this->provider === 'gemini') {
            return $this->generateImageWithGemini($prompt, $size, $quality, $style);
        }
        
        if ($this->provider === 'ideogram') {
            return $this->generateImageWithIdeogram($prompt, $size, $quality, $style);
        }
        
        return $this->generateImageWithOpenAI($prompt, $size, $quality, $style);
    }

    /**
     * Generate image using OpenAI DALL-E (gpt-image-1)
     */
    protected function generateImageWithOpenAI(
        string $prompt,
        string $size = '1024x1024',
        string $quality = 'standard',
        string $style = 'natural'
    ): array {
        try {
            // Convert quality parameter from DALL-E format to gpt-image-1 format
            $gptQuality = $quality === 'hd' ? 'high' : 'auto';
            // Convert legacy DALL-E sizes to gpt-image-1 supported sizes
            $gptSize = match ($size) {
                '1792x1024' => '1536x1024',
                '1024x1792' => '1024x1536',
                default => $size,
            };
            
            Log::info('AIImageService: Generating image with OpenAI', [
                'prompt' => Str::limit($prompt, 100),
                'size' => $gptSize,
                'quality' => $gptQuality,
                'model' => 'gpt-image-1'
            ]);

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

            $cost = $this->calculateImageCost($size, $quality);
            
            // Log API usage
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

            Log::info('AIImageService: OpenAI image generated successfully', ['cost' => $cost]);

            return [
                'url' => $imageUrl,
                'b64_json' => $imageBase64,
                'revised_prompt' => $revisedPrompt,
                'cost' => $cost,
                'provider' => 'openai'
            ];

        } catch (\Exception $e) {
            Log::error('AIImageService: Failed to generate image with OpenAI', [
                'error' => $e->getMessage(),
                'prompt' => Str::limit($prompt, 100)
            ]);
            throw $e;
        }
    }

    /**
     * Generate image using Google Gemini Imagen
     * Note: As of March 2026, Google's Imagen API is in limited preview.
     * This implementation uses Gemini's text-to-image capabilities through Vertex AI.
     * If you don't have access, the system will fall back to describing images instead.
     */
    protected function generateImageWithGemini(
        string $prompt,
        string $size = '1024x1024',
        string $quality = 'standard',
        string $style = 'natural'
    ): array {
        try {
            $apiKey = $this->user->gemini_api_key;
            
            Log::info('AIImageService: Attempting Gemini image generation', [
                'prompt' => Str::limit($prompt, 100),
                'note' => 'Gemini Imagen API access may be limited'
            ]);

            // Try the generateContent endpoint with multimodal capabilities
            // This is a workaround since direct Imagen API might not be publicly available
            $response = $this->client->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-vision:generateContent?key={$apiKey}",
                [
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => "IMPORTANT: I need you to generate a detailed image generation prompt that I can use with DALL-E or other image AI. Based on this request, create a comprehensive, detailed prompt:\n\n{$prompt}\n\nProvide ONLY the image generation prompt, nothing else."
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);
            
            // Since Gemini doesn't actually generate images yet in the public API,
            // we'll return an error explaining this
            throw new \Exception(
                'Gemini Imagen API is not publicly available yet. ' .
                'Please use OpenAI DALL-E or Ideogram for image generation, or wait for Google to release public access to Imagen. ' .
                'You can change your image provider in Settings > API Keys.'
            );

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($errorBody, true);
            $errorMessage = $errorData['error']['message'] ?? $e->getMessage();
            
            Log::error('AIImageService: Gemini image generation not available', [
                'error' => $errorMessage,
                'prompt' => Str::limit($prompt, 100)
            ]);
            
            throw new \Exception(
                'Gemini image generation is not currently available. ' .
                'The Imagen API requires special access from Google. ' .
                'Please switch to OpenAI DALL-E or Ideogram in your settings for image generation.'
            );
        } catch (\Exception $e) {
            Log::error('AIImageService: Failed Gemini image generation', [
                'error' => $e->getMessage(),
                'prompt' => Str::limit($prompt, 100)
            ]);
            throw $e;
        }
    }

    /**
     * Generate image using Ideogram API
     * Ideogram is specialized for high-quality, cost-effective image generation
     * particularly good for home decor, design, and realistic scenes
     */
    protected function generateImageWithIdeogram(
        string $prompt,
        string $size = '1024x1024',
        string $quality = 'standard',
        string $style = 'natural'
    ): array {
        try {
            $apiKey = $this->user->ideogram_api_key;
            
            Log::info('AIImageService: Generating image with Ideogram', [
                'prompt' => Str::limit($prompt, 100),
                'size' => $size,
                'quality' => $quality,
                'model' => 'ideogram-v3'
            ]);

            // Convert size to Ideogram resolution format
            $resolution = $this->convertSizeToIdeogramResolution($size);
            
            // Determine rendering speed based on quality
            // Ideogram V3 accepts: FLASH, TURBO, BALANCED, DEFAULT
            // TURBO = fast/cheap, BALANCED = standard, DEFAULT = high quality
            $renderingSpeed = $quality === 'hd' ? 'BALANCED' : 'TURBO';
            
            // Prepare multipart form data
            $multipartData = [
                [
                    'name' => 'prompt',
                    'contents' => $prompt
                ],
                [
                    'name' => 'resolution',
                    'contents' => $resolution
                ],
                [
                    'name' => 'rendering_speed',
                    'contents' => $renderingSpeed
                ],
                [
                    'name' => 'num_images',
                    'contents' => '1'
                ],
                [
                    'name' => 'magic_prompt',
                    'contents' => 'AUTO' // Let Ideogram enhance the prompt automatically
                ]
            ];

            $response = $this->client->post(
                'https://api.ideogram.ai/v1/ideogram-v3/generate',
                [
                    'multipart' => $multipartData,
                    'headers' => [
                        'Api-Key' => $apiKey,
                    ]
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result['data']) || empty($result['data'])) {
                throw new \Exception('No image data returned by Ideogram API');
            }

            $imageData = $result['data'][0];
            $imageUrl = $imageData['url'] ?? null;
            $revisedPrompt = $imageData['prompt'] ?? $prompt;

            if (!$imageUrl) {
                throw new \Exception('No image URL returned by Ideogram API');
            }

            $cost = $this->calculateIdeogramCost($resolution, $renderingSpeed);
            
            // Log API usage
            ApiUsageLog::create([
                'user_id' => $this->user->id,
                'provider' => 'ideogram',
                'model' => 'ideogram-v3',
                'operation' => 'image_generation',
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'estimated_cost' => $cost,
                'generation_mode' => 'ai_image',
                'metadata' => [
                    'prompt' => Str::limit($prompt, 500),
                    'resolution' => $resolution,
                    'rendering_speed' => $renderingSpeed,
                ]
            ]);

            Log::info('AIImageService: Ideogram image generated successfully', ['cost' => $cost]);

            return [
                'url' => $imageUrl,
                'b64_json' => null, // Ideogram returns URL, not base64
                'revised_prompt' => $revisedPrompt,
                'cost' => $cost,
                'provider' => 'ideogram'
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($errorBody, true);
            $errorMessage = $errorData['error']['message'] ?? $e->getMessage();
            
            Log::error('AIImageService: Failed to generate image with Ideogram', [
                'error' => $errorMessage,
                'prompt' => Str::limit($prompt, 100)
            ]);
            
            throw new \Exception('Ideogram API error: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('AIImageService: Failed to generate image with Ideogram', [
                'error' => $e->getMessage(),
                'prompt' => Str::limit($prompt, 100)
            ]);
            throw $e;
        }
    }

    /**
     * Convert size format to Gemini aspect ratio format
     */
    protected function convertSizeToAspectRatio(string $size): string
    {
        return match($size) {
            '1024x1024' => '1:1',
            '1792x1024', '1536x1024' => '16:9',
            '1024x1792', '1024x1536' => '9:16',
            default => '1:1'
        };
    }

    /**
     * Convert size format to Ideogram resolution format
     * Ideogram supports specific resolutions, so we map common sizes to closest Ideogram resolution
     */
    protected function convertSizeToIdeogramResolution(string $size): string
    {
        return match($size) {
            '1024x1024' => '1024x1024', // Square
            '1792x1024', '1536x1024' => '1088x768', // Landscape
            '1024x1792', '1024x1536' => '768x1088', // Portrait
            default => '1024x1024'
        };
    }

    /**
     * Calculate the cost for Ideogram image generation
     * 
     * Ideogram V3 Pricing (as of March 2026):
     * - TURBO/FLASH rendering: ~$0.08 per image (fastest, cheapest)
     * - BALANCED rendering: ~$0.12 per image (good balance)
     * - DEFAULT rendering: ~$0.20 per image (highest quality)
     * 
     * Note: Ideogram is generally more cost-effective than DALL-E for similar quality
     */
    protected function calculateIdeogramCost(string $resolution, string $renderingSpeed): float
    {
        return match(strtoupper($renderingSpeed)) {
            'TURBO', 'FLASH' => 0.08,
            'BALANCED' => 0.12,
            'DEFAULT' => 0.20,
            default => 0.08
        };
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
     * Build an optimized image prompt for home decor items.
     * Creates highly realistic professional photography prompts.
     * 
     * ENHANCED: Now uses the article title context to:
     * 1. Understand the theme (e.g., "luxe", "minimalist", "coastal")
     * 2. Extract the specific subject (villa, apartment, bedroom, etc.)
     * 3. Generate images that show EXACTLY what the article is about
     */
    protected function buildImagePrompt(array $item, string $articleContext, int $position): string
    {
        $title = $item['title'] ?? '';
        $description = $item['description'] ?? '';
        $isHero = !empty($item['is_hero']);
        
        // ENHANCED: Analyze the article title to understand the theme AND subject
        $themeAnalysis = self::analyzeArticleTitleStatic($articleContext);
        $primarySubject = self::extractPrimarySubjectPhrase($articleContext);
        
        // Build the core subject - use item title but ensure it relates to the subject type
        $subject = $title;
        if (!empty($description)) {
            $subject .= ". {$description}";
        }
        
        // Build theme-specific style description
        $themeStyle = "{$themeAnalysis['primary_theme']}, {$themeAnalysis['mood']} atmosphere";
        $materials = $themeAnalysis['materials'];
        $colorPalette = $themeAnalysis['color_palette'];
        $styleKeywords = $themeAnalysis['style_keywords'];
        
        // Determine broad shot direction from the detected subject phrase.
        $shotType = self::getShotTypeForSubject($primarySubject);
        
        // Construct the professional photography prompt with SUBJECT AWARENESS
        $prompt = <<<PROMPT
Create a highly realistic professional photograph illustrating: "{$subject}".

CRITICAL SUBJECT LOCK:
- Article title context: "{$articleContext}"
- Primary topic extracted from title: "{$primarySubject}"
- This image MUST clearly depict "{$primarySubject}".
- Do NOT replace the subject with a generic interior design scene.
- Keep the exact subject intent from the title; use the section title as a specific example within that subject.

{$shotType}

Style: {$themeStyle}
Style keywords: {$styleKeywords}

Scene requirements:
- This MUST visually match the topic "{$primarySubject}"
- {$themeAnalysis['primary_theme']} aesthetic
- balanced composition
- color palette: {$colorPalette}
- real materials: {$materials}
- natural lighting
- soft shadows
- clean organized space
- {$themeAnalysis['mood']} atmosphere

Camera:
Professional architectural/real estate photography.
Camera model: Canon EOS R5 Mark II
Lens: 24mm wide angle for exteriors, 35mm for interiors
Aperture: f/2.8
ISO: 100
Ultra sharp focus
HDR photography
Natural lighting

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
professional architecture photography
perfect perspective
balanced lighting
PROMPT;

        // Add hero-specific instructions for featured images
        if ($isHero) {
            $prompt .= "\n\nThis is the hero/featured image - make it especially stunning and eye-catching, clearly representing the title topic \"{$primarySubject}\".";
        }

        return $prompt;
    }
    
    /**
     * Extract the specific subject type from the article title.
     * This ensures images show exactly what the article is about.
     */
    public static function extractSubjectType(string $articleTitle): string
    {
        $title = strtolower($articleTitle);
        $compact = preg_replace('/[^a-z]/', '', $title) ?? '';
        
        // Villas and houses
        if (preg_match('/\b(villa|villas)\b/i', $title)) {
            return 'luxury villa with exterior view showing the full property, gardens, and architecture';
        }
        if (preg_match('/\b(mansion|mansions)\b/i', $title)) {
            return 'grand mansion with exterior and interior views';
        }
        if (preg_match('/\b(house|houses|home|homes)\b/i', $title)) {
            return 'beautiful house showing exterior architecture and surroundings';
        }
        if (preg_match('/\b(cottage|cottages)\b/i', $title)) {
            return 'charming cottage with exterior view';
        }
        if (preg_match('/\b(cabin|cabins)\b/i', $title)) {
            return 'cozy cabin in natural setting';
        }
        if (preg_match('/\b(chalet|chalets)\b/i', $title)) {
            return 'mountain chalet with exterior view';
        }
        if (preg_match('/\b(penthouse|penthouses)\b/i', $title)) {
            return 'luxury penthouse interior with city views';
        }
        
        // Apartments and condos
        if (
            preg_match('/\b(apartment|apartments|appartment|appartments|apartement|apartements|appartement|appartements|flat|flats)\b/i', $title) ||
            str_contains($compact, 'apartment') ||
            str_contains($compact, 'apartement') ||
            str_contains($compact, 'appartement') ||
            str_contains($compact, 'appart')
        ) {
            return 'apartment building or apartment unit with clear apartment context, urban setting, and residential architecture';
        }
        if (preg_match('/\b(condo|condos|condominium)\b/i', $title)) {
            return 'contemporary condominium interior';
        }
        if (preg_match('/\b(loft|lofts)\b/i', $title)) {
            return 'industrial loft apartment with high ceilings';
        }
        if (preg_match('/\b(studio|studios)\b/i', $title)) {
            return 'stylish studio apartment';
        }
        
        // Specific rooms
        if (preg_match('/\b(bedroom|bedrooms)\b/i', $title)) {
            return 'bedroom interior with bed, furniture, and decor';
        }
        if (preg_match('/\b(living room|living rooms|lounge|lounges)\b/i', $title)) {
            return 'living room interior with sofa, furniture, and decor';
        }
        if (preg_match('/\b(kitchen|kitchens)\b/i', $title)) {
            return 'kitchen interior with counters, appliances, and dining area';
        }
        if (preg_match('/\b(bathroom|bathrooms)\b/i', $title)) {
            return 'bathroom interior with fixtures and elegant finishes';
        }
        if (preg_match('/\b(dining room|dining rooms)\b/i', $title)) {
            return 'dining room with table, chairs, and elegant setting';
        }
        if (preg_match('/\b(office|offices|workspace)\b/i', $title)) {
            return 'home office or workspace interior';
        }
        if (preg_match('/\b(nursery|nurseries)\b/i', $title)) {
            return 'baby nursery room interior';
        }
        if (preg_match('/\b(closet|closets|wardrobe)\b/i', $title)) {
            return 'walk-in closet or wardrobe interior';
        }
        
        // Outdoor spaces
        if (preg_match('/\b(garden|gardens)\b/i', $title)) {
            return 'beautiful garden landscape with plants and design';
        }
        if (preg_match('/\b(patio|patios)\b/i', $title)) {
            return 'outdoor patio with furniture and decor';
        }
        if (preg_match('/\b(pool|pools)\b/i', $title)) {
            return 'swimming pool with surrounding deck and landscape';
        }
        if (preg_match('/\b(terrace|terraces|balcony|balconies)\b/i', $title)) {
            return 'terrace or balcony with outdoor furniture and views';
        }
        if (preg_match('/\b(backyard|backyards)\b/i', $title)) {
            return 'backyard outdoor living space';
        }
        
        // Decor and design elements
        if (preg_match('/\b(decor|decoration|decorating)\b/i', $title)) {
            return 'interior decor and design elements in a room setting';
        }
        if (preg_match('/\b(furniture)\b/i', $title)) {
            return 'furniture piece in a styled room setting';
        }
        if (preg_match('/\b(lighting|lights|lamp)\b/i', $title)) {
            return 'interior lighting design in a room';
        }
        
        // Default
        return 'interior space with beautiful design and decor';
    }

    /**
     * Extract the primary subject phrase directly from the article title/context.
     * This is intent-first and avoids hardcoded category dependency.
     *
     * Examples:
     * - "Top 5 Luxury Hotels" => "luxury hotels"
     * - "5 top appartements" => "appartements"
     * - "Top 8 Luxury Villas in Europe" => "luxury villas"
     */
    public static function extractPrimarySubjectPhrase(string $articleContext): string
    {
        $title = trim($articleContext);

        // Handle context wrapper: "For an article titled: XYZ"
        if (preg_match('/for an article titled:\s*(.+)$/i', $title, $m)) {
            $title = trim($m[1]);
        }

        $title = trim($title, " \t\n\r\0\x0B\"'“”");
        $title = preg_replace('/\s+/', ' ', $title) ?? $title;
        $lower = strtolower($title);

        // Remove common list prefixes/suffixes while preserving the core subject.
        $patterns = [
            '/^(top|best|most|amazing|beautiful|stunning|gorgeous|incredible)\s+\d+\s+/i',
            '/^\d+\s+(top|best|most|amazing|beautiful|stunning|gorgeous|incredible)\s+/i',
            '/^\d+\s+/i',
            '/\s+for\s+\d{4}\b/i',
            '/\s+in\s+the\s+world\b/i',
            '/\s+around\s+the\s+world\b/i',
        ];

        foreach ($patterns as $pattern) {
            $lower = trim((string) preg_replace($pattern, ' ', $lower));
        }

        // Keep a concise phrase for stronger prompt focus.
        $tokens = preg_split('/\s+/', $lower) ?: [];
        $stopWords = [
            'top', 'best', 'most', 'amazing', 'beautiful', 'stunning', 'gorgeous', 'incredible',
            'the', 'a', 'an', 'in', 'of', 'for', 'to', 'and', 'with', 'ideas', 'design', 'designs'
        ];
        $core = array_values(array_filter($tokens, static fn ($w) => !in_array($w, $stopWords, true)));

        if (empty($core)) {
            return trim($lower) !== '' ? trim($lower) : 'the exact article topic';
        }

        // Limit for concise, high-signal prompt anchor.
        return implode(' ', array_slice($core, 0, 4));
    }
    
    /**
     * Get the appropriate shot type description for a subject.
     */
    public static function getShotTypeForSubject(string $subjectType): string
    {
        // Exterior shots for villas, houses, mansions
        if (str_contains($subjectType, 'exterior') || str_contains($subjectType, 'villa') || 
            str_contains($subjectType, 'house') || str_contains($subjectType, 'mansion') ||
            str_contains($subjectType, 'cottage') || str_contains($subjectType, 'cabin') ||
            str_contains($subjectType, 'chalet')) {
            return "Shot type: Wide exterior photograph showing the full property, architecture, landscaping, and surroundings. Include sky, driveway or pathway, and any gardens or pools if applicable.";
        }

        // Apartment/condo shots should show the apartment context, not generic decor-only scenes
        if (str_contains($subjectType, 'apartment') || str_contains($subjectType, 'condominium') ||
            str_contains($subjectType, 'condo') || str_contains($subjectType, 'loft') ||
            str_contains($subjectType, 'studio') || str_contains($subjectType, 'penthouse')) {
            return "Shot type: Professional real-estate style image that clearly shows an apartment context (building facade, skyline, balcony, or unmistakable apartment unit layout). Avoid generic staged living-room-only shots without apartment identity.";
        }
        
        // Pool shots
        if (str_contains($subjectType, 'pool')) {
            return "Shot type: Exterior photograph showing the swimming pool, deck area, loungers, and surrounding landscape. Include the pool water reflection.";
        }
        
        // Garden/outdoor shots
        if (str_contains($subjectType, 'garden') || str_contains($subjectType, 'patio') || 
            str_contains($subjectType, 'terrace') || str_contains($subjectType, 'backyard')) {
            return "Shot type: Exterior/outdoor photograph showing the outdoor living space with natural lighting and surrounding greenery.";
        }
        
        // Interior shots
        return "Shot type: Wide angle interior photograph showing the full room with furniture, decor, and architectural details. Natural lighting from windows.";
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
     * Analyze article content to detect if it's a "list" article that needs multiple images.
     * 
     * ENHANCED: Now intelligently parses the article title to:
     * 1. Extract the exact number of items from the title (e.g., "26 Luxe Home Decor" → 26)
     * 2. Understand the theme/topic for contextually relevant images
     * 3. Return the count directly from the title when specified
     * 
     * Returns the list items found in the content
     */
    public static function detectListArticle(string $topic, string $content = ''): array
    {
        $items = [];
        $themeAnalysis = self::analyzeArticleTitleStatic($topic);
        
        // ENHANCED: First try to extract number directly from the title
        // This handles patterns like "26 Luxe Home Decor", "Top 10 Bedrooms", "Best 15 Living Rooms"
        $extractedCount = self::extractNumberFromTitle($topic);
        
        if ($extractedCount > 0) {
            return [
                'is_list' => true,
                'count' => $extractedCount,
                'needs_images' => true,
                'theme' => $themeAnalysis['primary_theme'],
                'style_keywords' => $themeAnalysis['style_keywords'],
                'extracted_from_title' => true
            ];
        }
        
        // Fallback: Check if topic contains a number pattern like "Top 10", "Best 5", "10 Best", etc.
        if (preg_match('/\b(\d+)\s*(best|top|most|amazing|beautiful|stunning|gorgeous|incredible|favorite|popular)\b/i', $topic, $matches) ||
            preg_match('/\b(best|top|most|amazing|beautiful|stunning|gorgeous|incredible|favorite|popular)\s*(\d+)\b/i', $topic, $matches)) {
            
            $count = isset($matches[1]) && is_numeric($matches[1]) ? (int)$matches[1] : (int)$matches[2];
            
            return [
                'is_list' => true,
                'count' => $count,
                'needs_images' => true,
                'theme' => $themeAnalysis['primary_theme'],
                'style_keywords' => $themeAnalysis['style_keywords'],
                'extracted_from_title' => true
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
            'needs_images' => count($items) >= 3,
            'theme' => $themeAnalysis['primary_theme'],
            'style_keywords' => $themeAnalysis['style_keywords'],
            'extracted_from_title' => false
        ];
    }
    
    /**
     * Extract a number from the article title.
     * Handles various patterns like:
     * - "26 Luxe Home Decor in the World"
     * - "Top 10 Best Bedrooms"
     * - "15 Most Beautiful Living Rooms"
     * - "The Best 20 Kitchen Designs"
     */
    public static function extractNumberFromTitle(string $title): int
    {
        // Pattern 1: Number at the start - "26 Luxe Home Decor", "10 Best Kitchens"
        if (preg_match('/^(\d+)\s+/i', trim($title), $matches)) {
            return (int) $matches[1];
        }
        
        // Pattern 2: "Top/Best X" - "Top 10 Homes", "Best 15 Designs"
        if (preg_match('/\b(top|best|most|amazing)\s+(\d+)\b/i', $title, $matches)) {
            return (int) $matches[2];
        }
        
        // Pattern 3: "X Top/Best" - "10 Top Homes", "15 Best Designs"
        if (preg_match('/\b(\d+)\s+(top|best|most|amazing|beautiful|stunning|gorgeous|incredible|luxe|luxury)\b/i', $title, $matches)) {
            return (int) $matches[1];
        }
        
        // Pattern 4: Any number in the title as fallback
        if (preg_match('/\b(\d+)\b/', $title, $matches)) {
            $num = (int) $matches[1];
            // Only return if it's a reasonable list count (between 3 and 100)
            if ($num >= 3 && $num <= 100) {
                return $num;
            }
        }
        
        return 0;
    }
    
    /**
     * Static version of analyzeArticleTheme for use in static methods.
     * Analyze the article title to extract theme, style, and relevant keywords.
     */
    public static function analyzeArticleTitleStatic(string $articleTitle): array
    {
        $title = strtolower($articleTitle);
        
        // Default theme values
        $analysis = [
            'primary_theme' => 'modern elegant home decor',
            'style_keywords' => 'modern, elegant, sophisticated, stylish',
            'materials' => 'wood, marble, fabric, glass, ceramic, metal',
            'mood' => 'inviting and sophisticated',
            'color_palette' => 'neutral with accent colors',
        ];
        
        // Luxe/Luxury theme detection
        if (preg_match('/\b(luxe|luxury|luxurious|opulent|lavish|high-end|premium|exclusive)\b/i', $title)) {
            $analysis['primary_theme'] = 'luxury high-end home decor';
            $analysis['style_keywords'] = 'luxurious, opulent, premium, exclusive, sophisticated, lavish';
            $analysis['materials'] = 'marble, velvet, gold accents, crystal, rich wood, silk, cashmere';
            $analysis['mood'] = 'opulent and exclusive';
            $analysis['color_palette'] = 'rich jewel tones, gold accents, deep colors with metallic touches';
        }
        
        // Minimalist theme detection
        if (preg_match('/\b(minimalist|minimal|simple|clean|scandinavian|nordic)\b/i', $title)) {
            $analysis['primary_theme'] = 'minimalist Scandinavian design';
            $analysis['style_keywords'] = 'minimalist, clean lines, simple, uncluttered, functional';
            $analysis['materials'] = 'light wood, white surfaces, natural fabrics, concrete, matte finishes';
            $analysis['mood'] = 'calm and serene';
            $analysis['color_palette'] = 'white, light grey, natural wood tones, soft pastels';
        }
        
        // Bohemian theme detection
        if (preg_match('/\b(boho|bohemian|eclectic|artistic|free-spirit)\b/i', $title)) {
            $analysis['primary_theme'] = 'bohemian eclectic style';
            $analysis['style_keywords'] = 'bohemian, eclectic, layered, artistic, worldly, free-spirited';
            $analysis['materials'] = 'rattan, macrame, textured fabrics, plants, natural fibers, woven textiles';
            $analysis['mood'] = 'warm and inviting with collected character';
            $analysis['color_palette'] = 'earthy tones, terracotta, mustard, teal, warm neutrals';
        }
        
        // Rustic/Farmhouse theme detection
        if (preg_match('/\b(rustic|farmhouse|country|cottage|barn|rural|vintage)\b/i', $title)) {
            $analysis['primary_theme'] = 'rustic farmhouse style';
            $analysis['style_keywords'] = 'rustic, cozy, charming, vintage, country, farmhouse';
            $analysis['materials'] = 'reclaimed wood, distressed finishes, wrought iron, natural stone, linen';
            $analysis['mood'] = 'warm and nostalgic';
            $analysis['color_palette'] = 'cream, sage green, warm browns, muted blues, natural tones';
        }
        
        // Industrial theme detection
        if (preg_match('/\b(industrial|loft|urban|warehouse|raw|exposed)\b/i', $title)) {
            $analysis['primary_theme'] = 'industrial loft style';
            $analysis['style_keywords'] = 'industrial, raw, urban, edgy, exposed elements, warehouse-inspired';
            $analysis['materials'] = 'exposed brick, steel, concrete, reclaimed wood, metal pipes, raw finishes';
            $analysis['mood'] = 'urban and edgy';
            $analysis['color_palette'] = 'grey, black, rust, exposed brick red, weathered metals';
        }
        
        // Mid-Century Modern theme detection
        if (preg_match('/\b(mid-century|midcentury|retro|60s|70s|atomic|eames)\b/i', $title)) {
            $analysis['primary_theme'] = 'mid-century modern design';
            $analysis['style_keywords'] = 'mid-century modern, retro, atomic age, sleek, iconic';
            $analysis['materials'] = 'teak, walnut, molded plastic, brass, leather, terrazzo';
            $analysis['mood'] = 'retro-chic and timeless';
            $analysis['color_palette'] = 'mustard yellow, avocado green, burnt orange, teak brown, cream';
        }
        
        // Contemporary/Modern theme detection
        if (preg_match('/\b(contemporary|modern|current|trendy|cutting-edge|sleek)\b/i', $title)) {
            $analysis['primary_theme'] = 'contemporary modern design';
            $analysis['style_keywords'] = 'contemporary, sleek, current trends, sophisticated, clean';
            $analysis['materials'] = 'glass, chrome, lacquered surfaces, leather, polished concrete';
            $analysis['mood'] = 'fresh and sophisticated';
            $analysis['color_palette'] = 'monochromatic with bold accents, black and white, metallic touches';
        }
        
        // Coastal/Beach theme detection
        if (preg_match('/\b(coastal|beach|nautical|seaside|ocean|marine|hamptons)\b/i', $title)) {
            $analysis['primary_theme'] = 'coastal beach house style';
            $analysis['style_keywords'] = 'coastal, breezy, relaxed, nautical, beachy, fresh';
            $analysis['materials'] = 'whitewashed wood, rattan, linen, seagrass, driftwood, natural rope';
            $analysis['mood'] = 'relaxed and breezy';
            $analysis['color_palette'] = 'white, navy blue, sandy beige, soft aqua, coral accents';
        }
        
        // Traditional/Classic theme detection
        if (preg_match('/\b(traditional|classic|timeless|elegant|formal|refined)\b/i', $title)) {
            $analysis['primary_theme'] = 'traditional classic elegance';
            $analysis['style_keywords'] = 'traditional, timeless, elegant, refined, formal, classic';
            $analysis['materials'] = 'dark wood, silk, velvet, brass, crown molding, ornate details';
            $analysis['mood'] = 'refined and sophisticated';
            $analysis['color_palette'] = 'navy, burgundy, forest green, gold, cream, rich wood tones';
        }
        
        // Art Deco theme detection
        if (preg_match('/\b(art deco|deco|gatsby|glamour|glam|hollywood)\b/i', $title)) {
            $analysis['primary_theme'] = 'art deco glamour';
            $analysis['style_keywords'] = 'art deco, glamorous, geometric, bold, luxurious, theatrical';
            $analysis['materials'] = 'lacquered surfaces, brass, mirrors, velvet, marble, geometric patterns';
            $analysis['mood'] = 'dramatic and glamorous';
            $analysis['color_palette'] = 'black, gold, emerald green, deep purple, blush pink';
        }
        
        // Japandi/Japanese theme detection
        if (preg_match('/\b(japandi|japanese|zen|wabi-sabi|asian|oriental)\b/i', $title)) {
            $analysis['primary_theme'] = 'Japandi zen style';
            $analysis['style_keywords'] = 'zen, peaceful, harmonious, balanced, organic, mindful';
            $analysis['materials'] = 'light wood, paper screens, bamboo, natural stone, ceramic, linen';
            $analysis['mood'] = 'peaceful and harmonious';
            $analysis['color_palette'] = 'off-white, soft grey, natural wood, black accents, sage green';
        }
        
        // Specific room type detection
        if (preg_match('/\b(bedroom|sleeping|master suite)\b/i', $title)) {
            $analysis['room_focus'] = 'bedroom';
        } elseif (preg_match('/\b(living room|lounge|sitting room|family room)\b/i', $title)) {
            $analysis['room_focus'] = 'living room';
        } elseif (preg_match('/\b(kitchen|cooking|culinary)\b/i', $title)) {
            $analysis['room_focus'] = 'kitchen';
        } elseif (preg_match('/\b(bathroom|bath|powder room|ensuite)\b/i', $title)) {
            $analysis['room_focus'] = 'bathroom';
        } elseif (preg_match('/\b(office|workspace|study|home office)\b/i', $title)) {
            $analysis['room_focus'] = 'home office';
        } elseif (preg_match('/\b(outdoor|patio|garden|terrace|balcony)\b/i', $title)) {
            $analysis['room_focus'] = 'outdoor space';
        } else {
            $analysis['room_focus'] = 'interior space';
        }
        
        return $analysis;
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
