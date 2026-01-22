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
            'ribbon_banner' => [
                'name' => 'Ribbon Banner',
                'description' => 'Elegant cream banner with a brown ribbon',
                'preview_colors' => ['bg' => '#fef9e7', 'primary' => '#8B4513', 'secondary' => '#ffffff'],
            ],
            'star_rating' => [
                'name' => 'Star Rating',
                'description' => 'Orange banner with star rating and capsule labels',
                'preview_colors' => ['bg' => '#e6a23c', 'primary' => '#1a1a1a', 'secondary' => '#ffffff'],
            ],
            'minimal_bold' => [
                'name' => 'Minimal Bold',
                'description' => 'White background with thick black borders and domain bar',
                'preview_colors' => ['bg' => '#ffffff', 'primary' => '#000000', 'secondary' => '#000000'],
            ],
            'crispy_orange' => [
                'name' => 'Crispy Orange',
                'description' => 'Orange banner with bright yellow accent lines',
                'preview_colors' => ['bg' => '#e67e22', 'primary' => '#ffffff', 'secondary' => '#f1c40f'],
            ],
            'torn_paper' => [
                'name' => 'Torn Paper',
                'description' => 'White background with jagged torn paper effect',
                'preview_colors' => ['bg' => '#ffffff', 'primary' => '#000000', 'secondary' => '#000000'],
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
                $pin->subheadline_font_size ?? 22,
                $pin->frame_settings['domain_name'] ?? ($pin->website?->domain ?? ($pin->website?->slug ? $pin->website->slug . '.com' : ''))
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
        int $subheadlineFontSize = 22,
        string $domainName = ''
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
            case 'green_dashed':
                $this->drawGreenDashedFrame(
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
            case 'ribbon_banner':
                $this->drawRibbonBannerFrame(
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
                    $subheadlineFontSize,
                    $domainName
                );
                break;
            case 'star_rating':
                $this->drawStarRatingFrame(
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
                    $subheadlineFontSize,
                    $domainName
                );
                break;
            case 'minimal_bold':
                $this->drawMinimalBoldFrame(
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
                    $subheadlineFontSize,
                    $domainName
                );
                break;
            case 'crispy_orange':
                $this->drawCrispyOrangeFrame(
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
            case 'torn_paper':
                $this->drawTornPaperFrame(
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
        
        // Use the same text transformations as drawing
        $transformedHeadline = strtolower($headline);
        $transformedSubheadline = $subheadline;
        
        // Calculate heights and scale down if needed
        $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
        $subheadlineH = $this->calculateTextHeight($transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
        $totalTextHeight = $headlineH + $subheadlineH + (!empty($transformedHeadline) && !empty($transformedSubheadline) ? $gap : 0);
        
        // If text is too tall or any line too wide, scale down proportionally
        while (($totalTextHeight > $availableHeight || 
               $this->isTextTooWide($transformedHeadline, $fontPath, $scaledHeadlineSize, 480) || 
               $this->isTextTooWide($transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize, 480)) && 
               $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubheadlineSize = max(10, $scaledSubheadlineSize - 2);
            $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
            $subheadlineH = $this->calculateTextHeight($transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
            $totalTextHeight = $headlineH + $subheadlineH + (!empty($transformedHeadline) && !empty($transformedSubheadline) ? $gap : 0);
        }
        
        // Start drawing from the vertically centered position
        $startY = $textBarStartY + self::TEXT_PADDING + ($availableHeight - $totalTextHeight) / 2;

        // Draw Headline first (at the top of the text block)
        if (!empty($transformedHeadline)) {
            $actualHeight = $this->drawCenteredText($canvas, $transformedHeadline, $fontPath, $scaledHeadlineSize, $centerX, $startY, $headlineTextColor);
            $startY += $actualHeight + $gap;
        }

        // Draw Subheadline below the headline
        if (!empty($transformedSubheadline)) {
            $this->drawCenteredText($canvas, $transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize, $centerX, $startY, $subheadlineTextColor);
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
        $headlineColor = '#ffffff',
        $subheadlineColor = '#ffffff',
        $overlayColor = '#000000',
        $overlayOpacity = 100,
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

        // Fill background with white (canvas default)
        $white = imagecolorallocate($canvas, 255, 255, 255);
        
        // Top image
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, (int)$topImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($topImage);
        }

        // Text bar background (overlay color)
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

        // Draw decorative horizontal lines at top and bottom of text bar
        // Use headline color for lines to match the text
        $headlineRgb = $this->hexToRgb($headlineColor);
        $lineColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $lineThickness = 2;
        $linePadding = 8;
        
        // Top line
        imagesetthickness($canvas, $lineThickness);
        imageline($canvas, 0, (int)$textBarStartY + $linePadding, self::PIN_WIDTH, (int)$textBarStartY + $linePadding, $lineColor);
        
        // Bottom line
        imageline($canvas, 0, (int)($textBarStartY + self::TEXT_BAR_HEIGHT - $linePadding), self::PIN_WIDTH, (int)($textBarStartY + self::TEXT_BAR_HEIGHT - $linePadding), $lineColor);

        // Draw text
        $subheadlineRgb = $this->hexToRgb($subheadlineColor);
        $headlineTextColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $subheadlineTextColor = imagecolorallocate($canvas, $subheadlineRgb['r'], $subheadlineRgb['g'], $subheadlineRgb['b']);

        $fontPath = $this->getFontPath($headlineFont);
        $scriptFontPath = $this->getFontPath($subheadlineFont);
        $centerX = self::PIN_WIDTH / 2;
        
        // Available height for text (with padding for decorative lines)
        $availableHeight = self::TEXT_BAR_HEIGHT - (self::TEXT_PADDING * 2);
        $gap = 15;
        
        // Auto-scale fonts if text doesn't fit
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubheadlineSize = $subheadlineFontSize;
        
        // Use the same text transformations as drawing
        $transformedHeadline = ucwords(strtolower($headline));
        $transformedSubheadline = $subheadline;
        
        $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
        $subheadlineH = $this->calculateTextHeight($transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
        $totalTextHeight = $headlineH + $subheadlineH + (!empty($transformedHeadline) && !empty($transformedSubheadline) ? $gap : 0);
        
        while (($totalTextHeight > $availableHeight || 
               $this->isTextTooWide($transformedHeadline, $fontPath, $scaledHeadlineSize, 480) || 
               $this->isTextTooWide($transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize, 480)) && 
               $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubheadlineSize = max(10, $scaledSubheadlineSize - 2);
            $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
            $subheadlineH = $this->calculateTextHeight($transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
            $totalTextHeight = $headlineH + $subheadlineH + (!empty($transformedHeadline) && !empty($transformedSubheadline) ? $gap : 0);
        }
        
        $startY = $textBarStartY + self::TEXT_PADDING + ($availableHeight - $totalTextHeight) / 2;

        // Headline (bold, title case)
        if (!empty($transformedHeadline)) {
            $actualHeight = $this->drawCenteredText($canvas, $transformedHeadline, $fontPath, $scaledHeadlineSize, $centerX, $startY, $headlineTextColor);
            $startY += $actualHeight + $gap;
        }

        // Subheadline (italic/script)
        if (!empty($transformedSubheadline)) {
            $this->drawCenteredText($canvas, $transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize, $centerX, $startY, $subheadlineTextColor);
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
        $headlineColor = '#ffffff',
        $subheadlineColor = '#166534',
        $overlayColor = '#22c55e',
        $overlayOpacity = 100,
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

        // Text bar background (overlay color)
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

        // Draw dashed border effect at top and bottom of green bar
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
        $headlineRgb = $this->hexToRgb($headlineColor);
        $subheadlineRgb = $this->hexToRgb($subheadlineColor);
        $headlineTextColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $subheadlineTextColor = imagecolorallocate($canvas, $subheadlineRgb['r'], $subheadlineRgb['g'], $subheadlineRgb['b']);

        $fontPath = $this->getFontPath($headlineFont);
        $scriptFontPath = $this->getFontPath($subheadlineFont);
        $centerX = self::PIN_WIDTH / 2;
        
        // Available height for text (with padding)
        $availableHeight = self::TEXT_BAR_HEIGHT - (self::TEXT_PADDING * 2);
        $gap = 15;
        
        // Auto-scale fonts if text doesn't fit
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubheadlineSize = $subheadlineFontSize;
        
        // Use the same text transformations as drawing
        $transformedHeadline = strtolower($headline);
        $transformedSubheadline = $subheadline;
        
        $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
        $subheadlineH = $this->calculateTextHeight($transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
        $totalTextHeight = $headlineH + $subheadlineH + (!empty($transformedHeadline) && !empty($transformedSubheadline) ? $gap : 0);
        
        while (($totalTextHeight > $availableHeight || 
               $this->isTextTooWide($transformedHeadline, $fontPath, $scaledHeadlineSize, 480) || 
               $this->isTextTooWide($transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize, 480)) && 
               $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubheadlineSize = max(10, $scaledSubheadlineSize - 2);
            $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
            $subheadlineH = $this->calculateTextHeight($transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize);
            $totalTextHeight = $headlineH + $subheadlineH + (!empty($transformedHeadline) && !empty($transformedSubheadline) ? $gap : 0);
        }
        
        $startY = $textBarStartY + self::TEXT_PADDING + ($availableHeight - $totalTextHeight) / 2;

        // Headline (lowercase)
        if (!empty($transformedHeadline)) {
            $actualHeight = $this->drawCenteredText($canvas, $transformedHeadline, $fontPath, $scaledHeadlineSize, $centerX, $startY, $headlineTextColor);
            $startY += $actualHeight + $gap;
        }

        // Subheadline (italic/script)
        if (!empty($transformedSubheadline)) {
            $this->drawCenteredText($canvas, $transformedSubheadline, $scriptFontPath ?? $fontPath, $scaledSubheadlineSize, $centerX, $startY, $subheadlineTextColor);
        }

        // Bottom image
        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }
    }

    /**
     * Frame: Ribbon Banner - Elegant cream banner with a brown ribbon
     * Based on the provided design with "BISCOFF COOKIE BUTTER" style
     */
    private function drawRibbonBannerFrame(
        $canvas, 
        $topImagePath, 
        $bottomImagePath, 
        $headline, 
        $subheadline,
        $headlineColor = '#8B4513',
        $subheadlineColor = '#8B4513',
        $overlayColor = '#fef9e7',
        $overlayOpacity = 100,
        $headlineFont = 'sans-serif',
        $subheadlineFont = 'script',
        int $headlineFontSize = 28,
        int $subheadlineFontSize = 22,
        string $domainName = ''
    ): void {
        // Calculate layout
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // 1. Place Images (Background)
        // Top image
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, (int)$topImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($topImage);
        }

        // Bottom image
        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }

        // 2. Draw the background for the text area (overlay color)
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

        // 2. Draw the thick bar at the top of the text area (using headline color)
        $headlineRgb = $this->hexToRgb($headlineColor);
        $primaryColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $barHeight = 25;
        imagefilledrectangle($canvas, 0, (int)$textBarStartY, self::PIN_WIDTH, (int)$textBarStartY + $barHeight, $primaryColor);

        // 3. Ribbon setup
        $ribbonHeight = 45;
        $ribbonY = $textBarStartY + self::TEXT_BAR_HEIGHT - $ribbonHeight - 20;
        $ribbonMargin = 40;
        $notchDepth = 15;

        // Draw text
        $subheadlineRgb = $this->hexToRgb($subheadlineColor);
        $headlineTextColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $subheadlineTextColor = imagecolorallocate($canvas, $subheadlineRgb['r'], $subheadlineRgb['g'], $subheadlineRgb['b']);

        $fontPath = $this->getFontPath($headlineFont);
        $scriptFontPath = $this->getFontPath($subheadlineFont);
        $centerX = self::PIN_WIDTH / 2;
        
        // Available height for text (main area, excluding bar and ribbon)
        $availableHeight = self::TEXT_BAR_HEIGHT - $barHeight - $ribbonHeight - 40;
        $gap = 10;
        
        // Auto-scale headline and subheadline
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubheadlineSize = $subheadlineFontSize;
        $transformedHeadline = strtoupper($headline);
        $transformedSubheadline = strtoupper($subheadline);
        
        $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
        $subheadlineH = $this->calculateTextHeight($transformedSubheadline, $fontPath, $scaledSubheadlineSize);
        $totalTextHeight = $headlineH + $subheadlineH + (!empty($transformedHeadline) && !empty($transformedSubheadline) ? $gap : 0);
        
        while (($totalTextHeight > $availableHeight || 
               $this->isTextTooWide($transformedHeadline, $fontPath, $scaledHeadlineSize, 460) ||
               $this->isTextTooWide($transformedSubheadline, $fontPath, $scaledSubheadlineSize, 460)) && 
               $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubheadlineSize = max(10, $scaledSubheadlineSize - 2);
            $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
            $subheadlineH = $this->calculateTextHeight($transformedSubheadline, $fontPath, $scaledSubheadlineSize);
            $totalTextHeight = $headlineH + $subheadlineH + (!empty($transformedHeadline) && !empty($transformedSubheadline) ? $gap : 0);
        }
        
        // Vertically center the text block in the available main area
        $mainAreaStartY = $textBarStartY + $barHeight + 10;
        $startY = $mainAreaStartY + ($availableHeight - $totalTextHeight) / 2;

        // Draw Headline (Bold, Uppercase)
        if (!empty($transformedHeadline)) {
            $actualHeight = $this->drawCenteredText($canvas, $transformedHeadline, $fontPath, $scaledHeadlineSize, $centerX, $startY, $headlineTextColor);
            $startY += $actualHeight + $gap;
        }

        // Draw Subheadline (Below Headline)
        if (!empty($transformedSubheadline)) {
            $this->drawCenteredText($canvas, $transformedSubheadline, $fontPath, $scaledSubheadlineSize, $centerX, $startY, $subheadlineTextColor);
        }

        // 4. Draw the ribbon (polygon points for notched ribbon)
        $points = [
            $ribbonMargin, (int)$ribbonY,
            self::PIN_WIDTH - $ribbonMargin, (int)$ribbonY,
            self::PIN_WIDTH - $ribbonMargin - $notchDepth, (int)$ribbonY + ($ribbonHeight / 2),
            self::PIN_WIDTH - $ribbonMargin, (int)$ribbonY + $ribbonHeight,
            $ribbonMargin, (int)$ribbonY + $ribbonHeight,
            $ribbonMargin + $notchDepth, (int)$ribbonY + ($ribbonHeight / 2),
        ];
        imagefilledpolygon($canvas, $points, 6, $primaryColor);

        // Draw Domain Name (inside the ribbon)
        $displayDomain = $domainName ?: 'WWW.YOURDOMAIN.COM';
        $transformedDomain = strtoupper($displayDomain);
        $domainFontSize = 18;
        $white = imagecolorallocate($canvas, 255, 255, 255);
        
        // Auto-scale domain to fit ribbon
        while (($this->calculateTextHeight($transformedDomain, $fontPath, $domainFontSize) > ($ribbonHeight - 10) || 
               $this->isTextTooWide($transformedDomain, $fontPath, $domainFontSize, self::PIN_WIDTH - ($ribbonMargin * 2) - ($notchDepth * 2) - 20)) && 
               $domainFontSize > 10) {
            $domainFontSize -= 1;
        }
        
        // Center in ribbon
        $domainH = $this->calculateTextHeight($transformedDomain, $fontPath, $domainFontSize);
        $ribbonTextY = $ribbonY + ($ribbonHeight - $domainH) / 2 - 2; 
        $this->drawCenteredText($canvas, $transformedDomain, $fontPath, (int)$domainFontSize, $centerX, $ribbonTextY, $white, 400);
    }

    /**
     * Frame: Star Rating - Orange background with star rating and capsule labels
     * Based on the provided design with yellowish bar and black capsules
     */
    private function drawStarRatingFrame(
        $canvas, 
        $topImagePath, 
        $bottomImagePath, 
        $headline, 
        $subheadline,
        $headlineColor = '#16120b',
        $subheadlineColor = '#16120b',
        $overlayColor = '#eba13e',
        $overlayOpacity = 100,
        $headlineFont = 'sans-serif',
        $subheadlineFont = 'script',
        int $headlineFontSize = 28,
        int $subheadlineFontSize = 22,
        string $domainName = ''
    ): void {
        // Calculate layout
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // Colors
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $starColor = imagecolorallocate($canvas, 227, 201, 172); // #e3c9ac - light beige star

        // Top image
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, (int)$topImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($topImage);
        }

        // Bottom image
        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }

        // 1. Draw the background for the text area (overlay color)
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

        // 2. Draw the top capsule with stars (using headline color for background)
        $headlineRgb = $this->hexToRgb($headlineColor);
        $darkCapsule = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        
        $capsuleWidth = 140;
        $capsuleHeight = 35;
        $capsuleX = (self::PIN_WIDTH - $capsuleWidth) / 2;
        $capsuleY = $textBarStartY - ($capsuleHeight / 2);

        // Draw rounded capsule (rectangle + circles)
        imagefilledrectangle($canvas, (int)($capsuleX + $capsuleHeight/2), (int)$capsuleY, (int)($capsuleX + $capsuleWidth - $capsuleHeight/2), (int)($capsuleY + $capsuleHeight), $darkCapsule);
        imagefilledellipse($canvas, (int)($capsuleX + $capsuleHeight/2), (int)($capsuleY + $capsuleHeight/2), (int)$capsuleHeight, (int)$capsuleHeight, $darkCapsule);
        imagefilledellipse($canvas, (int)($capsuleX + $capsuleWidth - $capsuleHeight/2), (int)($capsuleY + $capsuleHeight/2), (int)$capsuleHeight, (int)$capsuleHeight, $darkCapsule);

        // Draw 5 stars inside top capsule
        $starCount = 5;
        $starSize = 12;
        $starGap = 5;
        $totalStarsWidth = ($starSize * $starCount) + ($starGap * ($starCount - 1));
        $currentStarX = self::PIN_WIDTH / 2 - $totalStarsWidth / 2 + $starSize / 2;
        $starY = $capsuleY + $capsuleHeight / 2;

        for ($i = 0; $i < $starCount; $i++) {
            $this->drawStar($canvas, (float)$currentStarX, (float)$starY, (int)($starSize/2), (int)($starSize/4), $starColor);
            $currentStarX += $starSize + $starGap;
        }

        // 3. Draw the bottom capsule for domain name
        $bottomCapsuleWidth = 200;
        $bottomCapsuleHeight = 40;
        $bottomCapsuleX = (self::PIN_WIDTH - $bottomCapsuleWidth) / 2;
        $bottomCapsuleY = $bottomImageStartY - ($bottomCapsuleHeight / 2);

        imagefilledrectangle($canvas, (int)($bottomCapsuleX + $bottomCapsuleHeight/2), (int)$bottomCapsuleY, (int)($bottomCapsuleX + $bottomCapsuleWidth - $bottomCapsuleHeight/2), (int)($bottomCapsuleY + $bottomCapsuleHeight), $darkCapsule);
        imagefilledellipse($canvas, (int)($bottomCapsuleX + $bottomCapsuleHeight/2), (int)($bottomCapsuleY + $bottomCapsuleHeight/2), (int)$bottomCapsuleHeight, (int)$bottomCapsuleHeight, $darkCapsule);
        imagefilledellipse($canvas, (int)($bottomCapsuleX + $bottomCapsuleWidth - $bottomCapsuleHeight/2), (int)($bottomCapsuleY + $bottomCapsuleHeight/2), (int)$bottomCapsuleHeight, (int)$bottomCapsuleHeight, $darkCapsule);

        // Draw Domain Name in bottom capsule
        $displayDomain = strtoupper($domainName ?: 'WWW.HADIK.COM');
        $fontPath = $this->getFontPath($headlineFont);
        $domainSize = 14;
        
        while ($this->isTextTooWide($displayDomain, $fontPath, $domainSize, $bottomCapsuleWidth - 40) && $domainSize > 8) {
            $domainSize--;
        }
        
        $domainH = $this->calculateTextHeight($displayDomain, $fontPath, $domainSize);
        $domainTextY = $bottomCapsuleY + ($bottomCapsuleHeight - $domainH) / 2 - 2;
        $this->drawCenteredText($canvas, $displayDomain, $fontPath, $domainSize, self::PIN_WIDTH / 2, $domainTextY, $white);

        // 4. Draw Headline and Subheadline
        $subheadlineRgb = $this->hexToRgb($subheadlineColor);
        $headlineTextColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $subheadlineTextColor = imagecolorallocate($canvas, $subheadlineRgb['r'], $subheadlineRgb['g'], $subheadlineRgb['b']);

        $availableHeight = self::TEXT_BAR_HEIGHT - ($capsuleHeight / 2) - ($bottomCapsuleHeight / 2) - 40;
        $mainAreaStartY = $textBarStartY + ($capsuleHeight / 2) + 20;
        
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubSize = $subheadlineFontSize;
        $gap = 10;
        
        $transformedHeadline = strtoupper($headline);
        $transformedSub = strtoupper($subheadline);
        
        $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
        $subH = $this->calculateTextHeight($transformedSub, $fontPath, $scaledSubSize);
        $totalH = $headlineH + $subH + ($headline && $subheadline ? $gap : 0);
        
        while (($totalH > $availableHeight || 
               $this->isTextTooWide($transformedHeadline, $fontPath, $scaledHeadlineSize, 460) ||
               $this->isTextTooWide($transformedSub, $fontPath, $scaledSubSize, 460)) && 
               $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubSize = max(10, $scaledSubSize - 2);
            $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
            $subH = $this->calculateTextHeight($transformedSub, $fontPath, $scaledSubSize);
            $totalH = $headlineH + $subH + ($headline && $subheadline ? $gap : 0);
        }
        
        $startY = $mainAreaStartY + ($availableHeight - $totalH) / 2;
        
        if (!empty($transformedHeadline)) {
            $h = $this->drawCenteredText($canvas, $transformedHeadline, $fontPath, $scaledHeadlineSize, self::PIN_WIDTH / 2, $startY, $headlineTextColor);
            $startY += $h + $gap;
        }
        
        if (!empty($transformedSub)) {
            $this->drawCenteredText($canvas, $transformedSub, $fontPath, $scaledSubSize, self::PIN_WIDTH / 2, $startY, $subheadlineTextColor);
        }
    }

    /**
     * Frame: Minimal Bold - White background with thick black borders and domain bar
     * Based on the provided design with "yesy folder this" style
     */
    private function drawMinimalBoldFrame(
        $canvas, 
        $topImagePath, 
        $bottomImagePath, 
        $headline, 
        $subheadline,
        $headlineColor = '#000000',
        $subheadlineColor = '#000000',
        $overlayColor = '#ffffff',
        $overlayOpacity = 100,
        $headlineFont = 'sans-serif',
        $subheadlineFont = 'script',
        int $headlineFontSize = 28,
        int $subheadlineFontSize = 22,
        string $domainName = ''
    ): void {
        // Calculate layout
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // Colors
        $white = imagecolorallocate($canvas, 255, 255, 255);

        // Top image
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, (int)$topImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($topImage);
        }

        // Bottom image
        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }

        // 1. Draw background for text bar area (overlay color)
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

        // 2. Draw thick horizontal lines at top and bottom (using headline color)
        $headlineRgb = $this->hexToRgb($headlineColor);
        $primaryColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $lineThickness = 6;
        imagefilledrectangle($canvas, 0, (int)$textBarStartY, self::PIN_WIDTH, (int)($textBarStartY + $lineThickness), $primaryColor);
        imagefilledrectangle($canvas, 0, (int)($bottomImageStartY - $lineThickness), self::PIN_WIDTH, (int)$bottomImageStartY, $primaryColor);

        // Draw text
        $subheadlineRgb = $this->hexToRgb($subheadlineColor);
        $headlineTextColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $subheadlineTextColor = imagecolorallocate($canvas, $subheadlineRgb['r'], $subheadlineRgb['g'], $subheadlineRgb['b']);

        $fontPath = $this->getFontPath($headlineFont);
        $centerX = self::PIN_WIDTH / 2;
        
        // Transform text: Headline lowercase, bold
        $transformedHeadline = strtolower($headline);
        $transformedSub = strtolower($subheadline);
        
        // Available space
        $availableHeight = self::TEXT_BAR_HEIGHT - ($lineThickness * 2) - 40;
        $mainAreaStartY = $textBarStartY + $lineThickness + 20;
        
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubSize = $subheadlineFontSize;
        $gap = 10;
        
        $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
        $subH = $this->calculateTextHeight($transformedSub, $fontPath, $scaledSubSize);
        $totalH = $headlineH + $subH + ($headline && $subheadline ? $gap : 0);
        
        while (($totalH > $availableHeight || 
               $this->isTextTooWide($transformedHeadline, $fontPath, $scaledHeadlineSize, 460) ||
               $this->isTextTooWide($transformedSub, $fontPath, $scaledSubSize, 460)) && 
               $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubSize = max(10, $scaledSubSize - 2);
            $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
            $subH = $this->calculateTextHeight($transformedSub, $fontPath, $scaledSubSize);
            $totalH = $headlineH + $subH + ($headline && $subheadline ? $gap : 0);
        }
        
        $startY = $mainAreaStartY + ($availableHeight - $totalH) / 2;
        
        // Draw Headline
        if (!empty($transformedHeadline)) {
            $h = $this->drawCenteredText($canvas, $transformedHeadline, $fontPath, $scaledHeadlineSize, self::PIN_WIDTH / 2, $startY, $headlineTextColor);
            $startY += $h + $gap;
        }
        
        // Draw Subheadline
        if (!empty($transformedSub)) {
            $this->drawCenteredText($canvas, $transformedSub, $fontPath, $scaledSubSize, self::PIN_WIDTH / 2, $startY, $subheadlineTextColor);
        }

        // 3. Draw domain bar overlapping the bottom line
        $displayDomain = strtolower($domainName ?: 'www.yourdomain.com');
        $domainFontSize = 16;
        $domainPadding = 20;
        
        // Measure domain text width
        $bbox = imagettfbbox($domainFontSize, 0, $fontPath, $displayDomain);
        $domainTextWidth = abs($bbox[2] - $bbox[0]);
        $barWidth = $domainTextWidth + ($domainPadding * 2);
        $barHeight = 30;
        
        $barX = (self::PIN_WIDTH - $barWidth) / 2;
        $barY = $bottomImageStartY - ($barHeight / 2);
        
        // Draw rectangle for domain
        imagefilledrectangle($canvas, (int)$barX, (int)$barY, (int)($barX + $barWidth), (int)($barY + $barHeight), $primaryColor);
        
        // Draw white domain text
        $this->drawCenteredText($canvas, $displayDomain, $fontPath, $domainFontSize, self::PIN_WIDTH / 2, $barY + ($barHeight - $domainFontSize) / 2 - 2, $white);
    }

    /**
     * Frame: Crispy Orange - Orange background with bright yellow accent lines
     * Based on the provided design with "CRISPY OVEN ROASTED" style
     */
    private function drawCrispyOrangeFrame(
        $canvas, 
        $topImagePath, 
        $bottomImagePath, 
        $headline, 
        $subheadline,
        $headlineColor = '#ffffff',
        $subheadlineColor = '#ffffff',
        $overlayColor = '#e67e22',
        $overlayOpacity = 100,
        $headlineFont = 'sans-serif',
        $subheadlineFont = 'script',
        int $headlineFontSize = 32,
        int $subheadlineFontSize = 24
    ): void {
        // Calculate layout
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // Colors
        $yellow = imagecolorallocate($canvas, 241, 196, 15); // #f1c40f - bright yellow

        // 1. Place Images (Background)
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, (int)$topImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($topImage);
        }

        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }

        // 2. Draw background for the text area (overlay color)
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

        // 3. Draw thin accent lines at top and bottom of bar (using yellow or could use headline color?)
        // Let's stick to yellow as it's part of the "crispy" design, or we could use headline color. 
        // Given user wants to change colors, maybe we should use headline color for these too.
        $headlineRgb = $this->hexToRgb($headlineColor);
        $accentColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $lineThickness = 4;
        imagefilledrectangle($canvas, 0, (int)$textBarStartY, self::PIN_WIDTH, (int)($textBarStartY + $lineThickness), $accentColor);
        imagefilledrectangle($canvas, 0, (int)($bottomImageStartY - $lineThickness), self::PIN_WIDTH, (int)$bottomImageStartY, $accentColor);

        // Draw text
        $subheadlineRgb = $this->hexToRgb($subheadlineColor);
        $headlineTextColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $subheadlineTextColor = imagecolorallocate($canvas, $subheadlineRgb['r'], $subheadlineRgb['g'], $subheadlineRgb['b']);

        $fontPath = $this->getFontPath($headlineFont);
        $centerX = self::PIN_WIDTH / 2;
        
        // Transform text: Headline ALL CAPS, Subheadline Title Case
        $transformedHeadline = strtoupper($headline);
        $transformedSub = ucwords(strtolower($subheadline));
        
        // Available space
        $availableHeight = self::TEXT_BAR_HEIGHT - ($lineThickness * 2) - 40;
        $mainAreaStartY = $textBarStartY + $lineThickness + 20;
        
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubSize = $subheadlineFontSize;
        $gap = 10;
        
        $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
        $subH = $this->calculateTextHeight($transformedSub, $fontPath, $scaledSubSize);
        $totalH = $headlineH + $subH + ($headline && $subheadline ? $gap : 0);
        
        while (($totalH > $availableHeight || 
               $this->isTextTooWide($transformedHeadline, $fontPath, $scaledHeadlineSize, 480) ||
               $this->isTextTooWide($transformedSub, $fontPath, $scaledSubSize, 480)) && 
               $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubSize = max(10, $scaledSubSize - 2);
            $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
            $subH = $this->calculateTextHeight($transformedSub, $fontPath, $scaledSubSize);
            $totalH = $headlineH + $subH + ($headline && $subheadline ? $gap : 0);
        }
        
        $startY = $mainAreaStartY + ($availableHeight - $totalH) / 2;
        
        // Draw Headline
        if (!empty($transformedHeadline)) {
            $h = $this->drawCenteredText($canvas, $transformedHeadline, $fontPath, $scaledHeadlineSize, self::PIN_WIDTH / 2, $startY, $headlineTextColor);
            $startY += $h + $gap;
        }
        
        // Draw Subheadline
        if (!empty($transformedSub)) {
            $this->drawCenteredText($canvas, $transformedSub, $fontPath, $scaledSubSize, self::PIN_WIDTH / 2, $startY, $subheadlineTextColor);
        }
    }

    /**
     * Frame: Torn Paper - White background with jagged "torn" horizontal edges
     * Based on the provided design with a ripped paper look
     */
    private function drawTornPaperFrame(
        $canvas, 
        $topImagePath, 
        $bottomImagePath, 
        $headline, 
        $subheadline,
        $headlineColor = '#000000',
        $subheadlineColor = '#000000',
        $overlayColor = '#ffffff',
        $overlayOpacity = 100,
        $headlineFont = 'sans-serif',
        $subheadlineFont = 'sans-serif',
        int $headlineFontSize = 32,
        int $subheadlineFontSize = 32
    ): void {
        // Layout calculations
        $imageHeight = (self::PIN_HEIGHT - self::TEXT_BAR_HEIGHT) / 2;
        $topImageStartY = 0;
        $textBarStartY = $imageHeight;
        $bottomImageStartY = $imageHeight + self::TEXT_BAR_HEIGHT;

        // 1. Place Images (Background)
        $topImage = $this->loadImage($topImagePath);
        if ($topImage) {
            $this->placeImage($canvas, $topImage, 0, (int)$topImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($topImage);
        }

        $bottomImage = $this->loadImage($bottomImagePath);
        if ($bottomImage) {
            $this->placeImage($canvas, $bottomImage, 0, (int)$bottomImageStartY, self::PIN_WIDTH, (int)$imageHeight);
            imagedestroy($bottomImage);
        }

        // 2. Draw background for text area (overlay color)
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

        // 3. Draw jagged "torn" edges (using overlay color)
        $overlayFullColor = imagecolorallocate($canvas, $overlayRgb['r'], $overlayRgb['g'], $overlayRgb['b']);
        $this->drawJaggedEdge($canvas, 0, (int)$textBarStartY, self::PIN_WIDTH, true, $overlayFullColor);
        $this->drawJaggedEdge($canvas, 0, (int)$bottomImageStartY, self::PIN_WIDTH, false, $overlayFullColor);

        // 4. Text Rendering
        $headlineRgb = $this->hexToRgb($headlineColor);
        $subheadlineRgb = $this->hexToRgb($subheadlineColor);
        $headlineTextColor = imagecolorallocate($canvas, $headlineRgb['r'], $headlineRgb['g'], $headlineRgb['b']);
        $subheadlineTextColor = imagecolorallocate($canvas, $subheadlineRgb['r'], $subheadlineRgb['g'], $subheadlineRgb['b']);

        $fontPath = $this->getFontPath($headlineFont);
        $centerX = self::PIN_WIDTH / 2;
        
        // Transform text: Bold and prominent (similar to the provided image)
        $transformedHeadline = $headline;
        $transformedSub = $subheadline;
        
        // Available space
        $availableHeight = self::TEXT_BAR_HEIGHT - 60;
        $mainAreaStartY = $textBarStartY + 30;
        
        $scaledHeadlineSize = $headlineFontSize;
        $scaledSubSize = $subheadlineFontSize;
        $gap = 15;
        
        $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
        $subH = $this->calculateTextHeight($transformedSub, $fontPath, $scaledSubSize);
        $totalH = $headlineH + $subH + ($headline && $subheadline ? $gap : 0);
        
        while (($totalH > $availableHeight || 
               $this->isTextTooWide($transformedHeadline, $fontPath, $scaledHeadlineSize, 480) ||
               $this->isTextTooWide($transformedSub, $fontPath, $scaledSubSize, 480)) && 
               $scaledHeadlineSize > 12) {
            $scaledHeadlineSize = max(12, $scaledHeadlineSize - 2);
            $scaledSubSize = max(12, $scaledSubSize - 2);
            $headlineH = $this->calculateTextHeight($transformedHeadline, $fontPath, $scaledHeadlineSize);
            $subH = $this->calculateTextHeight($transformedSub, $fontPath, $scaledSubSize);
            $totalH = $headlineH + $subH + ($headline && $subheadline ? $gap : 0);
        }
        
        $startY = $mainAreaStartY + ($availableHeight - $totalH) / 2;
        
        // Draw Headline
        if (!empty($transformedHeadline)) {
            $h = $this->drawCenteredText($canvas, $transformedHeadline, $fontPath, $scaledHeadlineSize, $centerX, $startY, $headlineTextColor);
            $startY += $h + $gap;
        }
        
        // Draw Subheadline
        if (!empty($transformedSub)) {
            $this->drawCenteredText($canvas, $transformedSub, $fontPath, $scaledSubSize, $centerX, $startY, $subheadlineTextColor);
        }
    }

    /**
     * Helper: Draw a jagged "torn paper" edge
     */
    private function drawJaggedEdge($canvas, int $x, int $y, int $width, bool $isTop, $color): void
    {
        $points = [];
        $segmentWidth = 10;
        $jitter = 8;
        
        // Start point
        $points[] = $x;
        $points[] = $y + ($isTop ? -$jitter : $jitter);

        for ($currX = $x; $currX <= $x + $width; $currX += $segmentWidth) {
            $points[] = $currX;
            $points[] = $y + (rand(-$jitter, $jitter));
        }

        // End point
        $points[] = $x + $width;
        $points[] = $y + ($isTop ? -$jitter : $jitter);
        
        // To fill correctly, we need to close the polygon
        if ($isTop) {
            $points[] = $x + $width;
            $points[] = $y + 20; // Extend down into the white bar
            $points[] = $x;
            $points[] = $y + 20;
        } else {
            $points[] = $x + $width;
            $points[] = $y - 20; // Extend up into the white bar
            $points[] = $x;
            $points[] = $y - 20;
        }

        imagefilledpolygon($canvas, $points, count($points) / 2, $color);
    }

    /**
     * Helper: Draw a star shape.
     */
    private function drawStar($canvas, float $cx, float $cy, int $outerRadius, int $innerRadius, $color): void
    {
        $points = [];
        $numPoints = 5;
        $angle = pi() / $numPoints;
        
        // Start from top
        $startAngle = -pi() / 2;

        for ($i = 0; $i < 2 * $numPoints; $i++) {
            $r = ($i % 2 === 0) ? $outerRadius : $innerRadius;
            $points[] = (int)($cx + cos($startAngle + $i * $angle) * $r);
            $points[] = (int)($cy + sin($startAngle + $i * $angle) * $r);
        }

        imagefilledpolygon($canvas, $points, 2 * $numPoints, $color);
    }

    /**
     * Helper: Check if any line of text is too wide for the max width.
     */
    private function isTextTooWide(string $text, ?string $fontPath, int $fontSize, int $maxWidth): bool
    {
        if (empty($text)) return false;
        
        $lines = $this->wrapText($text, $fontPath, $fontSize, $maxWidth);
        
        if ($fontPath && file_exists($fontPath) && function_exists('imagettfbbox')) {
            foreach ($lines as $line) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
                $width = abs($bbox[2] - $bbox[0]);
                if ($width > $maxWidth) {
                    return true;
                }
            }
        }
        
        return false;
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
     * Priority: 1. Project fonts (resources/fonts), 2. System fonts
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
                    // Project fonts first (these will work on production)
                    $fontsDir . '/arialbd.ttf',
                    $fontsDir . '/arial.ttf',
                    $fontsDir . '/ariblk.ttf',
                    // Windows fallback
                    $winFonts . '\\arialbd.ttf',
                    $winFonts . '\\arial.ttf',
                    // Linux fallback
                    '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                ];
                break;
            case 'georgia':
                $fonts = [
                    // Project fonts first
                    $fontsDir . '/georgiab.ttf',
                    $fontsDir . '/georgia.ttf',
                    $fontsDir . '/georgiai.ttf',
                    // Windows fallback
                    $winFonts . '\\georgia.ttf',
                    $winFonts . '\\georgiab.ttf',
                    // Linux fallback
                    '/usr/share/fonts/truetype/freefont/FreeSerif.ttf',
                ];
                break;
            case 'times':
                $fonts = [
                    // Project fonts first
                    $fontsDir . '/timesbd.ttf',
                    $fontsDir . '/times.ttf',
                    $fontsDir . '/timesi.ttf',
                    // Windows fallback
                    $winFonts . '\\times.ttf',
                    $winFonts . '\\timesbd.ttf',
                    // Linux fallback
                    '/usr/share/fonts/truetype/liberation/LiberationSerif-Regular.ttf',
                ];
                break;
            case 'roboto':
                $fonts = [
                    $fontsDir . '/Roboto-Bold.ttf',
                    $fontsDir . '/Roboto-Regular.ttf',
                    // Fallback to Arial (project first, then system)
                    $fontsDir . '/arialbd.ttf',
                    $winFonts . '\\arialbd.ttf',
                ];
                break;
            case 'open-sans':
                $fonts = [
                    $fontsDir . '/OpenSans-Bold.ttf',
                    $fontsDir . '/OpenSans-Regular.ttf',
                    // Fallback to Arial (project first, then system)
                    $fontsDir . '/arialbd.ttf',
                    $winFonts . '\\arialbd.ttf',
                ];
                break;
            case 'dancing-script':
                $fonts = [
                    $fontsDir . '/DancingScript-Bold.ttf',
                    $fontsDir . '/DancingScript-Regular.ttf',
                    // Fallback to Georgia italic (project first, then system)
                    $fontsDir . '/georgiai.ttf',
                    $fontsDir . '/georgia.ttf',
                    $winFonts . '\\georgiai.ttf',
                    $winFonts . '\\georgia.ttf',
                ];
                break;
            case 'pacifico':
                $fonts = [
                    $fontsDir . '/Pacifico-Regular.ttf',
                    // Fallback to Georgia italic (project first, then system)
                    $fontsDir . '/georgiai.ttf',
                    $fontsDir . '/georgia.ttf',
                    $winFonts . '\\georgiai.ttf',
                    $winFonts . '\\georgia.ttf',
                ];
                break;
            case 'sans-serif':
                $fonts = [
                    // Project fonts first (bundled with the app)
                    $fontsDir . '/arialbd.ttf',
                    $fontsDir . '/arial.ttf',
                    $fontsDir . '/ariblk.ttf',
                    $fontsDir . '/OpenSans-Bold.ttf',
                    $fontsDir . '/Roboto-Bold.ttf',
                    $fontsDir . '/DejaVuSans-Bold.ttf',
                    // Windows fallback
                    $winFonts . '\\arialbd.ttf',
                    $winFonts . '\\arial.ttf',
                    $winFonts . '\\segoeuib.ttf',
                    // Linux fallback
                    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                    '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                ];
                break;
            case 'script':
                $fonts = [
                    // Project fonts first
                    $fontsDir . '/georgiai.ttf',
                    $fontsDir . '/georgia.ttf',
                    $fontsDir . '/timesi.ttf',
                    $fontsDir . '/GreatVibes-Regular.ttf',
                    $fontsDir . '/DancingScript-Bold.ttf',
                    $fontsDir . '/Pacifico-Regular.ttf',
                    // Windows fallback
                    $winFonts . '\\georgiai.ttf',
                    $winFonts . '\\georgia.ttf',
                    $winFonts . '\\timesi.ttf',
                    // Linux fallback
                    '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Italic.ttf',
                ];
                break;
            default:
                // Unknown font, try project fonts first then system
                $fonts = [
                    $fontsDir . '/arialbd.ttf',
                    $fontsDir . '/arial.ttf',
                    $fontsDir . '/georgia.ttf',
                ];
        }

        // Try to find the requested font
        foreach ($fonts as $font) {
            if (file_exists($font)) {
                Log::debug('Font found', ['family' => $fontFamily, 'path' => $font]);
                return $font;
            }
        }

        // Fallback: try project fonts first, then system fonts
        $fallbackFonts = [
            // Project fonts (these will work on any server)
            $fontsDir . '/arialbd.ttf',
            $fontsDir . '/arial.ttf',
            $fontsDir . '/georgia.ttf',
            $fontsDir . '/times.ttf',
            // Windows system fonts
            $winFonts . '\\arialbd.ttf',
            $winFonts . '\\arial.ttf',
            $winFonts . '\\georgia.ttf',
            $winFonts . '\\times.ttf',
            // Linux system fonts
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
        // Ensure website is loaded
        if (!$article->relationLoaded('website')) {
            $article->load('website');
        }

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
            'frame_settings' => [
                'domain_name' => $article->website?->domain ?? ($article->website?->slug ? $article->website->slug . '.com' : '')
            ],
            'status' => 'pending',
        ]);

        // Generate the image
        $service = new self();
        $service->generatePinImage($pin);

        return $pin;
    }
}
