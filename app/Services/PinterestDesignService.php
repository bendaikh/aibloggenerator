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
            'black_christmas' => [
                'name' => 'Black Christmas (Elegant)',
                'description' => 'Black background with white text and decorative lines',
                'preview_colors' => ['bg' => '#000000', 'primary' => '#ffffff', 'secondary' => '#ffffff'],
            ],
            'green_dashed' => [
                'name' => 'Green Dashed Border',
                'description' => 'Green background with dashed border effect',
                'preview_colors' => ['bg' => '#22c55e', 'primary' => '#ffffff', 'secondary' => '#166534'],
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

        // Draw frame based on selected design
        switch ($frameDesign) {
            case 'black_christmas':
                $this->drawBlackChristmasFrame(
                    $canvas, 
                    $topImagePath, 
                    $bottomImagePath, 
                    $headlineText, 
                    $subheadlineText
                );
                break;
            case 'green_dashed':
                $this->drawGreenDashedFrame(
                    $canvas, 
                    $topImagePath, 
                    $bottomImagePath, 
                    $headlineText, 
                    $subheadlineText
                );
                break;
            default:
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
                break;
        }

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
     * Frame: Black Christmas - Elegant black background with white text and decorative lines
     * Based on the provided design with "White christamas Mojitos" style
     */
    private function drawBlackChristmasFrame(
        $canvas, 
        $topImagePath, 
        $bottomImagePath, 
        $headline, 
        $subheadline
    ): void {
        // Calculate layout - images at top and bottom, text bar in middle
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // Fill background with black
        $black = imagecolorallocate($canvas, 0, 0, 0);
        imagefill($canvas, 0, 0, $black);

        // Top image
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, (int)$topImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($topImage);
        }

        // Black text bar
        imagefilledrectangle($canvas, 0, (int)$textBarStartY, self::PIN_WIDTH, (int)($textBarStartY + self::TEXT_BAR_HEIGHT), $black);

        // Draw decorative horizontal lines at top and bottom of text bar
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $lineThickness = 2;
        $linePadding = 8;
        
        // Top line
        imagesetthickness($canvas, $lineThickness);
        imageline($canvas, 0, (int)$textBarStartY + $linePadding, self::PIN_WIDTH, (int)$textBarStartY + $linePadding, $white);
        
        // Bottom line
        imageline($canvas, 0, (int)($textBarStartY + self::TEXT_BAR_HEIGHT - $linePadding), self::PIN_WIDTH, (int)($textBarStartY + self::TEXT_BAR_HEIGHT - $linePadding), $white);

        // Draw text - headline in bold white, subheadline in italic script white
        $fontPath = $this->getFontPath('sans-serif');
        $scriptFontPath = $this->getFontPath('script');
        $centerX = self::PIN_WIDTH / 2;
        $textCenterY = $textBarStartY + (self::TEXT_BAR_HEIGHT / 2);

        // Headline (bold, title case)
        if (!empty($headline)) {
            $this->drawCenteredText($canvas, ucwords(strtolower($headline)), $fontPath, 30, $centerX, $textCenterY - 10, $white);
        }

        // Subheadline (italic/script)
        if (!empty($subheadline)) {
            $this->drawCenteredText($canvas, $subheadline, $scriptFontPath ?? $fontPath, 24, $centerX, $textCenterY + 35, $white);
        }

        // Bottom image
        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }
    }

    /**
     * Frame: Green Dashed Border - Green background with dashed border effect
     * Based on the provided design with "chicken street tacod" style
     */
    private function drawGreenDashedFrame(
        $canvas, 
        $topImagePath, 
        $bottomImagePath, 
        $headline, 
        $subheadline
    ): void {
        // Calculate layout - images at top and bottom, text bar in middle
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // Fill background with white
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        // Top image
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, (int)$topImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($topImage);
        }

        // Green text bar - vibrant green like the design
        $green = imagecolorallocate($canvas, 34, 197, 94); // #22c55e - Tailwind green-500
        imagefilledrectangle($canvas, 0, (int)$textBarStartY, self::PIN_WIDTH, (int)($textBarStartY + self::TEXT_BAR_HEIGHT), $green);

        // Draw dashed border effect at top and bottom of green bar
        $darkGreen = imagecolorallocate($canvas, 21, 128, 61); // #15803d - Tailwind green-700
        $dashWidth = 12;
        $gapWidth = 8;
        $dashHeight = 4;
        $borderOffset = 6;

        // Top dashed line
        for ($x = 0; $x < self::PIN_WIDTH; $x += ($dashWidth + $gapWidth)) {
            imagefilledrectangle(
                $canvas, 
                $x, 
                (int)$textBarStartY + $borderOffset, 
                min($x + $dashWidth, self::PIN_WIDTH), 
                (int)$textBarStartY + $borderOffset + $dashHeight, 
                $white
            );
        }

        // Bottom dashed line
        for ($x = 0; $x < self::PIN_WIDTH; $x += ($dashWidth + $gapWidth)) {
            imagefilledrectangle(
                $canvas, 
                $x, 
                (int)($textBarStartY + self::TEXT_BAR_HEIGHT - $borderOffset - $dashHeight), 
                min($x + $dashWidth, self::PIN_WIDTH), 
                (int)($textBarStartY + self::TEXT_BAR_HEIGHT - $borderOffset), 
                $white
            );
        }

        // Draw text - headline in white, subheadline in dark green script
        $fontPath = $this->getFontPath('sans-serif');
        $scriptFontPath = $this->getFontPath('script');
        $centerX = self::PIN_WIDTH / 2;
        $textCenterY = $textBarStartY + (self::TEXT_BAR_HEIGHT / 2);

        // Headline (lowercase, white)
        if (!empty($headline)) {
            $this->drawCenteredText($canvas, strtolower($headline), $fontPath, 28, $centerX, $textCenterY - 10, $white);
        }

        // Subheadline (italic/script, dark green)
        if (!empty($subheadline)) {
            $this->drawCenteredText($canvas, $subheadline, $scriptFontPath ?? $fontPath, 22, $centerX, $textCenterY + 30, $darkGreen);
        }

        // Bottom image
        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }
    }

    /**
     * Helper: Draw centered text with proper sizing.
     */
    private function drawCenteredText($canvas, string $text, ?string $fontPath, int $fontSize, float $centerX, float $y, $color): void
    {
        if (empty($text)) return;

        if ($fontPath && file_exists($fontPath)) {
            // Use TTF font for proper text rendering
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            $textWidth = abs($bbox[2] - $bbox[0]);
            $textX = $centerX - ($textWidth / 2);
            imagettftext($canvas, $fontSize, 0, (int)$textX, (int)$y, $color, $fontPath, $text);
        } else {
            // Improved fallback - use larger built-in font and scale text properly
            // GD built-in fonts are small, so we need to draw the text using a workaround
            // Use maximum built-in font size and calculate positioning accordingly
            $font = 5; // Largest built-in font
            $charWidth = imagefontwidth($font);
            $charHeight = imagefontheight($font);
            
            // For better visibility, we'll draw text larger by repeating/scaling
            // Calculate scale factor based on desired font size
            $scaleFactor = max(1, $fontSize / 10); // Approximate scale
            
            // Create a temporary canvas with the text
            $textWidth = strlen($text) * $charWidth;
            $textHeight = $charHeight;
            
            // Create temp canvas for text
            $tempCanvas = imagecreatetruecolor($textWidth + 20, $textHeight + 10);
            $bgColor = imagecolorallocate($tempCanvas, 0, 0, 0);
            imagecolortransparent($tempCanvas, $bgColor);
            imagefill($tempCanvas, 0, 0, $bgColor);
            
            // Draw text on temp canvas
            $tempColor = imagecolorallocate($tempCanvas, 
                ($color >> 16) & 0xFF, 
                ($color >> 8) & 0xFF, 
                $color & 0xFF
            );
            imagestring($tempCanvas, $font, 10, 5, $text, $tempColor);
            
            // Scale up the text
            $newWidth = (int)($textWidth * $scaleFactor);
            $newHeight = (int)($textHeight * $scaleFactor);
            $destX = (int)($centerX - ($newWidth / 2));
            $destY = (int)($y - ($newHeight / 2));
            
            imagecopyresized($canvas, $tempCanvas, $destX, $destY, 0, 0, $newWidth, $newHeight, $textWidth + 20, $textHeight + 10);
            imagedestroy($tempCanvas);
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
                // Try common sans-serif fonts - expanded for various environments
                $fonts = [
                    // Application bundled fonts (highest priority)
                    $fontsDir . '/OpenSans-Bold.ttf',
                    $fontsDir . '/Roboto-Bold.ttf',
                    $fontsDir . '/Arial-Bold.ttf',
                    $fontsDir . '/DejaVuSans-Bold.ttf',
                    // Windows fonts
                    'C:/Windows/Fonts/arialbd.ttf',
                    'C:/Windows/Fonts/arial.ttf',
                    'C:/Windows/Fonts/calibrib.ttf',
                    'C:/Windows/Fonts/segoeui.ttf',
                    // Linux fonts (common locations)
                    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                    '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
                    '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                    '/usr/share/fonts/liberation/LiberationSans-Bold.ttf',
                    '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
                    '/usr/share/fonts/truetype/ubuntu/Ubuntu-Bold.ttf',
                    '/usr/share/fonts/google-noto/NotoSans-Bold.ttf',
                    '/usr/share/fonts/noto/NotoSans-Bold.ttf',
                    // macOS fonts
                    '/System/Library/Fonts/Helvetica.ttc',
                    '/Library/Fonts/Arial Bold.ttf',
                ];
                break;
            case 'script':
                // Try script/cursive fonts - expanded for various environments
                $fonts = [
                    // Application bundled fonts (highest priority)
                    $fontsDir . '/GreatVibes-Regular.ttf',
                    $fontsDir . '/DancingScript-Bold.ttf',
                    $fontsDir . '/Pacifico-Regular.ttf',
                    $fontsDir . '/DejaVuSerif-Italic.ttf',
                    // Windows fonts
                    'C:/Windows/Fonts/segoepr.ttf',
                    'C:/Windows/Fonts/segoesc.ttf',
                    'C:/Windows/Fonts/comic.ttf',
                    'C:/Windows/Fonts/georgia.ttf',
                    'C:/Windows/Fonts/times.ttf',
                    // Linux fonts (common locations)
                    '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Italic.ttf',
                    '/usr/share/fonts/dejavu/DejaVuSerif-Italic.ttf',
                    '/usr/share/fonts/truetype/liberation/LiberationSerif-Italic.ttf',
                    '/usr/share/fonts/liberation/LiberationSerif-Italic.ttf',
                    '/usr/share/fonts/truetype/freefont/FreeSerifItalic.ttf',
                    '/usr/share/fonts/truetype/ubuntu/Ubuntu-Italic.ttf',
                    '/usr/share/fonts/google-noto/NotoSerif-Italic.ttf',
                    // macOS fonts
                    '/System/Library/Fonts/Apple Chancery.ttc',
                    '/Library/Fonts/Georgia Italic.ttf',
                ];
                break;
            default:
                return null;
        }

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                Log::debug('Font found', ['family' => $fontFamily, 'path' => $font]);
                return $font;
            }
        }

        Log::warning('No font found for family', ['family' => $fontFamily, 'searched' => count($fonts) . ' locations']);
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
