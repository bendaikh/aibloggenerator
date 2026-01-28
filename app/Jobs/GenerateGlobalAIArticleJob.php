<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Website;
use App\Models\Category;
use App\Models\User;
use App\Models\Author;
use App\Models\ArticleGenerationJob;
use App\Services\PinterestDesignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateGlobalAIArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutes

    protected array $generationJobIds = []; // website_id => generation_job_id
    protected array $websiteIds = [];
    protected int $userId = 0;
    protected string $topic = '';
    protected string $tone = '';
    protected string $length = '';
    protected string $keywords = '';
    protected string $ingredients = '';
    protected bool $autoPublish = false;
    protected array $featuredImages = [];
    protected string $articleType = 'recipe';
    protected ?int $variationIndex = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        array $generationJobIds,
        array $websiteIds,
        int $userId,
        string $topic,
        string $tone = 'conversational',
        string $length = 'medium',
        string $keywords = '',
        string $ingredients = '',
        bool $autoPublish = false,
        array $featuredImages = [],
        string $articleType = 'recipe',
        ?int $variationIndex = null
    ) {
        $this->generationJobIds = $generationJobIds;
        $this->websiteIds = $websiteIds;
        $this->userId = $userId;
        $this->topic = $topic;
        $this->tone = $tone;
        $this->length = $length;
        $this->keywords = $keywords;
        $this->ingredients = $ingredients;
        $this->autoPublish = $autoPublish;
        $this->featuredImages = $featuredImages;
        $this->articleType = $articleType;
        $this->variationIndex = $variationIndex;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user || empty($user->openai_api_key)) {
            $this->failAllJobs('User or AI configuration not found.');
            return;
        }

        // Mark all jobs as processing
        foreach ($this->generationJobIds as $jobId) {
            $job = ArticleGenerationJob::find($jobId);
            if ($job) $job->markAsProcessing();
        }

        // Determine word count based on length (increased for more comprehensive articles)
        $wordCount = match($this->length) {
            'short' => '1000-1500',
            'medium' => '2000-3000',
            'long' => '4000-5000',
            default => '2000-3000'
        };

        try {
            Log::info('Starting global background AI article generation', [
                'topic' => $this->topic, 
                'websites' => count($this->websiteIds)
            ]);
            
            $apiKey = $user->openai_api_key;
            $client = \OpenAI::client($apiKey);
            $model = $user->ai_model ?? 'gpt-4o';

            $imageCount = count($this->featuredImages);

            // Generate UNIQUE article for EACH website
            foreach ($this->websiteIds as $index => $websiteId) {
                $website = Website::with(['categories', 'authors'])->find($websiteId);
                if (!$website) continue;

                $vIndex = $this->variationIndex !== null ? $this->variationIndex : $index;

                $jobId = $this->generationJobIds[$websiteId] ?? null;
                $generationJob = $jobId ? ArticleGenerationJob::find($jobId) : null;

                // Check if this website already has a completed job to avoid duplicates on retry
                if ($generationJob && $generationJob->status === 'completed') {
                    Log::info("Skipping website {$websiteId} as it already has a completed article.");
                    continue;
                }

                try {
                    // Build unique prompt for this website
                    $prompt = $this->buildPrompt($wordCount, $website, $vIndex);
                    
                    // Call OpenAI API for THIS specific website (unique content) with increased max_tokens
                    $result = $client->chat()->create([
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are an expert blog writer who creates engaging, SEO-optimized, comprehensive content. Each article you write must be completely unique and different from others on the same topic. You write detailed articles with well-organized paragraphs and in-depth coverage.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'max_tokens' => 8000,
                        'temperature' => 0.9, // Higher temperature for more variation
                    ]);

                    $generatedContent = $result->choices[0]->message->content ?? '';

                    // Check if job still exists before continuing (user might have cancelled)
                    $generationJob = ArticleGenerationJob::find($jobId);
                    if (!$generationJob) {
                        Log::info("GenerateGlobalAIArticleJob: Generation job {$jobId} was deleted/cancelled during processing");
                        continue;
                    }

                    if (empty($generatedContent)) {
                        if ($generationJob) {
                            $generationJob->markAsFailed('Empty content received from OpenAI');
                        }
                        continue;
                    }

                    // Parse the generated content
                    $parsed = $this->parseGeneratedContent($generatedContent);

                    // Per-site category determination
                    $category = null;
                    if ($website->categories->count() > 0) {
                        $category = $this->determineBestCategory($client, $model, $website);
                    }

                    // Assign image sequentially
                    $featuredImage = null;
                    $secondaryImage = null;
                    if ($imageCount > 0) {
                        $featuredImage = $this->featuredImages[$vIndex % $imageCount];
                        // If there are at least 2 images, use the next one as secondary
                        if ($imageCount >= 2) {
                            $secondaryImage = $this->featuredImages[($vIndex + 1) % $imageCount];
                        }
                    }

                    // Get default author for this website
                    $defaultAuthor = $this->getDefaultAuthor($website);

                    // Create the article
                    $article = Article::create([
                        'website_id' => $website->id,
                        'category_id' => $category?->id,
                        'user_id' => $this->userId,
                        'author_id' => $defaultAuthor?->id,
                        'title' => $parsed['title'],
                        'slug' => Str::slug($parsed['title']) . '-' . rand(100, 999),
                        'content' => $parsed['content'],
                        'excerpt' => $parsed['excerpt'],
                        'featured_image' => $featuredImage,
                        'secondary_image' => $secondaryImage,
                        'meta_title' => $parsed['meta_title'],
                        'meta_description' => $parsed['meta_description'],
                        'meta_tags' => $parsed['meta_tags'] ?? [],
                        'notes' => $parsed['notes'] ?? [],
                        'prep_time' => $parsed['prep_time'] ?? null,
                        'cook_time' => $parsed['cook_time'] ?? null,
                        'rest_time' => $parsed['rest_time'] ?? null,
                        'total_time' => $parsed['total_time'] ?? null,
                        'status' => $this->autoPublish ? 'published' : 'draft',
                        'published_at' => $this->autoPublish ? now() : null,
                        'ai_generated' => true,
                        'generation_type' => 'ai',
                        'article_type' => $this->articleType,
                    ]);

                    if ($generationJob) {
                        $generationJob->markAsCompleted($article->id);
                    }
                    
                    Log::info("Generated unique article for website {$websiteId}", ['title' => $parsed['title']]);

                    // Generate Pinterest pin if article has images (marked as missing design for manual review)
                    if ($article->featured_image) {
                        try {
                            PinterestDesignService::createFromArticle($article, null, null, 'simple_center', 'missing_design', true);
                            Log::info("Pinterest pin created (missing_design) for article on website {$websiteId}", ['article_id' => $article->id]);
                        } catch (\Exception $e) {
                            Log::warning("Failed to create Pinterest pin for website {$websiteId}", [
                                'article_id' => $article->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                } catch (\Exception $e) {
                    if ($generationJob) {
                        $generationJob->markAsFailed($e->getMessage());
                    }
                    Log::error("Failed to generate article for website {$websiteId}: " . $e->getMessage());
                }
            }

            Log::info('Global background AI articles created successfully');

        } catch (\Exception $e) {
            $this->failAllJobs($e->getMessage());
            Log::error('GenerateGlobalAIArticleJob Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function failAllJobs(string $message): void
    {
        foreach ($this->generationJobIds as $jobId) {
            $job = ArticleGenerationJob::find($jobId);
            if ($job) $job->markAsFailed($message);
        }
    }

    /**
     * Get the default author for a website (first active author).
     */
    private function getDefaultAuthor(Website $website): ?Author
    {
        // Get the first active author for this website
        return $website->authors
            ->where('is_active', true)
            ->first();
    }

    /**
     * Determine the best category for the article topic using AI
     */
    private function determineBestCategory($client, string $model, Website $website): ?Category
    {
        $categories = $website->categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'description' => $cat->description ?? ''
            ];
        })->toArray();

        if (empty($categories)) {
            return null;
        }

        $categoriesJson = json_encode($categories);

        $prompt = <<<PROMPT
Given the following article topic and available categories, determine which category is the BEST fit for this article.

Article Topic: "{$this->topic}"

Available Categories:
{$categoriesJson}

Respond with ONLY the category ID number that best matches the topic. Just the number, nothing else.
If none of the categories fit well, respond with the ID of the most general/closest category.
PROMPT;

        try {
            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a content categorization expert. Respond only with the category ID number.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 10,
                'temperature' => 0.1,
            ]);

            $categoryId = trim($result->choices[0]->message->content ?? '');
            $categoryId = preg_replace('/[^0-9]/', '', $categoryId);

            if (!empty($categoryId)) {
                $category = $website->categories->firstWhere('id', (int) $categoryId);
                if ($category) {
                    return $category;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to determine category via AI', ['error' => $e->getMessage()]);
        }

        return $website->categories->first();
    }

    /**
     * Build the AI prompt with variation for unique articles.
     */
    private function buildPrompt(string $wordCount, Website $website, int $variationIndex): string
    {
        $keywordsText = !empty($this->keywords) ? "\n- Naturally weave in these keywords: {$this->keywords}" : '';
        
        // Handle ingredients: if provided, we will add them programmatically at the end, so tell AI NOT to include them
        // But AI MUST still generate REAL cooking instructions (not ingredient descriptions!)
        $ingredientsText = '';
        if (!empty($this->ingredients)) {
            $ingredientsText = <<<INGREDIENTS_INSTRUCTION

IMPORTANT - INGREDIENTS ARE PROVIDED SEPARATELY:
- DO NOT include an Ingredients section - it will be added automatically.
- You MUST include an Instructions section with ACTUAL COOKING STEPS.

INSTRUCTIONS MUST BE COOKING ACTIONS, FOR EXAMPLE:
1. Preheat your oven to 350°F (175°C).
2. In a large bowl, combine the dry ingredients.
3. Heat oil in a pan over medium heat.
4. Add the onions and sauté until translucent.
5. Stir in the spices and cook for 1 minute until fragrant.
6. Add the meat and brown on all sides.
7. Simmer for 30 minutes until tender.

DO NOT repeat ingredient names as instructions. Instructions are VERBS/ACTIONS (preheat, mix, chop, sauté, bake, stir, simmer, serve).
INGREDIENTS_INSTRUCTION;
        } else {
            $ingredientsText = <<<INGREDIENTS_INSTRUCTION

FOR RECIPE CONTENT:
- Include an Ingredients section: a list of items with quantities (e.g., "2 cups flour", "1 lb chicken")
- Include an Instructions section: step-by-step COOKING ACTIONS (e.g., "1. Preheat oven to 350°F", "2. Mix ingredients in a bowl")
- These MUST be different! Ingredients = WHAT you need. Instructions = HOW to cook (action verbs).
INGREDIENTS_INSTRUCTION;
        }
        
        // Add variation instructions to ensure unique articles
        $variationStyles = [
            "Focus on beginner-friendly tips and simple explanations.",
            "Take an expert perspective with advanced techniques and insider knowledge.",
            "Use a storytelling approach with personal anecdotes and experiences.",
            "Focus on quick tips and time-saving hacks.",
            "Take a health-conscious and nutritional perspective.",
            "Focus on budget-friendly options and cost-saving ideas.",
            "Emphasize traditional methods and classic approaches.",
            "Take a modern, trendy perspective with current innovations.",
            "Focus on family-friendly adaptations and kid-approved variations.",
            "Take an international perspective, comparing different regional approaches.",
        ];
        
        $variationStyle = $variationStyles[$variationIndex % count($variationStyles)];
        $randomSeed = rand(1000, 9999);
        
        $ingredientsPrompt = "";
        if ($this->articleType === 'recipe') {
            $ingredientsPrompt = "CRITICAL FOR RECIPES - INGREDIENTS SECTION:\n";
            if (!empty($this->ingredients)) {
                $ingredientsPrompt .= "- Use ONLY these ingredients: {$this->ingredients}\n";
                $ingredientsPrompt .= "- Format them as a <ul> list under a <h2>Ingredients</h2> header.\n";
            } else {
                $ingredientsPrompt .= "- You MUST generate a comprehensive list of ingredients with quantities.\n";
                $ingredientsPrompt .= "- Format them as a <ul> list under a <h2>Ingredients</h2> header.\n";
            }
        }

        return <<<PROMPT
You are a professional blog writer who creates authentic, engaging content.

Write a DETAILED, COMPREHENSIVE and COMPLETELY UNIQUE blog post about: "{$this->topic}"

CRITICAL TITLE RULE:
- The TITLE field below is pre-filled with the exact title the user wants. DO NOT CHANGE IT. Use it exactly as written - no additions, no modifications, no "improvements".

UNIQUENESS REQUIREMENT (Variation #{$variationIndex}, Seed: {$randomSeed}):
- {$variationStyle}
- Use different examples, metaphors, and explanations than typical articles
- Create a fresh, original perspective that stands out

{$ingredientsPrompt}

MOST CRITICAL RULE - BOLD TITLES ON ALL CONTENT (DO NOT SKIP THIS):
**EVERY SINGLE PARAGRAPH AND LIST ITEM** in the article MUST begin with a bold title. This is NON-NEGOTIABLE.

FOR PARAGRAPHS:
- Format: <p><strong>Descriptive Title Here:</strong> Then your paragraph content...</p>
- WRONG: <p>Journeying into the world of Korean cuisine...</p>
- RIGHT: <p><strong>A Gateway to Korean Flavors:</strong> Journeying into the world of Korean cuisine...</p>

FOR LIST ITEMS (VERY IMPORTANT):
- Format: <li><strong>Title Here:</strong> Then the list item content...</li>
- WRONG: <li>All-in-one comfort meal with protein, potatoes, and cheese</li>
- RIGHT: <li><strong>Complete Comfort Meal:</strong> All-in-one comfort meal with protein, potatoes, and cheese</li>
- WRONG: <li>Savory ranch seasoning with juicy chicken</li>
- RIGHT: <li><strong>Savory Ranch Flavor:</strong> Delicious ranch seasoning perfectly coats the juicy chicken</li>

EVERY <p> and <li> tag MUST start with <strong>Title:</strong>
- NO paragraph or list item should EVER start without a bold title
- If I see ANY paragraph or list item without a bold title, the article is REJECTED

CRITICAL WRITING STYLE RULES - DO NOT VIOLATE THESE:
1. Use DESCRIPTIVE, ENGAGING headers (<h2> and <h3>) to organize your content. Avoid generic ones like "Introduction" or "Conclusion". Instead, use creative headers that fit the topic.
2. DO NOT start with generic phrases like "Are you looking for..." or "In this article, we will..."
3. DO NOT use phrases like "In conclusion", "To summarize", "Let's dive in", or "Without further ado"
4. DO NOT follow a formulaic structure
5. DO NOT use overused AI phrases like "game-changer", "elevate", "delve into", or "embark on a journey"
6. REMEMBER: Every <p> AND <li> tag MUST have <strong>Title:</strong> at the start!

HOW TO WRITE THIS (follow this closely):
- Start with a LONG, ENGAGING introduction (at least 5-7 detailed paragraphs). Use storytelling, personal anecdotes, or historical context to draw the reader in. Make readers feel connected to your story.
- Write like you're talking to a friend. Be warm, enthusiastic, and VERY thorough.

PARAGRAPH STRUCTURE (VERY IMPORTANT):
- Each paragraph should be 4-6 sentences minimum, not just 1-2 sentences.
- Use multiple paragraphs per section - don't cram everything into one paragraph.
- Add detailed explanations, examples, and context in each paragraph.
- Every major point deserves its own paragraph with full explanation.

REMINDER - BOLD TITLES ON EVERY PARAGRAPH AND LIST ITEM (MANDATORY):
- EVERY <p> tag = <p><strong>Title:</strong> content</p>
- EVERY <li> tag = <li><strong>Title:</strong> content</li>
- NO EXCEPTIONS. Check every paragraph and list item before submitting.

CONTENT DEPTH REQUIREMENTS:
- Include a section on "Why This Works" or "The Science Behind It" with at least 3 paragraphs explaining the technique or reasoning.
- Include a "Tips for Success" section with at least 4-5 detailed tips, each explained in its own paragraph.
- Include a section on variations with detailed explanations for each option.
- Include a "Common Mistakes to Avoid" section with at least 3 mistakes and how to fix them.
- Include a "Serving Suggestions" or "How to Use" section with detailed ideas.
- Include a "Storage Tips" or "Making Ahead" section with detailed instructions.
- Include a "Frequently Asked Questions" section with at least 5 Q&As.
- End naturally with a final section that encourages reader engagement - make this at least 2-3 paragraphs.

Requirements:
- Length: MINIMUM {$wordCount} words. This is a MINIMUM - feel free to write more! Be as detailed and comprehensive as possible. DO NOT stop early.
- Tone: {$this->tone} (but always authentic and personal)
- Use proper HTML formatting: <h2> for major sections, <h3> for subsections, <p>, <ul>, <ol>, <strong>, <em>, <blockquote> for tips/quotes
- Make it SEO-friendly but human-first{$keywordsText}

CRITICAL FOR RECIPES - INSTRUCTIONS SECTION IS MANDATORY:
- You MUST include an <h2>Instructions</h2> section with step-by-step cooking directions
- Use <ol> numbered list for the instructions
- Each instruction step must START WITH AN ACTION VERB: Preheat, Mix, Chop, Sauté, Bake, Stir, Add, Pour, Heat, Season, Serve, etc.
- Include at least 8-12 detailed instruction steps
- WRONG: "Meat: Traditionally lamb is used" (this is an ingredient description, NOT an instruction)
- RIGHT: "Season the lamb with salt and pepper, then sear in a hot pan for 3 minutes per side"
- THE ARTICLE WILL BE REJECTED IF THERE IS NO INSTRUCTIONS SECTION

CRITICAL OUTPUT FORMAT RULE:
- DO NOT use markdown syntax like ** or __ in your output
- Use HTML tags only: <strong> for bold, <em> for italic
- Times, notes, and all metadata must be plain text without any markdown formatting
- WRONG: PREP_TIME: **10 mins** or NOTES: **Tip:** Use fresh...
- RIGHT: PREP_TIME: 10 mins or NOTES: Use fresh ingredients for best results

Format your response EXACTLY as follows:

TITLE: {$this->topic}

EXCERPT: [2-3 sentences teaser - plain text, no markdown]

META_TITLE: [SEO title, 50-60 characters - plain text]

META_DESCRIPTION: [SEO description, 150-160 characters - plain text]

TAGS: [REQUIRED - Comma separated list of 5-8 relevant tags for this recipe/article - plain text]

PREP_TIME: [e.g. 10 mins - plain text only, NO ** markers]
COOK_TIME: [e.g. 25 mins - plain text only, NO ** markers]
REST_TIME: [e.g. 5 mins - plain text only, NO ** markers]
TOTAL_TIME: [e.g. 40 mins - plain text only, NO ** markers]

NOTES: [REQUIRED - 3-5 pro tips as separate lines. Plain text only, NO ** or markdown. DO NOT start tips with **. Just write the tip directly.]

CONTENT:
[Full article in HTML - use <strong> tags for bold, NOT ** markdown]
PROMPT;
    }

    /**
     * Parse the generated content.
     */
    private function parseGeneratedContent(string $content): array
    {
        $title = '';
        $excerpt = '';
        $metaTitle = '';
        $metaDescription = '';
        $metaTags = [];
        $notes = [];
        $prepTime = '';
        $cookTime = '';
        $restTime = '';
        $totalTime = '';
        $articleContent = '';

        // ALWAYS use the original topic as the title - never let AI change it
        $title = $this->topic;
        if (preg_match('/EXCERPT:\s*(.+?)(?=\n\n|META_TITLE|$)/is', $content, $matches)) {
            $excerpt = trim($matches[1]);
        }
        if (preg_match('/META_TITLE:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaTitle = trim($matches[1]);
        }
        if (preg_match('/META_DESCRIPTION:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaDescription = trim($matches[1]);
        }
        if (preg_match('/TAGS:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $tagsString = trim($matches[1]);
            $metaTags = array_map('trim', explode(',', $tagsString));
            // Remove empty tags and limit to 10
            $metaTags = array_slice(array_filter($metaTags), 0, 10);
        }
        if (preg_match('/PREP_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $prepTime = $this->cleanMarkdown(trim($matches[1]));
        }
        if (preg_match('/COOK_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $cookTime = $this->cleanMarkdown(trim($matches[1]));
        }
        if (preg_match('/REST_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $restTime = $this->cleanMarkdown(trim($matches[1]));
        }
        if (preg_match('/TOTAL_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $totalTime = $this->cleanMarkdown(trim($matches[1]));
        }
        // Extract NOTES
        if (preg_match('/NOTES:\s*(.+?)(?=\n\n|CONTENT:|$)/is', $content, $matches)) {
            $notesString = trim($matches[1]);
            // Split by newlines and clean up
            $notesArray = array_filter(array_map('trim', explode("\n", $notesString)));
            // Clean markdown from each note and remove empty notes, limit to 10
            $notes = array_slice(array_filter(array_map([$this, 'cleanMarkdown'], $notesArray)), 0, 10);
        }
        if (preg_match('/CONTENT:\s*(.+)$/is', $content, $matches)) {
            $articleContent = trim($matches[1]);
        }

        $articleContent = $this->cleanContent($articleContent);

        // Ensure ingredients are at the end if provided
        if (!empty($this->ingredients)) {
            $articleContent = $this->ensureIngredientsAtEnd($articleContent);
        }

        if (empty($title)) $title = $this->topic;
        if (empty($excerpt)) $excerpt = Str::limit(strip_tags($articleContent), 200);
        if (empty($metaTitle)) $metaTitle = Str::limit($title, 60);
        if (empty($metaDescription)) $metaDescription = Str::limit($excerpt, 160);

        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_tags' => $metaTags,
            'notes' => $notes,
            'prep_time' => $prepTime,
            'cook_time' => $cookTime,
            'rest_time' => $restTime,
            'total_time' => $totalTime,
            'content' => $articleContent,
        ];
    }

    /**
     * Ensure ingredients section appears at the end of the article.
     * This function will REMOVE any existing ingredients section and add it at the very end.
     * IMPORTANT: This method carefully preserves the Instructions section!
     */
    private function ensureIngredientsAtEnd(string $content): string
    {
        // Create a DOM parser to safely manipulate HTML without breaking other sections
        $dom = new \DOMDocument();
        // Suppress warnings for malformed HTML
        @$dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $xpath = new \DOMXPath($dom);
        
        // Find and remove ONLY the Ingredients section (not Instructions!)
        // Look for h2 or h3 headers that contain "Ingredient" but NOT "Instruction"
        $headers = $xpath->query('//h2|//h3');
        $elementsToRemove = [];
        
        foreach ($headers as $header) {
            $headerText = strtolower(trim($header->textContent));
            
            // Only target ingredients headers, NOT instructions
            if ((strpos($headerText, 'ingredient') !== false) && 
                (strpos($headerText, 'instruction') === false) &&
                (strpos($headerText, 'step') === false) &&
                (strpos($headerText, 'direction') === false)) {
                
                $elementsToRemove[] = $header;
                
                // Also remove the list that follows the header
                $sibling = $header->nextSibling;
                while ($sibling) {
                    // Skip text nodes
                    if ($sibling->nodeType === XML_TEXT_NODE) {
                        $sibling = $sibling->nextSibling;
                        continue;
                    }
                    
                    // If we hit a ul or ol, mark it for removal
                    if ($sibling->nodeName === 'ul' || $sibling->nodeName === 'ol') {
                        $elementsToRemove[] = $sibling;
                        break;
                    }
                    
                    // If we hit another header, stop
                    if ($sibling->nodeName === 'h2' || $sibling->nodeName === 'h3') {
                        break;
                    }
                    
                    $sibling = $sibling->nextSibling;
                }
            }
        }
        
        // Remove the marked elements
        foreach ($elementsToRemove as $element) {
            if ($element->parentNode) {
                $element->parentNode->removeChild($element);
            }
        }
        
        // Get the cleaned content
        $wrapper = $dom->getElementsByTagName('div')->item(0);
        $cleanedContent = '';
        if ($wrapper) {
            foreach ($wrapper->childNodes as $child) {
                $cleanedContent .= $dom->saveHTML($child);
            }
        }
        
        // Clean up any extra whitespace from removal
        $cleanedContent = preg_replace('/\n{3,}/', "\n\n", trim($cleanedContent));
        
        // Now append ingredients at the very end (BEFORE any existing Instructions section)
        $ingredientsList = array_map('trim', preg_split('/[,\n]+/', $this->ingredients));
        $ingredientsHtml = "\n\n<h2>Ingredients</h2>\n<ul>\n";
        foreach ($ingredientsList as $ingredient) {
            $ingredient = trim($ingredient);
            if (!empty($ingredient)) {
                $ingredientsHtml .= "    <li>" . htmlspecialchars($ingredient) . "</li>\n";
            }
        }
        $ingredientsHtml .= "</ul>";
        
        // Check if there's an Instructions section, and insert ingredients before it
        if (preg_match('/<h[23][^>]*>\s*(?:Instructions?|Steps?|Directions?)\s*<\/h[23]>/i', $cleanedContent, $matches, PREG_OFFSET_MATCH)) {
            $insertPosition = $matches[0][1];
            return substr($cleanedContent, 0, $insertPosition) . $ingredientsHtml . "\n\n" . substr($cleanedContent, $insertPosition);
        }
        
        // If no instructions section found, just append at the end
        return $cleanedContent . $ingredientsHtml;
    }

    /**
     * Clean the generated content.
     */
    private function cleanContent(string $content): string
    {
        $content = preg_replace('/^```(?:html|xml|markdown|md)?\s*\n?/i', '', $content);
        $content = preg_replace('/\n?```\s*$/i', '', $content);
        $content = preg_replace('/```(?:html|xml|markdown|md)?/i', '', $content);
        
        // Remove markdown bold markers (**text** -> text) but preserve HTML <strong> tags
        $content = preg_replace('/\*\*([^*]+)\*\*/', '$1', $content);
        // Remove any standalone ** markers that might be left over
        $content = preg_replace('/\*\*/', '', $content);
        
        $aiPhrases = [
            '/\b(In this article,? we will|In this blog post,? we will|Let\'s dive in|Without further ado|In conclusion,?|To summarize,?|To sum up,?|Let me explain)\b/i',
        ];
        
        foreach ($aiPhrases as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }
        
        $content = preg_replace('/  +/', ' ', $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return trim($content);
    }
    
    /**
     * Clean markdown markers from a string value.
     */
    private function cleanMarkdown(string $value): string
    {
        // Remove markdown bold markers (**text** -> text)
        $value = preg_replace('/\*\*([^*]+)\*\*/', '$1', $value);
        // Remove any standalone ** markers
        $value = preg_replace('/\*\*/', '', $value);
        return trim($value);
    }
}
