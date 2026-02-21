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
     * Synonym dictionary for semantic variation - expanded for more variety
     */
    private array $synonyms = [
        // Food & Cooking
        'delicious' => ['tasty', 'flavorful', 'scrumptious', 'mouthwatering', 'appetizing', 'savory', 'delectable'],
        'cook' => ['prepare', 'make', 'create', 'whip up', 'put together'],
        'recipe' => ['dish', 'meal', 'creation', 'preparation'],
        'ingredients' => ['components', 'items', 'elements', 'contents'],
        'flavor' => ['taste', 'essence', 'character', 'zest'],
        'meal' => ['dish', 'feast', 'spread', 'fare'],
        'serve' => ['present', 'offer', 'dish up', 'plate'],
        
        // Descriptive - Quality
        'easy' => ['simple', 'straightforward', 'effortless', 'uncomplicated', 'hassle-free'],
        'perfect' => ['ideal', 'excellent', 'outstanding', 'superb', 'flawless'],
        'amazing' => ['incredible', 'fantastic', 'remarkable', 'extraordinary', 'stunning'],
        'best' => ['finest', 'top', 'premier', 'superior', 'ultimate', 'greatest'],
        'great' => ['excellent', 'wonderful', 'fantastic', 'terrific', 'superb', 'magnificent'],
        'good' => ['excellent', 'fine', 'quality', 'solid', 'decent'],
        'beautiful' => ['gorgeous', 'stunning', 'lovely', 'attractive', 'elegant', 'exquisite'],
        'wonderful' => ['marvelous', 'splendid', 'fantastic', 'magnificent', 'superb'],
        
        // Descriptive - Importance
        'important' => ['crucial', 'essential', 'vital', 'significant', 'key', 'critical'],
        'essential' => ['crucial', 'vital', 'necessary', 'fundamental', 'key'],
        'necessary' => ['required', 'needed', 'essential', 'vital', 'important'],
        
        // Size & Amount
        'big' => ['large', 'substantial', 'considerable', 'significant', 'sizeable'],
        'small' => ['little', 'tiny', 'compact', 'modest', 'petite'],
        'many' => ['numerous', 'several', 'various', 'multiple', 'plenty of'],
        'few' => ['several', 'a handful of', 'some', 'a couple of'],
        
        // Speed & Time
        'quick' => ['fast', 'rapid', 'swift', 'speedy', 'prompt', 'brief'],
        'slow' => ['gradual', 'leisurely', 'unhurried', 'steady'],
        'fast' => ['quick', 'rapid', 'swift', 'speedy', 'prompt'],
        
        // Health & Wellness
        'healthy' => ['nutritious', 'wholesome', 'nourishing', 'beneficial', 'healthful'],
        'fresh' => ['crisp', 'vibrant', 'just-picked', 'newly-made', 'garden-fresh'],
        
        // Style
        'traditional' => ['classic', 'conventional', 'time-honored', 'authentic', 'heritage'],
        'modern' => ['contemporary', 'current', 'up-to-date', 'trendy', 'cutting-edge'],
        'simple' => ['basic', 'straightforward', 'uncomplicated', 'easy', 'plain'],
        'unique' => ['distinctive', 'special', 'one-of-a-kind', 'original', 'singular'],
        
        // Actions
        'make' => ['create', 'prepare', 'craft', 'produce', 'whip up'],
        'use' => ['utilize', 'employ', 'apply', 'incorporate'],
        'add' => ['include', 'incorporate', 'mix in', 'introduce'],
        'try' => ['attempt', 'give a go', 'experiment with', 'test out'],
        'enjoy' => ['savor', 'relish', 'appreciate', 'delight in'],
        'want' => ['desire', 'wish for', 'crave', 'seek'],
        'need' => ['require', 'must have', 'call for'],
        'get' => ['obtain', 'acquire', 'receive', 'gain'],
        'start' => ['begin', 'commence', 'kick off', 'initiate'],
        'help' => ['assist', 'aid', 'support', 'contribute to'],
        'show' => ['demonstrate', 'reveal', 'display', 'present'],
        'give' => ['provide', 'offer', 'supply', 'deliver'],
        
        // Common adjectives
        'different' => ['various', 'diverse', 'distinct', 'alternative'],
        'special' => ['unique', 'distinctive', 'particular', 'exceptional'],
        'popular' => ['well-liked', 'favored', 'beloved', 'sought-after'],
        'common' => ['typical', 'usual', 'frequent', 'standard'],
        'new' => ['fresh', 'recent', 'novel', 'latest'],
        'old' => ['classic', 'traditional', 'time-tested', 'vintage'],
        
        // Conjunctions & Transitions
        'also' => ['additionally', 'as well', 'too', 'moreover'],
        'however' => ['nevertheless', 'nonetheless', 'yet', 'still'],
        'because' => ['since', 'as', 'given that', 'due to the fact that'],
        'although' => ['though', 'even though', 'while', 'despite the fact that'],
        
        // Intensifiers
        'very' => ['extremely', 'highly', 'incredibly', 'remarkably', 'exceptionally'],
        'really' => ['truly', 'genuinely', 'absolutely', 'certainly'],
        'completely' => ['entirely', 'fully', 'totally', 'wholly'],
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
        
        // Get innerHTML
        $innerHTML = '';
        foreach ($paragraph->childNodes as $child) {
            $innerHTML .= $paragraph->ownerDocument->saveHTML($child);
        }
        
        // If content has HTML tags (like <strong>), do NOT rewrite to avoid corruption
        // Just apply safe synonym replacement without sentence manipulation
        if (preg_match('/<[^>]+>/', $innerHTML)) {
            $rewritten = $this->safeHtmlSynonymReplace($innerHTML);
            
            // Only update if something changed
            if ($rewritten !== $innerHTML) {
                $success = $this->setNodeInnerHTML($paragraph, $rewritten);
                if (!$success) {
                    return;
                }
            }
            return;
        }
        
        // For plain text paragraphs, do full rewriting
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
        
        // Try to set new content using loadHTML approach (more robust than appendXML)
        $success = $this->setNodeInnerHTML($paragraph, $newInnerHTML);
        
        // If setting failed, leave the original content intact
        if (!$success) {
            return;
        }
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
        
        // If content has HTML tags (like <strong>), do NOT rewrite to avoid corruption
        // Just apply safe synonym replacement without sentence manipulation
        if (preg_match('/<[^>]+>/', $innerHTML)) {
            $rewritten = $this->safeHtmlSynonymReplace($innerHTML);
            
            // Only update if something changed
            if ($rewritten !== $innerHTML) {
                $success = $this->setNodeInnerHTML($listItem, $rewritten);
                if (!$success) {
                    return;
                }
            }
            return;
        }
        
        // For plain text list items, do full rewriting
        $rewritten = $this->rewriteSentence($innerHTML);
        
        // Try to set new content using loadHTML approach (more robust than appendXML)
        $success = $this->setNodeInnerHTML($listItem, $rewritten);
        
        // If setting failed, leave the original content intact
        if (!$success) {
            return;
        }
    }

    /**
     * Safely set inner HTML of a node using loadHTML (more robust than appendXML for HTML content)
     * 
     * @param \DOMNode $node The node to modify
     * @param string $html The HTML content to set
     * @return bool True on success, false on failure
     */
    private function setNodeInnerHTML(\DOMNode $node, string $html): bool
    {
        // Skip if empty
        if (empty(trim($html))) {
            return false;
        }
        
        try {
            // Create a temporary DOM document to parse the HTML
            $tempDoc = new DOMDocument();
            $tempDoc->encoding = 'UTF-8';
            
            // Wrap in a container div to ensure proper parsing
            $wrappedHtml = '<?xml encoding="utf-8" ?><div>' . $html . '</div>';
            
            // Suppress warnings for HTML parsing
            $result = @$tempDoc->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            if (!$result) {
                return false;
            }
            
            // Get the wrapper div
            $wrapper = $tempDoc->getElementsByTagName('div')->item(0);
            
            if (!$wrapper || !$wrapper->hasChildNodes()) {
                return false;
            }
            
            // Clear the original node's content
            while ($node->firstChild) {
                $node->removeChild($node->firstChild);
            }
            
            // Import and append each child from the temporary document
            foreach ($wrapper->childNodes as $child) {
                $imported = $node->ownerDocument->importNode($child, true);
                $node->appendChild($imported);
            }
            
            return true;
        } catch (\Exception $e) {
            // If anything fails, return false to preserve original content
            return false;
        }
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
        // Check if sentence contains HTML tags - if so, only do safe replacements
        $hasHtmlTags = preg_match('/<[^>]+>/', $sentence);
        
        if ($hasHtmlTags) {
            // For content with HTML, only do synonym replacement on text OUTSIDE of tags
            // This prevents corrupting HTML structure
            $sentence = $this->safeHtmlSynonymReplace($sentence);
            // Do NOT add sentence starters or vary punctuation for HTML content
            return $sentence;
        }
        
        // For plain text content, apply full rewriting
        // Apply synonym replacement (70% chance per word - increased for more variation)
        $replacementCount = 0;
        foreach ($this->synonyms as $word => $synonymList) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            if (preg_match($pattern, $sentence) && mt_rand(1, 100) <= 70) {
                $replacement = $synonymList[array_rand($synonymList)];
                $sentence = preg_replace_callback($pattern, function($matches) use ($replacement) {
                    return $this->matchCase($replacement, $matches[0]);
                }, $sentence, 1);
                $replacementCount++;
                
                // Limit to 3 replacements per sentence to keep it readable
                if ($replacementCount >= 3) {
                    break;
                }
            }
        }
        
        // Add sentence starter (15% chance) - only for plain text
        if (mt_rand(1, 100) <= 15) {
            $starter = $this->sentenceStarters[array_rand($this->sentenceStarters)];
            $sentence = $starter . ' ' . lcfirst($sentence);
        }
        
        // Vary punctuation slightly
        $sentence = $this->varyPunctuation($sentence);
        
        return $sentence;
    }
    
    /**
     * Safely replace synonyms in HTML content without corrupting tags
     */
    private function safeHtmlSynonymReplace(string $html): string
    {
        // Split content into HTML tags and text segments
        $parts = preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        $result = '';
        $totalReplacements = 0;
        
        foreach ($parts as $part) {
            // If it's an HTML tag, keep it as-is
            if (preg_match('/^<[^>]+>$/', $part)) {
                $result .= $part;
            } else {
                // It's text content - apply synonym replacement (70% chance - increased)
                $text = $part;
                foreach ($this->synonyms as $word => $synonymList) {
                    $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
                    if (preg_match($pattern, $text) && mt_rand(1, 100) <= 70) {
                        $replacement = $synonymList[array_rand($synonymList)];
                        $text = preg_replace_callback($pattern, function($matches) use ($replacement) {
                            return $this->matchCase($replacement, $matches[0]);
                        }, $text, 1);
                        $totalReplacements++;
                        
                        // Limit total replacements per HTML block
                        if ($totalReplacements >= 5) {
                            break;
                        }
                    }
                }
                $result .= $text;
            }
        }
        
        return $result;
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
     * 
     * Note: This method is wrapped in try-catch because DOM manipulation
     * can fail in edge cases when nodes have been moved or parent references are invalid.
     */
    private function shuffleMinorSections(DOMDocument $dom, DOMXPath $xpath, int $variationIndex): void
    {
        try {
            // Find sections that can be reordered (e.g., tips, FAQs)
            $h2Headers = $xpath->query('//h2');
            
            if (!$h2Headers || $h2Headers->length === 0) {
                return;
            }
            
            $shufflableSections = [];
            $sectionNames = ['tips', 'variations', 'serving', 'storage', 'faq'];
            
            foreach ($h2Headers as $header) {
                // Verify header has a parent node
                if (!$header->parentNode) {
                    continue;
                }
                
                $headerText = strtolower($header->textContent);
                foreach ($sectionNames as $sectionName) {
                    if (strpos($headerText, $sectionName) !== false) {
                        // Collect this section and its content
                        $section = ['header' => $header, 'content' => [], 'parent' => $header->parentNode];
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
            
            // Only shuffle if we have exactly 2 or more sections with the same parent
            if (count($shufflableSections) < 2) {
                return;
            }
            
            // Verify all sections share the same parent
            $commonParent = $shufflableSections[0]['parent'];
            foreach ($shufflableSections as $section) {
                if ($section['parent'] !== $commonParent) {
                    // Different parents - skip shuffling to avoid DOM errors
                    return;
                }
            }
            
            // Use variation index to determine shuffle (consistent per variation)
            mt_srand($variationIndex * 54321);
            shuffle($shufflableSections);
            mt_srand($variationIndex * 12345);
            
            // Find a stable reference point - the node AFTER all shufflable sections
            // This ensures we have a valid insertion point
            $lastSection = end($shufflableSections);
            $lastContent = end($lastSection['content']);
            $insertionPoint = $lastContent ? $lastContent->nextSibling : null;
            
            // Re-append sections in shuffled order (append to parent, before insertion point)
            foreach ($shufflableSections as $section) {
                if (!$section['header']->parentNode) {
                    continue; // Skip if header was already removed
                }
                
                // Move header
                if ($insertionPoint && $insertionPoint->parentNode === $commonParent) {
                    $commonParent->insertBefore($section['header'], $insertionPoint);
                } else {
                    $commonParent->appendChild($section['header']);
                }
                
                // Move content
                foreach ($section['content'] as $contentNode) {
                    if (!$contentNode->parentNode) {
                        continue; // Skip if already removed
                    }
                    if ($insertionPoint && $insertionPoint->parentNode === $commonParent) {
                        $commonParent->insertBefore($contentNode, $insertionPoint);
                    } else {
                        $commonParent->appendChild($contentNode);
                    }
                }
            }
        } catch (\Exception $e) {
            // If DOM manipulation fails, silently continue without shuffling
            // The content will still be rewritten, just not shuffled
            return;
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
        
        // Heading variation prefixes based on variation index
        $headingPrefixes = [
            'Understanding', 'Exploring', 'Discovering', 'Mastering', 'Learning About',
            'A Guide to', 'All About', 'The Essentials of', 'Insights on', 'Tips for'
        ];
        
        // Replace words in headings
        $content = preg_replace_callback('/<h([23])>(.*?)<\/h\1>/i', function($matches) use ($headingPrefixes) {
            $level = $matches[1];
            $heading = $matches[2];
            
            // Apply synonym replacement (80% chance - increased for more variation)
            $replacementsMade = 0;
            foreach ($this->synonyms as $word => $synonymList) {
                $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
                if (preg_match($pattern, $heading) && mt_rand(1, 100) <= 80) {
                    $replacement = $synonymList[array_rand($synonymList)];
                    $heading = preg_replace_callback($pattern, function($m) use ($replacement) {
                        return $this->matchCase($replacement, $m[0]);
                    }, $heading, 1);
                    $replacementsMade++;
                    
                    // Allow up to 2 replacements per heading
                    if ($replacementsMade >= 2) {
                        break;
                    }
                }
            }
            
            // 25% chance to add prefix for more variation (only for short headings)
            if (mt_rand(1, 100) <= 25 && strlen($heading) < 35) {
                // Don't add if heading already starts with similar words
                if (!preg_match('/^(The|A|An|How|What|Why|When|Where|Understanding|Exploring|Discovering|Mastering|Learning|Guide|All|Insights|Tips|Essential|Key|Top|Best)/i', $heading)) {
                    $prefix = $headingPrefixes[array_rand($headingPrefixes)];
                    $heading = $prefix . ' ' . lcfirst($heading);
                }
            }
            
            return "<h{$level}>{$heading}</h{$level}>";
        }, $content);
        
        mt_srand();
        
        return $content;
    }
}
