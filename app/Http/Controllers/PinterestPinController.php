<?php

namespace App\Http\Controllers;

use App\Models\PinterestPin;
use App\Models\Website;
use App\Models\Article;
use App\Services\PinterestDesignService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class PinterestPinController extends Controller
{
    use AuthorizesRequests;

    private function resolveFrameDesign(Website $website, ?string $requested): string
    {
        $themeSlug = $website->themeSlug();
        $default = ($themeSlug === 'crochet') ? 'crochet' : 'simple_center';
        $design = $requested ?: $default;

        if ($design === 'crochet' && $themeSlug !== 'crochet') {
            return $default;
        }

        $allowed = array_keys(PinterestDesignService::getFrameDesigns());
        if (!in_array($design, $allowed, true)) {
            return $default;
        }

        return $design;
    }

    /**
     * Get common data for views (websites list and current website)
     */
    private function getCommonData(Website $website): array
    {
        return [
            'currentWebsite' => $website,
            'websites' => auth()->user()->accessibleWebsitesQuery()
                ->withCount(['articles', 'categories'])
                ->get(),
            'websiteThemeSlug' => $website->themeSlug(),
        ];
    }

    /**
     * Display the Pinterest pins listing.
     */
    public function index(Website $website): Response
    {
        $this->authorize('view', $website);

        $pins = PinterestPin::where('website_id', $website->id)
            ->where('status', '!=', 'missing_design')
            ->with(['article'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get missing design pins
        $missingDesignPins = PinterestPin::where('website_id', $website->id)
            ->where('status', 'missing_design')
            ->with(['article'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get articles that have featured_image but don't have a Pinterest pin
        $articleIdsWithPins = PinterestPin::where('website_id', $website->id)
            ->whereNotNull('article_id')
            ->pluck('article_id')
            ->toArray();

        $articlesWithoutPins = Article::where('website_id', $website->id)
            ->whereNotNull('featured_image')
            ->where('featured_image', '!=', '')
            ->whereNotIn('id', $articleIdsWithPins)
            ->with('website')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'website_id', 'slug', 'title', 'featured_image', 'status', 'created_at']);

        return Inertia::render('SuperAdmin/PinterestPins/Index', array_merge(
            $this->getCommonData($website),
            [
                'pins' => $pins,
                'missingDesignPins' => $missingDesignPins,
                'articlesWithoutPins' => $articlesWithoutPins,
            ]
        ));
    }

    /**
     * Show form to create a new Pinterest pin.
     */
    public function create(Website $website): Response
    {
        $this->authorize('view', $website);

        $articles = Article::where('website_id', $website->id)
            ->where('status', 'published')
            ->whereNotNull('featured_image')
            ->with('website')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'website_id', 'slug', 'title', 'featured_image', 'secondary_image', 'meta_description', 'excerpt']);

        return Inertia::render('SuperAdmin/PinterestPins/Create', array_merge(
            $this->getCommonData($website),
            [
                'articles' => $articles,
            ]
        ));
    }

    /**
     * Store a new Pinterest pin.
     */
    public function store(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'headline_text' => 'required|string|max:100',
            'subheadline_text' => 'required|string|max:100',
            'headline_color' => 'nullable|string|max:20',
            'subheadline_color' => 'nullable|string|max:20',
            'headline_font' => 'nullable|string|max:50',
            'subheadline_font' => 'nullable|string|max:50',
            'headline_font_size' => 'nullable|integer|min:10|max:100',
            'subheadline_font_size' => 'nullable|integer|min:10|max:100',
            'overlay_color' => 'nullable|string|max:20',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'frame_design' => 'nullable|string|max:50',
            'domain_name' => 'nullable|string|max:100',
        ]);

        // Verify article belongs to website
        $article = Article::findOrFail($validated['article_id']);
        if ($article->website_id !== $website->id) {
            abort(403, 'Article does not belong to this website.');
        }

        if (!$article->featured_image) {
            return back()->withErrors(['error' => 'Article must have a featured image.']);
        }

        try {
            // Create the pin
            $pin = PinterestPin::create([
                'website_id' => $website->id,
                'article_id' => $article->id,
                'user_id' => auth()->id(),
                'title' => $article->title,
                'description' => $article->meta_description ?? $article->excerpt,
                'link' => $article->url,
                'top_image' => $article->featured_image,
                'bottom_image' => $article->secondary_image ?? $article->featured_image,
                'headline_text' => $validated['headline_text'],
                'subheadline_text' => $validated['subheadline_text'],
                'headline_color' => $validated['headline_color'] ?? '#ffffff',
                'subheadline_color' => $validated['subheadline_color'] ?? '#d4a574',
                'headline_font' => $validated['headline_font'] ?? 'sans-serif',
                'subheadline_font' => $validated['subheadline_font'] ?? 'script',
                'headline_font_size' => $validated['headline_font_size'] ?? 28,
                'subheadline_font_size' => $validated['subheadline_font_size'] ?? 22,
                'overlay_color' => $validated['overlay_color'] ?? '#000000',
                'overlay_opacity' => $validated['overlay_opacity'] ?? 70,
                'frame_design' => $this->resolveFrameDesign($website, $validated['frame_design'] ?? null),
                'frame_settings' => [
                    'domain_name' => $validated['domain_name'] ?? ($website->domain ?: ($website->slug ? $website->slug . '.com' : ''))
                ],
                'status' => 'pending',
            ]);

            // Generate the pin image
            $service = new PinterestDesignService();
            $service->generatePinImage($pin);

            return redirect()->route('superadmin.pinterest-pins.index', ['website' => $website->id])
                ->with('success', 'Pinterest pin created successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to create Pinterest pin', [
                'error' => $e->getMessage(),
                'article_id' => $article->id
            ]);
            
            return back()->withErrors(['error' => 'Failed to create Pinterest pin: ' . $e->getMessage()]);
        }
    }

    /**
     * Show a single Pinterest pin.
     */
    public function show(Website $website, PinterestPin $pin): Response
    {
        $this->authorize('view', $website);

        if ($pin->website_id !== $website->id) {
            abort(404);
        }

        $pin->load('article');

        return Inertia::render('SuperAdmin/PinterestPins/Show', array_merge(
            $this->getCommonData($website),
            [
                'pin' => $pin,
            ]
        ));
    }

    /**
     * Regenerate a Pinterest pin image.
     */
    public function regenerate(Request $request, Website $website, PinterestPin $pin)
    {
        $this->authorize('view', $website);

        if ($pin->website_id !== $website->id) {
            abort(404);
        }

        // Update pin settings if provided
        $validated = $request->validate([
            'headline_text' => 'nullable|string|max:100',
            'subheadline_text' => 'nullable|string|max:100',
            'headline_color' => 'nullable|string|max:20',
            'subheadline_color' => 'nullable|string|max:20',
            'headline_font' => 'nullable|string|max:50',
            'subheadline_font' => 'nullable|string|max:50',
            'headline_font_size' => 'nullable|integer|min:10|max:100',
            'subheadline_font_size' => 'nullable|integer|min:10|max:100',
            'overlay_color' => 'nullable|string|max:20',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'frame_design' => 'nullable|string|max:50',
            'domain_name' => 'nullable|string|max:100',
            'use_ai_headlines' => 'nullable|boolean',
        ]);

        try {
            $updateData = array_filter($validated, function($key) {
                return !in_array($key, ['domain_name', 'use_ai_headlines']);
            }, ARRAY_FILTER_USE_KEY);

            // If AI headlines requested and we have an article
            if ($request->boolean('use_ai_headlines') && $pin->article) {
                $service = new PinterestDesignService();
                $headlines = $service->generateAIHeadlines($pin->article);
                if ($headlines) {
                    $updateData['headline_text'] = $headlines['headline'];
                    $updateData['subheadline_text'] = $headlines['subheadline'];
                }
            }

            // Prepare frame settings
            $frameSettings = $pin->frame_settings ?? [];
            if ($request->has('domain_name')) {
                $frameSettings['domain_name'] = $validated['domain_name'];
            }

            // Update pin with new settings
            $pin->update(array_merge(
                $updateData,
                ['frame_settings' => $frameSettings]
            ));
            $pin->update(['status' => 'pending']);

            // Regenerate the image
            $service = new PinterestDesignService();
            $service->generatePinImage($pin);

            return back()->with('success', 'Pinterest pin regenerated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to regenerate Pinterest pin', [
                'error' => $e->getMessage(),
                'pin_id' => $pin->id
            ]);
            
            return back()->withErrors(['error' => 'Failed to regenerate: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a Pinterest pin.
     */
    public function destroy(Website $website, PinterestPin $pin)
    {
        $this->authorize('view', $website);

        if ($pin->website_id !== $website->id) {
            abort(404);
        }

        try {
            // Delete the generated image file
            if ($pin->generated_image && file_exists(public_path($pin->generated_image))) {
                unlink(public_path($pin->generated_image));
            }

            $pin->delete();

            return redirect()->route('superadmin.pinterest-pins.index', ['website' => $website->id])
                ->with('success', 'Pinterest pin deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to delete Pinterest pin', [
                'error' => $e->getMessage(),
                'pin_id' => $pin->id
            ]);
            
            return back()->withErrors(['error' => 'Failed to delete pin.']);
        }
    }

    /**
     * Bulk delete Pinterest pins.
     */
    public function bulkDelete(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'pin_ids' => 'required|array|min:1',
            'pin_ids.*' => 'exists:pinterest_pins,id',
        ]);

        $deleted = 0;
        $errors = [];

        foreach ($validated['pin_ids'] as $pinId) {
            $pin = PinterestPin::find($pinId);
            
            if (!$pin || $pin->website_id !== $website->id) {
                continue;
            }

            try {
                // Delete the generated image file
                if ($pin->generated_image && file_exists(public_path($pin->generated_image))) {
                    unlink(public_path($pin->generated_image));
                }

                $pin->delete();
                $deleted++;
            } catch (\Exception $e) {
                $errors[] = "Failed to delete '{$pin->title}': " . $e->getMessage();
            }
        }

        $message = "Successfully deleted {$deleted} Pinterest pins.";
        if (!empty($errors)) {
            $message .= ' Some errors occurred: ' . implode('; ', array_slice($errors, 0, 3));
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk generate Pinterest pins for articles without pins.
     */
    public function bulkGenerate(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'article_ids' => 'required|array|min:1',
            'article_ids.*' => 'exists:articles,id',
            'headline_color' => 'nullable|string|max:20',
            'subheadline_color' => 'nullable|string|max:20',
            'headline_font' => 'nullable|string|max:50',
            'subheadline_font' => 'nullable|string|max:50',
            'headline_font_size' => 'nullable|integer|min:10|max:100',
            'subheadline_font_size' => 'nullable|integer|min:10|max:100',
            'overlay_color' => 'nullable|string|max:20',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'frame_design' => 'nullable|string|max:50',
            'domain_name' => 'nullable|string|max:100',
            'headline_text' => 'nullable|string|max:100',
            'subheadline_text' => 'nullable|string|max:100',
            'use_ai_headlines' => 'nullable|boolean',
        ]);

        $generated = 0;
        $errors = [];
        $service = new PinterestDesignService();
        $useAi = $request->boolean('use_ai_headlines');
        $user = auth()->user();

        foreach ($validated['article_ids'] as $articleId) {
            $article = Article::find($articleId);
            
            if (!$article || $article->website_id !== $website->id) {
                continue;
            }

            if (!$article->featured_image) {
                $errors[] = "Article '{$article->title}' has no featured image";
                continue;
            }

            try {
                // Determine headline/subheadline
                $headline = '';
                $subheadline = '';

                if ($useAi && !empty($user->openai_api_key)) {
                    $headlines = $service->generateAIHeadlines($article, $user);
                    if ($headlines) {
                        $headline = $headlines['headline'];
                        $subheadline = $headlines['subheadline'];
                    }
                }

                // Fallback to simple split if AI failed or wasn't requested
                if (empty($headline)) {
                    $titleWords = explode(' ', $article->title);
                    $midPoint = ceil(count($titleWords) / 2);
                    $headline = implode(' ', array_slice($titleWords, 0, $midPoint));
                    $subheadline = implode(' ', array_slice($titleWords, $midPoint));
                }

                // Create the pin record
                $pin = PinterestPin::create([
                    'website_id' => $website->id,
                    'article_id' => $article->id,
                    'user_id' => auth()->id(),
                    'title' => $article->title,
                    'description' => $article->meta_description ?? $article->excerpt,
                    'link' => $article->url,
                    'top_image' => $article->featured_image,
                    'bottom_image' => $article->secondary_image ?? $article->featured_image,
                    'headline_text' => $headline,
                    'subheadline_text' => $subheadline,
                    'headline_color' => $validated['headline_color'] ?? '#ffffff',
                    'subheadline_color' => $validated['subheadline_color'] ?? '#d4a574',
                    'headline_font' => $validated['headline_font'] ?? 'sans-serif',
                    'subheadline_font' => $validated['subheadline_font'] ?? 'georgia',
                    'headline_font_size' => $validated['headline_font_size'] ?? 28,
                    'subheadline_font_size' => $validated['subheadline_font_size'] ?? 22,
                    'overlay_color' => $validated['overlay_color'] ?? '#000000',
                    'overlay_opacity' => $validated['overlay_opacity'] ?? 70,
                    'frame_design' => $this->resolveFrameDesign($website, $validated['frame_design'] ?? null),
                    'frame_settings' => [
                        'domain_name' => $validated['domain_name'] ?? ($website->domain ?: ($website->slug ? $website->slug . '.com' : ''))
                    ],
                    'status' => 'pending',
                ]);

                // Generate the image
                $service = new PinterestDesignService();
                $service->generatePinImage($pin);
                
                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Failed for '{$article->title}': " . $e->getMessage();
            }
        }

        $message = "Generated {$generated} Pinterest pins.";
        if (!empty($errors)) {
            $message .= ' Some errors occurred: ' . implode('; ', array_slice($errors, 0, 3));
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk update and generate Pinterest pins.
     */
    public function bulkUpdateDesigns(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'pin_ids' => 'required|array|min:1',
            'pin_ids.*' => 'exists:pinterest_pins,id',
            'headline_color' => 'nullable|string|max:20',
            'subheadline_color' => 'nullable|string|max:20',
            'headline_font' => 'nullable|string|max:50',
            'subheadline_font' => 'nullable|string|max:50',
            'headline_font_size' => 'nullable|integer|min:10|max:100',
            'subheadline_font_size' => 'nullable|integer|min:10|max:100',
            'overlay_color' => 'nullable|string|max:20',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'frame_design' => 'nullable|string|max:50',
            'domain_name' => 'nullable|string|max:100',
            'headline_text' => 'nullable|string|max:100',
            'subheadline_text' => 'nullable|string|max:100',
            'use_ai_headlines' => 'nullable|boolean',
        ]);

        $generated = 0;
        $errors = [];
        $service = new PinterestDesignService();
        $useAi = $request->boolean('use_ai_headlines');
        $user = auth()->user();

        foreach ($validated['pin_ids'] as $pinId) {
            $pin = PinterestPin::find($pinId);
            
            if (!$pin || $pin->website_id !== $website->id) {
                continue;
            }

            try {
                // Prepare update data
                $updateData = array_filter($validated, function($key) {
                    return !in_array($key, ['pin_ids', 'domain_name', 'use_ai_headlines']);
                }, ARRAY_FILTER_USE_KEY);

                // If AI headlines requested and we have an article
                if ($useAi && $pin->article && !empty($user->openai_api_key)) {
                    $headlines = $service->generateAIHeadlines($pin->article, $user);
                    if ($headlines) {
                        $updateData['headline_text'] = $headlines['headline'];
                        $updateData['subheadline_text'] = $headlines['subheadline'];
                    }
                }

                // Prepare frame settings
                $frameSettings = $pin->frame_settings ?? [];
                if ($request->has('domain_name')) {
                    $frameSettings['domain_name'] = $validated['domain_name'];
                }

                // Update pin with new settings
                $pin->update(array_merge(
                    $updateData,
                    [
                        'frame_settings' => $frameSettings,
                        'status' => 'pending'
                    ]
                ));

                // Generate the image
                $service->generatePinImage($pin);
                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Failed for '{$pin->title}': " . $e->getMessage();
            }
        }

        $message = "Successfully updated and generated {$generated} Pinterest pins.";
        if (!empty($errors)) {
            $message .= ' Some errors occurred: ' . implode('; ', array_slice($errors, 0, 3));
        }

        return back()->with('success', $message);
    }

    /**
     * Download a Pinterest pin image.
     */
    public function download(Website $website, PinterestPin $pin)
    {
        $this->authorize('view', $website);

        if ($pin->website_id !== $website->id) {
            abort(404);
        }

        if (!$pin->generated_image || !file_exists(public_path($pin->generated_image))) {
            abort(404, 'Pin image not found');
        }

        $filename = 'pinterest-' . \Illuminate\Support\Str::slug($pin->title) . '.png';
        
        return response()->download(public_path($pin->generated_image), $filename);
    }

    /**
     * Bulk download Pinterest pin images as a ZIP file.
     */
    public function bulkDownload(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'pin_ids' => 'required|array|min:1',
            'pin_ids.*' => 'exists:pinterest_pins,id',
        ]);

        $pins = PinterestPin::whereIn('id', $validated['pin_ids'])
            ->where('website_id', $website->id)
            ->whereNotNull('generated_image')
            ->get();

        if ($pins->isEmpty()) {
            return back()->withErrors(['error' => 'No generated pin images found for selection.']);
        }

        $zip = new \ZipArchive();
        $zipFilename = 'pinterest-pins-' . ($website->slug ?? 'export') . '-' . time() . '.zip';
        
        // Ensure storage path exists
        $zipDir = storage_path('app/public/temp_exports');
        if (!file_exists($zipDir)) {
            mkdir($zipDir, 0755, true);
        }
        
        $zipPath = $zipDir . '/' . $zipFilename;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $nameCounts = [];
            foreach ($pins as $pin) {
                $filePath = public_path($pin->generated_image);
                if (file_exists($filePath)) {
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    $baseName = \Illuminate\Support\Str::slug($pin->title);
                    
                    // Handle duplicate names by adding a counter only if needed
                    if (isset($nameCounts[$baseName])) {
                        $nameCounts[$baseName]++;
                        $nameInZip = $baseName . '-' . $nameCounts[$baseName] . '.' . ($extension ?: 'png');
                    } else {
                        $nameCounts[$baseName] = 0;
                        $nameInZip = $baseName . '.' . ($extension ?: 'png');
                    }
                    
                    $zip->addFile($filePath, $nameInZip);
                }
            }
            $zip->close();

            if (!file_exists($zipPath)) {
                return back()->withErrors(['error' => 'Failed to generate ZIP file.']);
            }

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return back()->withErrors(['error' => 'Could not create ZIP file.']);
    }

    /**
     * Get Pinterest pin data as JSON (for copy functionality).
     */
    public function getPinData(Website $website, PinterestPin $pin)
    {
        $this->authorize('view', $website);

        if ($pin->website_id !== $website->id) {
            abort(404);
        }

        return response()->json([
            'title' => $pin->title,
            'description' => $pin->description,
            'link' => $pin->link,
            'image_url' => $pin->generated_image_url,
        ]);
    }

    /**
     * Generate a real server-side preview of the pin image (returns base64).
     */
    public function preview(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'headline_text' => 'required|string|max:100',
            'subheadline_text' => 'required|string|max:100',
            'headline_color' => 'nullable|string|max:20',
            'subheadline_color' => 'nullable|string|max:20',
            'headline_font' => 'nullable|string|max:50',
            'subheadline_font' => 'nullable|string|max:50',
            'headline_font_size' => 'nullable|integer|min:10|max:100',
            'subheadline_font_size' => 'nullable|integer|min:10|max:100',
            'overlay_color' => 'nullable|string|max:20',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'frame_design' => 'nullable|string|max:50',
            'domain_name' => 'nullable|string|max:100',
        ]);

        $article = Article::findOrFail($validated['article_id']);
        if ($article->website_id !== $website->id) {
            return response()->json(['error' => 'Article does not belong to this website.'], 403);
        }

        if (!$article->featured_image) {
            return response()->json(['error' => 'Article must have a featured image.'], 400);
        }

        try {
            $service = new PinterestDesignService();
            $base64 = $service->generatePreviewBase64(
                $article->featured_image,
                $article->secondary_image ?? $article->featured_image,
                $validated['headline_text'],
                $validated['subheadline_text'],
                $validated['headline_color'] ?? '#ffffff',
                $validated['subheadline_color'] ?? '#d4a574',
                $validated['overlay_color'] ?? '#000000',
                $validated['overlay_opacity'] ?? 70,
                $this->resolveFrameDesign($website, $validated['frame_design'] ?? null),
                $validated['headline_font'] ?? 'arial',
                $validated['subheadline_font'] ?? 'georgia',
                $validated['headline_font_size'] ?? 28,
                $validated['subheadline_font_size'] ?? 22,
                $validated['domain_name'] ?? ($website->domain ?: ($website->slug ? $website->slug . '.com' : ''))
            );

            return response()->json(['image' => $base64]);
        } catch (\Exception $e) {
            Log::error('Failed to generate preview', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to generate preview: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate AI headline and subheadline for an article.
     */
    public function generateHeadlines(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
        ]);

        $article = Article::findOrFail($validated['article_id']);
        if ($article->website_id !== $website->id) {
            return response()->json(['error' => 'Article does not belong to this website.'], 403);
        }

        $user = auth()->user();

        if (empty($user->openai_api_key)) {
            return response()->json(['error' => 'Please configure your OpenAI API key in Global Settings first.'], 400);
        }

        $service = new PinterestDesignService();
        $headlines = $service->generateAIHeadlines($article, $user);

        if ($headlines) {
            return response()->json($headlines);
        }

        return response()->json(['error' => 'Failed to generate headlines with AI.'], 500);
    }

    /**
     * Generate AI copy info for Pinterest posting.
     */
    public function generateCopyInfo(Request $request, Website $website, PinterestPin $pin)
    {
        $this->authorize('view', $website);

        if ($pin->website_id !== $website->id) {
            abort(404);
        }

        $user = auth()->user();

        if (empty($user->openai_api_key)) {
            return response()->json(['error' => 'Please configure your OpenAI API key in Global Settings first.'], 400);
        }

        try {
            $client = \OpenAI::client($user->openai_api_key);
            $model = $user->ai_model ?? 'gpt-4o';

            $article = $pin->article;
            $articleContent = $article ? strip_tags(substr($article->content ?? '', 0, 500)) : '';

            $prompt = <<<PROMPT
You are a Pinterest SEO and marketing expert. Generate optimized Pinterest pin copy for this content.

Pin Title: "{$pin->title}"
Pin Description: "{$pin->description}"
Article Content Preview: "{$articleContent}"
Link: "{$pin->link}"

Generate Pinterest-optimized copy with:
1. **Title** (max 100 chars): Compelling, keyword-rich title
2. **Description** (max 500 chars): SEO-friendly, includes relevant keywords, call-to-action, and hashtags
3. **Alt Text** (max 100 chars): Descriptive text for accessibility and SEO

Requirements:
- Include 3-5 relevant hashtags in the description
- Make it engaging and click-worthy
- Include a call-to-action
- Use keywords naturally

Respond in this exact JSON format:
{
    "title": "Pinterest-optimized title here",
    "description": "Full description with hashtags here",
    "alt_text": "Descriptive alt text here",
    "hashtags": ["hashtag1", "hashtag2", "hashtag3"]
}
PROMPT;

            $result = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a Pinterest SEO expert who creates high-performing pin copy.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            $content = $result->choices[0]->message->content ?? '';
            
            // Parse JSON response
            $jsonMatch = preg_match('/\{[\s\S]*\}/', $content, $matches);
            if ($jsonMatch) {
                $data = json_decode($matches[0], true);
                if ($data) {
                    return response()->json([
                        'title' => $data['title'] ?? $pin->title,
                        'description' => $data['description'] ?? $pin->description,
                        'alt_text' => $data['alt_text'] ?? '',
                        'hashtags' => $data['hashtags'] ?? [],
                        'link' => $pin->link,
                    ]);
                }
            }

            return response()->json(['error' => 'Failed to parse AI response.'], 500);

        } catch (\Exception $e) {
            Log::error('Failed to generate AI copy info', [
                'error' => $e->getMessage(),
                'pin_id' => $pin->id
            ]);
            
            return response()->json(['error' => 'Failed to generate copy info: ' . $e->getMessage()], 500);
        }
    }
}
