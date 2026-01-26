<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Website;
use App\Models\Category;
use App\Models\User;
use App\Models\ArticleGenerationJob;
use App\Services\PinterestDesignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateAIArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    protected int $generationJobId;
    protected int $websiteId;
    protected int $userId;
    protected string $topic;
    protected string $tone;
    protected string $length;
    protected string $keywords;
    protected bool $autoPublish;
    protected ?int $categoryId;
    protected ?string $featuredImage;
    protected ?string $secondaryImage;
    protected string $articleType;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $generationJobId,
        int $websiteId,
        int $userId,
        string $topic,
        string $tone = 'conversational',
        string $length = 'medium',
        string $keywords = '',
        bool $autoPublish = false,
        ?int $categoryId = null,
        ?string $featuredImage = null,
        ?string $secondaryImage = null,
        string $articleType = 'recipe'
    ) {
        $this->generationJobId = $generationJobId;
        $this->websiteId = $websiteId;
        $this->userId = $userId;
        $this->topic = $topic;
        $this->tone = $tone;
        $this->length = $length;
        $this->keywords = $keywords;
        $this->autoPublish = $autoPublish;
        $this->categoryId = $categoryId;
        $this->featuredImage = $featuredImage;
        $this->secondaryImage = $secondaryImage;
        $this->articleType = $articleType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $generationJob = ArticleGenerationJob::find($this->generationJobId);
        
        if (!$generationJob) {
            Log::error('GenerateAIArticleJob: Generation job not found', ['id' => $this->generationJobId]);
            return;
        }

        // Mark as processing
        $generationJob->markAsProcessing();

        $user = User::find($this->userId);
        $website = Website::with('categories')->find($this->websiteId);

        if (!$user || !$website) {
            $generationJob->markAsFailed('User or Website not found');
            Log::error('GenerateAIArticleJob: User or Website not found', [
                'user_id' => $this->userId,
                'website_id' => $this->websiteId
            ]);
            return;
        }

        if (empty($user->openai_api_key)) {
            $generationJob->markAsFailed('No API key configured');
            Log::error('GenerateAIArticleJob: No API key configured for user', ['user_id' => $this->userId]);
            return;
        }

        // Determine word count based on length (increased for more comprehensive articles)
        $wordCount = match($this->length) {
            'short' => '1000-1500',
            'medium' => '2000-3000',
            'long' => '4000-5000',
            default => '2000-3000'
        };

        try {
            Log::info('Starting background AI article generation', [
                'topic' => $this->topic,
                'featured_image' => $this->featuredImage ? 'provided' : 'not provided'
            ]);
            
            $apiKey = $user->openai_api_key;
            $client = \OpenAI::client($apiKey);
            $model = $user->ai_model ?? 'gpt-4o';

            // If no category specified, let AI determine the best category
            $category = null;
            if ($this->categoryId) {
                $category = Category::find($this->categoryId);
            }

            if (!$category && $website->categories->count() > 0) {
                // AI determines the best category
                $category = $this->determineBestCategory($client, $model, $website);
            }

            // Build the prompt
            $prompt = $this->buildPrompt($wordCount, $category?->name ?? 'General');

            // Call OpenAI API with increased max_tokens for comprehensive articles
            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert blog writer who creates engaging, SEO-optimized, comprehensive content. You write detailed articles with well-organized paragraphs and in-depth coverage of topics.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 8000,
                'temperature' => 0.7,
            ]);

            $generatedContent = $result->choices[0]->message->content ?? '';

            // Check if job still exists before continuing (user might have cancelled)
            $generationJob = ArticleGenerationJob::find($this->generationJobId);
            if (!$generationJob) {
                Log::info('GenerateAIArticleJob: Generation job was deleted/cancelled during processing', ['id' => $this->generationJobId]);
                return;
            }

            if (empty($generatedContent)) {
                $generationJob->markAsFailed('Empty content received from OpenAI');
                Log::error('GenerateAIArticleJob: Empty content received from OpenAI');
                return;
            }

            // Parse the generated content
            $parsed = $this->parseGeneratedContent($generatedContent);

            // Create the article
            $article = Article::create([
                'website_id' => $website->id,
                'category_id' => $category?->id,
                'user_id' => $this->userId,
                'title' => $parsed['title'],
                'slug' => Str::slug($parsed['title']),
                'content' => $parsed['content'],
                'excerpt' => $parsed['excerpt'],
                'featured_image' => $this->featuredImage,
                'secondary_image' => $this->secondaryImage,
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

            // Mark job as completed
            $generationJob->markAsCompleted($article->id);

            Log::info('Background AI article created successfully', [
                'article_id' => $article->id,
                'title' => $article->title,
                'category' => $category?->name,
                'featured_image' => $article->featured_image
            ]);

            // Generate Pinterest pin if article has images
            if ($article->featured_image) {
                try {
                    PinterestDesignService::createFromArticle($article);
                    Log::info('Pinterest pin created for article', ['article_id' => $article->id]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create Pinterest pin', [
                        'article_id' => $article->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\Exception $e) {
            $generationJob->markAsFailed($e->getMessage());
            Log::error('GenerateAIArticleJob Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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
                    Log::info('AI determined best category', [
                        'topic' => $this->topic,
                        'category' => $category->name
                    ]);
                    return $category;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to determine category via AI', ['error' => $e->getMessage()]);
        }

        // Fallback to first category
        return $website->categories->first();
    }

    /**
     * Build the AI prompt.
     */
    private function buildPrompt(string $wordCount, string $category): string
    {
        $keywordsText = !empty($this->keywords) ? "\n- Naturally weave in these keywords: {$this->keywords}" : '';
        
        return <<<PROMPT
You are a professional food blogger and recipe writer who creates authentic, engaging content that reads like it was written by a passionate home cook sharing their personal experience.

Write a detailed, comprehensive blog post about: "{$this->topic}"

CRITICAL TITLE RULE:
- The TITLE field below is pre-filled with the exact title the user wants. DO NOT CHANGE IT. Use it exactly as written - no additions, no modifications, no "improvements".

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
1. Use DESCRIPTIVE, ENGAGING headers (<h2> and <h3>) to organize your content. Avoid generic ones like "Introduction" or "Conclusion". Instead, use something like "The Secret to Perfect Chickpeas" or "Why This Salad is a Weeknight Hero".
2. DO NOT start with generic phrases like "Are you looking for..." or "In this article, we will..."
3. DO NOT use phrases like "In conclusion", "To summarize", "Let's dive in", or "Without further ado"
4. DO NOT follow a formulaic structure - let the content flow naturally like a real blogger would write
5. DO NOT use overused AI phrases like "game-changer", "elevate", "delve into", or "embark on a journey"
6. REMEMBER: Every <p> AND <li> tag MUST have <strong>Title:</strong> at the start!

HOW TO WRITE THIS (follow this closely):
- Start with a LONG, ENGAGING personal story or anecdote (at least 5-7 detailed paragraphs). Talk about why you love this dish, when you first had it, or a funny kitchen fail related to it. Make readers feel like they're sitting in your kitchen hearing the story.
- Write like you're talking to a friend who asked for your recipe/advice. Be warm, enthusiastic, and VERY thorough.
- Share personal tips, failures, and lessons learned that make it authentic.

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
- Include a section on "Why This Recipe Works" with at least 3 paragraphs explaining the science/technique behind the dish.
- Include a "Tips for Success" section with at least 4-5 detailed tips, each explained in its own paragraph.
- Include a section on variations (e.g., "How to Make it Vegan", "Add a Spicy Kick", "Make it Gluten-Free") with detailed explanations for each variation.
- Include a "Common Mistakes to Avoid" section with at least 3 mistakes and how to fix them.
- Include a "Serving Suggestions" section with pairing ideas, side dishes, and presentation tips.
- Include a "Storage and Reheating" section with detailed instructions.
- Include a "Frequently Asked Questions" section with at least 5 Q&As.

FOR RECIPE CONTENT:
- Ingredients section: List items with quantities (e.g., "2 cups flour", "1 lb lamb").
- Instructions section: Step-by-step COOKING ACTIONS starting with verbs (e.g., "1. Preheat oven to 350°F", "2. Sauté onions until golden"). Include detailed explanations for WHY each step matters.
- CRITICAL: Instructions must be ACTION STEPS (preheat, mix, chop, sauté, bake, simmer, serve) - NOT ingredient descriptions!
- End naturally with a "Final Thoughts" section (but don't call it "Conclusion") that encourages readers to try it and share their results - make this at least 2-3 paragraphs.

Requirements:
- Length: MINIMUM {$wordCount} words. This is a MINIMUM - feel free to write more! Be as detailed and comprehensive as possible. If you need more space to explain something, take it. DO NOT stop early.
- Tone: {$this->tone} (but always authentic and personal)
- Category: {$category}{$keywordsText}
- Use proper HTML formatting: <h2> for major sections, <h3> for subsections, <p>, <ul>, <ol>, <strong>, <em>, <blockquote> for tips/quotes
- Make it SEO-friendly but human-first

Format your response EXACTLY as follows (no markdown code blocks, just plain text):

TITLE: {$this->topic}

EXCERPT: [2-3 sentences that capture the essence and make readers want more - write it like a teaser, not a summary]

META_TITLE: [SEO title, 50-60 characters]

META_DESCRIPTION: [SEO description, 150-160 characters]

TAGS: [REQUIRED - Comma separated list of 5-8 relevant tags for this recipe/article. Examples: comfort food, easy recipe, family dinner, quick meals, vegetarian, etc.]

PREP_TIME: [e.g. 10 mins]
COOK_TIME: [e.g. 25 mins]
REST_TIME: [e.g. 5 mins]
TOTAL_TIME: [e.g. 40 mins]

NOTES: [REQUIRED - 3-5 pro tips, expert advice, or important notes as separate lines. Each tip should be practical and valuable. Format: one tip per line]

CONTENT:
[Full article in HTML - no ```html markers, just the HTML tags directly]
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

        // Extract EXCERPT
        if (preg_match('/EXCERPT:\s*(.+?)(?=\n\n|META_TITLE|$)/is', $content, $matches)) {
            $excerpt = trim($matches[1]);
        }

        // Extract META_TITLE
        if (preg_match('/META_TITLE:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaTitle = trim($matches[1]);
        }

        // Extract META_DESCRIPTION
        if (preg_match('/META_DESCRIPTION:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaDescription = trim($matches[1]);
        }

        // Extract TAGS
        if (preg_match('/TAGS:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $tagsString = trim($matches[1]);
            $metaTags = array_map('trim', explode(',', $tagsString));
            // Remove empty tags and limit to 10
            $metaTags = array_slice(array_filter($metaTags), 0, 10);
        }

        // Extract times
        if (preg_match('/PREP_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $prepTime = trim($matches[1]);
        }
        if (preg_match('/COOK_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $cookTime = trim($matches[1]);
        }
        if (preg_match('/REST_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $restTime = trim($matches[1]);
        }
        if (preg_match('/TOTAL_TIME:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $totalTime = trim($matches[1]);
        }

        // Extract NOTES
        if (preg_match('/NOTES:\s*(.+?)(?=\n\n|CONTENT:|$)/is', $content, $matches)) {
            $notesString = trim($matches[1]);
            // Split by newlines and clean up
            $notesArray = array_filter(array_map('trim', explode("\n", $notesString)));
            // Remove empty notes and limit to 10
            $notes = array_slice(array_filter($notesArray), 0, 10);
        }

        // Extract CONTENT
        if (preg_match('/CONTENT:\s*(.+)$/is', $content, $matches)) {
            $articleContent = trim($matches[1]);
        }

        // Clean the article content
        $articleContent = $this->cleanContent($articleContent);

        // Fallbacks - use original topic as title if parsing fails
        if (empty($title)) {
            $title = $this->topic;
        }
        if (empty($excerpt)) {
            $excerpt = Str::limit(strip_tags($articleContent), 200);
        }
        if (empty($metaTitle)) {
            $metaTitle = Str::limit($title, 60);
        }
        if (empty($metaDescription)) {
            $metaDescription = Str::limit($excerpt, 160);
        }

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
     * Clean the generated content.
     */
    private function cleanContent(string $content): string
    {
        // Remove markdown code block markers
        $content = preg_replace('/^```(?:html|xml|markdown|md)?\s*\n?/i', '', $content);
        $content = preg_replace('/\n?```\s*$/i', '', $content);
        $content = preg_replace('/```(?:html|xml|markdown|md)?/i', '', $content);
        
        // Remove common AI phrases
        $aiPhrases = [
            '/\b(In this article,? we will|In this blog post,? we will|Let\'s dive in|Without further ado|In conclusion,?|To summarize,?|To sum up,?|Let me explain)\b/i',
        ];
        
        foreach ($aiPhrases as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }
        
        // Clean up extra spaces
        $content = preg_replace('/  +/', ' ', $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return trim($content);
    }
}
