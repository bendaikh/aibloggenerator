<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\Article;
use App\Models\Category;
use App\Models\Page;
use App\Models\ArticleGenerationJob;
use App\Models\Subscriber;
use App\Models\Theme;
use App\Models\User;
use App\Jobs\GenerateGlobalAIArticleJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class OrganizationController extends Controller
{
    /**
     * Organization Dashboard
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        // Date filtering
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $datePreset = $request->input('preset', 'all_time');
        
        // Apply preset if no custom dates
        if (!$dateFrom && !$dateTo && $datePreset !== 'all_time') {
            switch ($datePreset) {
                case 'today':
                    $dateFrom = now()->startOfDay()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
                case 'yesterday':
                    $dateFrom = now()->subDay()->startOfDay()->toDateString();
                    $dateTo = now()->subDay()->endOfDay()->toDateString();
                    break;
                case 'last_7_days':
                    $dateFrom = now()->subDays(6)->startOfDay()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
                case 'last_30_days':
                    $dateFrom = now()->subDays(29)->startOfDay()->toDateString();
                    $dateTo = now()->endOfDay()->toDateString();
                    break;
                case 'this_month':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo = now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $dateFrom = now()->subMonth()->startOfMonth()->toDateString();
                    $dateTo = now()->subMonth()->endOfMonth()->toDateString();
                    break;
                case 'this_year':
                    $dateFrom = now()->startOfYear()->toDateString();
                    $dateTo = now()->endOfYear()->toDateString();
                    break;
            }
        }
        
        // Get websites based on user role - superadmins see all, website owners see only theirs
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        $websiteIds = $websites->pluck('id');

        // Base queries
        $articlesQuery = Article::whereIn('website_id', $websiteIds);
        $pagesQuery = Page::whereIn('website_id', $websiteIds);
        $subscribersQuery = Subscriber::whereIn('website_id', $websiteIds);
        
        // Apply date filters if set
        if ($dateFrom) {
            $articlesQuery->whereDate('created_at', '>=', $dateFrom);
            $pagesQuery->whereDate('created_at', '>=', $dateFrom);
            $subscribersQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $articlesQuery->whereDate('created_at', '<=', $dateTo);
            $pagesQuery->whereDate('created_at', '<=', $dateTo);
            $subscribersQuery->whereDate('created_at', '<=', $dateTo);
        }
        
        // Get articles created in date range for views calculation
        $articlesInRange = $articlesQuery->get();
        
        $stats = [
            'totalWebsites' => $websites->count(),
            'totalArticles' => $articlesInRange->count(),
            'totalVisitors' => (int) $articlesInRange->sum('views'),
            'totalPages' => $pagesQuery->count(),
            'totalSubscribers' => $subscribersQuery->count(),
        ];

        return Inertia::render('Organization/Dashboard', [
            'stats' => $stats,
            'websites' => $websites,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'preset' => $datePreset,
            ],
        ]);
    }

    /**
     * Global Settings Page
     */
    public function settings()
    {
        $user = Auth::user();
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        return Inertia::render('Organization/Settings', [
            'settings' => [
                'openai_api_key_set' => !empty($user->openai_api_key),
                'openai_api_key_masked' => $user->openai_api_key ? 'sk-....' . substr($user->openai_api_key, -4) : null,
                'ai_model' => $user->ai_model ?? 'gpt-4o',
                'ai_default_tone' => $user->ai_default_tone ?? 'conversational',
                'article_generation_mode' => $user->article_generation_mode ?? 'full_ai',
                'max_variations' => $user->max_variations ?? 5,
            ],
            'websites' => $websites,
        ]);
    }

    /**
     * API Keys Page
     */
    public function apiKeys()
    {
        $user = Auth::user();
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        return Inertia::render('Organization/ApiKeys', [
            'settings' => [
                'openai_api_key_set' => !empty($user->openai_api_key),
                'openai_api_key_masked' => $user->openai_api_key ? 'sk-....' . substr($user->openai_api_key, -4) : null,
                'gemini_api_key_set' => !empty($user->gemini_api_key),
                'gemini_api_key_masked' => $user->gemini_api_key ? substr($user->gemini_api_key, 0, 7) . '....' . substr($user->gemini_api_key, -4) : null,
                'ideogram_api_key_set' => !empty($user->ideogram_api_key),
                'ideogram_api_key_masked' => $user->ideogram_api_key ? substr($user->ideogram_api_key, 0, 7) . '....' . substr($user->ideogram_api_key, -4) : null,
                'image_generation_provider' => $user->image_generation_provider ?? 'openai',
                'ai_model' => $user->ai_model ?? 'gpt-4o',
                'ai_default_tone' => $user->ai_default_tone ?? 'conversational',
            ],
            'websites' => $websites,
        ]);
    }

    /**
     * Update API Keys Settings
     */
    public function updateApiKeys(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'openai_api_key' => 'nullable|string',
            'gemini_api_key' => 'nullable|string',
            'ideogram_api_key' => 'nullable|string',
            'image_generation_provider' => 'required|in:openai,gemini,ideogram',
            'ai_model' => 'required|in:gpt-4o,gpt-4-turbo,gpt-3.5-turbo',
            'ai_default_tone' => 'required|in:conversational,professional,casual,friendly,formal',
        ]);

        $updateData = [
            'image_generation_provider' => $validated['image_generation_provider'],
            'ai_model' => $validated['ai_model'],
            'ai_default_tone' => $validated['ai_default_tone'],
        ];

        if (!empty($validated['openai_api_key'])) {
            $updateData['openai_api_key'] = $validated['openai_api_key'];
        }

        if (!empty($validated['gemini_api_key'])) {
            $updateData['gemini_api_key'] = $validated['gemini_api_key'];
        }

        if (!empty($validated['ideogram_api_key'])) {
            $updateData['ideogram_api_key'] = $validated['ideogram_api_key'];
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'API Keys settings updated successfully!');
    }

    /**
     * Agent Rewrite Page
     */
    public function agentRewrite()
    {
        // Force fresh user data from database, not from cache
        $user = User::find(Auth::id());
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        \Log::info('Loading Agent Rewrite Page', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'article_generation_mode' => $user->article_generation_mode,
            'max_variations' => $user->max_variations,
        ]);

        return Inertia::render('Organization/AgentRewrite', [
            'settings' => [
                'article_generation_mode' => $user->article_generation_mode ?? 'full_ai',
                'max_variations' => $user->max_variations ?? 5,
            ],
            'websites' => $websites,
        ]);
    }

    /**
     * Update Agent Rewrite Settings
     */
    public function updateAgentRewrite(Request $request)
    {
        \Log::info('UPDATE AGENT REWRITE REQUEST RECEIVED', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'all_input' => $request->all(),
        ]);

        // Force fresh user data from database
        $user = User::find(Auth::id());

        $validated = $request->validate([
            'article_generation_mode' => 'required|in:full_ai,hybrid_rewrite',
            'max_variations' => 'required|integer|min:1|max:100',
        ]);

        \Log::info('Updating Agent Rewrite Settings', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'before_article_generation_mode' => $user->article_generation_mode,
            'before_max_variations' => $user->max_variations,
            'requested_article_generation_mode' => $validated['article_generation_mode'],
            'requested_max_variations' => $validated['max_variations'],
        ]);

        $updateResult = $user->update($validated);

        // Refresh the user model to get the latest data from database
        $user->refresh();

        // Force refresh the authenticated user in the session
        // This ensures the next request gets the fresh data
        if (Auth::id() === $user->id) {
            Auth::setUser($user);
        }

        \Log::info('After Updating Agent Rewrite Settings', [
            'user_id' => $user->id,
            'update_result' => $updateResult,
            'after_article_generation_mode' => $user->article_generation_mode,
            'after_max_variations' => $user->max_variations,
        ]);
        
        return redirect()->back()->with('success', 'Agent Rewrite settings updated successfully!');
    }

    /**
     * Themes Page (Read-Only)
     */
    public function themes()
    {
        $user = Auth::user();
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        // Get themes based on user role
        // Superadmins and Website Owners can see all themes, regular users only see public themes
        $themesQuery = \App\Models\Theme::where('is_active', true);
        
        if (!$user->isSuperAdmin() && !$user->isWebsiteOwner()) {
            $themesQuery->where('is_public', true);
        }
        
        $themes = $themesQuery->get();

        return Inertia::render('Organization/Themes', [
            'themes' => $themes,
            'websites' => $websites,
        ]);
    }

    /**
     * Update Global Settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'openai_api_key' => 'nullable|string',
            'ai_model' => 'required|in:gpt-4o,gpt-4-turbo,gpt-3.5-turbo',
            'ai_default_tone' => 'required|in:conversational,professional,casual,friendly,formal',
            'article_generation_mode' => 'required|in:full_ai,hybrid_rewrite',
            'max_variations' => 'required|integer|min:1|max:100',
        ]);

        $updateData = [
            'ai_model' => $validated['ai_model'],
            'ai_default_tone' => $validated['ai_default_tone'],
            'article_generation_mode' => $validated['article_generation_mode'],
            'max_variations' => $validated['max_variations'],
        ];

        if (!empty($validated['openai_api_key'])) {
            $updateData['openai_api_key'] = $validated['openai_api_key'];
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    /**
     * API Usage Page - Show API usage statistics and cost tracking
     */
    public function apiUsage()
    {
        $user = Auth::user();
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        // Get usage statistics
        $totalCost = \App\Models\ApiUsageLog::where('user_id', $user->id)->sum('estimated_cost');
        $totalTokens = \App\Models\ApiUsageLog::where('user_id', $user->id)->sum('total_tokens');
        $totalRequests = \App\Models\ApiUsageLog::where('user_id', $user->id)->count();

        // Full AI mode stats
        $fullAICost = \App\Models\ApiUsageLog::where('user_id', $user->id)
            ->where('generation_mode', 'full_ai')
            ->sum('estimated_cost');
        $fullAIRequests = \App\Models\ApiUsageLog::where('user_id', $user->id)
            ->where('generation_mode', 'full_ai')
            ->count();

        // Hybrid mode stats
        $hybridCost = \App\Models\ApiUsageLog::where('user_id', $user->id)
            ->where('generation_mode', 'hybrid_rewrite')
            ->sum('estimated_cost');
        $hybridRequests = \App\Models\ApiUsageLog::where('user_id', $user->id)
            ->where('generation_mode', 'hybrid_rewrite')
            ->count();

        // Get articles count with metadata
        $hybridArticles = Article::where('user_id', $user->id)
            ->where('generation_mode', 'hybrid_rewrite')
            ->count();

        // Calculate estimated savings (what Full AI would have cost for hybrid articles)
        $estimatedFullAICostForHybrid = $hybridArticles * ($fullAIRequests > 0 ? $fullAICost / $fullAIRequests : 0);
        $estimatedSavings = max(0, $estimatedFullAICostForHybrid - $hybridCost);

        $totalArticles = Article::where('user_id', $user->id)
            ->where('ai_generated', true)
            ->count();

        $usage = [
            'total_cost' => $totalCost,
            'total_tokens' => $totalTokens,
            'total_requests' => $totalRequests,
            'full_ai_cost' => $fullAICost,
            'full_ai_requests' => $fullAIRequests,
            'hybrid_cost' => $hybridCost,
            'hybrid_requests' => $hybridRequests,
            'hybrid_articles' => $hybridArticles,
            'estimated_savings' => $estimatedSavings,
            'total_articles' => $totalArticles,
        ];

        // Get recent logs
        $logs = \App\Models\ApiUsageLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Organization/ApiUsage', [
            'usage' => $usage,
            'logs' => $logs,
            'websites' => $websites,
        ]);
    }

    /**
     * Test AI Connection
     */
    public function testAiConnection()
    {
        $user = Auth::user();

        if (empty($user->openai_api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No API key configured'
            ]);
        }

        try {
            $client = \OpenAI::client($user->openai_api_key);
            $response = $client->models()->list();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful! API key is valid.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test Gemini AI Connection
     */
    public function testGeminiConnection()
    {
        $user = Auth::user();

        if (empty($user->gemini_api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No Gemini API key configured'
            ]);
        }

        try {
            $apiKey = $user->gemini_api_key;
            $client = new \GuzzleHttp\Client();
            
            // First, try to list available models to find the right one
            try {
                $listResponse = $client->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");
                $models = json_decode($listResponse->getBody()->getContents(), true);
                
                // Find a model that supports generateContent
                $availableModel = null;
                if (isset($models['models'])) {
                    foreach ($models['models'] as $model) {
                        $supportedMethods = $model['supportedGenerationMethods'] ?? [];
                        if (in_array('generateContent', $supportedMethods)) {
                            $availableModel = $model['name'];
                            break;
                        }
                    }
                }
                
                if (!$availableModel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No suitable Gemini model found that supports generateContent'
                    ]);
                }
            } catch (\Exception $e) {
                // If listing fails, try with a default model
                $availableModel = 'models/gemini-1.5-pro-latest';
            }
            
            // Test the Gemini API with a simple text generation request
            $response = $client->post("https://generativelanguage.googleapis.com/v1beta/{$availableModel}:generateContent?key={$apiKey}", [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Say "Connection successful!" in exactly those words.']
                            ]
                        ]
                    ]
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['candidates'][0]['content'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gemini connection successful! API key is valid. Using model: ' . basename($availableModel),
                    'response' => $result['candidates'][0]['content']['parts'][0]['text'] ?? ''
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unexpected response from Gemini API'
            ]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($errorBody, true);
            $errorMessage = $errorData['error']['message'] ?? $e->getMessage();
            
            return response()->json([
                'success' => false,
                'message' => 'Gemini connection failed: ' . $errorMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test Ideogram AI Connection
     */
    public function testIdeogramConnection()
    {
        $user = Auth::user();

        if (empty($user->ideogram_api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No Ideogram API key configured'
            ]);
        }

        try {
            $apiKey = $user->ideogram_api_key;
            $client = new \GuzzleHttp\Client();
            
            // Test the Ideogram API with a simple image generation request
            // We'll use a minimal prompt to test the connection
            $response = $client->post('https://api.ideogram.ai/v1/ideogram-v3/generate', [
                'multipart' => [
                    [
                        'name' => 'prompt',
                        'contents' => 'A simple test image: a red square on white background'
                    ],
                    [
                        'name' => 'resolution',
                        'contents' => '1024x1024'
                    ],
                    [
                        'name' => 'rendering_speed',
                        'contents' => 'TURBO'
                    ],
                    [
                        'name' => 'num_images',
                        'contents' => '1'
                    ]
                ],
                'headers' => [
                    'Api-Key' => $apiKey,
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['data']) && !empty($result['data'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ideogram connection successful! API key is valid and working. Model: Ideogram V3',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unexpected response from Ideogram API'
            ]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($errorBody, true);
            $errorMessage = $errorData['error']['message'] ?? $e->getMessage();
            
            return response()->json([
                'success' => false,
                'message' => 'Ideogram connection failed: ' . $errorMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ideogram connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Websites List
     */
    public function websitesIndex()
    {
        $user = Auth::user();
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        return Inertia::render('Organization/Websites/Index', [
            'websites' => $websites,
        ]);
    }

    /**
     * Create Website Form
     */
    public function websitesCreate()
    {
        $user = Auth::user();
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        // Get themes based on user role
        // Superadmins and Website Owners can see all themes, regular users only see public themes
        $themesQuery = \App\Models\Theme::where('is_active', true);
        
        if (!$user->isSuperAdmin() && !$user->isWebsiteOwner()) {
            $themesQuery->where('is_public', true);
        }
        
        $themes = $themesQuery->get();

        return Inertia::render('Organization/Websites/Create', [
            'websites' => $websites,
            'themes' => $themes,
        ]);
    }

    /**
     * Store New Website
     */
    public function websitesStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255|unique:websites,subdomain',
            'description' => 'nullable|string',
            'hbagency_script' => 'nullable|string',
            'ads_txt' => 'nullable|string',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,ico|max:2048',
            'theme_id' => 'required|exists:themes,id',
        ]);

        $user = Auth::user();

        // Generate subdomain if not provided
        if (empty($validated['subdomain'])) {
            $validated['subdomain'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $originalSubdomain = $validated['subdomain'];
            $counter = 1;
            while (Website::where('subdomain', $validated['subdomain'])->exists()) {
                $validated['subdomain'] = $originalSubdomain . '-' . $counter;
                $counter++;
            }
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoFilename = Str::uuid() . '.' . $logoFile->getClientOriginalExtension();
            $logoDirectory = public_path('uploads/images/website');
            
            if (!File::isDirectory($logoDirectory)) {
                File::makeDirectory($logoDirectory, 0755, true);
            }
            
            $logoFile->move($logoDirectory, $logoFilename);
            $validated['logo'] = 'uploads/images/website/' . $logoFilename;
        } else {
            $validated['logo'] = null;
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $faviconFile = $request->file('favicon');
            $faviconFilename = Str::uuid() . '.' . $faviconFile->getClientOriginalExtension();
            $faviconDirectory = public_path('uploads/images/website');
            
            if (!File::isDirectory($faviconDirectory)) {
                File::makeDirectory($faviconDirectory, 0755, true);
            }
            
            $faviconFile->move($faviconDirectory, $faviconFilename);
            $validated['favicon'] = 'uploads/images/website/' . $faviconFilename;
        } else {
            $validated['favicon'] = null;
        }

        $website = Website::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'domain' => $validated['domain'] ?? null,
            'subdomain' => $validated['subdomain'],
            'description' => $validated['description'] ?? null,
            'hbagency_script' => $validated['hbagency_script'] ?? null,
            'ads_txt' => $validated['ads_txt'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'logo' => $validated['logo'],
            'favicon' => $validated['favicon'],
            'theme_id' => $validated['theme_id'],
        ]);

        // Create default pages for the website
        $this->createDefaultPages($website);

        return redirect()->route('superadmin.dashboard', ['website' => $website->id])
            ->with('success', 'Website created successfully!');
    }

    /**
     * Create default pages (About Us, Contact Us, Privacy Policy) for a new website.
     */
    private function createDefaultPages(Website $website): void
    {
        $aboutUsContent = $this->getAboutUsContent($website);
        $contactUsContent = $this->getContactUsContent($website);
        $privacyPolicyContent = $this->getPrivacyPolicyContent($website);

        $defaultPages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => $aboutUsContent,
                'excerpt' => 'Learn more about ' . $website->name . ' and our mission to deliver quality content.',
                'meta_title' => 'About Us - ' . $website->name,
                'meta_description' => 'Discover who we are and what drives us at ' . $website->name . '. Learn about our mission, values, and the team behind the content.',
                'order' => 1,
                'is_active' => true,
                'show_in_menu' => true,
                'is_default' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => $contactUsContent,
                'excerpt' => 'Get in touch with us. We would love to hear from you!',
                'meta_title' => 'Contact Us - ' . $website->name,
                'meta_description' => 'Contact ' . $website->name . ' for questions, feedback, or business inquiries. We are here to help!',
                'order' => 2,
                'is_active' => true,
                'show_in_menu' => true,
                'is_default' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => $privacyPolicyContent,
                'excerpt' => 'Our commitment to protecting your privacy and personal information.',
                'meta_title' => 'Privacy Policy - ' . $website->name,
                'meta_description' => 'Read our Privacy Policy to understand how ' . $website->name . ' collects, uses, and protects your personal information.',
                'order' => 3,
                'is_active' => true,
                'show_in_menu' => false,
                'is_default' => true,
            ],
        ];

        foreach ($defaultPages as $pageData) {
            Page::create(array_merge($pageData, ['website_id' => $website->id]));
        }
    }

    /**
     * Get About Us page content with beautiful design
     */
    private function getAboutUsContent(Website $website): string
    {
        $name = $website->name;
        $themeSlug = $website->theme?->slug ?? 'recipe';
        
        if ($themeSlug === 'home-decor') {
            return $this->getHomeDecorAboutUsContent($name);
        }
        
        return $this->getRecipeAboutUsContent($name);
    }

    /**
     * Get About Us content for Home Decor theme
     */
    private function getHomeDecorAboutUsContent(string $name): string
    {
        return <<<HTML
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #d4a574 0%, #c4956a 100%); padding: 60px 40px; border-radius: 20px; margin-bottom: 40px; text-align: center; color: white;">
    <h1 style="font-size: 2.5rem; margin-bottom: 20px; font-weight: 700;">Welcome to {$name}</h1>
    <p style="font-size: 1.25rem; opacity: 0.95; max-width: 600px; margin: 0 auto;">We're passionate about creating beautiful living spaces that inspire comfort, style, and personal expression in every home.</p>
</div>

<!-- Our Story Section -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px; align-items: center;">
    <div>
        <h2 style="color: #44403c; font-size: 1.8rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="background: #c4956a; color: white; width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">🏠</span>
            Our Story
        </h2>
        <p style="color: #78716c; line-height: 1.8; font-size: 1.1rem;">
            Founded with a passion for interior design and cozy living, {$name} has grown from a small design blog into a thriving community of home enthusiasts. Every day, we strive to bring you inspiration that transforms houses into homes.
        </p>
        <p style="color: #78716c; line-height: 1.8; font-size: 1.1rem; margin-top: 15px;">
            Our journey began with a simple belief: everyone deserves a beautiful, comfortable space to call their own. Today, we continue that mission by curating the best in home decor, DIY projects, and interior design trends.
        </p>
    </div>
    <div style="background: linear-gradient(135deg, #fef7ed 0%, #fed7aa 100%); padding: 40px; border-radius: 16px; border: 2px solid #fdba74;">
        <div style="text-align: center;">
            <div style="font-size: 3rem; font-weight: 700; color: #c2410c;">500+</div>
            <div style="color: #ea580c; font-weight: 500;">Design Ideas</div>
        </div>
        <div style="text-align: center; margin-top: 30px;">
            <div style="font-size: 3rem; font-weight: 700; color: #c2410c;">25K+</div>
            <div style="color: #ea580c; font-weight: 500;">Happy Homeowners</div>
        </div>
    </div>
</div>

<!-- Our Values Section -->
<div style="background: #faf5f0; padding: 50px 40px; border-radius: 20px; margin-bottom: 50px;">
    <h2 style="text-align: center; color: #44403c; font-size: 1.8rem; margin-bottom: 40px;">Our Design Philosophy</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;">
        <div style="text-align: center; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">✨</div>
            <h3 style="color: #44403c; font-size: 1.2rem; margin-bottom: 10px;">Timeless Elegance</h3>
            <p style="color: #78716c; font-size: 0.95rem;">We believe in designs that stand the test of time, blending classic aesthetics with modern comfort.</p>
        </div>
        <div style="text-align: center; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">🌿</div>
            <h3 style="color: #44403c; font-size: 1.2rem; margin-bottom: 10px;">Natural Beauty</h3>
            <p style="color: #78716c; font-size: 0.95rem;">We embrace natural materials, earthy tones, and organic textures that bring warmth to any space.</p>
        </div>
        <div style="text-align: center; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">💝</div>
            <h3 style="color: #44403c; font-size: 1.2rem; margin-bottom: 10px;">Personal Touch</h3>
            <p style="color: #78716c; font-size: 0.95rem;">Your home should reflect who you are. We help you find your unique style and express it beautifully.</p>
        </div>
    </div>
</div>

<!-- What We Offer Section -->
<div style="margin-bottom: 50px;">
    <h2 style="color: #44403c; font-size: 1.8rem; margin-bottom: 30px; text-align: center;">What We Offer</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">🛋️</div>
            <div>
                <h4 style="color: #92400e; font-weight: 600; margin-bottom: 5px;">Room Makeovers</h4>
                <p style="color: #a16207; font-size: 0.9rem;">Complete room transformation ideas from living rooms to cozy bedrooms.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #fce7d6 0%, #f5d0b5 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">🎨</div>
            <div>
                <h4 style="color: #9a3412; font-weight: 600; margin-bottom: 5px;">Color Palettes</h4>
                <p style="color: #c2410c; font-size: 0.9rem;">Curated color schemes that create harmony and warmth in your spaces.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #e8e4df 0%, #d6cfc7 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">🔨</div>
            <div>
                <h4 style="color: #57534e; font-weight: 600; margin-bottom: 5px;">DIY Projects</h4>
                <p style="color: #78716c; font-size: 0.9rem;">Step-by-step guides for creating beautiful decor pieces yourself.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #d5e8d4 0%, #b8d4b4 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">🌱</div>
            <div>
                <h4 style="color: #166534; font-weight: 600; margin-bottom: 5px;">Sustainable Living</h4>
                <p style="color: #15803d; font-size: 0.9rem;">Eco-friendly decor ideas and sustainable home improvement tips.</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div style="background: linear-gradient(135deg, #44403c 0%, #57534e 100%); padding: 50px 40px; border-radius: 20px; text-align: center; color: white;">
    <h2 style="font-size: 1.8rem; margin-bottom: 15px;">Ready to Transform Your Space?</h2>
    <p style="opacity: 0.9; margin-bottom: 25px; max-width: 500px; margin-left: auto; margin-right: auto;">Explore our collection of home decor ideas and find inspiration for every room in your home.</p>
    <a href="/" style="display: inline-block; background: #c4956a; color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.3s;">Explore Ideas →</a>
</div>
HTML;
    }

    /**
     * Get About Us content for Recipe theme
     */
    private function getRecipeAboutUsContent(string $name): string
    {
        return <<<HTML
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 60px 40px; border-radius: 20px; margin-bottom: 40px; text-align: center; color: white;">
    <h1 style="font-size: 2.5rem; margin-bottom: 20px; font-weight: 700;">Welcome to {$name}</h1>
    <p style="font-size: 1.25rem; opacity: 0.95; max-width: 600px; margin: 0 auto;">We're passionate about creating delicious recipes that bring joy to your kitchen and your table.</p>
</div>

<!-- Our Story Section -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px; align-items: center;">
    <div>
        <h2 style="color: #1e293b; font-size: 1.8rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="background: #10b981; color: white; width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">👨‍🍳</span>
            Our Story
        </h2>
        <p style="color: #475569; line-height: 1.8; font-size: 1.1rem;">
            Founded with a love for home cooking, {$name} has grown from a small food blog into a thriving community of home chefs and food lovers. Every day, we strive to bring you recipes that are both delicious and achievable.
        </p>
        <p style="color: #475569; line-height: 1.8; font-size: 1.1rem; margin-top: 15px;">
            Our journey began in a tiny kitchen with a simple belief: everyone can create amazing meals at home. Today, we continue that mission by sharing tested recipes, cooking tips, and culinary inspiration.
        </p>
    </div>
    <div style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); padding: 40px; border-radius: 16px; border: 2px solid #99f6e4;">
        <div style="text-align: center;">
            <div style="font-size: 3rem; font-weight: 700; color: #0d9488;">1000+</div>
            <div style="color: #14b8a6; font-weight: 500;">Recipes Published</div>
        </div>
        <div style="text-align: center; margin-top: 30px;">
            <div style="font-size: 3rem; font-weight: 700; color: #0d9488;">50K+</div>
            <div style="color: #14b8a6; font-weight: 500;">Happy Home Cooks</div>
        </div>
    </div>
</div>

<!-- Our Values Section -->
<div style="background: #f8fafc; padding: 50px 40px; border-radius: 20px; margin-bottom: 50px;">
    <h2 style="text-align: center; color: #1e293b; font-size: 1.8rem; margin-bottom: 40px;">Our Kitchen Values</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;">
        <div style="text-align: center; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">🍳</div>
            <h3 style="color: #1e293b; font-size: 1.2rem; margin-bottom: 10px;">Tested Recipes</h3>
            <p style="color: #64748b; font-size: 0.95rem;">Every recipe is kitchen-tested multiple times to ensure perfect results every time you cook.</p>
        </div>
        <div style="text-align: center; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">🥗</div>
            <h3 style="color: #1e293b; font-size: 1.2rem; margin-bottom: 10px;">Fresh Ingredients</h3>
            <p style="color: #64748b; font-size: 0.95rem;">We believe in using fresh, quality ingredients that make every dish shine.</p>
        </div>
        <div style="text-align: center; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">❤️</div>
            <h3 style="color: #1e293b; font-size: 1.2rem; margin-bottom: 10px;">Made with Love</h3>
            <p style="color: #64748b; font-size: 0.95rem;">Cooking is an act of love. Our recipes are designed to bring families together.</p>
        </div>
    </div>
</div>

<!-- What We Offer Section -->
<div style="margin-bottom: 50px;">
    <h2 style="color: #1e293b; font-size: 1.8rem; margin-bottom: 30px; text-align: center;">What We Offer</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">🍰</div>
            <div>
                <h4 style="color: #92400e; font-weight: 600; margin-bottom: 5px;">Easy Recipes</h4>
                <p style="color: #a16207; font-size: 0.9rem;">Simple, straightforward recipes that anyone can follow and master.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">📝</div>
            <div>
                <h4 style="color: #166534; font-weight: 600; margin-bottom: 5px;">Detailed Instructions</h4>
                <p style="color: #15803d; font-size: 0.9rem;">Step-by-step guides with tips and tricks for perfect results.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">🌮</div>
            <div>
                <h4 style="color: #991b1b; font-weight: 600; margin-bottom: 5px;">World Cuisines</h4>
                <p style="color: #dc2626; font-size: 0.9rem;">Explore flavors from around the world, from Italian to Asian and beyond.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">⏱️</div>
            <div>
                <h4 style="color: #1e40af; font-weight: 600; margin-bottom: 5px;">Quick Meals</h4>
                <p style="color: #1d4ed8; font-size: 0.9rem;">Delicious recipes for busy weeknights when time is short.</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 50px 40px; border-radius: 20px; text-align: center; color: white;">
    <h2 style="font-size: 1.8rem; margin-bottom: 15px;">Ready to Start Cooking?</h2>
    <p style="opacity: 0.9; margin-bottom: 25px; max-width: 500px; margin-left: auto; margin-right: auto;">Dive into our collection of recipes and discover dishes that will delight your taste buds.</p>
    <a href="/" style="display: inline-block; background: #10b981; color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.3s;">Browse Recipes →</a>
</div>
HTML;
    }

    /**
     * Get Contact Us page content with contact form
     */
    private function getContactUsContent(Website $website): string
    {
        $name = $website->name;
        $email = 'contact@' . ($website->domain ?? $website->subdomain . '.example.com');
        $themeSlug = $website->theme?->slug ?? 'recipe';
        
        if ($themeSlug === 'home-decor') {
            return $this->getHomeDecorContactUsContent($name, $email);
        }
        
        return $this->getRecipeContactUsContent($name, $email);
    }

    /**
     * Get Contact Us content for Home Decor theme
     */
    private function getHomeDecorContactUsContent(string $name, string $email): string
    {
        return <<<HTML
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #d4a574 0%, #c4956a 100%); padding: 50px 40px; border-radius: 20px; margin-bottom: 40px; text-align: center; color: white;">
    <h1 style="font-size: 2.5rem; margin-bottom: 15px; font-weight: 700;">Get in Touch</h1>
    <p style="font-size: 1.15rem; opacity: 0.95;">We'd love to hear from you! Share your design questions or just say hello.</p>
</div>

<!-- Main Content Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px;">
    
    <!-- Contact Form -->
    <div style="background: #faf5f0; padding: 40px; border-radius: 20px; border: 1px solid #e7e0d8;">
        <h2 style="color: #44403c; font-size: 1.5rem; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.3rem;">✉️</span> Send Us a Message
        </h2>
        
        <form id="contact-form" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display: block; color: #57534e; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Your Name *</label>
                <input type="text" name="name" required placeholder="Jane Smith" style="width: 100%; padding: 14px 16px; border: 2px solid #e7e0d8; border-radius: 10px; font-size: 1rem; transition: border-color 0.3s; outline: none; box-sizing: border-box; background: white;" onfocus="this.style.borderColor='#c4956a'" onblur="this.style.borderColor='#e7e0d8'">
            </div>
            
            <div>
                <label style="display: block; color: #57534e; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Email Address *</label>
                <input type="email" name="email" required placeholder="jane@example.com" style="width: 100%; padding: 14px 16px; border: 2px solid #e7e0d8; border-radius: 10px; font-size: 1rem; transition: border-color 0.3s; outline: none; box-sizing: border-box; background: white;" onfocus="this.style.borderColor='#c4956a'" onblur="this.style.borderColor='#e7e0d8'">
            </div>
            
            <div>
                <label style="display: block; color: #57534e; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Subject *</label>
                <select name="subject" required style="width: 100%; padding: 14px 16px; border: 2px solid #e7e0d8; border-radius: 10px; font-size: 1rem; background: white; cursor: pointer; outline: none; box-sizing: border-box;">
                    <option value="">Select a topic...</option>
                    <option value="general">General Inquiry</option>
                    <option value="design">Design Advice</option>
                    <option value="diy">DIY Project Help</option>
                    <option value="partnership">Partnership / Collaboration</option>
                    <option value="advertising">Advertising</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; color: #57534e; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Your Message *</label>
                <textarea name="message" required rows="5" placeholder="Tell us about your home decor question or project..." style="width: 100%; padding: 14px 16px; border: 2px solid #e7e0d8; border-radius: 10px; font-size: 1rem; resize: vertical; font-family: inherit; transition: border-color 0.3s; outline: none; box-sizing: border-box; background: white;" onfocus="this.style.borderColor='#c4956a'" onblur="this.style.borderColor='#e7e0d8'"></textarea>
            </div>
            
            <button type="submit" style="background: linear-gradient(135deg, #d4a574 0%, #c4956a 100%); color: white; padding: 16px 32px; border: none; border-radius: 10px; font-size: 1.05rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(196,149,106,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                Send Message →
            </button>
        </form>
        
        <p style="color: #a8a29e; font-size: 0.85rem; margin-top: 15px; text-align: center;">We typically respond within 24-48 hours</p>
    </div>
    
    <!-- Contact Info & Other Ways -->
    <div>
        <!-- Direct Contact -->
        <div style="background: linear-gradient(135deg, #fef7ed 0%, #fed7aa 100%); padding: 30px; border-radius: 16px; margin-bottom: 25px; border: 2px solid #fdba74;">
            <h3 style="color: #c2410c; font-size: 1.2rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span>📧</span> Direct Contact
            </h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: white; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">📬</div>
                    <div>
                        <div style="color: #78716c; font-size: 0.85rem;">Email Us</div>
                        <a href="mailto:{$email}" style="color: #c2410c; font-weight: 600; text-decoration: none;">{$email}</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Response Time -->
        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px; border-radius: 16px; margin-bottom: 25px; border: 2px solid #fcd34d;">
            <h3 style="color: #92400e; font-size: 1.2rem; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <span>⏰</span> Response Time
            </h3>
            <p style="color: #a16207; font-size: 0.95rem; line-height: 1.6;">
                We aim to respond to all inquiries within <strong>24-48 business hours</strong>. For urgent matters, please indicate so in your message subject.
            </p>
        </div>
        
        <!-- Business Inquiries -->
        <div style="background: linear-gradient(135deg, #e8e4df 0%, #d6cfc7 100%); padding: 30px; border-radius: 16px; border: 2px solid #c4b9ad;">
            <h3 style="color: #44403c; font-size: 1.2rem; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <span>🏠</span> Collaboration Ideas
            </h3>
            <p style="color: #57534e; font-size: 0.95rem; line-height: 1.6; margin-bottom: 15px;">
                Interested in working together? We love collaborating with home decor brands, interior designers, and fellow creatives.
            </p>
            <ul style="color: #57534e; font-size: 0.9rem; padding-left: 20px; margin: 0;">
                <li style="margin-bottom: 8px;">Brand Partnerships</li>
                <li style="margin-bottom: 8px;">Product Reviews</li>
                <li style="margin-bottom: 8px;">Room Makeover Features</li>
                <li>Guest Design Posts</li>
            </ul>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: #faf5f0; padding: 50px 40px; border-radius: 20px; margin-bottom: 40px;">
    <h2 style="text-align: center; color: #44403c; font-size: 1.8rem; margin-bottom: 35px;">Frequently Asked Questions</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; max-width: 900px; margin: 0 auto;">
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #44403c; font-size: 1rem; margin-bottom: 10px;">Can you help with my room design?</h4>
            <p style="color: #78716c; font-size: 0.9rem; margin: 0;">While we don't offer personal design services, we're happy to point you to relevant articles and resources that might help!</p>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #44403c; font-size: 1rem; margin-bottom: 10px;">Do you feature products?</h4>
            <p style="color: #78716c; font-size: 0.9rem; margin: 0;">Yes! If you have a home decor product you'd like us to review or feature, please reach out with details.</p>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #44403c; font-size: 1rem; margin-bottom: 10px;">Can I contribute design ideas?</h4>
            <p style="color: #78716c; font-size: 0.9rem; margin: 0;">Absolutely! We love featuring room makeovers and DIY projects from our community. Share your project with us!</p>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #44403c; font-size: 1rem; margin-bottom: 10px;">Where do you find inspiration?</h4>
            <p style="color: #78716c; font-size: 0.9rem; margin: 0;">Everywhere! From nature and travel to vintage finds and modern design trends. Follow us for daily inspiration.</p>
        </div>
    </div>
</div>

<!-- Social Follow -->
<div style="background: linear-gradient(135deg, #44403c 0%, #57534e 100%); padding: 40px; border-radius: 20px; text-align: center; color: white;">
    <h3 style="font-size: 1.4rem; margin-bottom: 10px;">Connect With Us</h3>
    <p style="opacity: 0.8; margin-bottom: 20px;">Follow us for daily design inspiration and behind-the-scenes peeks at beautiful homes.</p>
    <div style="display: flex; justify-content: center; gap: 15px;">
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">📘</a>
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">📸</a>
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">📌</a>
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">🏠</a>
    </div>
</div>
HTML;
    }

    /**
     * Get Contact Us content for Recipe theme
     */
    private function getRecipeContactUsContent(string $name, string $email): string
    {
        return <<<HTML
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 50px 40px; border-radius: 20px; margin-bottom: 40px; text-align: center; color: white;">
    <h1 style="font-size: 2.5rem; margin-bottom: 15px; font-weight: 700;">Get in Touch</h1>
    <p style="font-size: 1.15rem; opacity: 0.95;">We'd love to hear from you! Share your recipes, questions, or just say hello.</p>
</div>

<!-- Main Content Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px;">
    
    <!-- Contact Form -->
    <div style="background: #f8fafc; padding: 40px; border-radius: 20px; border: 1px solid #e2e8f0;">
        <h2 style="color: #1e293b; font-size: 1.5rem; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.3rem;">✉️</span> Send Us a Message
        </h2>
        
        <form id="contact-form" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display: block; color: #475569; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Your Name *</label>
                <input type="text" name="name" required placeholder="John Doe" style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; transition: border-color 0.3s; outline: none; box-sizing: border-box;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            
            <div>
                <label style="display: block; color: #475569; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Email Address *</label>
                <input type="email" name="email" required placeholder="john@example.com" style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; transition: border-color 0.3s; outline: none; box-sizing: border-box;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            
            <div>
                <label style="display: block; color: #475569; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Subject *</label>
                <select name="subject" required style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; background: white; cursor: pointer; outline: none; box-sizing: border-box;">
                    <option value="">Select a topic...</option>
                    <option value="general">General Inquiry</option>
                    <option value="recipe">Recipe Question</option>
                    <option value="suggestion">Recipe Suggestion</option>
                    <option value="partnership">Partnership / Collaboration</option>
                    <option value="advertising">Advertising</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; color: #475569; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Your Message *</label>
                <textarea name="message" required rows="5" placeholder="Tell us about your cooking question or recipe idea..." style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; resize: vertical; font-family: inherit; transition: border-color 0.3s; outline: none; box-sizing: border-box;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
            </div>
            
            <button type="submit" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 16px 32px; border: none; border-radius: 10px; font-size: 1.05rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(16,185,129,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                Send Message →
            </button>
        </form>
        
        <p style="color: #94a3b8; font-size: 0.85rem; margin-top: 15px; text-align: center;">We typically respond within 24-48 hours</p>
    </div>
    
    <!-- Contact Info & Other Ways -->
    <div>
        <!-- Direct Contact -->
        <div style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); padding: 30px; border-radius: 16px; margin-bottom: 25px; border: 2px solid #99f6e4;">
            <h3 style="color: #0f766e; font-size: 1.2rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span>📧</span> Direct Contact
            </h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: white; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">📬</div>
                    <div>
                        <div style="color: #64748b; font-size: 0.85rem;">Email Us</div>
                        <a href="mailto:{$email}" style="color: #0f766e; font-weight: 600; text-decoration: none;">{$email}</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Response Time -->
        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px; border-radius: 16px; margin-bottom: 25px; border: 2px solid #fcd34d;">
            <h3 style="color: #92400e; font-size: 1.2rem; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <span>⏰</span> Response Time
            </h3>
            <p style="color: #a16207; font-size: 0.95rem; line-height: 1.6;">
                We aim to respond to all inquiries within <strong>24-48 business hours</strong>. For urgent matters, please indicate so in your message subject.
            </p>
        </div>
        
        <!-- Business Inquiries -->
        <div style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); padding: 30px; border-radius: 16px; border: 2px solid #86efac;">
            <h3 style="color: #166534; font-size: 1.2rem; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <span>🍳</span> Recipe Collaboration
            </h3>
            <p style="color: #15803d; font-size: 0.95rem; line-height: 1.6; margin-bottom: 15px;">
                Are you a fellow food lover, chef, or food brand? We'd love to collaborate on recipes and content!
            </p>
            <ul style="color: #15803d; font-size: 0.9rem; padding-left: 20px; margin: 0;">
                <li style="margin-bottom: 8px;">Recipe Development</li>
                <li style="margin-bottom: 8px;">Product Features</li>
                <li style="margin-bottom: 8px;">Guest Chef Posts</li>
                <li>Cookbook Reviews</li>
            </ul>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: #f8fafc; padding: 50px 40px; border-radius: 20px; margin-bottom: 40px;">
    <h2 style="text-align: center; color: #1e293b; font-size: 1.8rem; margin-bottom: 35px;">Frequently Asked Questions</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; max-width: 900px; margin: 0 auto;">
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #1e293b; font-size: 1rem; margin-bottom: 10px;">Can I submit my own recipe?</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Absolutely! We love featuring recipes from our community. Send us your recipe with photos and we'll review it!</p>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #1e293b; font-size: 1rem; margin-bottom: 10px;">Can I request a specific recipe?</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Yes! If there's a dish you'd love to see us make, let us know and we'll try to add it to our list.</p>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #1e293b; font-size: 1rem; margin-bottom: 10px;">Do you do sponsored content?</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">We work with select brands that align with our values. Contact us for our media kit and collaboration options.</p>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #1e293b; font-size: 1rem; margin-bottom: 10px;">How do I fix a recipe issue?</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">If you had trouble with one of our recipes, reach out! We'll help troubleshoot and update the recipe if needed.</p>
        </div>
    </div>
</div>

<!-- Social Follow -->
<div style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 40px; border-radius: 20px; text-align: center; color: white;">
    <h3 style="font-size: 1.4rem; margin-bottom: 10px;">Connect With Us</h3>
    <p style="opacity: 0.8; margin-bottom: 20px;">Follow us for daily recipe inspiration and behind-the-scenes kitchen moments.</p>
    <div style="display: flex; justify-content: center; gap: 15px;">
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">📘</a>
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">📸</a>
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">📌</a>
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">🍳</a>
    </div>
</div>
HTML;
    }

    /**
     * Get Privacy Policy page content
     */
    private function getPrivacyPolicyContent(Website $website): string
    {
        $name = $website->name;
        $date = now()->format('F j, Y');
        
        return <<<HTML
<h2>Privacy Policy</h2>
<p><strong>Last updated:</strong> {$date}</p>

<p>At {$name}, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.</p>

<h3>Information We Collect</h3>
<p>We may collect information about you in a variety of ways, including:</p>
<ul>
    <li><strong>Personal Data:</strong> Voluntarily provided information such as your name and email address when you subscribe to our newsletter or contact us.</li>
    <li><strong>Derivative Data:</strong> Information our servers automatically collect when you access the site, such as your IP address, browser type, and pages visited.</li>
    <li><strong>Cookies:</strong> We may use cookies and similar tracking technologies to enhance your experience on our site.</li>
</ul>

<h3>Use of Your Information</h3>
<p>We use the information we collect to:</p>
<ul>
    <li>Provide and maintain our website</li>
    <li>Improve user experience</li>
    <li>Send newsletters and updates (with your consent)</li>
    <li>Respond to your inquiries</li>
</ul>

<h3>Disclosure of Your Information</h3>
<p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as required by law or to protect our rights.</p>

<h3>Security of Your Information</h3>
<p>We implement appropriate technical and organizational measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.</p>

<h3>Contact Us</h3>
<p>If you have questions about this Privacy Policy, please contact us.</p>
HTML;
    }

    /**
     * Edit Website Form
     */
    public function websitesEdit(Website $website)
    {
        $this->authorize('update', $website);

        $user = Auth::user();
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        return Inertia::render('Organization/Websites/Edit', [
            'website' => $website,
            'websites' => $websites,
        ]);
    }

    /**
     * Update Website
     */
    public function websitesUpdate(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255|unique:websites,subdomain,' . $website->id,
            'description' => 'nullable|string',
            'hbagency_script' => 'nullable|string',
            'ads_txt' => 'nullable|string',
            'hbagency_placements' => 'nullable|array',
            'hbagency_placements.in_image' => 'nullable|string|max:50',
            'hbagency_placements.in_article_1' => 'nullable|string|max:50',
            'hbagency_placements.in_article_2' => 'nullable|string|max:50',
            'hbagency_placements.sidebar' => 'nullable|string|max:50',
            'hbagency_placements.sticky_footer' => 'nullable|string|max:50',
            'hbagency_placements.top_banner' => 'nullable|string|max:50',
            'hbagency_placements.bottom_banner' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,ico|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoFilename = Str::uuid() . '.' . $logoFile->getClientOriginalExtension();
            $logoDirectory = public_path('uploads/images/website');
            
            if (!\Illuminate\Support\Facades\File::isDirectory($logoDirectory)) {
                \Illuminate\Support\Facades\File::makeDirectory($logoDirectory, 0755, true);
            }
            
            $logoFile->move($logoDirectory, $logoFilename);
            $validated['logo'] = 'uploads/images/website/' . $logoFilename;
            
            // Delete old logo if exists
            if ($website->logo && File::exists(public_path($website->logo))) {
                File::delete(public_path($website->logo));
            }
        } else {
            unset($validated['logo']);
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $faviconFile = $request->file('favicon');
            $faviconFilename = Str::uuid() . '.' . $faviconFile->getClientOriginalExtension();
            $faviconDirectory = public_path('uploads/images/website');
            
            if (!File::isDirectory($faviconDirectory)) {
                File::makeDirectory($faviconDirectory, 0755, true);
            }
            
            $faviconFile->move($faviconDirectory, $faviconFilename);
            $validated['favicon'] = 'uploads/images/website/' . $faviconFilename;
            
            // Delete old favicon if exists
            if ($website->favicon && File::exists(public_path($website->favicon))) {
                File::delete(public_path($website->favicon));
            }
        } else {
            unset($validated['favicon']);
        }

        $website->update($validated);

        return redirect()->route('organization.websites.index')
            ->with('success', 'Website updated successfully!');
    }

    /**
     * Delete Website
     */
    public function websitesDestroy(Website $website)
    {
        $this->authorize('delete', $website);

        $website->delete();

        return redirect()->route('organization.websites.index')
            ->with('success', 'Website deleted successfully!');
    }

    /**
     * Global Articles Theme Selection
     */
    public function globalArticlesThemeSelect()
    {
        $user = Auth::user();
        
        // Fetch themes based on user role
        // Superadmin and Website Owners see all themes, regular users see only public themes
        $themesQuery = Theme::query();
        
        if (!$user->isSuperAdmin() && !$user->isWebsiteOwner()) {
            $themesQuery->where('is_public', true);
        }
        
        $themes = $themesQuery->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'is_public']);

        return Inertia::render('Organization/GlobalArticlesThemeSelect', [
            'themes' => $themes,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    /**
     * Global Articles Index
     */
    public function globalArticlesIndex(Request $request)
    {
        $user = Auth::user();
        
        // Get theme filter from query parameter
        $themeSlug = $request->query('theme');
        
        // If no theme selected, redirect to theme selection
        if (!$themeSlug) {
            return redirect()->route('organization.global-articles.theme-select');
        }
        
        // Get the theme
        $theme = \App\Models\Theme::where('slug', $themeSlug)->first();
        if (!$theme) {
            return redirect()->route('organization.global-articles.theme-select')
                ->withErrors(['error' => 'Invalid theme selected']);
        }
        
        // Get websites based on user role and filtered by theme
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->where('theme_id', $theme->id)
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/GlobalArticles', [
            'websites' => $websites,
            'hasApiKey' => !empty($user->openai_api_key),
            'defaultTone' => $user->ai_default_tone ?? 'conversational',
            'selectedTheme' => $themeSlug,
            'themeName' => $theme->name,
        ]);
    }

    /**
     * Generate Global AI Article
     */
    public function globalArticlesGenerate(Request $request)
    {
        $user = Auth::user();

        if (empty($user->openai_api_key)) {
            return back()->withErrors(['error' => 'Please configure your OpenAI API key in Global Settings first.']);
        }

        $validated = $request->validate([
            'topic' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:500',
            'tone' => 'nullable|string|in:professional,casual,friendly,formal,conversational',
            'length' => 'nullable|string|in:short,medium,long',
            'keywords' => 'nullable|string',
            'ingredients' => 'nullable|string|max:2000',
            'featured_images' => 'nullable|array',
            'auto_publish' => 'boolean',
            'website_ids' => 'required|array|min:1',
            'website_ids.*' => 'exists:websites,id',
            'article_type' => 'nullable|in:recipe,article',
            'theme' => 'nullable|string|in:recipe,home-decor,crochet',
        ]);

        // Ensure either topic or source_url is provided
        if (empty($validated['topic']) && empty($validated['source_url'])) {
            return back()->withErrors([
                'error' => 'Please provide either an article topic or a source URL.'
            ]);
        }

        // For crochet theme with source_url, use URL as topic temporarily (will be replaced after fetching)
        if (!empty($validated['source_url'])) {
            $validated['topic'] = $validated['source_url'];
        }

        // Replace hyphens with spaces in the topic (title) - only if it's not a URL
        if (empty($validated['source_url'])) {
            $validated['topic'] = str_replace('-', ' ', $validated['topic']);
        }

        $websiteIds = $validated['website_ids'];
        $generationJobIds = [];

        // CRITICAL: Check for duplicate pending/processing jobs with the same topic
        // This prevents creating duplicate articles if the user submits the form multiple times
        $recentJob = ArticleGenerationJob::where('user_id', $user->id)
            ->where('topic', $validated['topic'])
            ->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '>=', now()->subMinutes(2)) // Check last 2 minutes
            ->first();

        if ($recentJob) {
            return back()->withErrors([
                'error' => 'A job for this topic is already being processed. Please wait for it to complete or try again in a few minutes.'
            ]);
        }

        // Verify websites belong to user and create tracking jobs
        foreach ($websiteIds as $websiteId) {
            $website = Website::where('id', $websiteId)->where('user_id', $user->id)->first();
            if (!$website) {
                continue; // Or abort/error
            }

            $trackingJob = ArticleGenerationJob::create([
                'user_id' => $user->id,
                'website_id' => $website->id,
                'topic' => $validated['topic'],
                'status' => 'pending',
            ]);

            $generationJobIds[$websiteId] = $trackingJob->id;
        }

        if (empty($generationJobIds)) {
            return back()->withErrors(['error' => 'No valid websites selected.']);
        }

        // Filter out empty images
        $featuredImages = array_filter($validated['featured_images'] ?? [], function($img) {
            // For home-decor theme, images are {url, title} objects
            // For other themes, images are just URL strings
            if (is_array($img)) {
                return !empty($img['url']);
            }
            return !empty($img);
        });

        // Check the user's generation mode
        $generationMode = $user->article_generation_mode ?? 'full_ai';

        if ($generationMode === 'hybrid_rewrite') {
            // HYBRID MODE: Dispatch ONE job with ALL websites
            // This generates 1 master article via AI + local variations for other websites
            // Cost: 1 API call total (regardless of website count)
            
            Log::info('About to dispatch hybrid job', [
                'topic' => $validated['topic'],
                'source_url' => $validated['source_url'] ?? null,
                'theme' => $validated['theme'] ?? null,
                'website_count' => count($generationJobIds)
            ]);
            
            try {
                GenerateGlobalAIArticleJob::dispatch(
                    $generationJobIds,
                    array_keys($generationJobIds),
                    $user->id,
                    $validated['topic'],
                    $validated['tone'] ?? $user->ai_default_tone ?? 'conversational',
                    $validated['length'] ?? 'medium',
                    $validated['keywords'] ?? '',
                    $validated['ingredients'] ?? '',
                    $validated['auto_publish'] ?? false,
                    $featuredImages,
                    $validated['article_type'] ?? 'recipe',
                    null,
                    $validated['theme'] ?? null,
                    $validated['source_url'] ?? null
                );
                
                Log::info('Hybrid job dispatched successfully');
            } catch (\Exception $e) {
                Log::error('Failed to dispatch hybrid job', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->withErrors(['error' => 'Failed to dispatch job: ' . $e->getMessage()]);
            }

            return redirect()->back()->with('success', 'Hybrid mode: Generating 1 master article + ' . (count($generationJobIds) - 1) . ' local variations for ' . count($generationJobIds) . ' websites. Only 1 API call will be made!');
        } else {
            // FULL AI MODE: Dispatch individual jobs for each website
            // Each website gets a unique AI-generated article
            // Cost: 1 API call per website
            $index = 0;
            foreach ($generationJobIds as $websiteId => $trackingJobId) {
                GenerateGlobalAIArticleJob::dispatch(
                    [$websiteId => $trackingJobId],
                    [$websiteId],
                    $user->id,
                    $validated['topic'],
                    $validated['tone'] ?? $user->ai_default_tone ?? 'conversational',
                    $validated['length'] ?? 'medium',
                    $validated['keywords'] ?? '',
                    $validated['ingredients'] ?? '',
                    $validated['auto_publish'] ?? false,
                    $featuredImages,
                    $validated['article_type'] ?? 'recipe',
                    $index++,
                    $validated['theme'] ?? null,
                    $validated['source_url'] ?? null
                );
            }

            return redirect()->back()->with('success', 'Full AI mode: Generating ' . count($generationJobIds) . ' unique articles. Each website will get a unique AI-generated article.');
        }
    }

    /**
     * Global Subscribers Index - Shows all subscribers from all websites
     */
    public function globalSubscribersIndex()
    {
        $user = Auth::user();
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websites = $websitesQuery->withCount(['articles', 'categories'])->get();

        $websiteIds = $websites->pluck('id');

        // Get all subscribers from all user's websites
        $subscribers = Subscriber::whereIn('website_id', $websiteIds)
            ->with(['website:id,name,subdomain,domain', 'website.user:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Stats
        $stats = [
            'total' => Subscriber::whereIn('website_id', $websiteIds)->count(),
            'active' => Subscriber::whereIn('website_id', $websiteIds)->where('is_active', true)->count(),
            'this_month' => Subscriber::whereIn('website_id', $websiteIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return Inertia::render('Organization/GlobalSubscribers', [
            'subscribers' => $subscribers,
            'stats' => $stats,
            'websites' => $websites,
        ]);
    }

    /**
     * Export global subscribers as CSV
     */
    public function globalSubscribersExport()
    {
        $user = Auth::user();
        
        // Get websites based on user role
        $websitesQuery = $user->canSeeAllWebsites() 
            ? Website::query()
            : Website::where('user_id', $user->id);
        
        $websiteIds = $websitesQuery->pluck('id');

        $subscribers = Subscriber::whereIn('website_id', $websiteIds)
            ->with(['website:id,name,subdomain,domain', 'website.user:id,name'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'global-subscribers-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($subscribers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Email', 'Name', 'Website', 'Website Owner', 'Source', 'Subscribed At', 'Created At']);

            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber->email,
                    $subscriber->name ?? '',
                    $subscriber->website->name ?? 'Unknown',
                    $subscriber->website->user->name ?? 'Unknown',
                    $subscriber->source ?? 'website',
                    $subscriber->subscribed_at?->format('Y-m-d H:i:s'),
                    $subscriber->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

