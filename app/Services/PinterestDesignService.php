<?php

namespace App\Services;

use App\Models\PinterestPin;
use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class PinterestDesignService
{
    // Pinterest recommended dimensions (2:3 ratio)
    const PIN_WIDTH = 1000;
    const PIN_HEIGHT = 1500;
    
    // Text overlay bar height
    const TEXT_BAR_HEIGHT = 220;

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
            $pinImage = $this->createPinImage(
                $topImagePath,
                $bottomImagePath,
                $pin->headline_text ?? '',
                $pin->subheadline_text ?? '',
                $pin->headline_color ?? '#ffffff',
                $pin->subheadline_color ?? '#d4a574',
                $pin->overlay_color ?? '#000000',
                $pin->overlay_opacity ?? 70
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
                'path' => $relativePath
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
     * Create the Pinterest pin image combining two images with text overlay.
     */
    private function createPinImage(
        string $topImagePath,
        string $bottomImagePath,
        string $headlineText,
        string $subheadlineText,
        string $headlineColor,
        string $subheadlineColor,
        string $overlayColor,
        int $overlayOpacity
    ) {
        // Create the main canvas
        $canvas = imagecreatetruecolor(self::PIN_WIDTH, self::PIN_HEIGHT);
        
        // Enable alpha blending
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        // Calculate image heights (each image takes half minus half the text bar)
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // Load and place top image
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, $topImageStartY, self::PIN_WIDTH, $imageHeight);
            imagedestroy($topImage);
        }

        // Load and place bottom image
        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, $bottomImageStartY, self::PIN_WIDTH, $imageHeight);
            imagedestroy($bottomImage);
        }

        // Draw the text overlay bar
        $this->drawTextOverlay(
            $canvas,
            $textBarStartY,
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
     * Draw the text overlay bar with styled text.
     */
    private function drawTextOverlay(
        $canvas,
        int $startY,
        string $headlineText,
        string $subheadlineText,
        string $headlineColor,
        string $subheadlineColor,
        string $overlayColor,
        int $overlayOpacity
    ): void {
        // Parse overlay color
        $overlayRgb = $this->hexToRgb($overlayColor);
        $alphaValue = (int) ((100 - $overlayOpacity) * 1.27); // Convert percentage to 0-127 range
        
        // Create semi-transparent overlay
        $overlayAlphaColor = imagecolorallocatealpha(
            $canvas,
            $overlayRgb['r'],
            $overlayRgb['g'],
            $overlayRgb['b'],
            $alphaValue
        );

        // Draw the overlay rectangle
        imagefilledrectangle(
            $canvas,
            0,
            $startY,
            self::PIN_WIDTH,
            $startY + self::TEXT_BAR_HEIGHT,
            $overlayAlphaColor
        );

        // Parse text colors
        $headlineRgb = $this->hexToRgb($headlineColor);
        $subheadlineRgb = $this->hexToRgb($subheadlineColor);

        $headlineTextColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $subheadlineTextColor = imagecolorallocate($canvas, $subheadlineRgb['r'], $subheadlineRgb['g'], $subheadlineRgb['b']);

        // Font paths - using system fonts or fallback to built-in
        $fontPath = $this->getFontPath('sans-serif');
        $scriptFontPath = $this->getFontPath('script');

        $centerX = self::PIN_WIDTH / 2;
        $textCenterY = $startY + (self::TEXT_BAR_HEIGHT / 2);

        // Draw headline text (lowercase, simple font)
        if (!empty($headlineText)) {
            $headlineFontSize = 42;
            $headlineText = strtolower($headlineText);
            
            if ($fontPath && file_exists($fontPath)) {
                $bbox = imagettfbbox($headlineFontSize, 0, $fontPath, $headlineText);
                $textWidth = abs($bbox[2] - $bbox[0]);
                $textX = $centerX - ($textWidth / 2);
                $textY = $textCenterY - 20;
                imagettftext($canvas, $headlineFontSize, 0, (int)$textX, (int)$textY, $headlineTextColor, $fontPath, $headlineText);
            } else {
                // Fallback to built-in font
                $textWidth = strlen($headlineText) * imagefontwidth(5);
                $textX = $centerX - ($textWidth / 2);
                imagestring($canvas, 5, (int)$textX, (int)($textCenterY - 30), $headlineText, $headlineTextColor);
            }
        }

        // Draw subheadline text (script font style)
        if (!empty($subheadlineText)) {
            $subheadlineFontSize = 52;
            
            if ($scriptFontPath && file_exists($scriptFontPath)) {
                $bbox = imagettfbbox($subheadlineFontSize, 0, $scriptFontPath, $subheadlineText);
                $textWidth = abs($bbox[2] - $bbox[0]);
                $textX = $centerX - ($textWidth / 2);
                $textY = $textCenterY + 50;
                imagettftext($canvas, $subheadlineFontSize, 0, (int)$textX, (int)$textY, $subheadlineTextColor, $scriptFontPath, $subheadlineText);
            } else {
                // Fallback to built-in font
                $textWidth = strlen($subheadlineText) * imagefontwidth(5);
                $textX = $centerX - ($textWidth / 2);
                imagestring($canvas, 5, (int)$textX, (int)($textCenterY + 20), $subheadlineText, $subheadlineTextColor);
            }
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
    public static function createFromArticle(Article $article, ?string $headlineOverride = null, ?string $subheadlineOverride = null): ?PinterestPin
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
            'status' => 'pending',
        ]);

        // Generate the image
        $service = new self();
        $service->generatePinImage($pin);

        return $pin;
    }
}
