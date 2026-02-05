import { ref, readonly } from 'vue';

// Consent categories
export const CONSENT_CATEGORIES = {
    ESSENTIAL: 'essential',      // Always required - site functionality
    ANALYTICS: 'analytics',       // Analytics & performance cookies
    ADVERTISING: 'advertising',   // Advertising & targeting cookies (HBAgency, etc.)
    FUNCTIONAL: 'functional',     // Functional cookies (preferences, etc.)
};

// Storage key for consent
const CONSENT_STORAGE_KEY = 'cookie_consent';
const CONSENT_TIMESTAMP_KEY = 'cookie_consent_timestamp';

// Shared state (singleton pattern)
const hasConsent = ref(false);
const consentGiven = ref(false);
const consentCategories = ref({
    [CONSENT_CATEGORIES.ESSENTIAL]: true, // Always true
    [CONSENT_CATEGORIES.ANALYTICS]: false,
    [CONSENT_CATEGORIES.ADVERTISING]: false,
    [CONSENT_CATEGORIES.FUNCTIONAL]: false,
});

// Check if consent was already given
const initializeConsent = () => {
    if (typeof window === 'undefined' || typeof localStorage === 'undefined') {
        return;
    }

    try {
        const storedConsent = localStorage.getItem(CONSENT_STORAGE_KEY);
        const storedTimestamp = localStorage.getItem(CONSENT_TIMESTAMP_KEY);

        if (storedConsent) {
            const parsed = JSON.parse(storedConsent);
            
            // Check if consent is still valid (expires after 365 days)
            if (storedTimestamp) {
                const timestamp = parseInt(storedTimestamp, 10);
                const now = Date.now();
                const daysSinceConsent = (now - timestamp) / (1000 * 60 * 60 * 24);
                
                if (daysSinceConsent > 365) {
                    // Consent expired, clear it
                    localStorage.removeItem(CONSENT_STORAGE_KEY);
                    localStorage.removeItem(CONSENT_TIMESTAMP_KEY);
                    return;
                }
            }

            consentCategories.value = {
                [CONSENT_CATEGORIES.ESSENTIAL]: true,
                [CONSENT_CATEGORIES.ANALYTICS]: parsed[CONSENT_CATEGORIES.ANALYTICS] ?? false,
                [CONSENT_CATEGORIES.ADVERTISING]: parsed[CONSENT_CATEGORIES.ADVERTISING] ?? false,
                [CONSENT_CATEGORIES.FUNCTIONAL]: parsed[CONSENT_CATEGORIES.FUNCTIONAL] ?? false,
            };
            consentGiven.value = true;
            hasConsent.value = true;

            // Notify that consent was loaded
            dispatchConsentEvent('consent_loaded', consentCategories.value);
        }
    } catch (e) {
        console.error('[CMP] Error loading consent:', e);
    }
};

// Save consent to localStorage
const saveConsent = (categories) => {
    if (typeof window === 'undefined' || typeof localStorage === 'undefined') {
        return;
    }

    try {
        localStorage.setItem(CONSENT_STORAGE_KEY, JSON.stringify(categories));
        localStorage.setItem(CONSENT_TIMESTAMP_KEY, Date.now().toString());
    } catch (e) {
        console.error('[CMP] Error saving consent:', e);
    }
};

// Dispatch custom event for consent changes
const dispatchConsentEvent = (eventName, detail) => {
    if (typeof window === 'undefined') return;
    
    window.dispatchEvent(new CustomEvent(eventName, { detail }));
    
    // Also dispatch generic consent update event
    window.dispatchEvent(new CustomEvent('consent_updated', { detail }));
};

// Accept all cookies
const acceptAll = () => {
    const newConsent = {
        [CONSENT_CATEGORIES.ESSENTIAL]: true,
        [CONSENT_CATEGORIES.ANALYTICS]: true,
        [CONSENT_CATEGORIES.ADVERTISING]: true,
        [CONSENT_CATEGORIES.FUNCTIONAL]: true,
    };
    
    consentCategories.value = newConsent;
    consentGiven.value = true;
    hasConsent.value = true;
    
    saveConsent(newConsent);
    dispatchConsentEvent('consent_accepted_all', newConsent);
    
    // Load ads after consent
    loadAdsAfterConsent();
    
    return newConsent;
};

// Accept only essential cookies
const acceptEssential = () => {
    const newConsent = {
        [CONSENT_CATEGORIES.ESSENTIAL]: true,
        [CONSENT_CATEGORIES.ANALYTICS]: false,
        [CONSENT_CATEGORIES.ADVERTISING]: false,
        [CONSENT_CATEGORIES.FUNCTIONAL]: false,
    };
    
    consentCategories.value = newConsent;
    consentGiven.value = true;
    hasConsent.value = true;
    
    saveConsent(newConsent);
    dispatchConsentEvent('consent_essential_only', newConsent);
    
    return newConsent;
};

