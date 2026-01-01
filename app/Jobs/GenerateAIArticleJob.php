<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Website;
use App\Models\Category;
use App\Models\User;
use App\Models\ArticleGenerationJob;
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
        ?string $featuredImage = null
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

        // Determine word count based on length
        $wordCount = match($this->length) {
            'short' => '500-700',
            'medium' => '1000-1500',
            'long' => '2000-3000',
            default => '1000-1500'
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

            // Call OpenAI API
            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert blog writer who creates engaging, SEO-optimized content.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 4000,
                'temperature' => 0.7,
            ]);

            $generatedContent = $result->choices[0]->message->content ?? '';

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
                'meta_title' => $parsed['meta_title'],
                'meta_description' => $parsed['meta_description'],
                'status' => $this->autoPublish ? 'published' : 'draft',
                'published_at' => $this->autoPublish ? now() : null,
                'ai_generated' => true,
                'generation_type' => 'ai',
            ]);

            // Mark job as completed
            $generationJob->markAsCompleted($article->id);

            Log::info('Background AI article created successfully', [
                'article_id' => $article->id,
                'title' => $article->title,
                'category' => $category?->name,
                'featured_image' => $article->featured_image
            ]);

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

Write a blog post about: "{$this->topic}"

CRITICAL WRITING STYLE RULES - DO NOT VIOLATE THESE:
1. DO NOT use section headers like "Introduction" or "Conclusion" - these scream AI-generated content
2. DO NOT start with generic phrases like "Are you looking for..." or "In this article, we will..."
3. DO NOT use phrases like "In conclusion", "To summarize", "Let's dive in", or "Without further ado"
4. DO NOT follow a formulaic structure - let the content flow naturally like a real blogger would write
5. DO NOT use overused AI phrases like "game-changer", "elevate", "delve into", or "embark on a journey"

HOW TO WRITE THIS (follow this closely):
- Start with a personal anecdote, a relatable moment, or jump straight into the topic with enthusiasm
- Write like you're talking to a friend who asked for your recipe/advice
- Share personal tips, failures, and lessons learned that make it authentic
- Use casual transitions between sections, not formal headers for every paragraph
- If it's a recipe, tell the story behind it before diving into ingredients
- Use descriptive, sensory language (how things smell, taste, feel)
- Include "Pro Tips" or "What You Must Know" boxes where relevant
- For recipes: organize with clear Ingredients and Instructions sections (these can have headers)
- End naturally - maybe with a call to try the recipe, a personal note, or asking readers to share their experience

Requirements:
- Length: {$wordCount} words
- Tone: {$this->tone} (but always authentic and personal)
- Category: {$category}{$keywordsText}
- Use proper HTML formatting: <h2> for major sections (Ingredients, Instructions, Pro Tips), <h3> for subsections, <p>, <ul>, <ol>, <strong>, <em>, <blockquote> for tips/quotes
- Make it SEO-friendly but human-first

Format your response EXACTLY as follows (no markdown code blocks, just plain text):

TITLE: [Write a specific, enticing title that promises value - not generic]

EXCERPT: [2-3 sentences that capture the essence and make readers want more - write it like a teaser, not a summary]

META_TITLE: [SEO title, 50-60 characters]

META_DESCRIPTION: [SEO description, 150-160 characters]

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
        $articleContent = '';

        // Extract TITLE
        if (preg_match('/TITLE:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $title = trim($matches[1]);
        }

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

        // Extract CONTENT
        if (preg_match('/CONTENT:\s*(.+)$/is', $content, $matches)) {
            $articleContent = trim($matches[1]);
        }

        // Clean the article content
        $articleContent = $this->cleanContent($articleContent);

        // Fallbacks
        if (empty($title)) {
            $title = 'Untitled Article';
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
