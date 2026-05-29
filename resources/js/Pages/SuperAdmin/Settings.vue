<script setup>
import { useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    currentWebsite: {
        type: Object,
        required: true
    }
});

const activeTab = ref('general');

const tabs = [
    { id: 'general', name: 'General', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
    { id: 'search-console', name: 'Search Console', icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' },
    { id: 'analytics', name: 'Google Analytics', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
];

const form = useForm({
    name: props.currentWebsite.name || '',
    ads_txt: props.currentWebsite.ads_txt || '',
    pinterest_verification: props.currentWebsite.pinterest_verification || '',
    logo: null,
    favicon: null,
    remove_logo: false,
    remove_favicon: false,
});

const searchConsoleForm = useForm({
    google_verification_method: props.currentWebsite.google_verification_method || 'meta_tag',
    google_verification: props.currentWebsite.google_verification || '',
    google_verification_file: props.currentWebsite.google_verification_file || '',
    google_verification_file_content: props.currentWebsite.google_verification_file_content || '',
});

const analyticsForm = useForm({
    google_analytics_id: props.currentWebsite.google_analytics_id || '',
    gtm_id: props.currentWebsite.gtm_id || '',
});

const logoPreview = ref(props.currentWebsite.logo_url || '');
const faviconPreview = ref(props.currentWebsite.favicon_url || '');

const searchConsoleConnected = computed(() => props.currentWebsite.is_google_search_console_connected);
const analyticsConnected = computed(() => props.currentWebsite.is_google_analytics_connected);

const verificationFileUrl = computed(() => props.currentWebsite.google_verification_file_url);

const handleLogoChange = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        form.logo = file;
        form.remove_logo = false;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleFaviconChange = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        form.favicon = file;
        form.remove_favicon = false;
        const reader = new FileReader();
        reader.onload = (e) => {
            faviconPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    logoPreview.value = '';
    const input = document.getElementById('logo');
    if (input) input.value = '';
};

const removeFavicon = () => {
    form.favicon = null;
    form.remove_favicon = true;
    faviconPreview.value = '';
    const input = document.getElementById('favicon');
    if (input) input.value = '';
};

const handleVerificationFileUpload = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    if (!/^google[a-f0-9]+\.html$/i.test(file.name)) {
        alert('Please upload the exact file provided by Google Search Console (e.g., google1234567890abcdef.html).');
        return;
    }

    searchConsoleForm.google_verification_file = file.name;

    const reader = new FileReader();
    reader.onload = (e) => {
        searchConsoleForm.google_verification_file_content = e.target.result.trim();
    };
    reader.readAsText(file);
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('superadmin.settings.update', props.currentWebsite.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};

const submitSearchConsole = () => {
    searchConsoleForm.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('superadmin.settings.search-console.update', props.currentWebsite.id), {
        preserveScroll: true,
    });
};

const submitAnalytics = () => {
    analyticsForm.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('superadmin.settings.analytics.update', props.currentWebsite.id), {
        preserveScroll: true,
    });
};

const openVerificationFile = () => {
    if (verificationFileUrl.value) {
        window.open(verificationFileUrl.value, '_blank');
    }
};
</script>

<template>
    <Head title="Website Settings" />

    <SuperAdminLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="mb-6 lg:mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Website Settings</h1>
                <p class="text-gray-400 mt-1">Manage your website configuration, Google Search Console, and analytics</p>
            </div>

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
                                <span>{{ tab.name }}</span>
                            </button>
                        </div>
                    </nav>

                    <!-- Integration Status -->
                    <div class="hidden lg:block mt-6 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 rounded-xl border border-emerald-500/20 p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-emerald-400 font-semibold text-sm">Integrations</p>
                                <p class="text-gray-400 text-xs">Connection status</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Search Console</span>
                                <span :class="searchConsoleConnected ? 'text-emerald-400' : 'text-gray-500'">
                                    {{ searchConsoleConnected ? 'Configured' : 'Not set' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Google Analytics</span>
                                <span :class="analyticsConnected ? 'text-emerald-400' : 'text-gray-500'">
                                    {{ analyticsConnected ? 'Active' : 'Not set' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 min-w-0">
                    <!-- General Tab -->
                    <form v-show="activeTab === 'general'" @submit.prevent="submit" class="max-w-3xl">
                        <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 space-y-6">
                            <div>
                                <h2 class="text-lg font-semibold text-white mb-1">General Settings</h2>
                                <p class="text-gray-400 text-sm">Manage your website name, logo, and favicon</p>
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                                    Website Name *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="My Awesome Blog"
                                    required
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label for="ads_txt" class="block text-sm font-medium text-gray-300 mb-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Ads.txt Content (Optional)
                                    </div>
                                </label>
                                <textarea
                                    id="ads_txt"
                                    v-model="form.ads_txt"
                                    rows="6"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                                    placeholder="google.com, pub-xxxxxxxxxxxxxxxx, DIRECT, f08c47fec0942fa0"
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500">Paste the content for your website's ads.txt file.</p>
                                <p v-if="form.errors.ads_txt" class="mt-1 text-sm text-red-500">{{ form.errors.ads_txt }}</p>
                            </div>

                            <div>
                                <label for="pinterest_verification" class="block text-sm font-medium text-gray-300 mb-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-red-400" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 0a12 12 0 00-4.373 23.178c-.07-.627-.133-1.59.028-2.273.146-.622.937-3.977.937-3.977s-.239-.479-.239-1.188c0-1.113.645-1.944 1.448-1.944.683 0 1.013.513 1.013 1.128 0 .687-.437 1.713-.663 2.664-.189.797.4 1.447 1.185 1.447 1.422 0 2.515-1.5 2.515-3.664 0-1.915-1.377-3.254-3.342-3.254-2.276 0-3.612 1.707-3.612 3.471 0 .688.265 1.425.595 1.826a.24.24 0 01.056.23c-.061.252-.196.797-.222.908-.035.146-.115.177-.267.107-1-.465-1.624-1.927-1.624-3.1 0-2.523 1.834-4.84 5.286-4.84 2.775 0 4.932 1.977 4.932 4.62 0 2.757-1.739 4.976-4.151 4.976-.811 0-1.573-.421-1.834-.919l-.498 1.902c-.181.695-.669 1.566-.995 2.097A12 12 0 1012 0z"/>
                                        </svg>
                                        Pinterest Website Claim (Optional)
                                    </div>
                                </label>
                                <input
                                    id="pinterest_verification"
                                    v-model="form.pinterest_verification"
                                    type="text"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 font-mono text-sm"
                                    placeholder="71443e52cd737e0a1bf625ef293124db"
                                />
                                <p v-if="form.errors.pinterest_verification" class="mt-1 text-sm text-red-500">{{ form.errors.pinterest_verification }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Logo</label>
                                <div v-if="logoPreview" class="mb-3">
                                    <div class="relative inline-block">
                                        <img :src="logoPreview" alt="Logo preview" class="h-32 w-auto rounded-lg object-cover border border-[#3a3a3a]" />
                                        <button type="button" @click="removeLogo" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors" title="Remove logo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="border-2 border-dashed border-[#3a3a3a] rounded-lg p-6 text-center hover:border-[#4a4a4a] transition-colors cursor-pointer" @click="$refs.logoInput?.click()">
                                    <input id="logo" ref="logoInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml" class="hidden" @change="handleLogoChange" />
                                    <p class="text-white text-sm font-medium">{{ logoPreview ? 'Click to change logo' : 'Drop an image here or click to browse' }}</p>
                                    <p class="text-gray-500 text-xs mt-1">PNG, JPG, GIF, WebP, SVG up to 5MB</p>
                                </div>
                                <p v-if="form.errors.logo" class="mt-1 text-sm text-red-500">{{ form.errors.logo }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Favicon</label>
                                <div v-if="faviconPreview" class="mb-3">
                                    <div class="relative inline-block">
                                        <img :src="faviconPreview" alt="Favicon preview" class="h-16 w-16 rounded-lg object-cover border border-[#3a3a3a]" />
                                        <button type="button" @click="removeFavicon" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors" title="Remove favicon">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="border-2 border-dashed border-[#3a3a3a] rounded-lg p-6 text-center hover:border-[#4a4a4a] transition-colors cursor-pointer" @click="$refs.faviconInput?.click()">
                                    <input id="favicon" ref="faviconInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp,image/x-icon" class="hidden" @change="handleFaviconChange" />
                                    <p class="text-white text-sm font-medium">{{ faviconPreview ? 'Click to change favicon' : 'Drop an image here or click to browse' }}</p>
                                    <p class="text-gray-500 text-xs mt-1">PNG, JPG, GIF, WebP, ICO up to 2MB</p>
                                </div>
                                <p v-if="form.errors.favicon" class="mt-1 text-sm text-red-500">{{ form.errors.favicon }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 flex items-center gap-2">
                                <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Saving...' : 'Save General Settings' }}
                            </button>
                        </div>
                    </form>

                    <!-- Search Console Tab -->
                    <form v-show="activeTab === 'search-console'" @submit.prevent="submitSearchConsole" class="max-w-3xl">
                        <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 space-y-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-white mb-1">Google Search Console</h2>
                                    <p class="text-gray-400 text-sm">Verify site ownership to connect with Google Search Console</p>
                                </div>
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-medium shrink-0',
                                        searchConsoleConnected ? 'bg-emerald-500/20 text-emerald-400' : 'bg-gray-500/20 text-gray-400'
                                    ]"
                                >
                                    {{ searchConsoleConnected ? 'Configured' : 'Not configured' }}
                                </span>
                            </div>

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
                                        <p class="text-white font-medium">Website URL</p>
                                        <p class="text-emerald-400 text-sm font-mono mt-1">{{ currentWebsite.url }}</p>
                                        <p class="text-gray-500 text-xs mt-2">Add this exact URL as a URL-prefix property in Google Search Console.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Verification Method</label>
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <label
                                        :class="[
                                            'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all',
                                            searchConsoleForm.google_verification_method === 'meta_tag'
                                                ? 'border-emerald-500 bg-emerald-500/10'
                                                : 'border-[#3a3a3a] bg-[#252525] hover:border-[#4a4a4a]'
                                        ]"
                                    >
                                        <input type="radio" v-model="searchConsoleForm.google_verification_method" value="meta_tag" class="sr-only" />
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            <span class="text-white font-medium">Meta Tag</span>
                                        </div>
                                        <p class="text-gray-400 text-xs">Paste the verification code from Google's HTML meta tag method.</p>
                                    </label>

                                    <label
                                        :class="[
                                            'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all',
                                            searchConsoleForm.google_verification_method === 'html_file'
                                                ? 'border-emerald-500 bg-emerald-500/10'
                                                : 'border-[#3a3a3a] bg-[#252525] hover:border-[#4a4a4a]'
                                        ]"
                                    >
                                        <input type="radio" v-model="searchConsoleForm.google_verification_method" value="html_file" class="sr-only" />
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-white font-medium">HTML File</span>
                                        </div>
                                        <p class="text-gray-400 text-xs">Upload or paste the HTML verification file from Google.</p>
                                    </label>
                                </div>
                            </div>

                            <!-- Meta Tag Method -->
                            <div v-if="searchConsoleForm.google_verification_method === 'meta_tag'" class="space-y-4">
                                <div>
                                    <label for="google_verification" class="block text-sm font-medium text-gray-300 mb-2">Verification Code</label>
                                    <input
                                        id="google_verification"
                                        v-model="searchConsoleForm.google_verification"
                                        type="text"
                                        class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-sm"
                                        placeholder="e.g., dN3e7F2G8h9..."
                                    />
                                    <p v-if="searchConsoleForm.errors.google_verification" class="mt-1 text-sm text-red-500">{{ searchConsoleForm.errors.google_verification }}</p>
                                </div>

                                <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                                    <p class="text-xs text-gray-400 mb-2"><strong class="text-gray-300">How to get this code:</strong></p>
                                    <ol class="text-xs text-gray-500 space-y-1 list-decimal list-inside">
                                        <li>Go to <a href="https://search.google.com/search-console" target="_blank" class="text-emerald-400 hover:underline">Google Search Console</a></li>
                                        <li>Add your website URL and choose the <strong class="text-gray-400">HTML tag</strong> verification method</li>
                                        <li>Copy only the <code class="text-amber-400 bg-[#1a1a1a] px-1 rounded">content</code> value from the meta tag</li>
                                        <li>Paste it here and save, then click Verify in Search Console</li>
                                    </ol>
                                    <p class="text-xs text-gray-500 mt-3">
                                        Example: From <code class="text-amber-400 bg-[#1a1a1a] px-1 rounded">&lt;meta name="google-site-verification" content="abc123..."&gt;</code>
                                        paste only <code class="text-emerald-400 bg-[#1a1a1a] px-1 rounded">abc123...</code>
                                    </p>
                                </div>
                            </div>

                            <!-- HTML File Method -->
                            <div v-else class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Upload Verification File</label>
                                    <div class="border-2 border-dashed border-[#3a3a3a] rounded-lg p-6 text-center hover:border-[#4a4a4a] transition-colors cursor-pointer" @click="$refs.verificationFileInput?.click()">
                                        <input ref="verificationFileInput" type="file" accept=".html" class="hidden" @change="handleVerificationFileUpload" />
                                        <svg class="w-10 h-10 text-gray-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <p class="text-white text-sm font-medium">Click to upload Google's HTML file</p>
                                        <p class="text-gray-500 text-xs mt-1">Must be named like google1234567890abcdef.html</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="google_verification_file" class="block text-sm font-medium text-gray-300 mb-2">Filename</label>
                                    <input
                                        id="google_verification_file"
                                        v-model="searchConsoleForm.google_verification_file"
                                        type="text"
                                        class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-sm"
                                        placeholder="google1234567890abcdef.html"
                                    />
                                    <p v-if="searchConsoleForm.errors.google_verification_file" class="mt-1 text-sm text-red-500">{{ searchConsoleForm.errors.google_verification_file }}</p>
                                </div>

                                <div>
                                    <label for="google_verification_file_content" class="block text-sm font-medium text-gray-300 mb-2">File Content</label>
                                    <textarea
                                        id="google_verification_file_content"
                                        v-model="searchConsoleForm.google_verification_file_content"
                                        rows="4"
                                        class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-sm"
                                        placeholder="google-site-verification: google1234567890abcdef.html"
                                    ></textarea>
                                    <p v-if="searchConsoleForm.errors.google_verification_file_content" class="mt-1 text-sm text-red-500">{{ searchConsoleForm.errors.google_verification_file_content }}</p>
                                </div>

                                <div v-if="verificationFileUrl" class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                                    <p class="text-emerald-400 text-sm font-medium mb-1">Verification file will be served at:</p>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <code class="text-emerald-300 text-xs font-mono break-all">{{ verificationFileUrl }}</code>
                                        <button type="button" @click="openVerificationFile" class="text-xs text-emerald-400 hover:text-emerald-300 underline">Test URL</button>
                                    </div>
                                </div>

                                <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                                    <p class="text-xs text-gray-400 mb-2"><strong class="text-gray-300">How to verify with HTML file:</strong></p>
                                    <ol class="text-xs text-gray-500 space-y-1 list-decimal list-inside">
                                        <li>In Search Console, choose the <strong class="text-gray-400">HTML file</strong> verification method</li>
                                        <li>Download the file Google provides (e.g., google1234567890abcdef.html)</li>
                                        <li>Upload it here or paste the filename and content manually</li>
                                        <li>Save settings, then click Verify in Search Console</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" :disabled="searchConsoleForm.processing" class="px-8 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 flex items-center gap-2">
                                <svg v-if="searchConsoleForm.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ searchConsoleForm.processing ? 'Saving...' : 'Save Search Console Settings' }}
                            </button>
                        </div>
                    </form>

                    <!-- Google Analytics Tab -->
                    <form v-show="activeTab === 'analytics'" @submit.prevent="submitAnalytics" class="max-w-3xl">
                        <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 space-y-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-white mb-1">Google Analytics</h2>
                                    <p class="text-gray-400 text-sm">Connect analytics tools to track traffic and visitor behavior</p>
                                </div>
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-medium shrink-0',
                                        analyticsConnected ? 'bg-emerald-500/20 text-emerald-400' : 'bg-gray-500/20 text-gray-400'
                                    ]"
                                >
                                    {{ analyticsConnected ? 'Active' : 'Not configured' }}
                                </span>
                            </div>

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
                                        <h3 class="text-white font-medium">Google Analytics 4 (GA4)</h3>
                                        <p class="text-gray-400 text-sm mt-1">Track page views, user sessions, conversions, and audience insights.</p>
                                        <div class="mt-3">
                                            <label for="google_analytics_id" class="block text-sm text-gray-400 mb-1">Measurement ID</label>
                                            <input
                                                id="google_analytics_id"
                                                v-model="analyticsForm.google_analytics_id"
                                                type="text"
                                                class="w-full bg-[#1a1a1a] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm font-mono"
                                                placeholder="G-XXXXXXXXXX"
                                            />
                                            <p v-if="analyticsForm.errors.google_analytics_id" class="mt-1 text-sm text-red-500">{{ analyticsForm.errors.google_analytics_id }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Find this in Google Analytics under Admin → Data Streams.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-white font-medium">Google Tag Manager (Optional)</h3>
                                        <p class="text-gray-400 text-sm mt-1">Manage all tracking tags centrally without editing site code.</p>
                                        <div class="mt-3">
                                            <label for="gtm_id" class="block text-sm text-gray-400 mb-1">Container ID</label>
                                            <input
                                                id="gtm_id"
                                                v-model="analyticsForm.gtm_id"
                                                type="text"
                                                class="w-full bg-[#1a1a1a] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-mono"
                                                placeholder="GTM-XXXXXXX"
                                            />
                                            <p v-if="analyticsForm.errors.gtm_id" class="mt-1 text-sm text-red-500">{{ analyticsForm.errors.gtm_id }}</p>
                                            <p class="mt-1 text-xs text-gray-500">Find this in GTM under Admin → Container Settings.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4">
                                <h3 class="text-blue-400 font-medium mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Privacy & Cookie Consent
                                </h3>
                                <p class="text-gray-300 text-sm">Analytics scripts are loaded only after visitors accept analytics cookies via your site's cookie consent banner.</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" :disabled="analyticsForm.processing" class="px-8 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 flex items-center gap-2">
                                <svg v-if="analyticsForm.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ analyticsForm.processing ? 'Saving...' : 'Save Analytics Settings' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
