<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AIArticleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PublicWebsiteController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Get the base domain from config
$baseDomain = config('app.base_domain', 'localhost');

// --- Main App Routes ---
// These routes work on the main domain (websaasmanager.com or localhost)

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('superadmin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// SuperAdmin Routes
Route::middleware(['auth', 'verified'])->prefix('superadmin')->group(function () {
    // Dashboard
    Route::get('/', function () {
        return Inertia::render('SuperAdmin/Dashboard');
    })->name('superadmin.dashboard');

    // Websites Management
    Route::resource('websites', WebsiteController::class)->names([
        'index' => 'superadmin.websites.index',
        'create' => 'superadmin.websites.create',
        'store' => 'superadmin.websites.store',
        'show' => 'superadmin.websites.show',
        'edit' => 'superadmin.websites.edit',
        'update' => 'superadmin.websites.update',
        'destroy' => 'superadmin.websites.destroy',
    ]);

    // Articles Management
    Route::resource('articles', ArticleController::class)->names([
        'index' => 'superadmin.articles.index',
        'create' => 'superadmin.articles.create',
        'store' => 'superadmin.articles.store',
        'show' => 'superadmin.articles.show',
        'edit' => 'superadmin.articles.edit',
        'update' => 'superadmin.articles.update',
        'destroy' => 'superadmin.articles.destroy',
    ]);

    // AI Article Generation
    Route::get('/ai-articles', [AIArticleController::class, 'index'])->name('superadmin.ai-articles.index');
    Route::post('/ai-articles/generate', [AIArticleController::class, 'generate'])->name('superadmin.ai-articles.generate');

    // Content Management - Categories
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy'])->names([
        'index' => 'superadmin.categories.index',
        'store' => 'superadmin.categories.store',
        'update' => 'superadmin.categories.update',
        'destroy' => 'superadmin.categories.destroy',
    ]);

    // Content Management - Pages
    Route::resource('pages', PageController::class)->only(['index', 'store', 'update', 'destroy'])->names([
        'index' => 'superadmin.pages.index',
        'store' => 'superadmin.pages.store',
        'update' => 'superadmin.pages.update',
        'destroy' => 'superadmin.pages.destroy',
    ]);

    Route::get('/authors', function () {
        return Inertia::render('SuperAdmin/Authors');
    })->name('superadmin.authors');

    // Appearance
    Route::get('/appearance', function () {
        return Inertia::render('SuperAdmin/Appearance');
    })->name('superadmin.appearance');

    // Social Media
    Route::get('/social-media', function () {
        return Inertia::render('SuperAdmin/SocialMedia');
    })->name('superadmin.social-media');

    // Assets
    Route::get('/assets', function () {
        return Inertia::render('SuperAdmin/Assets');
    })->name('superadmin.assets');

    // Deployment
    Route::get('/deployment', function () {
        return Inertia::render('SuperAdmin/Deployment');
    })->name('superadmin.deployment');

    // Ads Management
    Route::get('/ads', function () {
        return Inertia::render('SuperAdmin/Ads');
    })->name('superadmin.ads');

    // Website Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('superadmin.settings');
    Route::post('/settings/ai', [SettingsController::class, 'updateAiSettings'])->name('superadmin.settings.ai.update');
    Route::post('/settings/ai/test', [SettingsController::class, 'testAiConnection'])->name('superadmin.settings.ai.test');
});

// --- Public Website Routes (for /site/ URLs) ---
Route::get('/site/{website}', [PublicWebsiteController::class, 'show'])->name('website.show');
Route::get('/site/{website}/category/{category}', [PublicWebsiteController::class, 'showCategory'])->name('website.category');
Route::get('/site/{website}/article/{article}', [PublicWebsiteController::class, 'showArticle'])->name('article.show');
Route::get('/site/{website}/page/{page}', [PublicWebsiteController::class, 'showPage'])->name('website.page');

// --- Subdomain Routes (For production subdomains like example.websaasmanager.com) ---
// These only work when accessing via a subdomain
Route::domain('{subdomain}.' . $baseDomain)->middleware('identify.website')->group(function () {
    Route::get('/', [PublicWebsiteController::class, 'showByDomain'])->name('website.home');
    Route::get('/category/{category}', [PublicWebsiteController::class, 'showCategoryByDomain'])->name('website.category.subdomain');
    Route::get('/article/{article}', [PublicWebsiteController::class, 'showArticleByDomain'])->name('article.show.subdomain');
    Route::get('/page/{page}', [PublicWebsiteController::class, 'showPageByDomain'])->name('website.page.subdomain');
});

require __DIR__.'/auth.php';
