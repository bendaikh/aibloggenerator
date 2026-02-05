<script setup>
import { useForm } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    website: Object
});

const showDomainInstructions = ref(false);
const logoPreview = ref(props.website.logo_url || '');
const faviconPreview = ref(props.website.favicon_url || '');

const showPlacementsSection = ref(false);

const form = useForm({
    name: props.website.name,
    domain: props.website.domain || '',
    subdomain: props.website.subdomain || '',
    description: props.website.description || '',
    hbagency_script: props.website.hbagency_script || '',
    ads_txt: props.website.ads_txt || '',
    hbagency_placements: {
        in_image: props.website.hbagency_placements?.in_image || '',
        in_article_1: props.website.hbagency_placements?.in_article_1 || '',
        in_article_2: props.website.hbagency_placements?.in_article_2 || '',
        sidebar: props.website.hbagency_placements?.sidebar || '',
        sticky_footer: props.website.hbagency_placements?.sticky_footer || '',
        top_banner: props.website.hbagency_placements?.top_banner || '',
        bottom_banner: props.website.hbagency_placements?.bottom_banner || '',
    },
    is_active: props.website.is_active,
    logo: null,
    favicon: null,
});

const handleLogoChange = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        form.logo = file;
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
        const reader = new FileReader();
        reader.onload = (e) => {
            faviconPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    logoPreview.value = props.website.logo_url || '';
    const input = document.getElementById('logo');
    if (input) input.value = '';
};

