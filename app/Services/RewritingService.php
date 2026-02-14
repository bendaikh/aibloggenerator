<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;

/**
 * RewritingService
 * 
 * Handles local rewriting of article content without external AI API calls.
 * Uses advanced text manipulation techniques to create unique variations.
 */
class RewritingService
{
    /**
     * Synonym dictionary for semantic variation
     */
    private array $synonyms = [
        'delicious' => ['tasty', 'flavorful', 'scrumptious', 'mouthwatering', 'appetizing'],
        'easy' => ['simple', 'straightforward', 'effortless', 'uncomplicated', 'quick'],
        'perfect' => ['ideal', 'excellent', 'outstanding', 'superb', 'wonderful'],
        'amazing' => ['incredible', 'fantastic', 'remarkable', 'extraordinary', 'wonderful'],
        'best' => ['finest', 'top', 'premier', 'superior', 'ultimate'],
        'great' => ['excellent', 'wonderful', 'fantastic', 'terrific', 'superb'],
        'beautiful' => ['gorgeous', 'stunning', 'lovely', 'attractive', 'elegant'],
        'important' => ['crucial', 'essential', 'vital', 'significant', 'key'],
        'big' => ['large', 'substantial', 'considerable', 'significant', 'major'],
        'small' => ['little', 'tiny', 'compact', 'minor', 'modest'],
        'quick' => ['fast', 'rapid', 'swift', 'speedy', 'prompt'],
        'healthy' => ['nutritious', 'wholesome', 'nourishing', 'beneficial', 'good'],
        'fresh' => ['new', 'recent', 'crisp', 'vibrant', 'just-picked'],
        'traditional' => ['classic', 'conventional', 'time-honored', 'authentic', 'original'],
        'modern' => ['contemporary', 'current', 'up-to-date', 'trendy', 'recent'],
    ];

    /**
     * Sentence starters for variation
     */
    private array $sentenceStarters = [
        'Moreover,', 'Additionally,', 'Furthermore,', 'In fact,', 'Indeed,',
        'Notably,', 'Interestingly,', 'Importantly,', 'Essentially,', 'Ultimately,',
        'Specifically,', 'Particularly,', 'Generally,', 'Typically,', 'Usually,',
    ];

    /**
     * Rewrite article content to create a unique variation
     * 
     * @param string $originalContent The original HTML content
     * @param int $variationIndex Index of the variation (for seeding randomness)
     * @return string Rewritten HTML content
     */
    public function rewriteContent(string $originalContent, int $variationIndex): string
    {
        // Seed randomness based on variation index for consistency
        mt_srand($variationIndex * 12345);
        
        // Parse HTML safely
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $originalContent . '</div>', 
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $xpath = new DOMXPath($dom);
        
        // Rewrite paragraphs
        $paragraphs = $xpath->query('//p');
        foreach ($paragraphs as $paragraph) {
            $this->rewriteParagraph($paragraph);
        }
        
        // Rewrite list items
        $listItems = $xpath->query('//li');
        foreach ($listItems as $listItem) {
            $this->rewriteListItem($listItem);
        }
        
        // Shuffle some sections for structural variation
        $this->shuffleMinorSections($dom, $xpath, $variationIndex);
        
        // Extract cleaned HTML
        $wrapper = $dom->getElementsByTagName('div')->item(0);
        $rewrittenContent = '';
        if ($wrapper) {
            foreach ($wrapper->childNodes as $child) {
                $rewrittenContent .= $dom->saveHTML($child);
            }
        }
        
        // Reset random seed
        mt_srand();
        
        return trim($rewrittenContent);
    }

    /**
     * Rewrite a paragraph node
     */
    private function rewriteParagraph(\DOMNode $paragraph): void
    {
        $originalText = $paragraph->textContent;
        
        if (empty(trim($originalText))) {
            return;
        }
        
        // Preserve strong tags
        $innerHTML = '';
        foreach ($paragraph->childNodes as $child) {
            $innerHTML .= $paragraph->ownerDocument->saveHTML($child);
        }
        
        // Split into sentences
        $sentences = $this->extractSentences($innerHTML);
        
        if (count($sentences) < 2) {
            return; // Too short to rewrite meaningfully
        }
        
        // Rewrite sentences
        $rewrittenSentences = array_map(function($sentence) {
            return $this->rewriteSentence($sentence);
        }, $sentences);
        
        // Occasionally shuffle sentence order (20% chance)
        if (mt_rand(1, 100) <= 20 && count($rewrittenSentences) >= 3) {
            $firstSentence = array_shift($rewrittenSentences);
            shuffle($rewrittenSentences);
            array_unshift($rewrittenSentences, $firstSentence);
        }
        
        // Rebuild paragraph
        $newInnerHTML = implode(' ', $rewrittenSentences);
        
        // Clear old content
        while ($paragraph->firstChild) {
            $paragraph->removeChild($paragraph->firstChild);
        }
        
        // Create new fragment
        $fragment = $paragraph->ownerDocument->createDocumentFragment();
        @$fragment->appendXML($newInnerHTML);
        $paragraph->appendChild($fragment);
    }

