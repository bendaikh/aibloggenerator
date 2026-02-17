<script setup>
import { useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: () => null
    }
});

const form = useForm({
    google_adsense_id: props.currentWebsite?.google_adsense_id || '',
    ads_txt: props.currentWebsite?.ads_txt || '',
    google_ads_placements: {
        in_image: props.currentWebsite?.google_ads_placements?.in_image || '',
        in_article_1: props.currentWebsite?.google_ads_placements?.in_article_1 || '',
        in_article_2: props.currentWebsite?.google_ads_placements?.in_article_2 || '',
        sidebar: props.currentWebsite?.google_ads_placements?.sidebar || '',
        sticky_footer: props.currentWebsite?.google_ads_placements?.sticky_footer || '',
        top_banner: props.currentWebsite?.google_ads_placements?.top_banner || '',
        bottom_banner: props.currentWebsite?.google_ads_placements?.bottom_banner || '',
    },
    google_ads_active: props.currentWebsite?.google_ads_active || false,
});

const submit = () => {
    form.post(route('superadmin.ads.google.update', props.currentWebsite.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Google Ads Configuration" />

    <SuperAdminLayout :currentWebsite="currentWebsite">
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Google Ads Configuration</h1>
                <p class="text-gray-400 mt-1">Configure Google AdSense and ads.txt for {{ currentWebsite?.name }}</p>
            </div>

            <form @submit.prevent="submit" class="max-w-3xl space-y-6">
                <!-- Activation Toggle -->
                <div class="bg-gradient-to-r from-blue-900/20 to-purple-900/20 border border-blue-800/50 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-lg">Google Ads Status</h4>
                                <p class="text-blue-200/70 text-sm mt-1">
                                    {{ form.google_ads_active ? 'Google Ads are currently ACTIVE on this website' : 'Google Ads are currently INACTIVE' }}
                                </p>
                                <p v-if="currentWebsite?.hbagency_active" class="text-amber-300 text-xs mt-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Note: HBAgency is currently active. Activating Google Ads will automatically deactivate HBAgency.
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.google_ads_active" class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Google AdSense -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-white font-semibold text-lg">Google AdSense</h3>
                            <p class="text-gray-400 text-sm">Connect your Google AdSense account</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="google_adsense_id" class="text-gray-300 text-sm block mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    Publisher ID
                                </span>
                            </label>
                            <input 
                                id="google_adsense_id"
                                v-model="form.google_adsense_id"
                                type="text" 
                                placeholder="ca-pub-xxxxxxxxxxxxxxxxxx"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                            />
                            <p class="mt-1 text-xs text-gray-500">Your Google AdSense Publisher ID (starts with "ca-pub-")</p>
                            <p v-if="form.errors.google_adsense_id" class="mt-1 text-sm text-red-500">{{ form.errors.google_adsense_id }}</p>
                        </div>

                        <div v-if="form.google_adsense_id" class="bg-blue-900/30 border border-blue-800/50 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h5 class="text-blue-200 font-medium text-sm">Next Steps</h5>
                                    <p class="text-blue-300/70 text-xs mt-1">
                                        After saving, add your website to your Google AdSense account and create ad units. 
                                        Don't forget to add your ads.txt content below.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ads.txt Section -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-white font-semibold text-lg">Ads.txt Content</h3>
                            <p class="text-gray-400 text-sm">Authorized Digital Sellers</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="ads_txt" class="text-gray-300 text-sm block mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Ads.txt File Content
                                </span>
                            </label>
                            <textarea
                                id="ads_txt"
                                v-model="form.ads_txt"
                                rows="8"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 font-mono text-sm"
                                placeholder="google.com, pub-xxxxxxxxxxxxxxxx, DIRECT, f08c47fec0942fa0"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">Paste the content for your website's ads.txt file. This is required by many ad networks like Google Ads and HBAgency.</p>
                            <p v-if="form.errors.ads_txt" class="mt-1 text-sm text-red-500">{{ form.errors.ads_txt }}</p>
                        </div>

                        <div class="bg-yellow-900/30 border border-yellow-800/50 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-yellow-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <h5 class="text-yellow-200 font-medium text-sm">Important</h5>
                                    <p class="text-yellow-300/70 text-xs mt-1">
                                        The ads.txt file will be automatically served at <code class="bg-black/30 px-1 rounded">{{ currentWebsite?.domain || currentWebsite?.subdomain + '.websaasmanager.com' }}/ads.txt</code>.
                                        Make sure to include entries for all ad networks you use (Google AdSense, HBAgency, etc.).
                                    </p>
                                </div>
                            </div>
                        </div>

                        <a 
                            v-if="currentWebsite?.domain || currentWebsite?.subdomain"
                            :href="'https://' + (currentWebsite?.domain || currentWebsite?.subdomain + '.websaasmanager.com') + '/ads.txt'" 
                            target="_blank" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-medium transition-colors text-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            View Current ads.txt
                        </a>
                    </div>
                </div>

                <!-- Google Ads Ad Placements Configuration -->
                <div class="bg-gradient-to-r from-blue-900/20 to-indigo-900/20 border border-blue-800/50 rounded-xl p-6">
                    <div class="flex items-start gap-3 mb-6">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-white font-semibold text-lg">Google Ads Placement Units</h4>
                            <p class="text-blue-200/70 text-sm">Enter the Ad Unit IDs from Google AdSense (e.g., ca-pub-1234567890123456/1234567890)</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- In Image Placement -->
                        <div>
                            <label class="text-gray-300 text-sm block mb-2">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                    In Image Ad Unit
                                </span>
                            </label>
                            <input 
                                v-model="form.google_ads_placements.in_image"
                                type="text" 
                                placeholder="ca-pub-xxx/xxx"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
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
                                v-model="form.google_ads_placements.top_banner"
                                type="text" 
                                placeholder="ca-pub-xxx/xxx"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
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
                                v-model="form.google_ads_placements.in_article_1"
                                type="text" 
                                placeholder="ca-pub-xxx/xxx"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
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
                                v-model="form.google_ads_placements.in_article_2"
                                type="text" 
                                placeholder="ca-pub-xxx/xxx"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
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
                                v-model="form.google_ads_placements.sidebar"
                                type="text" 
                                placeholder="ca-pub-xxx/xxx"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
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
                                v-model="form.google_ads_placements.bottom_banner"
                                type="text" 
                                placeholder="ca-pub-xxx/xxx"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
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
                                v-model="form.google_ads_placements.sticky_footer"
                                type="text" 
                                placeholder="ca-pub-xxx/xxx"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                            />
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-[#0a0a0a]/50 rounded-lg">
                        <p class="text-blue-200 text-xs flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Get Ad Unit IDs from Google AdSense: Ads → Ad Units → Copy the data-ad-slot value (e.g., 1234567890)</span>
                        </p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : 'Save Google Ads Configuration' }}
                    </button>
                </div>
            </form>
        </div>
    </SuperAdminLayout>
</template>