const removeFavicon = () => {
    form.favicon = null;
    faviconPreview.value = props.website.favicon_url || '';
    const input = document.getElementById('favicon');
    if (input) input.value = '';
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('organization.websites.update', props.website.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="`Edit ${website.name}`" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <Link 
                    :href="route('organization.websites.index')" 
                    class="text-gray-400 hover:text-white text-sm flex items-center gap-2 mb-4"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Websites
                </Link>
                <h1 class="text-3xl font-bold text-white">Edit Website</h1>
                <p class="text-gray-400 mt-1">Update {{ website.name }} settings</p>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 space-y-6">
                    <!-- Website Name -->
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

                    <!-- Subdomain -->
                    <div>
                        <label for="subdomain" class="block text-sm font-medium text-gray-300 mb-2">
                            Subdomain
                        </label>
                        <div class="flex items-center">
                            <input
                                id="subdomain"
                                v-model="form.subdomain"
                                type="text"
                                class="flex-1 bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="mysite"
                            />
                            <span class="bg-[#1f1f1f] border border-l-0 border-[#3a3a3a] text-gray-400 px-4 py-3 rounded-r-lg">
                                .websaasmanager.com
                            </span>
                        </div>
                        <p v-if="form.errors.subdomain" class="mt-1 text-sm text-red-500">{{ form.errors.subdomain }}</p>
                    </div>

                    <!-- Custom Domain -->
                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-300 mb-2">
                            Custom Domain (Optional)
                        </label>
                        <input
                            id="domain"
                            v-model="form.domain"
                            type="text"
                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="www.example.com"
                        />
                        <p class="mt-1 text-xs text-gray-500">Add a custom domain after DNS configuration</p>
                        <p v-if="form.errors.domain" class="mt-1 text-sm text-red-500">{{ form.errors.domain }}</p>
                        
                        <!-- Toggle Instructions Button -->
                        <button 
                            type="button"
                            @click="showDomainInstructions = !showDomainInstructions"
                            class="mt-2 text-sm text-emerald-400 hover:text-emerald-300 flex items-center gap-1"
                        >
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showDomainInstructions }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            {{ showDomainInstructions ? 'Hide' : 'Show' }} Domain Setup Instructions
                        </button>
                    </div>

                    <!-- Custom Domain Instructions -->
                    <div v-if="showDomainInstructions" class="bg-gradient-to-r from-blue-900/30 to-indigo-900/30 border border-blue-800/50 rounded-xl p-5">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-lg">How to Connect Your Custom Domain</h4>
                                <p class="text-blue-200/70 text-sm">Follow these steps to point your domain to your website</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                    Log into your domain registrar
                                </h5>
                                <p class="text-gray-300 text-sm">Access the DNS management panel at your domain registrar (GoDaddy, Namecheap, Cloudflare, etc.)</p>
                            </div>

                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                    Add a CNAME Record
                                </h5>
                                <div class="text-gray-300 text-sm space-y-2">
                                    <p>Create a new CNAME record with these settings:</p>
                                    <div class="bg-[#1a1a1a] rounded-lg p-3 font-mono text-xs">
                                        <div class="grid grid-cols-2 gap-2">
                                            <span class="text-gray-500">Type:</span>
                                            <span class="text-white">CNAME</span>
                                            <span class="text-gray-500">Name/Host:</span>
                                            <span class="text-white">www</span>
                                            <span class="text-gray-500">Value/Target:</span>
                                            <span class="text-emerald-400">websaasmanager.com</span>
                                            <span class="text-gray-500">TTL:</span>
                                            <span class="text-white">Auto or 3600</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                    For Root Domain (Optional)
                                </h5>
                                <div class="text-gray-300 text-sm space-y-2">
                                    <p>To use your root domain (example.com without www), add an A Record:</p>
                                    <div class="bg-[#1a1a1a] rounded-lg p-3 font-mono text-xs">
                                        <div class="grid grid-cols-2 gap-2">
                                            <span class="text-gray-500">Type:</span>
                                            <span class="text-white">A</span>
                                            <span class="text-gray-500">Name/Host:</span>
                                            <span class="text-white">@</span>
                                            <span class="text-gray-500">Value/IP:</span>
                                            <span class="text-emerald-400">YOUR_SERVER_IP</span>
                                            <span class="text-gray-500">TTL:</span>
                                            <span class="text-white">Auto or 3600</span>
                                        </div>
                                    </div>
                                    <p class="text-yellow-400 text-xs mt-2">⚠️ Contact support for the exact IP address if using root domain.</p>
                                </div>
                            </div>

                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">4</span>
                                    Wait for DNS Propagation
                                </h5>
                                <p class="text-gray-300 text-sm">DNS changes can take up to 48 hours to propagate worldwide, but usually complete within 1-4 hours.</p>
                            </div>

                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">5</span>
                                    Enter Your Domain Here
                                </h5>
                                <p class="text-gray-300 text-sm">Once DNS is configured, enter your domain in the field above (e.g., www.example.com or example.com)</p>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-yellow-900/30 border border-yellow-800/50 rounded-lg">
                            <p class="text-yellow-200 text-xs flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><strong>Need help?</strong> If you're using Cloudflare, make sure the proxy status is set to "DNS only" (gray cloud) for the initial setup.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Current Domain Status -->
                    <div v-if="website.domain" class="bg-emerald-900/30 border border-emerald-800/50 rounded-lg p-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-emerald-200 text-sm">
                                Custom domain <strong>{{ website.domain }}</strong> is configured
                            </span>
                        </div>
                        <p class="text-emerald-300/70 text-xs mt-2">
                            Your website is accessible at: <a :href="'https://' + website.domain" target="_blank" class="underline hover:text-white">https://{{ website.domain }}</a>
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="A brief description of your website..."
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- HBAgency Ads Script -->
                    <div>
                        <label for="hbagency_script" class="block text-sm font-medium text-gray-300 mb-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                HBAgency Ads Script (Optional)
                            </div>
                        </label>
                        <textarea
                            id="hbagency_script"
                            v-model="form.hbagency_script"
                            rows="4"
                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm"
                            placeholder='<script src="https://d3u598arehftfk.cloudfront.net/prebid_hb_XXXXX_XXXXX.js" async></script>'
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Paste the complete script tag from HBAgency. This enables ads monetization for this website.</p>
                        <p v-if="form.errors.hbagency_script" class="mt-1 text-sm text-red-500">{{ form.errors.hbagency_script }}</p>
                        
                        <!-- CMP Info Box -->
                        <div v-if="form.hbagency_script" class="mt-3 bg-emerald-900/30 border border-emerald-800/50 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-emerald-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <div>
                                    <h5 class="text-emerald-200 font-medium text-sm">GDPR Consent Banner Enabled</h5>
                                    <p class="text-emerald-300/70 text-xs mt-1">
                                        A cookie consent banner (CMP) is automatically enabled when HBAgency ads are configured. 
                                        This ensures GDPR compliance by asking visitors for consent before loading advertising scripts.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Toggle Placements Section -->
                        <button 
                            type="button"
                            @click="showPlacementsSection = !showPlacementsSection"
                            class="mt-3 text-sm text-amber-400 hover:text-amber-300 flex items-center gap-1"
                        >
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showPlacementsSection }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            {{ showPlacementsSection ? 'Hide' : 'Configure' }} Ad Placements
                        </button>
                    </div>

                    <!-- Ads.txt Section -->
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
                        <p class="mt-1 text-xs text-gray-500">Paste the content for your website's ads.txt file. This is required by many ad networks like Google Ads and HB Agency.</p>
                        <p v-if="form.errors.ads_txt" class="mt-1 text-sm text-red-500">{{ form.errors.ads_txt }}</p>
                    </div>

                    <!-- HBAgency Ad Placements Configuration -->
                    <div v-if="showPlacementsSection" class="bg-gradient-to-r from-amber-900/20 to-orange-900/20 border border-amber-800/50 rounded-xl p-5">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-lg">HBAgency Ad Placements</h4>
                                <p class="text-amber-200/70 text-sm">Enter the placement IDs from HBAgency (e.g., 264555)</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- In Image Placement -->
                            <div>
                                <label class="text-gray-300 text-sm block mb-2">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                        In Image (Inimage)
                                    </span>
                                </label>
                                <input 
                                    v-model="form.hbagency_placements.in_image"
                                    type="text" 
                                    placeholder="e.g., 264555"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm"
                                />
                            </div>

                            <!-- Top Banner Placement -->
                            <div>
                                <label class="text-gray-300 text-sm block mb-2">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                                        Top Banner (728x90)
                                    </span>
                                </label>
                                <input 
                                    v-model="form.hbagency_placements.top_banner"
                                    type="text" 
                                    placeholder="e.g., 264556"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm"
                                />
                            </div>

                            <!-- In Article 1 Placement -->
                            <div>
                                <label class="text-gray-300 text-sm block mb-2">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-purple-400 rounded-full"></span>
                                        In Article - Before Content
                                    </span>
                                </label>
                                <input 
                                    v-model="form.hbagency_placements.in_article_1"
                                    type="text" 
                                    placeholder="e.g., 264556"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm"
                                />
                            </div>

                            <!-- In Article 2 Placement -->
                            <div>
                                <label class="text-gray-300 text-sm block mb-2">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-pink-400 rounded-full"></span>
                                        In Article - After Content
                                    </span>
                                </label>
                                <input 
                                    v-model="form.hbagency_placements.in_article_2"
                                    type="text" 
                                    placeholder="e.g., 264556"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm"
                                />
                            </div>

                            <!-- Sidebar Placement -->
                            <div>
                                <label class="text-gray-300 text-sm block mb-2">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-teal-400 rounded-full"></span>
                                        Sidebar (300x600)
                                    </span>
                                </label>
                                <input 
                                    v-model="form.hbagency_placements.sidebar"
                                    type="text" 
                                    placeholder="e.g., 264553"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm"
                                />
                            </div>

                            <!-- Bottom Banner Placement -->
                            <div>
                                <label class="text-gray-300 text-sm block mb-2">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-orange-400 rounded-full"></span>
                                        Bottom Banner (After Recipe)
                                    </span>
                                </label>
                                <input 
                                    v-model="form.hbagency_placements.bottom_banner"
                                    type="text" 
                                    placeholder="e.g., 264557"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm"
                                />
                            </div>

                            <!-- Sticky Footer Placement -->
                            <div class="md:col-span-2">
                                <label class="text-gray-300 text-sm block mb-2">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                                        Sticky Footer (728x90)
                                    </span>
                                </label>
                                <input 
                                    v-model="form.hbagency_placements.sticky_footer"
                                    type="text" 
                                    placeholder="e.g., 264552"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono text-sm"
                                />
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-[#0a0a0a]/50 rounded-lg">
                            <p class="text-amber-200 text-xs flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Get placement IDs from HBAgency: Click "Placement code" → copy the number from <code class="bg-black/30 px-1 rounded">hbagency_space_XXXXXX</code></span>
                            </p>
                        </div>
                    </div>

                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Logo (Optional)
                        </label>
                        <div v-if="logoPreview" class="mb-3">
                            <div class="relative inline-block">
                                <img 
                                    :src="logoPreview" 
                                    alt="Logo preview"
                                    class="h-32 w-auto rounded-lg object-cover border border-[#3a3a3a]"
                                />
                                <button
                                    type="button"
                                    @click="removeLogo"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors"
                                    title="Remove logo"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="border-2 border-dashed border-[#3a3a3a] rounded-lg p-6 text-center hover:border-[#4a4a4a] transition-colors cursor-pointer" @click="$refs.logoInput?.click()">
                            <input
                                id="logo"
                                ref="logoInput"
                                type="file"
                                accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                                class="hidden"
                                @change="handleLogoChange"
                            />
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-[#252525] rounded-lg flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-white text-sm font-medium mb-1">
                                    {{ logoPreview ? 'Click to change logo' : 'Drop an image here or click to browse' }}
                                </p>
                                <p class="text-gray-500 text-xs">PNG, JPG, GIF, WebP, SVG up to 5MB</p>
                            </div>
                        </div>
                        <p v-if="form.errors.logo" class="mt-1 text-sm text-red-500">{{ form.errors.logo }}</p>
                    </div>

                    <!-- Favicon Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Favicon (Optional)
                        </label>
                        <div v-if="faviconPreview" class="mb-3">
                            <div class="relative inline-block">
                                <img 
                                    :src="faviconPreview" 
                                    alt="Favicon preview"
                                    class="h-16 w-16 rounded-lg object-cover border border-[#3a3a3a]"
                                />
                                <button
                                    type="button"
                                    @click="removeFavicon"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors"
                                    title="Remove favicon"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="border-2 border-dashed border-[#3a3a3a] rounded-lg p-6 text-center hover:border-[#4a4a4a] transition-colors cursor-pointer" @click="$refs.faviconInput?.click()">
                            <input
                                id="favicon"
                                ref="faviconInput"
                                type="file"
                                accept="image/jpeg,image/png,image/gif,image/webp,image/x-icon"
                                class="hidden"
                                @change="handleFaviconChange"
                            />
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-[#252525] rounded-lg flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-white text-sm font-medium mb-1">
                                    {{ faviconPreview ? 'Click to change favicon' : 'Drop an image here or click to browse' }}
                                </p>
                                <p class="text-gray-500 text-xs">PNG, JPG, GIF, WebP, ICO up to 2MB. Recommended: 32x32 or 16x16 pixels</p>
                            </div>
                        </div>
                        <p v-if="form.errors.favicon" class="mt-1 text-sm text-red-500">{{ form.errors.favicon }}</p>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-[#3a3a3a] peer-focus:ring-2 peer-focus:ring-emerald-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                        <span class="text-gray-300">Active</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex items-center gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <Link
                        :href="route('organization.websites.index')"
                        class="px-6 py-3 text-gray-400 hover:text-white transition"
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </OrganizationLayout>
</template>
