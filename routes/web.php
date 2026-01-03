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
use App\Http\Controllers\AuthorController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Get the base domain from config
$baseDomain = config('app.base_domain', 'localhost');

// Check if we're in local development
$isLocalDev = in_array($baseDomain, ['localhost', '127.0.0.1']) || app()->environment('local');

// =============================================================================
// SUBDOMAIN ROUTES (production only)
// For subdomains like example.websaasmanager.com
// =============================================================================
if (!$isLocalDev) {
    Route::domain('{subdomain}.' . $baseDomain)->middleware('identify.website')->group(function () {
        Route::get('/', [PublicWebsiteController::class, 'showByDomain'])->name('website.home');
        Route::get('/category/{category}', [PublicWebsiteController::class, 'showCategoryByDomain'])->name('website.category.subdomain');
        Route::get('/article/{article}', [PublicWebsiteController::class, 'showArticleByDomain'])->name('article.show.subdomain');
        Route::get('/page/{page}', [PublicWebsiteController::class, 'showPageByDomain'])->name('website.page.subdomain');
    });
}

// =============================================================================
// MAIN APP ROUTES
// In production: constrained to websaasmanager.com
// In local dev: no constraint (works on localhost/127.0.0.1)
// =============================================================================
$registerMainAppRoutes = function () {
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
        Route::get('/', [OrganizationController::class, 'dashboard'])->name('organization.dashboard');
        Route::get('/settings', [OrganizationController::class, 'settings'])->name('organization.settings');
        Route::post('/settings', [OrganizationController::class, 'updateSettings'])->name('organization.settings.update');
        Route::post('/settings/test', [OrganizationController::class, 'testAiConnection'])->name('organization.settings.test');
        Route::get('/websites', [OrganizationController::class, 'websitesIndex'])->name('organization.websites.index');
        Route::get('/websites/create', [OrganizationController::class, 'websitesCreate'])->name('organization.websites.create');
        Route::post('/websites', [OrganizationController::class, 'websitesStore'])->name('organization.websites.store');
        Route::get('/websites/{website}/edit', [OrganizationController::class, 'websitesEdit'])->name('organization.websites.edit');
        Route::put('/websites/{website}', [OrganizationController::class, 'websitesUpdate'])->name('organization.websites.update');
        Route::delete('/websites/{website}', [OrganizationController::class, 'websitesDestroy'])->name('organization.websites.destroy');

        // Global Articles
        Route::get('/global-articles', [OrganizationController::class, 'globalArticlesIndex'])->name('organization.global-articles.index');
        Route::post('/global-articles/generate', [OrganizationController::class, 'globalArticlesGenerate'])->name('organization.global-articles.generate');
    });

    // SuperAdmin Routes (Website-Specific)
    Route::middleware(['auth', 'verified'])->prefix('superadmin')->group(function () {
        Route::get('/{website}', [WebsiteController::class, 'dashboard'])->name('superadmin.dashboard');
        Route::get('/{website}/articles', [ArticleController::class, 'index'])->name('superadmin.articles.index');
        Route::get('/{website}/articles/create', [ArticleController::class, 'create'])->name('superadmin.articles.create');
        Route::post('/{website}/articles', [ArticleController::class, 'store'])->name('superadmin.articles.store');
        Route::get('/{website}/articles/{article}', [ArticleController::class, 'show'])->name('superadmin.articles.show');
        Route::get('/{website}/articles/{article}/edit', [ArticleController::class, 'edit'])->name('superadmin.articles.edit');
        Route::put('/{website}/articles/{article}', [ArticleController::class, 'update'])->name('superadmin.articles.update');
        Route::delete('/{website}/articles/{article}', [ArticleController::class, 'destroy'])->name('superadmin.articles.destroy');
        Route::get('/{website}/ai-articles', [AIArticleController::class, 'index'])->name('superadmin.ai-articles.index');
        Route::post('/{website}/ai-articles/generate', [AIArticleController::class, 'generate'])->name('superadmin.ai-articles.generate');
        Route::get('/api/generation-jobs', [AIArticleController::class, 'getGenerationJobs'])->name('api.generation-jobs');
        Route::delete('/api/generation-jobs/{jobId}', [AIArticleController::class, 'dismissJob'])->name('api.generation-jobs.dismiss');
        Route::delete('/api/generation-jobs', [AIArticleController::class, 'clearCompletedJobs'])->name('api.generation-jobs.clear');
        Route::get('/{website}/categories', [CategoryController::class, 'index'])->name('superadmin.categories.index');
        Route::post('/{website}/categories', [CategoryController::class, 'store'])->name('superadmin.categories.store');
        Route::put('/{website}/categories/{category}', [CategoryController::class, 'update'])->name('superadmin.categories.update');
        Route::delete('/{website}/categories/{category}', [CategoryController::class, 'destroy'])->name('superadmin.categories.destroy');
        Route::get('/{website}/pages', [PageController::class, 'index'])->name('superadmin.pages.index');
        Route::post('/{website}/pages', [PageController::class, 'store'])->name('superadmin.pages.store');
        Route::put('/{website}/pages/{page}', [PageController::class, 'update'])->name('superadmin.pages.update');
        Route::delete('/{website}/pages/{page}', [PageController::class, 'destroy'])->name('superadmin.pages.destroy');
        
        Route::get('/{website}/authors', [AuthorController::class, 'index'])->name('superadmin.authors');
        Route::post('/{website}/authors', [AuthorController::class, 'store'])->name('superadmin.authors.store');
        Route::put('/{website}/authors/{author}', [AuthorController::class, 'update'])->name('superadmin.authors.update');
        Route::delete('/{website}/authors/{author}', [AuthorController::class, 'destroy'])->name('superadmin.authors.destroy');

        Route::get('/{website}/appearance', function ($website) {
            return Inertia::render('SuperAdmin/Appearance', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.appearance');

        Route::get('/{website}/social-media', [SocialMediaController::class, 'index'])->name('superadmin.social-media');
        Route::post('/{website}/social-media', [SocialMediaController::class, 'update'])->name('superadmin.social-media.update');
        Route::delete('/{website}/social-media/{platform}', [SocialMediaController::class, 'disconnect'])->name('superadmin.social-media.disconnect');

        Route::get('/{website}/assets', function ($website) {
            return Inertia::render('SuperAdmin/Assets', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.assets');

        Route::get('/{website}/deployment', function ($website) {
            return Inertia::render('SuperAdmin/Deployment', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.deployment');

        Route::get('/{website}/ads', function ($website) {
            return Inertia::render('SuperAdmin/Ads', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.ads');

        Route::get('/{website}/settings', [WebsiteController::class, 'settings'])->name('superadmin.settings');
        Route::put('/{website}/settings', [WebsiteController::class, 'updateSettings'])->name('superadmin.settings.update');
    });

    // Legacy routes
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/superadmin', fn() => redirect()->route('organization.dashboard'));
        Route::get('/superadmin/websites', fn() => redirect()->route('organization.websites.index'))->name('superadmin.websites.index');
        Route::get('/superadmin/settings', fn() => redirect()->route('organization.settings'))->name('superadmin.settings');
    });

    // Public Website Routes (for /site/ URLs - works everywhere)
    Route::get('/site/{website}', [PublicWebsiteController::class, 'show'])->name('website.show');
    Route::get('/site/{website}/category/{category}', [PublicWebsiteController::class, 'showCategory'])->name('website.category');
    Route::get('/site/{website}/article/{article}', [PublicWebsiteController::class, 'showArticle'])->name('article.show');
    Route::get('/site/{website}/page/{page}', [PublicWebsiteController::class, 'showPage'])->name('website.page');
    
    // Auth routes
    require __DIR__.'/auth.php';
};

// Register main app routes with or without domain constraint
if ($isLocalDev) {
    // Local dev: no domain constraint - works on localhost/127.0.0.1
    $registerMainAppRoutes();
} else {
    // Production: constrain to base domain (websaasmanager.com)
    Route::domain($baseDomain)->group($registerMainAppRoutes);
    
    // Also handle www.websaasmanager.com
    Route::domain('www.' . $baseDomain)->group($registerMainAppRoutes);
}

// =============================================================================
// CUSTOM DOMAIN ROUTES (production only)
// For custom domains like craftorigami.com
// These are registered LAST as a fallback for any unmatched domain
// =============================================================================
if (!$isLocalDev) {
    Route::middleware('identify.website')->group(function () {
        Route::get('/', [PublicWebsiteController::class, 'showByDomain'])->name('website.home.custom');
        Route::get('/category/{category}', [PublicWebsiteController::class, 'showCategoryByDomain'])->name('website.category.custom');
        Route::get('/article/{article}', [PublicWebsiteController::class, 'showArticleByDomain'])->name('article.show.custom');
        Route::get('/page/{page}', [PublicWebsiteController::class, 'showPageByDomain'])->name('website.page.custom');
    });
}
