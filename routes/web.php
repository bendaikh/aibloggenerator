<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AIArticleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\SocialMediaController;
use App\Http\Controllers\ImageUploadController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Get the base domain from config
$baseDomain = config('app.base_domain', 'localhost');

// --- Subdomain Routes (For production subdomains like example.websaasmanager.com) ---
// These must be defined FIRST to take priority over main domain routes
Route::domain('{subdomain}.' . $baseDomain)->middleware('identify.website')->group(function () {
    Route::get('/', [PublicWebsiteController::class, 'showByDomain'])->name('website.home');
    Route::get('/category/{category}', [PublicWebsiteController::class, 'showCategoryByDomain'])->name('website.category.subdomain');
    Route::get('/article/{article}', [PublicWebsiteController::class, 'showArticleByDomain'])->name('article.show.subdomain');
    Route::get('/page/{page}', [PublicWebsiteController::class, 'showPageByDomain'])->name('website.page.subdomain');
});

// --- Main App Routes (websaasmanager.com only) ---
// These routes work on the main domain (websaasmanager.com or localhost)
Route::domain($baseDomain)->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/dashboard', function () {
        return redirect()->route('organization.dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        
        // Image Upload Routes
        Route::post('/upload/image', [ImageUploadController::class, 'upload'])->name('upload.image');
        Route::delete('/upload/image', [ImageUploadController::class, 'destroy'])->name('upload.image.destroy');
    });

    // Organization Routes (Global/All Websites)
    Route::middleware(['auth', 'verified'])->prefix('organization')->group(function () {
        // Dashboard
        Route::get('/', [OrganizationController::class, 'dashboard'])->name('organization.dashboard');

        // Global Settings (AI Settings, etc.)
        Route::get('/settings', [OrganizationController::class, 'settings'])->name('organization.settings');
        Route::post('/settings', [OrganizationController::class, 'updateSettings'])->name('organization.settings.update');
        Route::post('/settings/test', [OrganizationController::class, 'testAiConnection'])->name('organization.settings.test');

        // Websites Management (at organization level)
        Route::get('/websites', [OrganizationController::class, 'websitesIndex'])->name('organization.websites.index');
        Route::get('/websites/create', [OrganizationController::class, 'websitesCreate'])->name('organization.websites.create');
        Route::post('/websites', [OrganizationController::class, 'websitesStore'])->name('organization.websites.store');
        Route::get('/websites/{website}/edit', [OrganizationController::class, 'websitesEdit'])->name('organization.websites.edit');
        Route::put('/websites/{website}', [OrganizationController::class, 'websitesUpdate'])->name('organization.websites.update');
        Route::delete('/websites/{website}', [OrganizationController::class, 'websitesDestroy'])->name('organization.websites.destroy');
    });

    // SuperAdmin Routes (Website-Specific)
    Route::middleware(['auth', 'verified'])->prefix('superadmin')->group(function () {
        // Dashboard (requires website context)
        Route::get('/{website}', [WebsiteController::class, 'dashboard'])->name('superadmin.dashboard');

        // Articles Management
        Route::get('/{website}/articles', [ArticleController::class, 'index'])->name('superadmin.articles.index');
        Route::get('/{website}/articles/create', [ArticleController::class, 'create'])->name('superadmin.articles.create');
        Route::post('/{website}/articles', [ArticleController::class, 'store'])->name('superadmin.articles.store');
        Route::get('/{website}/articles/{article}', [ArticleController::class, 'show'])->name('superadmin.articles.show');
        Route::get('/{website}/articles/{article}/edit', [ArticleController::class, 'edit'])->name('superadmin.articles.edit');
        Route::put('/{website}/articles/{article}', [ArticleController::class, 'update'])->name('superadmin.articles.update');
        Route::delete('/{website}/articles/{article}', [ArticleController::class, 'destroy'])->name('superadmin.articles.destroy');

        // AI Article Generation
        Route::get('/{website}/ai-articles', [AIArticleController::class, 'index'])->name('superadmin.ai-articles.index');
        Route::post('/{website}/ai-articles/generate', [AIArticleController::class, 'generate'])->name('superadmin.ai-articles.generate');
        
        // AI Generation Jobs API
        Route::get('/api/generation-jobs', [AIArticleController::class, 'getGenerationJobs'])->name('api.generation-jobs');
        Route::delete('/api/generation-jobs/{jobId}', [AIArticleController::class, 'dismissJob'])->name('api.generation-jobs.dismiss');
        Route::delete('/api/generation-jobs', [AIArticleController::class, 'clearCompletedJobs'])->name('api.generation-jobs.clear');

        // Categories Management
        Route::get('/{website}/categories', [CategoryController::class, 'index'])->name('superadmin.categories.index');
        Route::post('/{website}/categories', [CategoryController::class, 'store'])->name('superadmin.categories.store');
        Route::put('/{website}/categories/{category}', [CategoryController::class, 'update'])->name('superadmin.categories.update');
        Route::delete('/{website}/categories/{category}', [CategoryController::class, 'destroy'])->name('superadmin.categories.destroy');

        // Pages Management
        Route::get('/{website}/pages', [PageController::class, 'index'])->name('superadmin.pages.index');
        Route::post('/{website}/pages', [PageController::class, 'store'])->name('superadmin.pages.store');
        Route::put('/{website}/pages/{page}', [PageController::class, 'update'])->name('superadmin.pages.update');
        Route::delete('/{website}/pages/{page}', [PageController::class, 'destroy'])->name('superadmin.pages.destroy');

        // Authors
        Route::get('/{website}/authors', function ($website) {
            return Inertia::render('SuperAdmin/Authors', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.authors');

        // Appearance
        Route::get('/{website}/appearance', function ($website) {
            return Inertia::render('SuperAdmin/Appearance', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.appearance');

        // Social Media
        Route::get('/{website}/social-media', [SocialMediaController::class, 'index'])->name('superadmin.social-media');
        Route::post('/{website}/social-media', [SocialMediaController::class, 'update'])->name('superadmin.social-media.update');
        Route::delete('/{website}/social-media/{platform}', [SocialMediaController::class, 'disconnect'])->name('superadmin.social-media.disconnect');

        // Assets
        Route::get('/{website}/assets', function ($website) {
            return Inertia::render('SuperAdmin/Assets', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.assets');

        // Deployment
        Route::get('/{website}/deployment', function ($website) {
            return Inertia::render('SuperAdmin/Deployment', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.deployment');

        // Ads Management
        Route::get('/{website}/ads', function ($website) {
            return Inertia::render('SuperAdmin/Ads', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.ads');
    });

    // Legacy routes for backwards compatibility (redirect to organization)
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/superadmin', function () {
            return redirect()->route('organization.dashboard');
        });
        
        Route::get('/superadmin/websites', function () {
            return redirect()->route('organization.websites.index');
        })->name('superadmin.websites.index');

        Route::get('/superadmin/settings', function () {
            return redirect()->route('organization.settings');
        })->name('superadmin.settings');
    });

    // --- Public Website Routes (for /site/ URLs) ---
    Route::get('/site/{website}', [PublicWebsiteController::class, 'show'])->name('website.show');
    Route::get('/site/{website}/category/{category}', [PublicWebsiteController::class, 'showCategory'])->name('website.category');
    Route::get('/site/{website}/article/{article}', [PublicWebsiteController::class, 'showArticle'])->name('article.show');
    Route::get('/site/{website}/page/{page}', [PublicWebsiteController::class, 'showPage'])->name('website.page');
});

// Auth routes must be accessible on the main domain
Route::domain($baseDomain)->group(function () {
    require __DIR__.'/auth.php';
});

// --- Custom Domain Routes (For domains like craftorigami.com) ---
// These are defined LAST to catch any domain that isn't the main domain or a subdomain
// The identify.website middleware will look up the website by the custom domain
Route::middleware('identify.website')->group(function () {
    Route::get('/', [PublicWebsiteController::class, 'showByDomain'])->name('website.home.custom');
    Route::get('/category/{category}', [PublicWebsiteController::class, 'showCategoryByDomain'])->name('website.category.custom');
    Route::get('/article/{article}', [PublicWebsiteController::class, 'showArticleByDomain'])->name('article.show.custom');
    Route::get('/page/{page}', [PublicWebsiteController::class, 'showPageByDomain'])->name('website.page.custom');
});
