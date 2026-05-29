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
use App\Http\Controllers\PinterestPinController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DomainRequestController;
use App\Http\Controllers\ProductController;
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
        Route::get('/articles', [PublicWebsiteController::class, 'showAllArticlesByDomain'])->name('website.articles.subdomain');
        Route::get('/category/{category}', [PublicWebsiteController::class, 'showCategoryByDomain'])->name('website.category.subdomain');
        Route::get('/recipes/{article}', [PublicWebsiteController::class, 'showArticleByDomain'])->name('article.show.subdomain');
        Route::get('/page/{page}', [PublicWebsiteController::class, 'showPageByDomain'])->name('website.page.subdomain');
        Route::get('/search', [PublicWebsiteController::class, 'search'])->name('website.search.subdomain');
        Route::post('/subscribe', [\App\Http\Controllers\SubscriberController::class, 'subscribeByDomain'])->name('website.subscribe.subdomain');
        Route::get('/ads.txt', [PublicWebsiteController::class, 'adsTxt'])->name('website.ads_txt');
        // Shop routes
        Route::get('/shop', [PublicWebsiteController::class, 'shopByDomain'])->name('website.shop.subdomain');
        Route::get('/shop/{product}', [PublicWebsiteController::class, 'showProductByDomain'])->name('website.product.subdomain');
        // Checkout routes
        Route::get('/checkout/{product}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('website.checkout.subdomain');
        Route::post('/checkout/{product}/stripe', [\App\Http\Controllers\CheckoutController::class, 'createStripeSession'])->name('website.checkout.stripe.subdomain');
        Route::post('/checkout/{product}/paypal/create', [\App\Http\Controllers\CheckoutController::class, 'createPaypalOrder'])->name('website.checkout.paypal.create.subdomain');
        Route::post('/checkout/{product}/paypal/capture', [\App\Http\Controllers\CheckoutController::class, 'capturePaypalOrder'])->name('website.checkout.paypal.capture.subdomain');
        Route::get('/checkout/{product}/success', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('website.checkout.success.subdomain');
        Route::get('/checkout/{product}/paypal-return', [\App\Http\Controllers\CheckoutController::class, 'paypalReturn'])->name('website.checkout.paypal.return.subdomain');
        // Sitemap and robots.txt
        Route::get('/sitemap.xml', [PublicWebsiteController::class, 'sitemapByDomain'])->name('website.sitemap.subdomain');
        Route::get('/robots.txt', [PublicWebsiteController::class, 'robotsTxtByDomain'])->name('website.robots.subdomain');
        Route::get('/{verificationFile}', [PublicWebsiteController::class, 'googleVerificationFileByDomain'])
            ->where('verificationFile', 'google[a-f0-9]+\.html')
            ->name('website.google_verification.subdomain');
        // Regular articles at root path (must be last to avoid conflicts)
        Route::get('/{article}', [PublicWebsiteController::class, 'showRegularArticleByDomain'])->name('article.show.regular.subdomain');
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
        Route::put('/settings/global-users/{user}/status', [OrganizationController::class, 'updateGlobalUserStatus'])->name('organization.settings.global-users.status');
        Route::put('/settings/global-users/{user}/assignments', [OrganizationController::class, 'updateGlobalUserAssignments'])->name('organization.settings.global-users.assignments');
        
        // API Keys Routes
        Route::get('/api-keys', [OrganizationController::class, 'apiKeys'])->name('organization.api-keys');
        Route::post('/api-keys', [OrganizationController::class, 'updateApiKeys'])->name('organization.api-keys.update');
        Route::post('/api-keys/test-gemini', [OrganizationController::class, 'testGeminiConnection'])->name('organization.api-keys.test-gemini');
        Route::post('/api-keys/test-ideogram', [OrganizationController::class, 'testIdeogramConnection'])->name('organization.api-keys.test-ideogram');
        
        // Agent Rewrite Routes
        Route::get('/agent-rewrite', [OrganizationController::class, 'agentRewrite'])->name('organization.agent-rewrite');
        Route::post('/agent-rewrite', [OrganizationController::class, 'updateAgentRewrite'])->name('organization.agent-rewrite.update');
        
        // Security Notifications Routes
        Route::get('/security-notifications', [OrganizationController::class, 'securityNotifications'])->name('organization.security-notifications');
        Route::post('/security-notifications', [OrganizationController::class, 'updateSecurityNotifications'])->name('organization.security-notifications.update');
        Route::post('/security-notifications/test-twilio', [OrganizationController::class, 'testTwilioConnection'])->name('organization.security-notifications.test-twilio');
        
        // API Usage & Cost Tracking
        Route::get('/api-usage', [OrganizationController::class, 'apiUsage'])->name('organization.api-usage');
        
        // Payment Integration Routes
        Route::get('/payments', [\App\Http\Controllers\PaymentController::class, 'index'])->name('organization.payments');
        Route::post('/payments', [\App\Http\Controllers\PaymentController::class, 'update'])->name('organization.payments.update');
        Route::post('/payments/test-stripe', [\App\Http\Controllers\PaymentController::class, 'testStripe'])->name('organization.payments.test-stripe');
        Route::post('/payments/test-paypal', [\App\Http\Controllers\PaymentController::class, 'testPaypal'])->name('organization.payments.test-paypal');
        Route::post('/payments/disconnect-stripe', [\App\Http\Controllers\PaymentController::class, 'disconnectStripe'])->name('organization.payments.disconnect-stripe');
        Route::post('/payments/disconnect-paypal', [\App\Http\Controllers\PaymentController::class, 'disconnectPaypal'])->name('organization.payments.disconnect-paypal');
        
        // Themes Routes (Read-Only - just view available themes)
        Route::get('/themes', [OrganizationController::class, 'themes'])->name('organization.themes');
        
        // Theme Management Routes (Superadmin only) - auth check handled in controller
        Route::put('/themes/{theme}/toggle-public', [\App\Http\Controllers\ThemeController::class, 'togglePublic'])->name('organization.themes.toggle-public');
        Route::get('/themes/manage', [\App\Http\Controllers\ThemeController::class, 'index'])->name('organization.themes.manage');
        Route::post('/themes', [\App\Http\Controllers\ThemeController::class, 'store'])->name('organization.themes.store');
        Route::put('/themes/{theme}', [\App\Http\Controllers\ThemeController::class, 'update'])->name('organization.themes.update');
        Route::delete('/themes/{theme}', [\App\Http\Controllers\ThemeController::class, 'destroy'])->name('organization.themes.destroy');
        
        Route::get('/websites', [OrganizationController::class, 'websitesIndex'])->name('organization.websites.index');
        Route::get('/websites/create', [OrganizationController::class, 'websitesCreate'])->name('organization.websites.create');
        Route::post('/websites', [OrganizationController::class, 'websitesStore'])->name('organization.websites.store');
        Route::get('/websites/{website}/edit', [OrganizationController::class, 'websitesEdit'])->name('organization.websites.edit');
        Route::put('/websites/{website}', [OrganizationController::class, 'websitesUpdate'])->name('organization.websites.update');
        Route::delete('/websites/{website}', [OrganizationController::class, 'websitesDestroy'])->name('organization.websites.destroy');

        // Global Articles
        Route::get('/global-articles/theme-select', [OrganizationController::class, 'globalArticlesThemeSelect'])->name('organization.global-articles.theme-select');
        Route::get('/global-articles', [OrganizationController::class, 'globalArticlesIndex'])->name('organization.global-articles.index');
        Route::post('/global-articles/generate', [OrganizationController::class, 'globalArticlesGenerate'])->name('organization.global-articles.generate');

        // Global Subscribers
        Route::get('/global-subscribers', [OrganizationController::class, 'globalSubscribersIndex'])->name('organization.global-subscribers.index');
        Route::post('/global-subscribers/export', [OrganizationController::class, 'globalSubscribersExport'])->name('organization.global-subscribers.export');

        // User Management Routes (Organization-wide)
        Route::get('/users', [UserManagementController::class, 'index'])->name('organization.users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('organization.users.show');
        Route::post('/users', [UserManagementController::class, 'store'])->name('organization.users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('organization.users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('organization.users.destroy');
        Route::post('/users/{user}/approve', [UserManagementController::class, 'approve'])->name('organization.users.approve');
        Route::post('/users/{user}/decline', [UserManagementController::class, 'decline'])->name('organization.users.decline');
        Route::post('/users/{user}/login-as', [UserManagementController::class, 'loginAs'])->name('organization.users.login-as');
        Route::post('/users/stop-impersonation', [UserManagementController::class, 'stopImpersonation'])->name('organization.users.stop-impersonation');

        // Role Management Routes (Organization-wide)
        Route::get('/roles', [RoleController::class, 'index'])->name('organization.roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('organization.roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('organization.roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('organization.roles.destroy');

        // Permission Management Routes (Organization-wide)
        Route::get('/permissions', [PermissionController::class, 'index'])->name('organization.permissions.index');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('organization.permissions.store');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('organization.permissions.update');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('organization.permissions.destroy');
        Route::put('/roles/{role}/permissions', [PermissionController::class, 'updateRolePermissions'])->name('organization.roles.permissions.update');

        // Domain Onboarding Routes (User)
        Route::get('/domains', [DomainRequestController::class, 'index'])->name('organization.domains.index');
        Route::post('/domains', [DomainRequestController::class, 'store'])->name('organization.domains.store');
        Route::delete('/domains/{domainRequest}', [DomainRequestController::class, 'destroy'])->name('organization.domains.destroy');

        // Pending Domains Routes (Superadmin only)
        Route::get('/pending-domains', [DomainRequestController::class, 'adminIndex'])->name('organization.pending-domains.index');
        Route::post('/domains/{domainRequest}/parking', [DomainRequestController::class, 'markParking'])->name('organization.domains.parking');
        Route::post('/domains/{domainRequest}/parked', [DomainRequestController::class, 'markParked'])->name('organization.domains.parked');
        Route::post('/domains/{domainRequest}/approve', [DomainRequestController::class, 'approve'])->name('organization.domains.approve');
        Route::post('/domains/{domainRequest}/reject', [DomainRequestController::class, 'reject'])->name('organization.domains.reject');
        Route::put('/domains/{domainRequest}/notes', [DomainRequestController::class, 'updateNotes'])->name('organization.domains.notes');
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
        
        // AI Image Generation Routes (for home decor and similar themes)
        Route::post('/{website}/articles/{article}/generate-images', [AIArticleController::class, 'generateImages'])->name('superadmin.articles.generate-images');
        Route::get('/{website}/articles/{article}/images', [AIArticleController::class, 'getArticleImages'])->name('superadmin.articles.images');
        Route::delete('/{website}/articles/{article}/images/{imageId}', [AIArticleController::class, 'deleteArticleImage'])->name('superadmin.articles.images.delete');
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

        // Pinterest Pins Routes
        Route::get('/{website}/pinterest-pins', [PinterestPinController::class, 'index'])->name('superadmin.pinterest-pins.index');
        Route::get('/{website}/pinterest-pins/create', [PinterestPinController::class, 'create'])->name('superadmin.pinterest-pins.create');
        Route::post('/{website}/pinterest-pins', [PinterestPinController::class, 'store'])->name('superadmin.pinterest-pins.store');
        Route::get('/{website}/pinterest-pins/{pin}', [PinterestPinController::class, 'show'])->name('superadmin.pinterest-pins.show');
        Route::post('/{website}/pinterest-pins/{pin}/regenerate', [PinterestPinController::class, 'regenerate'])->name('superadmin.pinterest-pins.regenerate');
        Route::delete('/{website}/pinterest-pins/{pin}', [PinterestPinController::class, 'destroy'])->name('superadmin.pinterest-pins.destroy');
        Route::post('/{website}/pinterest-pins/bulk-generate', [PinterestPinController::class, 'bulkGenerate'])->name('superadmin.pinterest-pins.bulk-generate');
        Route::post('/{website}/pinterest-pins/bulk-update-designs', [PinterestPinController::class, 'bulkUpdateDesigns'])->name('superadmin.pinterest-pins.bulk-update-designs');
        Route::get('/{website}/pinterest-pins/{pin}/download', [PinterestPinController::class, 'download'])->name('superadmin.pinterest-pins.download');
        Route::post('/{website}/pinterest-pins/bulk-download', [PinterestPinController::class, 'bulkDownload'])->name('superadmin.pinterest-pins.bulk-download');
        Route::post('/{website}/pinterest-pins/bulk-delete', [PinterestPinController::class, 'bulkDelete'])->name('superadmin.pinterest-pins.bulk-delete');
        Route::get('/{website}/pinterest-pins/{pin}/data', [PinterestPinController::class, 'getPinData'])->name('superadmin.pinterest-pins.data');
        Route::post('/{website}/pinterest-pins/generate-headlines', [PinterestPinController::class, 'generateHeadlines'])->name('superadmin.pinterest-pins.generate-headlines');
        Route::post('/{website}/pinterest-pins/preview', [PinterestPinController::class, 'preview'])->name('superadmin.pinterest-pins.preview');
        Route::post('/{website}/pinterest-pins/{pin}/generate-copy-info', [PinterestPinController::class, 'generateCopyInfo'])->name('superadmin.pinterest-pins.generate-copy-info');

        // Subscribers Routes
        Route::get('/{website}/subscribers', [\App\Http\Controllers\SubscriberController::class, 'index'])->name('superadmin.subscribers.index');
        Route::delete('/{website}/subscribers/{subscriber}', [\App\Http\Controllers\SubscriberController::class, 'destroy'])->name('superadmin.subscribers.destroy');
        Route::post('/{website}/subscribers/export', [\App\Http\Controllers\SubscriberController::class, 'export'])->name('superadmin.subscribers.export');

        Route::get('/{website}/appearance', [WebsiteController::class, 'appearance'])->name('superadmin.appearance');
        Route::put('/{website}/appearance', [WebsiteController::class, 'updateAppearance'])->name('superadmin.appearance.update');

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

        // Ads Management Routes
        Route::get('/{website}/ads/hbagency', function ($website) {
            return Inertia::render('SuperAdmin/HBAgency', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.ads.hbagency');

        Route::post('/{website}/ads/hbagency', [\App\Http\Controllers\AdsController::class, 'updateHBAgency'])->name('superadmin.ads.hbagency.update');

        Route::get('/{website}/ads/google', function ($website) {
            return Inertia::render('SuperAdmin/GoogleAds', [
                'currentWebsite' => \App\Models\Website::findOrFail($website),
                'websites' => \App\Models\Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            ]);
        })->name('superadmin.ads.google');

        Route::post('/{website}/ads/google', [\App\Http\Controllers\AdsController::class, 'updateGoogleAds'])->name('superadmin.ads.google.update');

        Route::get('/{website}/settings', [WebsiteController::class, 'settings'])->name('superadmin.settings');
        Route::put('/{website}/settings', [WebsiteController::class, 'updateSettings'])->name('superadmin.settings.update');
        Route::put('/{website}/settings/search-console', [WebsiteController::class, 'updateSearchConsole'])->name('superadmin.settings.search-console.update');
        Route::put('/{website}/settings/analytics', [WebsiteController::class, 'updateAnalytics'])->name('superadmin.settings.analytics.update');

        // SEO Routes
        Route::get('/{website}/seo', [WebsiteController::class, 'seo'])->name('superadmin.seo');
        Route::put('/{website}/seo', [WebsiteController::class, 'updateSeo'])->name('superadmin.seo.update');
        Route::get('/{website}/seo/sitemap', [WebsiteController::class, 'generateSitemap'])->name('superadmin.seo.sitemap');
        
        // GEO (Generative Engine Optimization) Routes
        Route::get('/{website}/geo', [\App\Http\Controllers\GeoController::class, 'index'])->name('superadmin.geo');
        Route::post('/{website}/geo', [\App\Http\Controllers\GeoController::class, 'update'])->name('superadmin.geo.update');

        // Shop Products Routes
        Route::get('/{website}/products', [ProductController::class, 'index'])->name('superadmin.products.index');
        Route::get('/{website}/products/create', [ProductController::class, 'create'])->name('superadmin.products.create');
        Route::post('/{website}/products', [ProductController::class, 'store'])->name('superadmin.products.store');
        Route::get('/{website}/products/{product}/edit', [ProductController::class, 'edit'])->name('superadmin.products.edit');
        Route::put('/{website}/products/{product}', [ProductController::class, 'update'])->name('superadmin.products.update');
        Route::delete('/{website}/products/{product}', [ProductController::class, 'destroy'])->name('superadmin.products.destroy');
        Route::post('/{website}/products/order', [ProductController::class, 'updateOrder'])->name('superadmin.products.order');
    });

    // Legacy routes
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/superadmin', fn() => redirect()->route('organization.dashboard'));
        Route::get('/superadmin/websites', fn() => redirect()->route('organization.websites.index'))->name('superadmin.websites.index');
        Route::get('/superadmin/settings', fn() => redirect()->route('organization.settings'))->name('superadmin.settings');
    });

    // Public Website Routes (for /site/ URLs - works everywhere)
    Route::get('/site/{website}', [PublicWebsiteController::class, 'show'])->name('website.show');
    Route::get('/site/{website}/articles', [PublicWebsiteController::class, 'showAllArticles'])->name('website.articles');
    Route::get('/site/{website}/category/{category}', [PublicWebsiteController::class, 'showCategory'])->name('website.category');
    Route::get('/site/{website}/recipes/{article}', [PublicWebsiteController::class, 'showArticle'])->name('article.show');
    Route::get('/site/{website}/page/{page}', [PublicWebsiteController::class, 'showPage'])->name('website.page');
    Route::get('/site/{website}/search', [PublicWebsiteController::class, 'searchLegacy'])->name('website.search');
    Route::post('/site/{website}/subscribe', [\App\Http\Controllers\SubscriberController::class, 'subscribe'])->name('website.subscribe');
    Route::get('/site/{website}/ads.txt', [PublicWebsiteController::class, 'adsTxtLegacy'])->name('website.ads_txt.legacy');
    // Shop routes
    Route::get('/site/{website}/shop', [PublicWebsiteController::class, 'shop'])->name('website.shop');
    Route::get('/site/{website}/shop/{product}', [PublicWebsiteController::class, 'showProduct'])->name('website.product');
    
    // Checkout routes
    Route::get('/site/{website}/checkout/{product}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('website.checkout');
    Route::post('/site/{website}/checkout/{product}/stripe', [\App\Http\Controllers\CheckoutController::class, 'createStripeSession'])->name('website.checkout.stripe');
    Route::post('/site/{website}/checkout/{product}/paypal/create', [\App\Http\Controllers\CheckoutController::class, 'createPaypalOrder'])->name('website.checkout.paypal.create');
    Route::post('/site/{website}/checkout/{product}/paypal/capture', [\App\Http\Controllers\CheckoutController::class, 'capturePaypalOrder'])->name('website.checkout.paypal.capture');
    Route::get('/site/{website}/checkout/{product}/success', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('website.checkout.success');
    Route::get('/site/{website}/checkout/{product}/paypal-return', [\App\Http\Controllers\CheckoutController::class, 'paypalReturn'])->name('website.checkout.paypal.return');
    // Sitemap and robots.txt routes
    Route::get('/site/{website}/sitemap.xml', [PublicWebsiteController::class, 'sitemap'])->name('website.sitemap');
    Route::get('/site/{website}/robots.txt', [PublicWebsiteController::class, 'robotsTxt'])->name('website.robots');
    Route::get('/site/{website}/ai.txt', [PublicWebsiteController::class, 'aiTxt'])->name('website.ai_txt');
    Route::get('/site/{website}/{verificationFile}', [PublicWebsiteController::class, 'googleVerificationFile'])
        ->where('verificationFile', 'google[a-f0-9]+\.html')
        ->name('website.google_verification');
    // AI API for crawler access
    Route::get('/site/{website}/api/ai/articles', [\App\Http\Controllers\Api\AiArticleController::class, 'index'])->name('website.ai.articles');
    Route::get('/site/{website}/api/ai/articles/{article}', [\App\Http\Controllers\Api\AiArticleController::class, 'show'])->name('website.ai.article');
    // Regular articles at root path (for /site/{website}/{article})
    Route::get('/site/{website}/{article}', [PublicWebsiteController::class, 'showRegularArticle'])->name('article.show.regular');
    
    // Auth routes
    require __DIR__.'/auth.php';
    
    // Stripe Webhook (outside auth - must be accessible without authentication)
    Route::post('/webhooks/stripe', [\App\Http\Controllers\CheckoutController::class, 'stripeWebhook'])
        ->name('webhooks.stripe')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    
    // Diagnostics route (can be removed after verifying fix)
    Route::get('/diagnostics/inertia', [\App\Http\Controllers\DiagnosticsController::class, 'index'])->name('diagnostics.inertia');
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
        Route::get('/articles', [PublicWebsiteController::class, 'showAllArticlesByDomain'])->name('website.articles.custom');
        Route::get('/category/{category}', [PublicWebsiteController::class, 'showCategoryByDomain'])->name('website.category.custom');
        Route::get('/recipes/{article}', [PublicWebsiteController::class, 'showArticleByDomain'])->name('article.show.custom');
        Route::get('/page/{page}', [PublicWebsiteController::class, 'showPageByDomain'])->name('website.page.custom');
        Route::get('/search', [PublicWebsiteController::class, 'search'])->name('website.search.custom');
        Route::post('/subscribe', [\App\Http\Controllers\SubscriberController::class, 'subscribeByDomain'])->name('website.subscribe.custom');
        Route::get('/ads.txt', [PublicWebsiteController::class, 'adsTxt'])->name('website.ads_txt.custom');
        // Shop routes
        Route::get('/shop', [PublicWebsiteController::class, 'shopByDomain'])->name('website.shop.custom');
        Route::get('/shop/{product}', [PublicWebsiteController::class, 'showProductByDomain'])->name('website.product.custom');
        // Checkout routes
        Route::get('/checkout/{product}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('website.checkout.custom');
        Route::post('/checkout/{product}/stripe', [\App\Http\Controllers\CheckoutController::class, 'createStripeSession'])->name('website.checkout.stripe.custom');
        Route::post('/checkout/{product}/paypal/create', [\App\Http\Controllers\CheckoutController::class, 'createPaypalOrder'])->name('website.checkout.paypal.create.custom');
        Route::post('/checkout/{product}/paypal/capture', [\App\Http\Controllers\CheckoutController::class, 'capturePaypalOrder'])->name('website.checkout.paypal.capture.custom');
        Route::get('/checkout/{product}/success', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('website.checkout.success.custom');
        Route::get('/checkout/{product}/paypal-return', [\App\Http\Controllers\CheckoutController::class, 'paypalReturn'])->name('website.checkout.paypal.return.custom');
        // Sitemap and robots.txt
        Route::get('/sitemap.xml', [PublicWebsiteController::class, 'sitemapByDomain'])->name('website.sitemap.custom');
        Route::get('/robots.txt', [PublicWebsiteController::class, 'robotsTxtByDomain'])->name('website.robots.custom');
        Route::get('/ai.txt', [PublicWebsiteController::class, 'aiTxtByDomain'])->name('website.ai_txt.custom');
        Route::get('/{verificationFile}', [PublicWebsiteController::class, 'googleVerificationFileByDomain'])
            ->where('verificationFile', 'google[a-f0-9]+\.html')
            ->name('website.google_verification.custom');
        // AI API for crawler access
        Route::get('/api/ai/articles', [\App\Http\Controllers\Api\AiArticleController::class, 'index'])->name('website.ai.articles.custom');
        Route::get('/api/ai/articles/{article}', [\App\Http\Controllers\Api\AiArticleController::class, 'show'])->name('website.ai.article.custom');
        // Regular articles at root path (must be last to avoid conflicts)
        Route::get('/{article}', [PublicWebsiteController::class, 'showRegularArticleByDomain'])->name('article.show.regular.custom');
    });
}
