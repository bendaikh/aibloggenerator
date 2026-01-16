<?php

namespace App\Services;

use App\Models\PinterestPin;
use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class PinterestDesignService
{
    // Pinterest dimensions (1:2 ratio for optimal Pinterest display)
    const PIN_WIDTH = 512;
    const PIN_HEIGHT = 1024;
    
    // Text overlay bar height
    const TEXT_BAR_HEIGHT = 150;

    /**
     * Available frame designs with their configurations.
     */
    public static function getFrameDesigns(): array
    {
        return [
            'simple_center' => [
                'name' => 'Simple Center Text',
                'description' => 'Text on white background with images',
                'preview_colors' => ['bg' => '#ffffff', 'primary' => '#8B4513', 'secondary' => '#CD853F'],
            ],
        ];
    }

    /**
     * Generate a Pinterest pin image from an article.
     */
    public function generatePinImage(PinterestPin $pin): ?string
    {
        try {
            // Get image paths
            $topImagePath = $this->resolveImagePath($pin->top_image);
            $bottomImagePath = $this->resolveImagePath($pin->bottom_image);

            if (!$topImagePath || !$bottomImagePath) {
                throw new \Exception('Top or bottom image not found');
            }

            // Create the Pinterest pin image
            $frameDesign = $pin->frame_design ?? 'simple_center';
            
            $pinImage = $this->createPinImage(
                $topImagePath,
                $bottomImagePath,
                $pin->headline_text ?? '',
                $pin->subheadline_text ?? '',
                $pin->headline_color ?? '#ffffff',
                $pin->subheadline_color ?? '#d4a574',
                $pin->overlay_color ?? '#000000',
                $pin->overlay_opacity ?? 70,
                $frameDesign
            );

            // Save the generated image
            $outputFilename = Str::uuid() . '.png';
            $outputDirectory = public_path('uploads/images/pinterest');
            
            if (!File::isDirectory($outputDirectory)) {
                File::makeDirectory($outputDirectory, 0755, true);
            }

            $outputPath = $outputDirectory . '/' . $outputFilename;
            imagepng($pinImage, $outputPath, 9);
            imagedestroy($pinImage);

            $relativePath = 'uploads/images/pinterest/' . $outputFilename;
            
            // Update the pin with the generated image path
            $pin->markAsGenerated($relativePath);

            Log::info('Pinterest pin image generated successfully', [
                'pin_id' => $pin->id,
                'path' => $relativePath,
                'frame_design' => $frameDesign
            ]);

            return $relativePath;

        } catch (\Exception $e) {
            Log::error('Failed to generate Pinterest pin image', [
                'pin_id' => $pin->id,
                'error' => $e->getMessage()
            ]);
            
            $pin->markAsFailed($e->getMessage());
            return null;
        }
    }

    /**
     * Create the Pinterest pin image based on frame design.
     */
    private function createPinImage(
        string $topImagePath,
        string $bottomImagePath,
        string $headlineText,
        string $subheadlineText,
        string $headlineColor,
        string $subheadlineColor,
        string $overlayColor,
        int $overlayOpacity,
        string $frameDesign = 'simple_center'
    ) {
        // Create the main canvas
        $canvas = imagecreatetruecolor(self::PIN_WIDTH, self::PIN_HEIGHT);
        
        // Enable alpha blending
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        // Fill with white background
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        // For now, use simple center frame (we'll add more step by step)
        $this->drawSimpleCenterFrame(
            $canvas, 
            $topImagePath, 
            $bottomImagePath, 
            $headlineText, 
            $subheadlineText, 
            $headlineColor, 
            $subheadlineColor,
            $overlayColor,
            $overlayOpacity
        );

        return $canvas;
    }

    /**
     * Frame: Simple Center Text with images top and bottom
     */
    private function drawSimpleCenterFrame(
        $canvas, 
        $topImagePath, 
        $bottomImagePath, 
        $headline, 
        $subheadline, 
        $headlineColor = '#8B4513', 
        $subheadlineColor = '#CD853F',
        $overlayColor = '#000000',
        $overlayOpacity = 70
    ): void {
        // Calculate layout proportions
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // Top image (40% of height)
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, (int)$topImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($topImage);
        }

        // Text overlay bar with semi-transparent background
        $overlayRgb = $this->hexToRgb($overlayColor);
        $alphaValue = (int) ((100 - $overlayOpacity) * 1.27);
        $overlayAlphaColor = imagecolorallocatealpha(
            $canvas,
            $overlayRgb['r'],
            $overlayRgb['g'],
            $overlayRgb['b'],
            $alphaValue
        );
        imagefilledrectangle($canvas, 0, (int)$textBarStartY, self::PIN_WIDTH, (int)($textBarStartY + self::TEXT_BAR_HEIGHT), $overlayAlphaColor);

        // Draw text
        $headlineRgb = $this->hexToRgb($headlineColor);
        $subheadlineRgb = $this->hexToRgb($subheadlineColor);
        $headlineTextColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $subheadlineTextColor = imagecolorallocate($canvas, $subheadlineRgb['r'], $subheadlineRgb['g'], $subheadlineRgb['b']);

        $fontPath = $this->getFontPath('sans-serif');
        $scriptFontPath = $this->getFontPath('script');
        $centerX = self::PIN_WIDTH / 2;
        $textCenterY = $textBarStartY + (self::TEXT_BAR_HEIGHT / 2);

        // Headline (bold, lowercase)
        if (!empty($headline)) {
            $this->drawCenteredText($canvas, strtolower($headline), $fontPath, 28, $centerX, $textCenterY - 15, $headlineTextColor);
        }

        // Subheadline (italic/script)
        if (!empty($subheadline)) {
            $this->drawCenteredText($canvas, $subheadline, $scriptFontPath ?? $fontPath, 22, $centerX, $textCenterY + 25, $subheadlineTextColor);
        }

        // Bottom image
        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }
    }

    /**
     * Helper: Draw centered text.
     */
    private function drawCenteredText($canvas, string $text, ?string $fontPath, int $fontSize, float $centerX, float $y, $color): void
    {
        if (empty($text)) return;

        if ($fontPath && file_exists($fontPath)) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            $textWidth = abs($bbox[2] - $bbox[0]);
            $textX = $centerX - ($textWidth / 2);
            imagettftext($canvas, $fontSize, 0, (int)$textX, (int)$y, $color, $fontPath, $text);
        } else {
            // Fallback to built-in font
            $font = min(5, max(1, (int)($fontSize / 8)));
            $textWidth = strlen($text) * imagefontwidth($font);
            $textX = $centerX - ($textWidth / 2);
            imagestring($canvas, $font, (int)$textX, (int)($y - imagefontheight($font) / 2), $text, $color);
        }
    }

    /**
     * Load an image from path.
     */
    private function loadImage(string $path)
    {
        if (!file_exists($path)) {
            return null;
        }

        $imageInfo = getimagesize($path);
        if (!$imageInfo) {
            return null;
        }

        $mimeType = $imageInfo['mime'];

        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            case 'image/webp':
                return imagecreatefromwebp($path);
            default:
                return null;
        }
    }

    /**
     * Place an image on the canvas with proper scaling and cropping.
     */
    private function placeImage($canvas, $sourceImage, int $destX, int $destY, int $destWidth, int $destHeight): void
    {
        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);

        // Calculate aspect ratios
        $srcAspect = $srcWidth / $srcHeight;
        $destAspect = $destWidth / $destHeight;

        // Crop and scale to fill the destination area
        if ($srcAspect > $destAspect) {
            // Source is wider - crop horizontally
            $newSrcHeight = $srcHeight;
            $newSrcWidth = $srcHeight * $destAspect;
            $srcX = ($srcWidth - $newSrcWidth) / 2;
            $srcY = 0;
        } else {
            // Source is taller - crop vertically
            $newSrcWidth = $srcWidth;
            $newSrcHeight = $srcWidth / $destAspect;
            $srcX = 0;
            $srcY = ($srcHeight - $newSrcHeight) / 2;
        }

        imagecopyresampled(
            $canvas,
            $sourceImage,
            $destX,
            $destY,
            (int)$srcX,
            (int)$srcY,
            $destWidth,
            (int)$destHeight,
            (int)$newSrcWidth,
            (int)$newSrcHeight
        );
    }

    /**
     * Resolve image path to absolute path.
     */
    private function resolveImagePath(?string $imagePath): ?string
    {
        if (empty($imagePath)) {
            return null;
        }

        // If it's already an absolute path
        if (file_exists($imagePath)) {
            return $imagePath;
        }

        // Try relative to public directory
        $publicPath = public_path($imagePath);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        // Try with leading slash removed
        $cleanPath = ltrim($imagePath, '/');
        $publicPath = public_path($cleanPath);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        // Try extracting from URL
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($imagePath);
            $path = $parsedUrl['path'] ?? '';
            $cleanPath = ltrim($path, '/');
            $publicPath = public_path($cleanPath);
            if (file_exists($publicPath)) {
                return $publicPath;
            }
        }

        return null;
    }

    /**
     * Get font path based on font family.
     */
    private function getFontPath(string $fontFamily): ?string
    {
        $fontsDir = resource_path('fonts');
        
        // Create fonts directory if it doesn't exist
        if (!File::isDirectory($fontsDir)) {
            File::makeDirectory($fontsDir, 0755, true);
        }

        switch ($fontFamily) {
            case 'sans-serif':
                // Try common sans-serif fonts
                $fonts = [
                    $fontsDir . '/OpenSans-Bold.ttf',
                    $fontsDir . '/Roboto-Bold.ttf',
                    $fontsDir . '/Arial-Bold.ttf',
                    'C:/Windows/Fonts/arial.ttf',
                    'C:/Windows/Fonts/arialbd.ttf',
                    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                    '/System/Library/Fonts/Helvetica.ttc',
                ];
                break;
            case 'script':
                // Try script/cursive fonts
                $fonts = [
                    $fontsDir . '/GreatVibes-Regular.ttf',
                    $fontsDir . '/DancingScript-Bold.ttf',
                    $fontsDir . '/Pacifico-Regular.ttf',
                    'C:/Windows/Fonts/segoepr.ttf', // Segoe Print
                    'C:/Windows/Fonts/comic.ttf',
                    '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Italic.ttf',
                    '/System/Library/Fonts/Apple Chancery.ttc',
                ];
                break;
            default:
                return null;
        }

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        return null;
    }

    /**
     * Convert hex color to RGB array.
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Create a Pinterest pin from an article.
     */
    public static function createFromArticle(Article $article, ?string $headlineOverride = null, ?string $subheadlineOverride = null, string $frameDesign = 'simple_center'): ?PinterestPin
    {
        // Get images from article
        $topImage = $article->featured_image;
        $bottomImage = $article->secondary_image ?? $article->featured_image;

        if (!$topImage) {
            Log::warning('Cannot create Pinterest pin - no featured image', ['article_id' => $article->id]);
            return null;
        }

        // Generate headline and subheadline from title
        $title = $article->title;
        $titleWords = explode(' ', $title);
        $midPoint = ceil(count($titleWords) / 2);
        
        $headline = $headlineOverride ?? implode(' ', array_slice($titleWords, 0, $midPoint));
        $subheadline = $subheadlineOverride ?? implode(' ', array_slice($titleWords, $midPoint));

        // Create the pin record
        $pin = PinterestPin::create([
            'website_id' => $article->website_id,
            'article_id' => $article->id,
            'user_id' => $article->user_id,
            'title' => $title,
            'description' => $article->meta_description ?? $article->excerpt,
            'link' => $article->url,
            'top_image' => $topImage,
            'bottom_image' => $bottomImage,
            'headline_text' => $headline,
            'subheadline_text' => $subheadline,
            'frame_design' => $frameDesign,
            'status' => 'pending',
        ]);

        // Generate the image
        $service = new self();
        $service->generatePinImage($pin);

        return $pin;
    }
}