    /**
     * Rewrite a list item node
     */
    private function rewriteListItem(\DOMNode $listItem): void
    {
        $innerHTML = '';
        foreach ($listItem->childNodes as $child) {
            $innerHTML .= $listItem->ownerDocument->saveHTML($child);
        }
        
        $rewritten = $this->rewriteSentence($innerHTML);
        
        // Clear old content
        while ($listItem->firstChild) {
            $listItem->removeChild($listItem->firstChild);
        }
        
        // Create new fragment
        $fragment = $listItem->ownerDocument->createDocumentFragment();
        @$fragment->appendXML($rewritten);
        $listItem->appendChild($fragment);
    }

    /**
     * Extract sentences from HTML text
     */
    private function extractSentences(string $html): array
    {
        // Split on period, exclamation, question mark followed by space or HTML tag
        $sentences = preg_split('/([.!?])(\s+|<)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        $result = [];
        $buffer = '';
        
        for ($i = 0; $i < count($sentences); $i++) {
            $buffer .= $sentences[$i];
            
            // Check if we hit a sentence delimiter
            if (isset($sentences[$i + 1]) && in_array($sentences[$i + 1], ['.', '!', '?'])) {
                $buffer .= $sentences[$i + 1];
                $i++;
                
                // Add the space/tag after delimiter
                if (isset($sentences[$i + 1])) {
                    if ($sentences[$i + 1] === '<') {
                        $buffer .= $sentences[$i + 1];
                        $i++;
                    } else {
                        $buffer .= ' ';
                        $i++;
                    }
                }
                
                $result[] = trim($buffer);
                $buffer = '';
            }
        }
        
        if (!empty(trim($buffer))) {
            $result[] = trim($buffer);
        }
        
        return array_filter($result);
    }

    /**
     * Rewrite a single sentence
     */
    private function rewriteSentence(string $sentence): string
    {
        // Preserve HTML tags
        $hasStrongTag = preg_match('/<strong>(.*?)<\/strong>/', $sentence, $strongMatch);
        
        // Apply synonym replacement (30% chance per word)
        foreach ($this->synonyms as $word => $synonymList) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            if (preg_match($pattern, $sentence) && mt_rand(1, 100) <= 30) {
                $replacement = $synonymList[array_rand($synonymList)];
                $sentence = preg_replace_callback($pattern, function($matches) use ($replacement) {
                    return $this->matchCase($replacement, $matches[0]);
                }, $sentence, 1);
            }
        }
        
        // Add sentence starter (15% chance)
        if (mt_rand(1, 100) <= 15 && !$hasStrongTag) {
            $starter = $this->sentenceStarters[array_rand($this->sentenceStarters)];
            $sentence = $starter . ' ' . lcfirst($sentence);
        }
        
        // Vary punctuation slightly
        $sentence = $this->varyPunctuation($sentence);
        
        return $sentence;
    }

    /**
     * Match case of replacement word to original
     */
    private function matchCase(string $replacement, string $original): string
    {
        if (ctype_upper($original[0])) {
            return ucfirst($replacement);
        }
        return $replacement;
    }

    /**
     * Vary punctuation for natural variation
     */
    private function varyPunctuation(string $sentence): string
    {
        // 10% chance to change period to exclamation for emphasis
        if (mt_rand(1, 100) <= 10 && str_ends_with(trim($sentence), '.')) {
            $sentence = substr(rtrim($sentence), 0, -1) . '!';
        }
        
        return $sentence;
    }

    /**
     * Shuffle minor sections for structural variation
     */
    private function shuffleMinorSections(DOMDocument $dom, DOMXPath $xpath, int $variationIndex): void
    {
        // Find sections that can be reordered (e.g., tips, FAQs)
        $h2Headers = $xpath->query('//h2');
        
        $shufflableSections = [];
        $sectionNames = ['tips', 'variations', 'serving', 'storage', 'faq'];
        
        foreach ($h2Headers as $header) {
            $headerText = strtolower($header->textContent);
            foreach ($sectionNames as $sectionName) {
                if (strpos($headerText, $sectionName) !== false) {
                    // Collect this section and its content
                    $section = ['header' => $header, 'content' => []];
                    $sibling = $header->nextSibling;
                    
                    while ($sibling && $sibling->nodeName !== 'h2') {
                        if ($sibling->nodeType === XML_ELEMENT_NODE) {
                            $section['content'][] = $sibling;
                        }
                        $sibling = $sibling->nextSibling;
                    }
                    
                    if (count($section['content']) > 0) {
                        $shufflableSections[] = $section;
                    }
                    break;
                }
            }
        }
        
        // Shuffle sections if we have at least 2 (based on variation index)
        if (count($shufflableSections) >= 2) {
            // Use variation index to determine shuffle (consistent per variation)
            mt_srand($variationIndex * 54321);
            shuffle($shufflableSections);
            mt_srand($variationIndex * 12345);
            
            // Reorder in DOM
            $parent = $shufflableSections[0]['header']->parentNode;
            $insertBefore = $shufflableSections[0]['header'];
            
            foreach ($shufflableSections as $section) {
                // Move header
                $parent->insertBefore($section['header'], $insertBefore);
                // Move content
                foreach ($section['content'] as $contentNode) {
                    $parent->insertBefore($contentNode, $insertBefore);
                }
            }
        }
    }