// Accept custom selection
const acceptCustom = (categories) => {
    const newConsent = {
        [CONSENT_CATEGORIES.ESSENTIAL]: true, // Always true
        [CONSENT_CATEGORIES.ANALYTICS]: categories[CONSENT_CATEGORIES.ANALYTICS] ?? false,
        [CONSENT_CATEGORIES.ADVERTISING]: categories[CONSENT_CATEGORIES.ADVERTISING] ?? false,
        [CONSENT_CATEGORIES.FUNCTIONAL]: categories[CONSENT_CATEGORIES.FUNCTIONAL] ?? false,
    };
    
    consentCategories.value = newConsent;
    consentGiven.value = true;
    hasConsent.value = true;
    
    saveConsent(newConsent);
    dispatchConsentEvent('consent_custom', newConsent);
    
    // Load ads if advertising consent was given
    if (newConsent[CONSENT_CATEGORIES.ADVERTISING]) {
        loadAdsAfterConsent();
    }
    
    return newConsent;
};

// Revoke consent (clear all)
const revokeConsent = () => {
    if (typeof localStorage !== 'undefined') {
        localStorage.removeItem(CONSENT_STORAGE_KEY);
        localStorage.removeItem(CONSENT_TIMESTAMP_KEY);
    }
    
    consentCategories.value = {
        [CONSENT_CATEGORIES.ESSENTIAL]: true,
        [CONSENT_CATEGORIES.ANALYTICS]: false,
        [CONSENT_CATEGORIES.ADVERTISING]: false,
        [CONSENT_CATEGORIES.FUNCTIONAL]: false,
    };
    consentGiven.value = false;
    hasConsent.value = false;
    
    dispatchConsentEvent('consent_revoked', consentCategories.value);
};

// Check if a specific category has consent
const hasConsentFor = (category) => {
    return consentCategories.value[category] ?? false;
};

// Load HBAgency ads after consent is given
const loadAdsAfterConsent = () => {
    if (typeof window === 'undefined') return;
    
    console.log('[CMP] Loading ads after consent...');
    
    // Dispatch event to signal that ads can be loaded
    window.dispatchEvent(new CustomEvent('ads_consent_granted'));
    
    // Try to initialize HBAgency if script is already loaded
    setTimeout(() => {
        initializeHBAgencyAds();
    }, 500);
};

// Initialize HBAgency ads
const initializeHBAgencyAds = () => {
    if (typeof window === 'undefined') return;
    
    console.log('[CMP] Initializing HBAgency ads...');
    
    // Method 1: Try HBAgency's global refresh/render functions
    if (window.hbagency) {
        console.log('[CMP] hbagency object found:', Object.keys(window.hbagency));
        if (typeof window.hbagency.refresh === 'function') {
            window.hbagency.refresh();
        }
        if (typeof window.hbagency.render === 'function') {
            window.hbagency.render();
        }
        if (typeof window.hbagency.init === 'function') {
            window.hbagency.init();
        }
    }
    
    // Method 2: Try HB (Header Bidding) object
    if (window.HB) {
        console.log('[CMP] HB object found');
        if (typeof window.HB.refresh === 'function') {
            window.HB.refresh();
        }
    }
    
    // Method 3: Try googletag (Google Publisher Tag)
    if (window.googletag && window.googletag.cmd) {
        console.log('[CMP] googletag found');
        window.googletag.cmd.push(function() {
            if (window.googletag.pubads) {
                window.googletag.pubads().refresh();
            }
        });
    }
    
    // Method 4: Try pbjs (Prebid.js)
    if (window.pbjs) {
        console.log('[CMP] pbjs found');
        window.pbjs.que = window.pbjs.que || [];
        window.pbjs.que.push(function() {
            if (typeof window.pbjs.requestBids === 'function') {
                window.pbjs.requestBids({
                    bidsBackHandler: function() {
                        console.log('[CMP] Prebid bids returned');
                    }
                });
            }
        });
    }
    
    // Dispatch custom events
    window.dispatchEvent(new Event('load'));
    window.dispatchEvent(new CustomEvent('DOMContentLoaded'));
    window.dispatchEvent(new CustomEvent('hbagency:ready'));
};

// Main composable function
export function useConsentManagement() {
    // Initialize on first use
    if (!consentGiven.value) {
        initializeConsent();
    }
    
    return {
        // State (read-only)
        hasConsent: readonly(hasConsent),
        consentGiven: readonly(consentGiven),
        consentCategories: readonly(consentCategories),
        
        // Actions
        acceptAll,
        acceptEssential,
        acceptCustom,
        revokeConsent,
        
        // Helpers
        hasConsentFor,
        initializeConsent,
        loadAdsAfterConsent,
        initializeHBAgencyAds,
        
        // Constants
        CONSENT_CATEGORIES,
    };
}

// Export for direct use
export default useConsentManagement;
