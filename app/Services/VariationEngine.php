<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * VariationEngine
 * 
 * Orchestrates the creation of article variations from a master article.
 * Handles title, meta, and content variation generation.
 */
class VariationEngine
{
    private RewritingService $rewritingService;
    
    public function __construct(RewritingService $rewritingService)
    {
        $this->rewritingService = $rewritingService;
    }

    /**
     * Create a complete article variation from master article data
     * 
     * @param array $masterArticleData Parsed article data from AI
     * @param int $variationIndex Index of the variation
     * @return array Modified article data for the variation
     */
    public function createVariation(array $masterArticleData, int $variationIndex): array
    {
        // Deep clone to avoid reference issues
        $variation = $masterArticleData;
        
        // Rewrite title
        if (!empty($variation['title'])) {
            $variation['title'] = $this->rewritingService->rewriteTitle(
                $variation['title'], 
                $variationIndex
            );
        }
        
        // Rewrite meta title
        if (!empty($variation['meta_title'])) {
            $variation['meta_title'] = $this->rewritingService->rewriteTitle(
                $variation['meta_title'], 
                $variationIndex
            );
            // Ensure it stays within meta title length
            $variation['meta_title'] = Str::limit($variation['meta_title'], 60);
        }
        
        // Rewrite meta description
        if (!empty($variation['meta_description'])) {
            $variation['meta_description'] = $this->rewritingService->rewriteMetaDescription(
                $variation['meta_description'], 
                $variationIndex
            );
        }
        
        // Rewrite excerpt
        if (!empty($variation['excerpt'])) {
            $variation['excerpt'] = $this->rewritingService->rewriteMetaDescription(
                $variation['excerpt'], 
                $variationIndex
            );
            $variation['excerpt'] = Str::limit($variation['excerpt'], 200);
        }
        
        // Rewrite tags (shuffle and replace some)
        if (!empty($variation['meta_tags']) && is_array($variation['meta_tags'])) {
            $variation['meta_tags'] = $this->varyTags($variation['meta_tags'], $variationIndex);
        }
        
        // Rewrite notes (shuffle order)
        if (!empty($variation['notes']) && is_array($variation['notes'])) {
            $variation['notes'] = $this->varyNotes($variation['notes'], $variationIndex);
        }
        
        // Rewrite main content
        if (!empty($variation['content'])) {
            // First rewrite headings
            $variation['content'] = $this->rewritingService->rewriteHeadings(
                $variation['content'], 
                $variationIndex
            );
            
            // Then rewrite content
            $variation['content'] = $this->rewritingService->rewriteContent(
                $variation['content'], 
                $variationIndex
            );
        }
        
        // Vary ingredients order slightly (if they exist)
        if (!empty($variation['ingredients']) && is_array($variation['ingredients'])) {
            $variation['ingredients'] = $this->varyIngredientsOrder($variation['ingredients'], $variationIndex);
        }
        
        // Instructions stay mostly the same (cooking steps should be in order)
        // but we can vary the wording slightly
        if (!empty($variation['instructions']) && is_array($variation['instructions'])) {
            $variation['instructions'] = $this->varyInstructions($variation['instructions'], $variationIndex);
        }
        
        return $variation;
    }

    /**
     * Vary tags for uniqueness
     */
    private function varyTags(array $tags, int $variationIndex): array
    {
        mt_srand($variationIndex * 12345);
        
        // Shuffle tags
        shuffle($tags);
        
        // Replace 1-2 tags with similar alternatives if possible
        $replacements = [
            'recipe' => ['cooking', 'culinary', 'dish'],
            'easy' => ['simple', 'quick', 'beginner-friendly'],
            'healthy' => ['nutritious', 'wholesome', 'clean-eating'],
            'dinner' => ['supper', 'evening-meal', 'main-course'],
            'dessert' => ['sweet', 'treat', 'confection'],
        ];
        
        foreach ($tags as $index => $tag) {
            $tagLower = strtolower($tag);
            if (isset($replacements[$tagLower]) && mt_rand(1, 100) <= 30) {
                $tags[$index] = $replacements[$tagLower][array_rand($replacements[$tagLower])];
            }
        }
        
        mt_srand();
        
        return array_slice($tags, 0, 8); // Limit to 8 tags
    }

    /**
     * Vary notes order and wording
     */
    private function varyNotes(array $notes, int $variationIndex): array
    {
        mt_srand($variationIndex * 12345);
        
        // Shuffle notes order
        shuffle($notes);
        
        mt_srand();
        
        return $notes;
    }

