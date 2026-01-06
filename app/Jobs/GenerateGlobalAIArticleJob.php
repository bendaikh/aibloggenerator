<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Website;
use App\Models\Category;
use App\Models\User;
use App\Models\Author;
use App\Models\ArticleGenerationJob;
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
        array $featuredImages = []
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

        // Determine word count based on length
        $wordCount = match($this->length) {
            'short' => '500-700',
            'medium' => '1000-1500',
            'long' => '2000-3000',
            default => '1000-1500'
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

                $jobId = $this->generationJobIds[$websiteId] ?? null;
                $generationJob = $jobId ? ArticleGenerationJob::find($jobId) : null;

                try {
                    // Build unique prompt for this website
                    $prompt = $this->buildPrompt($wordCount, $website, $index);
                    
                    // Call OpenAI API for THIS specific website (unique content)
                    $result = $client->chat()->create([
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are an expert blog writer who creates engaging, SEO-optimized content. Each article you write must be completely unique and different from others on the same topic.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'max_tokens' => 4000,
                        'temperature' => 0.9, // Higher temperature for more variation
                    ]);

                    $generatedContent = $result->choices[0]->message->content ?? '';

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
                    if ($imageCount > 0) {
                        $featuredImage = $this->featuredImages[$index % $imageCount];
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
                        'meta_title' => $parsed['meta_title'],
                        'meta_description' => $parsed['meta_description'],
                        'status' => $this->autoPublish ? 'published' : 'draft',
                        'published_at' => $this->autoPublish ? now() : null,
                        'ai_generated' => true,
                        'generation_type' => 'ai',
                    ]);

                    if ($generationJob) {
                        $generationJob->markAsCompleted($article->id);
                    }
                    
                    Log::info("Generated unique article for website {$websiteId}", ['title' => $parsed['title']]);
                    
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
        $ingredientsText = '';
        if (!empty($this->ingredients)) {
            $ingredientsText = "\n- DO NOT include an ingredients section - it will be added automatically at the end.";
        } else {
            $ingredientsText = "\n- If the topic is recipe-related, include a well-formatted ingredients list at the END of the article.";
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
        
        return <<<PROMPT
You are a professional blog writer who creates authentic, engaging content.

Write a COMPLETELY UNIQUE blog post about: "{$this->topic}"

UNIQUENESS REQUIREMENT (Variation #{$variationIndex}, Seed: {$randomSeed}):
- {$variationStyle}
- Use different examples, metaphors, and explanations than typical articles
- Create a fresh, original perspective that stands out

CRITICAL WRITING STYLE RULES - DO NOT VIOLATE THESE:
1. DO NOT use section headers like "Introduction" or "Conclusion"
2. DO NOT start with generic phrases like "Are you looking for..." or "In this article, we will..."
3. DO NOT use phrases like "In conclusion", "To summarize", "Let's dive in", or "Without further ado"
4. DO NOT follow a formulaic structure
5. DO NOT use overused AI phrases like "game-changer", "elevate", "delve into", or "embark on a journey"

Requirements:
- Length: {$wordCount} words
- Tone: {$this->tone} (but always authentic and personal)
- Use proper HTML formatting: <h2> for major sections, <h3> for subsections, <p>, <ul>, <ol>, <strong>, <em>, <blockquote> for tips/quotes
- Make it SEO-friendly but human-first{$keywordsText}{$ingredientsText}

Format your response EXACTLY as follows:

TITLE: [Write a specific, enticing title - must be UNIQUE]

EXCERPT: [2-3 sentences teaser]

META_TITLE: [SEO title, 50-60 characters]

META_DESCRIPTION: [SEO description, 150-160 characters]

CONTENT:
[Full article in HTML - no markers, just the HTML tags directly]
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

        if (preg_match('/TITLE:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $title = trim($matches[1]);
        }
        if (preg_match('/EXCERPT:\s*(.+?)(?=\n\n|META_TITLE|$)/is', $content, $matches)) {
            $excerpt = trim($matches[1]);
        }
        if (preg_match('/META_TITLE:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaTitle = trim($matches[1]);
        }
        if (preg_match('/META_DESCRIPTION:\s*(.+?)(?:\n|$)/i', $content, $matches)) {
            $metaDescription = trim($matches[1]);
        }
        if (preg_match('/CONTENT:\s*(.+)$/is', $content, $matches)) {
            $articleContent = trim($matches[1]);
        }

        $articleContent = $this->cleanContent($articleContent);

        // Ensure ingredients are at the end if provided
        if (!empty($this->ingredients)) {
            $articleContent = $this->ensureIngredientsAtEnd($articleContent);
        }

        if (empty($title)) $title = 'Untitled Article';
        if (empty($excerpt)) $excerpt = Str::limit(strip_tags($articleContent), 200);
        if (empty($metaTitle)) $metaTitle = Str::limit($title, 60);
        if (empty($metaDescription)) $metaDescription = Str::limit($excerpt, 160);

        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'content' => $articleContent,
        ];
    }

    /**
     * Ensure ingredients section appears at the end of the article.
     * This function will REMOVE any existing ingredients section and add it at the very end.
     */
    private function ensureIngredientsAtEnd(string $content): string
    {
        // First, remove any existing Ingredients section from the content
        // This regex matches <h2>Ingredients</h2> followed by <ul>...</ul> or <ol>...</ol>
        $pattern = '/<h[23][^>]*>\s*Ingredients?\s*<\/h[23]>\s*(<ul[^>]*>.*?<\/ul>|<ol[^>]*>.*?<\/ol>)/is';
        $content = preg_replace($pattern, '', $content);
        
        // Also try to remove any standalone ingredients list that might be wrapped differently
        $pattern2 = '/<h[23][^>]*>\s*Ingredients?\s*<\/h[23]>\s*(<p>.*?<\/p>\s*)*(<ul[^>]*>.*?<\/ul>|<ol[^>]*>.*?<\/ol>)/is';
        $content = preg_replace($pattern2, '', $content);
        
        // Clean up any extra whitespace from removal
        $content = preg_replace('/\n{3,}/', "\n\n", trim($content));
        
        // Now append ingredients at the very end
        $ingredientsList = array_map('trim', preg_split('/[,\n]+/', $this->ingredients));
        $ingredientsHtml = "\n\n<h2>Ingredients</h2>\n<ul>\n";
        foreach ($ingredientsList as $ingredient) {
            $ingredient = trim($ingredient);
            if (!empty($ingredient)) {
                $ingredientsHtml .= "    <li>" . htmlspecialchars($ingredient) . "</li>\n";
            }
        }
        $ingredientsHtml .= "</ul>";
        
        return $content . $ingredientsHtml;
    }

    /**
     * Clean the generated content.
     */
    private function cleanContent(string $content): string
    {
        $content = preg_replace('/^```(?:html|xml|markdown|md)?\s*\n?/i', '', $content);
        $content = preg_replace('/\n?```\s*$/i', '', $content);
        $content = preg_replace('/```(?:html|xml|markdown|md)?/i', '', $content);
        
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
}
