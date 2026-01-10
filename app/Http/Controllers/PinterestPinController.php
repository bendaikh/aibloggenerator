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
     * Display the Pinterest pins listing.
     */
    public function index(Website $website): Response
    {
        $this->authorize('view', $website);

        $pins = PinterestPin::where('website_id', $website->id)
            ->with(['article'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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
            'overlay_color' => 'nullable|string|max:20',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
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
                'overlay_color' => $validated['overlay_color'] ?? '#000000',
                'overlay_opacity' => $validated['overlay_opacity'] ?? 70,
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
            'overlay_color' => 'nullable|string|max:20',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
        ]);

        try {
            // Update pin with new settings
            $pin->update(array_filter($validated));
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
     * Bulk generate Pinterest pins for articles without pins.
     */
    public function bulkGenerate(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'article_ids' => 'required|array|min:1',
            'article_ids.*' => 'exists:articles,id',
        ]);

        $generated = 0;
        $errors = [];

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
                $pin = PinterestDesignService::createFromArticle($article);
                if ($pin) {
                    $generated++;
                }
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
}
