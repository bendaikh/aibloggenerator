<template>
    <Teleport to="body">
        <!-- Cookie Consent Banner -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0"
        >
            <div 
                v-if="showBanner && !consentGiven"
                class="fixed bottom-0 left-0 right-0 z-[9999] bg-white shadow-2xl border-t border-gray-200"
            >
                <div class="container mx-auto px-4 py-4 md:py-6">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4 lg:gap-6">
                        <!-- Cookie Icon & Text -->
                        <div class="flex-1">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">We Value Your Privacy</h3>
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        We use cookies to enhance your browsing experience, serve personalized ads, and analyze our traffic. 
                                        By clicking "Accept All", you consent to our use of cookies including advertising partners like HBAgency.
                                        <button 
                                            @click="showDetails = !showDetails" 
                                            class="text-emerald-600 hover:text-emerald-700 font-medium ml-1 underline"
                                        >
                                            {{ showDetails ? 'Hide details' : 'Learn more' }}
                                        </button>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Detailed Cookie Settings (expandable) -->
                            <Transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-96"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100 max-h-96"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <div v-if="showDetails" class="mt-4 ml-13 overflow-hidden">
                                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                                        <!-- Essential Cookies -->
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="font-medium text-gray-900 text-sm">Essential Cookies</span>
                                                <p class="text-xs text-gray-500">Required for basic site functionality</p>
                                            </div>
                                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-full">Always On</span>
                                        </div>
                                        
                                        <!-- Analytics Cookies -->
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="font-medium text-gray-900 text-sm">Analytics Cookies</span>
                                                <p class="text-xs text-gray-500">Help us understand how visitors use our site</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="customConsent.analytics" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-300 peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                            </label>
                                        </div>
                                        
                                        <!-- Advertising Cookies -->
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="font-medium text-gray-900 text-sm">Advertising Cookies</span>
                                                <p class="text-xs text-gray-500">Used to show relevant ads (HBAgency, etc.)</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="customConsent.advertising" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-300 peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                            </label>
                                        </div>
                                        
                                        <!-- Functional Cookies -->
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="font-medium text-gray-900 text-sm">Functional Cookies</span>
                                                <p class="text-xs text-gray-500">Remember your preferences and settings</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="customConsent.functional" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-300 peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                            <button 
                                v-if="showDetails"
                                @click="handleSavePreferences"
                                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-full transition order-3 sm:order-1"
                            >
                                Save Preferences
                            </button>
                            <button 
                                @click="handleRejectAll"
                                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-full transition order-2"
                            >
                                Reject All
                            </button>
                            <button 
                                @click="handleAcceptAll"
                                class="px-6 py-2.5 text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-600 rounded-full transition shadow-lg shadow-emerald-200 order-1 sm:order-3"
                            >
                                Accept All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
        
        <!-- Small "Cookie Settings" button (shows after consent is given) -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-90"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-90"
        >
            <button
                v-if="showSettingsButton && consentGiven"
                @click="reopenBanner"
                class="fixed bottom-4 left-4 z-[9998] w-10 h-10 bg-white shadow-lg rounded-full flex items-center justify-center hover:bg-gray-50 transition border border-gray-200"
                title="Cookie Settings"
            >
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useConsentManagement, CONSENT_CATEGORIES } from '@/composables/useConsentManagement';

const props = defineProps({
    // Whether to show the settings button after consent is given
    showSettingsButton: {
        type: Boolean,
        default: true
    },
    // Auto-show banner on mount if consent not given
    autoShow: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['consent-given', 'consent-rejected']);

const {
    consentGiven,
    consentCategories,
    acceptAll,
    acceptEssential,
    acceptCustom,
    revokeConsent,
    CONSENT_CATEGORIES: categories
} = useConsentManagement();

const showBanner = ref(false);
const showDetails = ref(false);

// Custom consent preferences (for the detailed settings)
const customConsent = reactive({
    analytics: false,
    advertising: true, // Default to true since we want ads
    functional: false
});

// Show banner on mount if consent not given
onMounted(() => {
    if (props.autoShow && !consentGiven.value) {
        // Small delay for smoother page load
        setTimeout(() => {
            showBanner.value = true;
        }, 500);
    }
});

// Handle accept all cookies
const handleAcceptAll = () => {
    acceptAll();
    showBanner.value = false;
    emit('consent-given', { all: true });
};

// Handle reject all (only essential)
const handleRejectAll = () => {
    acceptEssential();
    showBanner.value = false;
    emit('consent-rejected');
};

// Handle save custom preferences
const handleSavePreferences = () => {
    acceptCustom({
        [CONSENT_CATEGORIES.ANALYTICS]: customConsent.analytics,
        [CONSENT_CATEGORIES.ADVERTISING]: customConsent.advertising,
        [CONSENT_CATEGORIES.FUNCTIONAL]: customConsent.functional
    });
    showBanner.value = false;
    emit('consent-given', { custom: true, preferences: customConsent });
};

// Reopen the banner to modify settings
const reopenBanner = () => {
    // Pre-fill the custom consent with current values
    customConsent.analytics = consentCategories.value[CONSENT_CATEGORIES.ANALYTICS];
    customConsent.advertising = consentCategories.value[CONSENT_CATEGORIES.ADVERTISING];
    customConsent.functional = consentCategories.value[CONSENT_CATEGORIES.FUNCTIONAL];
    
    // Revoke current consent and show banner
    revokeConsent();
    showDetails.value = true; // Show details by default when reopening
    showBanner.value = true;
};

// Expose methods for parent components
defineExpose({
    showBanner,
    reopenBanner,
    handleAcceptAll,
    handleRejectAll
});
</script>
