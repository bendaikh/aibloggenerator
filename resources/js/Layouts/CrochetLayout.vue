<template>
    <div class="min-h-screen bg-[#F0F4EF]">
        <!-- Global Meta Tags for Website Verification -->
        <Head>
            <meta v-if="website.pinterest_verification" name="p:domain_verify" :content="website.pinterest_verification" />
            <meta v-if="website.google_verification" name="google-site-verification" :content="website.google_verification" />
            <meta v-if="website.bing_verification" name="msvalidate.01" :content="website.bing_verification" />
            <meta v-if="website.yandex_verification" name="yandex-verification" :content="website.yandex_verification" />
        </Head>
        
        <!-- Cookie Consent Banner -->
        <CookieConsent 
            v-if="showCustomCMP"
            :show-settings-button="true"
            :auto-show="true"
            @consent-given="onConsentGiven"
            @consent-rejected="onConsentRejected"
        />

        <!-- Header -->
        <header class="bg-white/90 backdrop-blur-md sticky top-0 z-50 border-b border-[#E0E7E0]">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between py-4">
                    <!-- Logo -->
                    <a :href="website.url" class="flex items-center gap-2">
                        <img v-if="website.logo_url" :src="website.logo_url" :alt="website.name" class="h-10 md:h-12" />
                        <span v-else class="text-2xl font-black text-[#3A5A40]">{{ website.name }}</span>
                    </a>

                    <!-- Navigation -->
                    <nav class="hidden md:flex items-center space-x-1">
                        <a 
                            :href="website.url"
                            class="px-4 py-2 rounded-full text-sm font-black text-[#3A5A40] hover:bg-[#A3B18A]/20 transition-colors uppercase tracking-widest"
                        >
                            Home
                        </a>
                        <div class="relative group">
                            <button class="px-4 py-2 rounded-full text-sm font-black text-[#3A5A40] hover:bg-[#A3B18A]/20 transition-colors uppercase tracking-widest flex items-center gap-1">
                                Categories
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="absolute left-0 mt-2 w-56 bg-white rounded-3xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 border border-[#E0E7E0] overflow-hidden">
                                <div class="py-2">
                                    <a
                                        v-for="category in website.categories"
                                        :key="category.id"
                                        :href="category.url"
                                        class="block px-6 py-3 text-sm font-bold text-[#3A5A40] hover:bg-[#F0F4EF] transition-colors"
                                    >
                                        {{ category.name }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <a 
                            v-for="page in website.pages" 
                            :key="page.id"
                            :href="page.url"
                            class="px-4 py-2 rounded-full text-sm font-black text-[#588157] hover:bg-[#A3B18A]/10 transition-colors uppercase tracking-widest"
                        >
                            {{ page.title }}
                        </a>
                    </nav>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-[#3A5A40]">
                        <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Menu -->
                <div v-show="mobileMenuOpen" class="md:hidden py-4 border-t border-[#E0E7E0] space-y-1">
                    <a 
                        :href="website.url"
                        class="block px-4 py-2 text-base font-black text-[#3A5A40] uppercase tracking-widest"
                    >
                        Home
                    </a>
                    <div class="px-4 py-2 text-xs font-black text-[#A3B18A] uppercase tracking-widest">Categories</div>
                    <a 
                        v-for="category in website.categories" 
                        :key="category.id"
                        :href="category.url"
                        class="block px-6 py-2 text-base font-bold text-[#3A5A40]"
                    >
                        {{ category.name }}
                    </a>
                    <div v-if="website.pages?.length" class="px-4 py-2 text-xs font-black text-[#A3B18A] uppercase tracking-widest">Pages</div>
                    <a 
                        v-for="page in website.pages" 
                        :key="page.id"
                        :href="page.url"
                        class="block px-6 py-2 text-base font-bold text-[#588157]"
                    >
                        {{ page.title }}
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-[#3A5A40] text-white py-16 rounded-t-[3rem] md:rounded-t-[5rem]">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
                    <div class="text-center md:text-left">
                        <h2 class="text-3xl font-black mb-6">{{ website.name }}</h2>
                        <p class="text-[#DAD7CD] leading-relaxed">{{ website.description || 'Your cozy corner for crochet inspiration and patterns.' }}</p>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-[#A3B18A] mb-6">Quick Links</h3>
                        <div class="flex flex-col gap-4">
                            <a :href="website.url" class="hover:text-[#A3B18A] transition-colors font-bold">Home</a>
                            <a v-for="page in website.pages" :key="page.id" :href="page.url" class="hover:text-[#A3B18A] transition-colors font-bold">{{ page.title }}</a>
                        </div>
                    </div>
                    <div class="text-center md:text-right">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-[#A3B18A] mb-6">Follow Us</h3>
                        <div class="flex justify-center md:justify-end gap-6">
                            <a v-if="website.social_media?.instagram" :href="website.social_media.instagram" class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center hover:bg-white/20 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a v-if="website.social_media?.pinterest" :href="website.social_media.pinterest" class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center hover:bg-white/20 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="pt-12 border-t border-white/10 text-center">
                    <p class="text-sm text-[#A3B18A] font-bold uppercase tracking-widest">&copy; {{ new Date().getFullYear() }} {{ website.name }}. Made with ❤️ for the crochet community.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useConsentManagement, CONSENT_CATEGORIES } from '@/composables/useConsentManagement';
import CookieConsent from '@/Components/CookieConsent.vue';

const props = defineProps({
    website: {
        type: Object,
        required: true
    }
});

const { hasConsentFor, consentGiven, initializeHBAgencyAds } = useConsentManagement();

const showCustomCMP = computed(() => {
    const hasActiveHBAgency = props.website?.hbagency_script && props.website?.hbagency_active;
    if (hasActiveHBAgency) {
        return false;
    }
    return true;
});

const onConsentGiven = (details) => {
    console.log('[CrochetLayout] Consent given:', details);
};

const onConsentRejected = () => {
    console.log('[CrochetLayout] Consent rejected');
};

onMounted(() => {
    nextTick(() => {
        if (props.website?.gtm_id) {
            initGTM(props.website.gtm_id);
        }
        
        if (props.website?.google_analytics_id && hasConsentFor(CONSENT_CATEGORIES.ANALYTICS)) {
            initGoogleAnalytics(props.website.google_analytics_id);
        }
        
        if (props.website?.google_ads_active && props.website?.google_adsense_id) {
            if (hasConsentFor(CONSENT_CATEGORIES.ADVERTISING)) {
                setTimeout(() => {
                    initGoogleAdsenseAds();
                }, 500);
            }
        }
    });
});

const initGTM = (gtmId) => {
    if (typeof window === 'undefined' || !gtmId) return;
    (function(w,d,s,l,i){
        w[l]=w[l]||[];
        w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
        j.async=true;
        j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
        f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer',gtmId);
};

const initGoogleAnalytics = (gaId) => {
    if (typeof window === 'undefined' || !gaId) return;
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
    document.head.appendChild(script);
    window.dataLayer = window.dataLayer || [];
    function gtag(){window.dataLayer.push(arguments);}
    window.gtag = gtag;
    gtag('js', new Date());
    gtag('config', gaId);
};

const initGoogleAdsenseAds = () => {
    if (typeof window === 'undefined') return;
    if (!props.website?.google_ads_active || !props.website?.google_adsense_id) return;
    const adElements = document.querySelectorAll('.adsbygoogle');
    adElements.forEach((adElement) => {
        try {
            const status = adElement.getAttribute('data-adsbygoogle-status');
            if (status === 'done') return;
            (window.adsbygoogle = window.adsbygoogle || []).push({});
        } catch (e) {
            console.error('[Google Ads] Error:', e);
        }
    });
};

const mobileMenuOpen = ref(false);
</script>