    /**
     * Vary ingredients order slightly
     * Groups dry/wet ingredients but shuffles within groups
     */
    private function varyIngredientsOrder(array $ingredients, int $variationIndex): array
    {
        mt_srand($variationIndex * 12345);
        
        // Don't shuffle too much - just swap a few items
        if (count($ingredients) > 4) {
            $swapCount = min(2, (int)(count($ingredients) / 4));
            for ($i = 0; $i < $swapCount; $i++) {
                $idx1 = mt_rand(1, count($ingredients) - 2); // Avoid first and last
                $idx2 = mt_rand(1, count($ingredients) - 2);
                if ($idx1 !== $idx2) {
                    $temp = $ingredients[$idx1];
                    $ingredients[$idx1] = $ingredients[$idx2];
                    $ingredients[$idx2] = $temp;
                }
            }
        }
        
        mt_srand();
        
        return $ingredients;
    }

    /**
     * Vary instruction wording slightly
     */
    private function varyInstructions(array $instructions, int $variationIndex): array
    {
        mt_srand($variationIndex * 12345);
        
        $synonyms = [
            'mix' => ['combine', 'blend', 'stir together'],
            'add' => ['incorporate', 'mix in', 'fold in'],
            'heat' => ['warm', 'bring to temperature'],
            'cook' => ['prepare', 'heat'],
            'stir' => ['mix', 'blend', 'combine'],
            'place' => ['put', 'set', 'arrange'],
            'pour' => ['transfer', 'add', 'drizzle'],
        ];
        
        foreach ($instructions as $index => $instruction) {
            // 20% chance to vary each instruction
            if (mt_rand(1, 100) <= 20) {
                foreach ($synonyms as $word => $alternatives) {
                    $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
                    if (preg_match($pattern, $instruction)) {
                        $replacement = $alternatives[array_rand($alternatives)];
                        $instructions[$index] = preg_replace_callback($pattern, function($matches) use ($replacement) {
                            return ctype_upper($matches[0][0]) ? ucfirst($replacement) : $replacement;
                        }, $instruction, 1);
                        break; // Only replace one word per instruction
                    }
                }
            }
        }
        
        mt_srand();
        
        return $instructions;
    }

    /**
     * Calculate uniqueness score between two articles
     * Returns a percentage (0-100) of how different they are
     * 
     * @param array $article1
     * @param array $article2
     * @return float
     */
    public function calculateUniquenessScore(array $article1, array $article2): float
    {
        $scores = [];
        
        // Title uniqueness (weight: 15%)
        if (!empty($article1['title']) && !empty($article2['title'])) {
            $scores['title'] = $this->textSimilarity($article1['title'], $article2['title']);
        }
        
        // Content uniqueness (weight: 60%)
        if (!empty($article1['content']) && !empty($article2['content'])) {
            $scores['content'] = $this->textSimilarity(
                strip_tags($article1['content']), 
                strip_tags($article2['content'])
            );
        }
        
        // Meta description uniqueness (weight: 15%)
        if (!empty($article1['meta_description']) && !empty($article2['meta_description'])) {
            $scores['meta'] = $this->textSimilarity(
                $article1['meta_description'], 
                $article2['meta_description']
            );
        }
        
        // Tags uniqueness (weight: 10%)
        if (!empty($article1['meta_tags']) && !empty($article2['meta_tags'])) {
            $common = count(array_intersect($article1['meta_tags'], $article2['meta_tags']));
            $total = count(array_unique(array_merge($article1['meta_tags'], $article2['meta_tags'])));
            $scores['tags'] = $total > 0 ? ($common / $total) * 100 : 0;
        }
        
        // Calculate weighted average
        $weights = ['title' => 0.15, 'content' => 0.60, 'meta' => 0.15, 'tags' => 0.10];
        $totalScore = 0;
        $totalWeight = 0;
        
        foreach ($scores as $key => $score) {
            $totalScore += $score * $weights[$key];
            $totalWeight += $weights[$key];
        }
        
        $similarity = $totalWeight > 0 ? $totalScore / $totalWeight : 0;
        
        // Return uniqueness (100 - similarity)
        return 100 - $similarity;
    }

    /**
     * Calculate text similarity percentage
     * Uses word-level comparison for efficiency
     */
    private function textSimilarity(string $text1, string $text2): float
    {
        // Normalize and extract words
        $words1 = $this->extractWords($text1);
        $words2 = $this->extractWords($text2);
        
        if (empty($words1) || empty($words2)) {
            return 0;
        }
        
        // Calculate Jaccard similarity
        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));
        
        return $union > 0 ? ($intersection / $union) * 100 : 0;
    }

    /**
     * Extract meaningful words from text
     */
    private function extractWords(string $text): array
    {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove punctuation
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        
        // Extract words
        $words = preg_split('/\s+/', $text);
        
        // Remove stop words and short words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been', 'being'];
        $words = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
        
        return array_values($words);
    }

    /**
     * Validate that a variation is sufficiently unique
     * 
     * @param array $variation
     * @param array $master
     * @param float $minUniquenessScore Minimum required uniqueness (default 40%)
     * @return bool
     */
    public function isVariationUnique(array $variation, array $master, float $minUniquenessScore = 40.0): bool
    {
        $score = $this->calculateUniquenessScore($variation, $master);
        return $score >= $minUniquenessScore;
    }
}
