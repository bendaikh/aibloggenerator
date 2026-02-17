<script setup>
import { useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: () => null
    }
});

const showPlacementsSection = ref(true);

const form = useForm({
    hbagency_script: props.currentWebsite?.hbagency_script || '',
    hbagency_placements: {
        in_image: props.currentWebsite?.hbagency_placements?.in_image || '',
        in_article_1: props.currentWebsite?.hbagency_placements?.in_article_1 || '',
        in_article_2: props.currentWebsite?.hbagency_placements?.in_article_2 || '',
        sidebar: props.currentWebsite?.hbagency_placements?.sidebar || '',
        sticky_footer: props.currentWebsite?.hbagency_placements?.sticky_footer || '',
        top_banner: props.currentWebsite?.hbagency_placements?.top_banner || '',
        bottom_banner: props.currentWebsite?.hbagency_placements?.bottom_banner || '',
    },
    hbagency_active: props.currentWebsite?.hbagency_active || false,
});

const submit = () => {
    form.post(route('superadmin.ads.hbagency.update', props.currentWebsite.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="HBAgency Configuration" />

    <SuperAdminLayout :currentWebsite="currentWebsite">
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">HBAgency Configuration</h1>
                <p class="text-gray-400 mt-1">Configure HBAgency ads script and placements for {{ currentWebsite?.name }}</p>
            </div>

            <form @submit.prevent="submit" class="max-w-3xl">
                <!-- Activation Toggle -->
                <div class="bg-gradient-to-r from-amber-900/20 to-orange-900/20 border border-amber-800/50 rounded-xl p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-lg">HBAgency Status</h4>
                                <p class="text-amber-200/70 text-sm mt-1">
                                    {{ form.hbagency_active ? 'HBAgency ads are currently ACTIVE on this website' : 'HBAgency ads are currently INACTIVE' }}
                                </p>
                                <p v-if="currentWebsite?.google_ads_active" class="text-blue-300 text-xs mt-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Note: Google Ads is currently active. Activating HBAgency will automatically deactivate Google Ads.
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.hbagency_active" class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-amber-600"></div>
                        </label>
                    </div>
                </div>

                <!-- HBAgency Ads Script -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 space-y-6 mb-6">
                    <div>
                        <label for="hbagency_script" class="block text-sm font-medium text-gray-300 mb-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                HBAgency Ads Script *
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
                    </div>
                </div>

                <!-- HBAgency Ad Placements Configuration -->
                <div class="bg-gradient-to-r from-amber-900/20 to-orange-900/20 border border-amber-800/50 rounded-xl p-6">
                    <div class="flex items-start gap-3 mb-6">
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

                <!-- Submit Button -->
                <div class="mt-6 flex items-center gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : 'Save HBAgency Configuration' }}
                    </button>
                </div>
            </form>
        </div>
    </SuperAdminLayout>
</template>
