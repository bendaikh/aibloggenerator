<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Website;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AIArticleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get common data for views (websites list and current website)
     */
    private function getCommonData(Website $website): array
    {
        return [
            'currentWebsite' => $website,
            'websites' => auth()->user()->websites()
                ->withCount(['articles', 'categories'])
                ->get(),
        ];
    }

    /**
     * Display the AI article generation form.
     */
    public function index(Website $website): Response
    {
        $this->authorize('view', $website);

        $user = auth()->user();
        
        $website->load('categories');

        $recentArticles = Article::where('website_id', $website->id)
            ->where('ai_generated', true)
            ->with(['category'])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('SuperAdmin/AIArticles/Generate', array_merge(
            $this->getCommonData($website),
            [
                'recentArticles' => $recentArticles,
                'hasApiKey' => !empty($user->openai_api_key),
                'defaultTone' => $user->ai_default_tone ?? 'conversational',
            ]
        ));
    }

    /**
     * Generate an article using AI.
     */
    public function generate(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $user = auth()->user();

        // Check if user has configured OpenAI
        if (empty($user->openai_api_key)) {
            return back()->withErrors(['error' => 'Please configure your OpenAI API key in Global Settings first.']);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'topic' => 'required|string|max:255',
            'tone' => 'nullable|string|in:professional,casual,friendly,formal,conversational',
            'length' => 'nullable|string|in:short,medium,long',
            'keywords' => 'nullable|string',
            'auto_publish' => 'boolean',
        ]);

        $category = Category::findOrFail($validated['category_id']);
        if ($category->website_id !== $website->id) {
            abort(403, 'Category does not belong to this website.');
        }

        // Determine word count based on length
        $wordCount = match($validated['length'] ?? 'medium') {
            'short' => '500-700',
            'medium' => '1000-1500',
            'long' => '2000-3000',
            default => '1000-1500'
        };

        $tone = $validated['tone'] ?? $user->ai_default_tone ?? 'conversational';
        $keywords = $validated['keywords'] ?? '';

        // Build the prompt
        $prompt = $this->buildPrompt(
            $validated['topic'],
            $wordCount,
            $tone,
            $keywords,
            $category->name
        );

        try {
            Log::info('Starting AI article generation', ['topic' => $validated['topic']]);
            
            // The User model's 'encrypted' cast handles decryption automatically
            $apiKey = $user->openai_api_key;
            
            // Create OpenAI client with user's API key
            $client = \OpenAI::client($apiKey);
            
            // Get user's preferred model
            $model = $user->ai_model ?? 'gpt-4o';
            
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

            Log::info('OpenAI response received');

            $generatedContent = $result->choices[0]->message->content ?? '';

            if (empty($generatedContent)) {
                Log::error('Empty content received from OpenAI');
                return back()->withErrors(['error' => 'Failed to generate article content. Please try again.']);
            }

            Log::info('Parsing generated content');

            // Parse the generated content
            $parsed = $this->parseGeneratedContent($generatedContent);

            Log::info('Creating article', ['title' => $parsed['title']]);

            // Create the article
            $article = Article::create([
                'website_id' => $website->id,
                'category_id' => $category->id,
                'user_id' => auth()->id(),
                'title' => $parsed['title'],
                'slug' => Str::slug($parsed['title']),
                'content' => $parsed['content'],
                'excerpt' => $parsed['excerpt'],
                'featured_image' => null,
                'meta_title' => $parsed['meta_title'],
                'meta_description' => $parsed['meta_description'],
                'status' => $validated['auto_publish'] ?? false ? 'published' : 'draft',
                'published_at' => $validated['auto_publish'] ?? false ? now() : null,
                'ai_generated' => true,
            ]);

            Log::info('Article created successfully', ['article_id' => $article->id]);

            return redirect()->route('superadmin.articles.edit', ['website' => $website->id, 'article' => $article->id])
                ->with('success', 'AI article generated successfully! You can review and edit it before publishing.');

        } catch (\Exception $e) {
            Log::error('AI Article Generation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Error generating article: ' . $e->getMessage()]);
        }
    }

    /**
     * Build the AI prompt.
     */
    private function buildPrompt(string $topic, string $wordCount, string $tone, string $keywords, string $category): string
    {
        $keywordsText = !empty($keywords) ? "\n- Include these keywords naturally: $keywords" : '';
        
        return <<<PROMPT
Write a comprehensive, engaging blog article about: "{$topic}"

Requirements:
- Length: {$wordCount} words
- Tone: {$tone}
- Category: {$category}
- Make it SEO-optimized and reader-friendly
- Use proper HTML formatting with headings (h2, h3), paragraphs, lists, and bold text where appropriate{$keywordsText}
- Include an engaging introduction and conclusion
- Use subheadings to break up the content

Format your response EXACTLY as follows:

TITLE: [Catchy, SEO-friendly title]

EXCERPT: [2-3 sentence summary that entices readers]

META_TITLE: [SEO-optimized title for search engines, 50-60 characters]

META_DESCRIPTION: [SEO-optimized description for search engines, 150-160 characters]

CONTENT:
[Full article content in HTML format with <h2>, <h3>, <p>, <ul>, <ol>, <strong>, <em> tags]
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
}
