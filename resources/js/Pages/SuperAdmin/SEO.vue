<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    currentWebsite: {
        type: Object,
        required: true
    },
    seoSettings: {
        type: Object,
        default: () => ({})
    }
});

const activeTab = ref('general');

const tabs = [
    { id: 'general', name: 'General SEO', icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' },
    { id: 'social', name: 'Social Media', icon: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z' },
    { id: 'verification', name: 'Search Engines', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { id: 'analytics', name: 'Analytics', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { id: 'sitemap', name: 'Sitemap & Robots', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { id: 'schema', name: 'Structured Data', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' },
];

const form = useForm({
    // Search Engine Verification
    google_verification: props.currentWebsite.google_verification || '',
    bing_verification: props.currentWebsite.bing_verification || '',
    yandex_verification: props.currentWebsite.yandex_verification || '',
    
    // Analytics
    google_analytics_id: props.currentWebsite.google_analytics_id || '',
    gtm_id: props.currentWebsite.gtm_id || '',
    
    // Robots.txt
    robots_txt: props.currentWebsite.robots_txt || '',
    
    // SEO Settings
    seo_settings: {
        // Meta tags
        meta_title: props.seoSettings.meta_title || props.currentWebsite.name,
        meta_description: props.seoSettings.meta_description || '',
        meta_keywords: props.seoSettings.meta_keywords || '',
        
        // Open Graph
        og_title: props.seoSettings.og_title || props.currentWebsite.name,
        og_description: props.seoSettings.og_description || '',
        og_image: props.seoSettings.og_image || '',
        
        // Twitter
        twitter_card: props.seoSettings.twitter_card || 'summary_large_image',
        twitter_title: props.seoSettings.twitter_title || props.currentWebsite.name,
        twitter_description: props.seoSettings.twitter_description || '',
        twitter_image: props.seoSettings.twitter_image || '',
        
        // Canonical
        canonical_url: props.seoSettings.canonical_url || props.currentWebsite.url,
        
        // Schema
        schema_type: props.seoSettings.schema_type || 'Organization',
        schema_name: props.seoSettings.schema_name || props.currentWebsite.name,
        schema_description: props.seoSettings.schema_description || '',
        schema_logo: props.seoSettings.schema_logo || '',
        
        // Indexing
        enable_indexing: props.seoSettings.enable_indexing !== false,
        enable_follow_links: props.seoSettings.enable_follow_links !== false,
        
        // Sitemap
        sitemap_enabled: props.seoSettings.sitemap_enabled !== false,
        sitemap_frequency: props.seoSettings.sitemap_frequency || 'daily',
        sitemap_priority: props.seoSettings.sitemap_priority || '0.8',
    }
});

const metaTitleLength = computed(() => form.seo_settings.meta_title?.length || 0);
const metaDescriptionLength = computed(() => form.seo_settings.meta_description?.length || 0);

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('superadmin.seo.update', props.currentWebsite.id), {
        preserveScroll: true,
    });
};

const downloadSitemap = () => {
    window.open(route('superadmin.seo.sitemap', props.currentWebsite.id), '_blank');
};

const viewSitemap = () => {
    window.open(props.currentWebsite.url + '/sitemap.xml', '_blank');
};

const viewRobotsTxt = () => {
    window.open(props.currentWebsite.url + '/robots.txt', '_blank');
};

const generateDefaultRobots = () => {
    const sitemap = form.seo_settings.sitemap_enabled ? `\n# Sitemap\nSitemap: ${props.currentWebsite.url}/sitemap.xml\n` : '';
    form.robots_txt = `# Robots.txt for ${props.currentWebsite.name}
# Generated automatically

User-agent: *
Allow: /
${sitemap}
# Disallow common paths
Disallow: /admin
Disallow: /api
Disallow: /login
Disallow: /register`;
};
</script>

<template>
    <Head title="SEO Settings" />

    <SuperAdminLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="mb-6 lg:mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">SEO Settings</h1>
                <p class="text-gray-400 mt-1">Optimize your website for search engines and improve your rankings</p>
            </div>

            <form @submit.prevent="submit">
                <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
                    <!-- Sidebar Tabs -->
                    <div class="lg:w-64 shrink-0">
                        <nav class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] overflow-hidden">
                            <div class="flex lg:flex-col overflow-x-auto lg:overflow-visible">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.id"
                                    type="button"
                                    @click="activeTab = tab.id"
                                    :class="[
                                        'flex items-center gap-3 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap lg:whitespace-normal',
                                        activeTab === tab.id
                                            ? 'bg-emerald-500/10 text-emerald-400 border-b-2 lg:border-b-0 lg:border-l-2 border-emerald-500'
                                            : 'text-gray-400 hover:text-white hover:bg-[#252525] border-b-2 lg:border-b-0 lg:border-l-2 border-transparent'
                                    ]"
                                >
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                                    </svg>
                                    <span class="hidden sm:inline">{{ tab.name }}</span>
                                </button>
                            </div>
                        </nav>

                        <!-- SEO Score Card (Desktop) -->
                        <div class="hidden lg:block mt-6 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 rounded-xl border border-emerald-500/20 p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-emerald-400 font-semibold text-sm">SEO Status</p>
                                    <p class="text-gray-400 text-xs">Configuration check</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Meta Title</span>
                                    <span :class="metaTitleLength > 0 && metaTitleLength <= 70 ? 'text-emerald-400' : 'text-amber-400'">
                                        {{ metaTitleLength > 0 && metaTitleLength <= 70 ? 'Good' : 'Needs work' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Meta Description</span>
                                    <span :class="metaDescriptionLength >= 120 && metaDescriptionLength <= 160 ? 'text-emerald-400' : 'text-amber-400'">
                                        {{ metaDescriptionLength >= 120 && metaDescriptionLength <= 160 ? 'Good' : 'Needs work' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Google Verification</span>
                                    <span :class="form.google_verification ? 'text-emerald-400' : 'text-gray-500'">
                                        {{ form.google_verification ? 'Connected' : 'Not set' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Analytics</span>
                                    <span :class="form.google_analytics_id ? 'text-emerald-400' : 'text-gray-500'">
                                        {{ form.google_analytics_id ? 'Active' : 'Not set' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1 min-w-0">
                        <!-- General SEO Tab -->
                        <div v-show="activeTab === 'general'" class="space-y-6">
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    Meta Tags
                                </h2>
                                
                                <div class="space-y-4">
                                    <!-- Meta Title -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">
                                            Meta Title
                                            <span :class="metaTitleLength > 70 ? 'text-red-400' : 'text-gray-500'" class="ml-2 text-xs">
                                                ({{ metaTitleLength }}/70 characters)
                                            </span>
                                        </label>
                                        <input
                                            v-model="form.seo_settings.meta_title"
                                            type="text"
                                            maxlength="70"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            placeholder="Your Website Title | Brand Name"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">The title tag appears in search results and browser tabs. Keep it under 70 characters.</p>
                                    </div>

                                    <!-- Meta Description -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">
                                            Meta Description
                                            <span :class="metaDescriptionLength > 160 ? 'text-red-400' : 'text-gray-500'" class="ml-2 text-xs">
                                                ({{ metaDescriptionLength }}/160 characters)
                                            </span>
                                        </label>
                                        <textarea
                                            v-model="form.seo_settings.meta_description"
                                            rows="3"
                                            maxlength="160"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            placeholder="A compelling description of your website that will appear in search results..."
                                        ></textarea>
                                        <p class="mt-1 text-xs text-gray-500">Write a compelling description between 120-160 characters to maximize click-through rates.</p>
                                    </div>

                                    <!-- Meta Keywords -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">
                                            Meta Keywords (Optional)
                                        </label>
                                        <input
                                            v-model="form.seo_settings.meta_keywords"
                                            type="text"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            placeholder="keyword1, keyword2, keyword3"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">Comma-separated keywords (less important for modern SEO but still used by some engines).</p>
                                    </div>

                                    <!-- Canonical URL -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">
                                            Canonical URL
                                        </label>
                                        <input
                                            v-model="form.seo_settings.canonical_url"
                                            type="url"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            :placeholder="currentWebsite.url"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">The preferred URL for your website. This helps prevent duplicate content issues.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Indexing Settings -->
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Indexing Settings
                                </h2>
                                
                                <div class="space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="form.seo_settings.enable_indexing"
                                            class="w-5 h-5 rounded bg-[#252525] border-[#3a3a3a] text-emerald-500 focus:ring-emerald-500"
                                        />
                                        <div>
                                            <span class="text-white font-medium">Allow search engines to index this website</span>
                                            <p class="text-gray-500 text-sm">When enabled, search engines can crawl and index your content.</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="form.seo_settings.enable_follow_links"
                                            class="w-5 h-5 rounded bg-[#252525] border-[#3a3a3a] text-emerald-500 focus:ring-emerald-500"
                                        />
                                        <div>
                                            <span class="text-white font-medium">Allow search engines to follow links</span>
                                            <p class="text-gray-500 text-sm">When enabled, search engines will follow links on your pages.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media Tab -->
                        <div v-show="activeTab === 'social'" class="space-y-6">
                            <!-- Open Graph -->
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                    Open Graph (Facebook, LinkedIn)
                                </h2>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">OG Title</label>
                                        <input
                                            v-model="form.seo_settings.og_title"
                                            type="text"
                                            maxlength="95"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Title for Facebook sharing"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">OG Description</label>
                                        <textarea
                                            v-model="form.seo_settings.og_description"
                                            rows="2"
                                            maxlength="200"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Description for Facebook sharing"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">OG Image URL</label>
                                        <input
                                            v-model="form.seo_settings.og_image"
                                            type="url"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="https://example.com/image.jpg"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">Recommended size: 1200x630 pixels</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Twitter Card -->
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-sky-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                    </svg>
                                    Twitter Card
                                </h2>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Card Type</label>
                                        <select
                                            v-model="form.seo_settings.twitter_card"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                                        >
                                            <option value="summary">Summary</option>
                                            <option value="summary_large_image">Summary with Large Image</option>
                                            <option value="app">App</option>
                                            <option value="player">Player</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Twitter Title</label>
                                        <input
                                            v-model="form.seo_settings.twitter_title"
                                            type="text"
                                            maxlength="70"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                                            placeholder="Title for Twitter sharing"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Twitter Description</label>
                                        <textarea
                                            v-model="form.seo_settings.twitter_description"
                                            rows="2"
                                            maxlength="200"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                                            placeholder="Description for Twitter sharing"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Twitter Image URL</label>
                                        <input
                                            v-model="form.seo_settings.twitter_image"
                                            type="url"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500"
                                            placeholder="https://example.com/twitter-image.jpg"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Engines Verification Tab -->
                        <div v-show="activeTab === 'verification'" class="space-y-6">
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-white mb-4">Search Engine Verification</h2>
                                <p class="text-gray-400 text-sm mb-6">Connect your website with search engines to monitor performance, submit sitemaps, and receive indexing notifications.</p>
                                
                                <div class="space-y-6">
                                    <!-- Google Search Console - managed in Website Settings -->
                                    <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shrink-0">
                                                <svg class="w-8 h-8" viewBox="0 0 24 24">
                                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-white font-medium">Google Search Console</h3>
                                                <p class="text-gray-400 text-sm mt-1">Configure meta tag or HTML file verification in Website Settings.</p>
                                                <Link
                                                    :href="route('superadmin.settings', currentWebsite.id)"
                                                    class="inline-flex items-center gap-2 mt-3 text-sm text-emerald-400 hover:text-emerald-300 transition-colors"
                                                >
                                                    Open Search Console settings
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bing Webmaster Tools -->
                                    <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-[#008373] rounded-lg flex items-center justify-center shrink-0">
                                                <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M5 3v18l5-2.5v-5L16 16l.001-5.5L5 3zm12 8.5l-5-2.5V6.5l5 2.5v2.5z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-white font-medium">Bing Webmaster Tools</h3>
                                                <p class="text-gray-400 text-sm mt-1">Connect with Bing to improve visibility on Microsoft search engines.</p>
                                                <div class="mt-3">
                                                    <label class="block text-sm text-gray-400 mb-1">Verification Code</label>
                                                    <input
                                                        v-model="form.bing_verification"
                                                        type="text"
                                                        class="w-full bg-[#1a1a1a] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm font-mono"
                                                        placeholder="e.g., 5A3B4C2D1E..."
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Yandex -->
                                    <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center shrink-0">
                                                <span class="text-white font-bold text-lg">Y</span>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-white font-medium">Yandex Webmaster</h3>
                                                <p class="text-gray-400 text-sm mt-1">Verify your site for Yandex search engine (popular in Russia).</p>
                                                <div class="mt-3">
                                                    <label class="block text-sm text-gray-400 mb-1">Verification Code</label>
                                                    <input
                                                        v-model="form.yandex_verification"
                                                        type="text"
                                                        class="w-full bg-[#1a1a1a] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm font-mono"
                                                        placeholder="e.g., a1b2c3d4e5..."
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- How to Verify Info Box -->
                            <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 sm:p-6">
                                <h3 class="text-blue-400 font-medium mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    How to Verify Your Website
                                </h3>
                                <ol class="text-gray-300 text-sm space-y-2 list-decimal list-inside">
                                    <li>Go to the search engine's webmaster tools</li>
                                    <li>Add your website and select "HTML tag" verification method</li>
                                    <li>Copy only the <code class="bg-[#252525] px-1 rounded text-emerald-400">content</code> value from the meta tag</li>
                                    <li>Paste it in the field above and save</li>
                                    <li>Return to the webmaster tools and click "Verify"</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Analytics Tab -->
                        <div v-show="activeTab === 'analytics'" class="space-y-6">
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-white mb-4">Analytics & Tracking</h2>
                                <p class="text-gray-400 text-sm mb-6">Google Analytics and Tag Manager are configured in Website Settings.</p>

                                <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center shrink-0">
                                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M22.84 2.998c.646.646.646 1.692 0 2.339l-9.195 9.195a1.655 1.655 0 01-2.339 0l-4.243-4.244a1.654 1.654 0 010-2.338l9.195-9.195c.646-.646 1.693-.646 2.339 0l4.243 4.243z"/>
                                                <path d="M12 22a3 3 0 100-6 3 3 0 000 6z"/>
                                                <path d="M4 22a3 3 0 100-6 3 3 0 000 6z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-white font-medium">Google Analytics & Tag Manager</h3>
                                            <p class="text-gray-400 text-sm mt-1">Set your GA4 measurement ID and GTM container ID in Website Settings.</p>
                                            <Link
                                                :href="route('superadmin.settings', currentWebsite.id)"
                                                class="inline-flex items-center gap-2 mt-3 text-sm text-emerald-400 hover:text-emerald-300 transition-colors"
                                            >
                                                Open Analytics settings
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sitemap & Robots Tab -->
                        <div v-show="activeTab === 'sitemap'" class="space-y-6">
                            <!-- Sitemap Settings -->
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Sitemap Settings
                                </h2>
                                
                                <div class="space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="form.seo_settings.sitemap_enabled"
                                            class="w-5 h-5 rounded bg-[#252525] border-[#3a3a3a] text-emerald-500 focus:ring-emerald-500"
                                        />
                                        <div>
                                            <span class="text-white font-medium">Enable XML Sitemap</span>
                                            <p class="text-gray-500 text-sm">Generate an XML sitemap for search engines.</p>
                                        </div>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Update Frequency</label>
                                            <select
                                                v-model="form.seo_settings.sitemap_frequency"
                                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            >
                                                <option value="always">Always</option>
                                                <option value="hourly">Hourly</option>
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="yearly">Yearly</option>
                                                <option value="never">Never</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Priority</label>
                                            <select
                                                v-model="form.seo_settings.sitemap_priority"
                                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            >
                                                <option value="1.0">1.0 (Highest)</option>
                                                <option value="0.9">0.9</option>
                                                <option value="0.8">0.8</option>
                                                <option value="0.7">0.7</option>
                                                <option value="0.6">0.6</option>
                                                <option value="0.5">0.5 (Default)</option>
                                                <option value="0.4">0.4</option>
                                                <option value="0.3">0.3</option>
                                                <option value="0.2">0.2</option>
                                                <option value="0.1">0.1 (Lowest)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-3 pt-4 border-t border-[#2a2a2a]">
                                        <button
                                            type="button"
                                            @click="viewSitemap"
                                            class="px-4 py-2 bg-[#252525] text-white rounded-lg hover:bg-[#303030] transition flex items-center gap-2 text-sm"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            View Live Sitemap
                                        </button>
                                        <button
                                            type="button"
                                            @click="downloadSitemap"
                                            class="px-4 py-2 bg-emerald-500/20 text-emerald-400 rounded-lg hover:bg-emerald-500/30 transition flex items-center gap-2 text-sm"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Download Sitemap
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Robots.txt -->
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Robots.txt
                                </h2>
                                
                                <div class="space-y-4">
                                    <div class="flex flex-wrap gap-3 mb-4">
                                        <button
                                            type="button"
                                            @click="generateDefaultRobots"
                                            class="px-4 py-2 bg-[#252525] text-white rounded-lg hover:bg-[#303030] transition flex items-center gap-2 text-sm"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Generate Default
                                        </button>
                                        <button
                                            type="button"
                                            @click="viewRobotsTxt"
                                            class="px-4 py-2 bg-[#252525] text-white rounded-lg hover:bg-[#303030] transition flex items-center gap-2 text-sm"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            View Live Robots.txt
                                        </button>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Custom Robots.txt Content</label>
                                        <textarea
                                            v-model="form.robots_txt"
                                            rows="10"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm"
                                            placeholder="# Leave empty to use auto-generated robots.txt
User-agent: *
Allow: /"
                                        ></textarea>
                                        <p class="mt-1 text-xs text-gray-500">Leave empty to use automatically generated robots.txt based on your settings.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Structured Data Tab -->
                        <div v-show="activeTab === 'schema'" class="space-y-6">
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                    Schema.org Structured Data
                                </h2>
                                <p class="text-gray-400 text-sm mb-6">Add structured data to help search engines understand your website better and display rich results.</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Organization Type</label>
                                        <select
                                            v-model="form.seo_settings.schema_type"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                        >
                                            <option value="Organization">Organization</option>
                                            <option value="LocalBusiness">Local Business</option>
                                            <option value="Person">Person / Blog</option>
                                            <option value="WebSite">Website</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                                        <input
                                            v-model="form.seo_settings.schema_name"
                                            type="text"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                            placeholder="Your Organization Name"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                                        <textarea
                                            v-model="form.seo_settings.schema_description"
                                            rows="3"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                            placeholder="A brief description of your organization..."
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Logo URL</label>
                                        <input
                                            v-model="form.seo_settings.schema_logo"
                                            type="url"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                            :placeholder="currentWebsite.logo_url || 'https://example.com/logo.png'"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Schema Preview -->
                            <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4 sm:p-6">
                                <h3 class="text-white font-medium mb-3">JSON-LD Preview</h3>
                                <pre class="bg-[#0a0a0a] p-4 rounded-lg text-sm text-gray-300 overflow-x-auto"><code>{
  "@context": "https://schema.org",
  "@type": "{{ form.seo_settings.schema_type }}",
  "name": "{{ form.seo_settings.schema_name || currentWebsite.name }}",
  "description": "{{ form.seo_settings.schema_description || 'No description set' }}",
  "url": "{{ currentWebsite.url }}",
  "logo": "{{ form.seo_settings.schema_logo || currentWebsite.logo_url || '' }}"
}</code></pre>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex items-center gap-4 sticky bottom-4 bg-[#0f0f0f] p-4 -mx-4 rounded-xl border border-[#2a2a2a]">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-8 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 flex items-center gap-2"
                            >
                                <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Saving...' : 'Save SEO Settings' }}
                            </button>
                            <span v-if="form.recentlySuccessful" class="text-emerald-400 text-sm flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Saved successfully!
                            </span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </SuperAdminLayout>
</template>