    /**
     * Generate a variation of the title
     * 
     * @param string $originalTitle
     * @param int $variationIndex
     * @return string
     */
    public function rewriteTitle(string $originalTitle, int $variationIndex): string
    {
        mt_srand($variationIndex * 12345);
        
        $variations = [
            // Pattern: prepend descriptive words
            fn($t) => $this->prependDescriptiveWord($t),
            // Pattern: change adjectives
            fn($t) => $this->replaceAdjectives($t),
            // Pattern: rephrase structure
            fn($t) => $this->rephraseTitle($t),
        ];
        
        // Select variation pattern based on index
        $pattern = $variations[$variationIndex % count($variations)];
        $rewritten = $pattern($originalTitle);
        
        mt_srand();
        
        return $rewritten;
    }

    /**
     * Prepend a descriptive word to title
     */
    private function prependDescriptiveWord(string $title): string
    {
        $descriptors = ['Ultimate', 'Perfect', 'Easy', 'Authentic', 'Homemade', 'Classic', 'Delicious', 'Quick', 'Traditional', 'Simple'];
        
        // Don't add if already has a descriptor
        foreach ($descriptors as $descriptor) {
            if (stripos($title, $descriptor) === 0) {
                return $title;
            }
        }
        
        $descriptor = $descriptors[array_rand($descriptors)];
        return $descriptor . ' ' . $title;
    }

    /**
     * Replace adjectives in title
     */
    private function replaceAdjectives(string $title): string
    {
        foreach ($this->synonyms as $word => $synonymList) {
            if (stripos($title, $word) !== false) {
                $replacement = $synonymList[array_rand($synonymList)];
                $title = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', $replacement, $title, 1);
                break; // Only replace one word
            }
        }
        
        return $title;
    }

    /**
     * Rephrase title structure
     */
    private function rephraseTitle(string $title): string
    {
        // Pattern: "Recipe Name" -> "How to Make Recipe Name"
        if (mt_rand(0, 1) === 0) {
            return 'How to Make ' . $title;
        }
        
        // Pattern: "Recipe Name" -> "Recipe Name Recipe"
        if (!str_contains(strtolower($title), 'recipe')) {
            return $title . ' Recipe';
        }
        
        return $title;
    }

    /**
     * Generate a variation of meta description
     * 
     * @param string $originalDescription
     * @param int $variationIndex
     * @return string
     */
    public function rewriteMetaDescription(string $originalDescription, int $variationIndex): string
    {
        mt_srand($variationIndex * 12345);
        
        // Split into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', $originalDescription);
        
        if (count($sentences) > 1) {
            // Shuffle sentences
            shuffle($sentences);
            $rewritten = implode(' ', $sentences);
        } else {
            $rewritten = $originalDescription;
        }
        
        // Apply synonym replacement
        foreach ($this->synonyms as $word => $synonymList) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            if (preg_match($pattern, $rewritten)) {
                $replacement = $synonymList[array_rand($synonymList)];
                $rewritten = preg_replace_callback($pattern, function($matches) use ($replacement) {
                    return $this->matchCase($replacement, $matches[0]);
                }, $rewritten, 1);
            }
        }
        
        mt_srand();
        
        // Ensure it's still within meta description length
        return Str::limit($rewritten, 160);
    }

    /**
     * Rewrite headings (H2/H3) for variation
     * 
     * @param string $content
     * @param int $variationIndex
     * @return string
     */
    public function rewriteHeadings(string $content, int $variationIndex): string
    {
        mt_srand($variationIndex * 12345);
        
        // Replace words in headings
        $content = preg_replace_callback('/<h([23])>(.*?)<\/h\1>/i', function($matches) {
            $level = $matches[1];
            $heading = $matches[2];
            
            // Apply synonym replacement
            foreach ($this->synonyms as $word => $synonymList) {
                $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
                if (preg_match($pattern, $heading) && mt_rand(1, 100) <= 50) {
                    $replacement = $synonymList[array_rand($synonymList)];
                    $heading = preg_replace_callback($pattern, function($m) use ($replacement) {
                        return $this->matchCase($replacement, $m[0]);
                    }, $heading, 1);
                    break;
                }
            }
            
            return "<h{$level}>{$heading}</h{$level}>";
        }, $content);
        
        mt_srand();
        
        return $content;
    }
}
