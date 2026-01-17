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
    const TEXT_BAR_HEIGHT = 200;
    
    // Maximum text area (with padding from top/bottom of bar)
    const TEXT_PADDING = 20;

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
            $headlineFont = $pin->headline_font ?? 'sans-serif';
            $subheadlineFont = $pin->subheadline_font ?? 'script';
            
            // Log font information for debugging
            Log::info('Pinterest pin fonts requested', [
                'pin_id' => $pin->id,
                'headline_font' => $headlineFont,
                'subheadline_font' => $subheadlineFont,
                'headline_font_path' => $this->getFontPath($headlineFont),
                'subheadline_font_path' => $this->getFontPath($subheadlineFont),
            ]);
            
            $pinImage = $this->createPinImage(
                $topImagePath,
                $bottomImagePath,
                $pin->headline_text ?? '',
                $pin->subheadline_text ?? '',
                $pin->headline_color ?? '#ffffff',
                $pin->subheadline_color ?? '#d4a574',
                $pin->overlay_color ?? '#000000',
                $pin->overlay_opacity ?? 70,
                $frameDesign,
                $headlineFont,
                $subheadlineFont,
                $pin->headline_font_size ?? 28,
                $pin->subheadline_font_size ?? 22
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
        string $frameDesign = 'simple_center',
        string $headlineFont = 'sans-serif',
        string $subheadlineFont = 'script',
        int $headlineFontSize = 28,
        int $subheadlineFontSize = 22
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
                    $subheadlineText,
                    $headlineFont,
                    $subheadlineFont,
                    $headlineFontSize,
                    $subheadlineFontSize
                );
                break;
            case 'green_dashed':
                $this->drawGreenDashedFrame(
                    $canvas, 
                    $topImagePath, 
                    $bottomImagePath, 
                    $headlineText, 
                    $subheadlineText,
                    $headlineFont,
                    $subheadlineFont,
                    $headlineFontSize,
                    $subheadlineFontSize
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
                    $overlayOpacity,
                    $headlineFont,
                    $subheadlineFont,
                    $headlineFontSize,
                    $subheadlineFontSize
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
        $overlayOpacity = 70,
        $headlineFont = 'sans-serif',
        $subheadlineFont = 'script',
        int $headlineFontSize = 28,
        int $subheadlineFontSize = 22
    ): void {
        // Calculate layout proportions
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // Top image
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

        $fontPath = $this->getFontPath($headlineFont);
        $scriptFontPath = $this->getFontPath($subheadlineFont);
        $centerX = self::PIN_WIDTH / 2;
        
        // Available height for text (with padding)
        $availableHeight = self::TEXT_BAR_HEIGHT - (self::TEXT_PADDING * 2);
        $gap = 15; // Gap between headline and subheadline
        
        // Auto-scale fonts if text doesn't fit
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubheadlineSize = $subheadlineFontSize;
        
        // Calculate heights and scale down if needed
        $headlineH = $this->calculateTextHeight($headline, $fontPath, $scaledHeadlineSize);
        $subheadlineH = $this->calculateTextHeight($subheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
        $totalTextHeight = $headlineH + $subheadlineH + (!empty($headline) && !empty($subheadline) ? $gap : 0);
        
        // If text is too tall, scale down proportionally
        while ($totalTextHeight > $availableHeight && $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubheadlineSize = max(10, $scaledSubheadlineSize - 2);
            $headlineH = $this->calculateTextHeight($headline, $fontPath, $scaledHeadlineSize);
            $subheadlineH = $this->calculateTextHeight($subheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
            $totalTextHeight = $headlineH + $subheadlineH + (!empty($headline) && !empty($subheadline) ? $gap : 0);
        }
        
        // Start drawing from the vertically centered position
        $startY = $textBarStartY + self::TEXT_PADDING + ($availableHeight - $totalTextHeight) / 2;

        // Draw Headline first (at the top of the text block)
        if (!empty($headline)) {
            $actualHeight = $this->drawCenteredText($canvas, strtolower($headline), $fontPath, $scaledHeadlineSize, $centerX, $startY, $headlineTextColor);
            $startY += $actualHeight + $gap;
        }

        // Draw Subheadline below the headline
        if (!empty($subheadline)) {
            $this->drawCenteredText($canvas, $subheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize, $centerX, $startY, $subheadlineTextColor);
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
        $subheadline,
        $headlineFont = 'sans-serif',
        $subheadlineFont = 'script',
        int $headlineFontSize = 30,
        int $subheadlineFontSize = 24
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

        // Draw text
        $fontPath = $this->getFontPath($headlineFont);
        $scriptFontPath = $this->getFontPath($subheadlineFont);
        $centerX = self::PIN_WIDTH / 2;
        
        // Available height for text (with padding for decorative lines)
        $availableHeight = self::TEXT_BAR_HEIGHT - (self::TEXT_PADDING * 2);
        $gap = 15;
        
        // Auto-scale fonts if text doesn't fit
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubheadlineSize = $subheadlineFontSize;
        
        $headlineH = $this->calculateTextHeight($headline, $fontPath, $scaledHeadlineSize);
        $subheadlineH = $this->calculateTextHeight($subheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
        $totalTextHeight = $headlineH + $subheadlineH + (!empty($headline) && !empty($subheadline) ? $gap : 0);
        
        while ($totalTextHeight > $availableHeight && $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubheadlineSize = max(10, $scaledSubheadlineSize - 2);
            $headlineH = $this->calculateTextHeight($headline, $fontPath, $scaledHeadlineSize);
            $subheadlineH = $this->calculateTextHeight($subheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
            $totalTextHeight = $headlineH + $subheadlineH + (!empty($headline) && !empty($subheadline) ? $gap : 0);
        }
        
        $startY = $textBarStartY + self::TEXT_PADDING + ($availableHeight - $totalTextHeight) / 2;

        // Headline (bold, title case)
        if (!empty($headline)) {
            $actualHeight = $this->drawCenteredText($canvas, ucwords(strtolower($headline)), $fontPath, $scaledHeadlineSize, $centerX, $startY, $white);
            $startY += $actualHeight + $gap;
        }

        // Subheadline (italic/script)
        if (!empty($subheadline)) {
            $this->drawCenteredText($canvas, $subheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize, $centerX, $startY, $white);
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
        $subheadline,
        $headlineFont = 'sans-serif',
        $subheadlineFont = 'script',
        $headlineFontSize = 28,
        $subheadlineFontSize = 22
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

        // Draw text
        $fontPath = $this->getFontPath($headlineFont);
        $scriptFontPath = $this->getFontPath($subheadlineFont);
        $centerX = self::PIN_WIDTH / 2;
        
        // Available height for text (with padding)
        $availableHeight = self::TEXT_BAR_HEIGHT - (self::TEXT_PADDING * 2);
        $gap = 15;
        
        // Auto-scale fonts if text doesn't fit
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubheadlineSize = $subheadlineFontSize;
        
        $headlineH = $this->calculateTextHeight($headline, $fontPath, $scaledHeadlineSize);
        $subheadlineH = $this->calculateTextHeight($subheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
        $totalTextHeight = $headlineH + $subheadlineH + (!empty($headline) && !empty($subheadline) ? $gap : 0);
        
        while ($totalTextHeight > $availableHeight && $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubheadlineSize = max(10, $scaledSubheadlineSize - 2);
            $headlineH = $this->calculateTextHeight($headline, $fontPath, $scaledHeadlineSize);
            $subheadlineH = $this->calculateTextHeight($subheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
            $totalTextHeight = $headlineH + $subheadlineH + (!empty($headline) && !empty($subheadline) ? $gap : 0);
        }
        
        $startY = $textBarStartY + self::TEXT_PADDING + ($availableHeight - $totalTextHeight) / 2;

        // Headline (lowercase, white)
        if (!empty($headline)) {
            $actualHeight = $this->drawCenteredText($canvas, strtolower($headline), $fontPath, $scaledHeadlineSize, $centerX, $startY, $white);
            $startY += $actualHeight + $gap;
        }

        // Subheadline (italic/script, dark green)
        if (!empty($subheadline)) {
            $this->drawCenteredText($canvas, $subheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize, $centerX, $startY, $darkGreen);
        }

        // Bottom image
        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }
    }

    /**
     * Helper: Draw centered text with word wrapping.
     * Returns the total height of the text block drawn.
     */
    private function drawCenteredText($canvas, string $text, ?string $fontPath, int $fontSize, float $centerX, float $startY, $color, int $maxWidth = 480): int
    {
        if (empty($text)) return 0;

        $lines = $this->wrapText($text, $fontPath, $fontSize, $maxWidth);
        $lineHeight = $fontSize * 1.5;
        $totalHeight = count($lines) * $lineHeight;

        if ($fontPath && file_exists($fontPath) && function_exists('imagettftext')) {
            foreach ($lines as $index => $line) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
                $textWidth = abs($bbox[2] - $bbox[0]);
                $textX = $centerX - ($textWidth / 2);
                
                // Draw line starting from startY, offset by line index
                $lineY = $startY + ($index * $lineHeight) + $fontSize;
                
                imagettftext($canvas, $fontSize, 0, (int)$textX, (int)$lineY, $color, $fontPath, $line);
            }
        } else {
            // Fallback for built-in GD fonts
            $font = 5;
            $charWidth = imagefontwidth($font);
            $charHeight = imagefontheight($font);
            $r = ($color >> 16) & 0xFF;
            $g = ($color >> 8) & 0xFF;
            $b = $color & 0xFF;
            $allocatedColor = imagecolorallocate($canvas, $r, $g, $b);

            foreach ($lines as $index => $line) {
                $textWidth = strlen($line) * $charWidth;
                $textX = $centerX - ($textWidth / 2);
                $lineY = $startY + ($index * $charHeight);
                imagestring($canvas, $font, (int)$textX, (int)$lineY, $line, $allocatedColor);
            }
            return count($lines) * $charHeight;
        }

        return (int)$totalHeight;
    }

    /**
     * Helper: Wrap text into lines based on max width.
     */
    private function wrapText(string $text, ?string $fontPath, int $fontSize, int $maxWidth): array
    {
        if (empty($text)) return [];
        
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        if ($fontPath && file_exists($fontPath) && function_exists('imagettfbbox')) {
            foreach ($words as $word) {
                $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $testLine);
                $width = abs($bbox[2] - $bbox[0]);

                if ($width <= $maxWidth) {
                    $currentLine = $testLine;
                } else {
                    if ($currentLine !== '') $lines[] = $currentLine;
                    $currentLine = $word;
                }
            }
        } else {
            // Simple char-based wrap for fallback
            $maxChars = max(1, (int)floor($maxWidth / 10));
            $wrapped = wordwrap($text, $maxChars, "\n");
            return explode("\n", $wrapped);
        }

        if ($currentLine !== '') $lines[] = $currentLine;
        return $lines;
    }

    /**
     * Helper: Calculate total height of wrapped text.
     */
    private function calculateTextHeight(string $text, ?string $fontPath, int $fontSize, int $maxWidth = 480): int
    {
        if (empty($text)) return 0;
        $lines = $this->wrapText($text, $fontPath, $fontSize, $maxWidth);
        
        if ($fontPath && file_exists($fontPath) && function_exists('imagettftext')) {
            return (int)(count($lines) * ($fontSize * 1.5));
        } else {
            return (int)(count($lines) * imagefontheight(5));
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

        // Windows fonts directory
        $winFonts = 'C:\\Windows\\Fonts';

        $fonts = [];

        switch ($fontFamily) {
            case 'arial':
                $fonts = [
                    $fontsDir . '/Arial-Bold.ttf',
                    $fontsDir . '/arialbd.ttf',
                    $winFonts . '\\arialbd.ttf',
                    $winFonts . '\\arial.ttf',
                    $winFonts . '\\ARIALBD.TTF',
                    $winFonts . '\\ARIAL.TTF',
                    '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                ];
                break;
            case 'georgia':
                $fonts = [
                    $fontsDir . '/Georgia.ttf',
                    $fontsDir . '/georgia.ttf',
                    $winFonts . '\\georgia.ttf',
                    $winFonts . '\\georgiab.ttf',
                    $winFonts . '\\GEORGIA.TTF',
                    $winFonts . '\\GEORGIAB.TTF',
                    '/usr/share/fonts/truetype/freefont/FreeSerif.ttf',
                ];
                break;
            case 'times':
                $fonts = [
                    $fontsDir . '/Times.ttf',
                    $fontsDir . '/times.ttf',
                    $winFonts . '\\times.ttf',
                    $winFonts . '\\timesbd.ttf',
                    $winFonts . '\\TIMES.TTF',
                    $winFonts . '\\TIMESBD.TTF',
                    '/usr/share/fonts/truetype/liberation/LiberationSerif-Regular.ttf',
                ];
                break;
            case 'roboto':
                $fonts = [
                    $fontsDir . '/Roboto-Bold.ttf',
                    $fontsDir . '/Roboto-Regular.ttf',
                    $winFonts . '\\arialbd.ttf', // Fallback to Arial on Windows
                ];
                break;
            case 'open-sans':
                $fonts = [
                    $fontsDir . '/OpenSans-Bold.ttf',
                    $fontsDir . '/OpenSans-Regular.ttf',
                    $winFonts . '\\arialbd.ttf', // Fallback to Arial on Windows
                ];
                break;
            case 'dancing-script':
                $fonts = [
                    $fontsDir . '/DancingScript-Bold.ttf',
                    $fontsDir . '/DancingScript-Regular.ttf',
                    $winFonts . '\\georgia.ttf', // Fallback to Georgia on Windows
                ];
                break;
            case 'pacifico':
                $fonts = [
                    $fontsDir . '/Pacifico-Regular.ttf',
                    $winFonts . '\\georgia.ttf', // Fallback to Georgia on Windows
                ];
                break;
            case 'sans-serif':
                $fonts = [
                    $fontsDir . '/OpenSans-Bold.ttf',
                    $fontsDir . '/Roboto-Bold.ttf',
                    $fontsDir . '/Arial-Bold.ttf',
                    $fontsDir . '/DejaVuSans-Bold.ttf',
                    $winFonts . '\\arialbd.ttf',
                    $winFonts . '\\arial.ttf',
                    $winFonts . '\\segoeuib.ttf',
                    $winFonts . '\\segoeui.ttf',
                    $winFonts . '\\ARIALBD.TTF',
                    $winFonts . '\\ARIAL.TTF',
                    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                    '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                ];
                break;
            case 'script':
                $fonts = [
                    $fontsDir . '/GreatVibes-Regular.ttf',
                    $fontsDir . '/DancingScript-Bold.ttf',
                    $fontsDir . '/Pacifico-Regular.ttf',
                    $winFonts . '\\georgia.ttf',
                    $winFonts . '\\georgiai.ttf',
                    $winFonts . '\\times.ttf',
                    $winFonts . '\\timesi.ttf',
                    $winFonts . '\\GEORGIA.TTF',
                    $winFonts . '\\GEORGIAI.TTF',
                    '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Italic.ttf',
                ];
                break;
            default:
                // Unknown font, try to find ANY available font
                $fonts = [];
        }

        // Try to find the requested font
        foreach ($fonts as $font) {
            if (file_exists($font)) {
                Log::debug('Font found', ['family' => $fontFamily, 'path' => $font]);
                return $font;
            }
        }

        // Fallback: try common Windows fonts if specific font not found
        $fallbackFonts = [
            $winFonts . '\\arialbd.ttf',
            $winFonts . '\\arial.ttf',
            $winFonts . '\\georgia.ttf',
            $winFonts . '\\times.ttf',
            $winFonts . '\\ARIALBD.TTF',
            $winFonts . '\\ARIAL.TTF',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];

        foreach ($fallbackFonts as $font) {
            if (file_exists($font)) {
                Log::warning('Using fallback font', ['requested' => $fontFamily, 'using' => $font]);
                return $font;
            }
        }

        Log::error('No font found at all', ['family' => $fontFamily]);
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
