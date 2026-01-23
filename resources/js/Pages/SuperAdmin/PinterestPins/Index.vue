<script setup>
import { ref, computed, onMounted } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: null
    },
    pins: {
        type: Object,
        default: () => ({ data: [] })
    },
    articlesWithoutPins: {
        type: Array,
        default: () => []
    },
    missingDesignPins: {
        type: Array,
        default: () => []
    }
});

const showDeleteModal = ref(false);
const pinToDelete = ref(null);
const selectedPins = ref([]);
const showBulkActions = computed(() => selectedPins.value.length > 0);

// Missing Design Selection
const selectedMissingPins = ref([]);
const showBulkDesignModal = ref(false);
const isUpdatingDesigns = ref(false);

const selectAllMissing = computed({
    get: () => selectedMissingPins.value.length === props.missingDesignPins.length && props.missingDesignPins.length > 0,
    set: (value) => {
        if (value) {
            selectedMissingPins.value = props.missingDesignPins.map(p => p.id);
        } else {
            selectedMissingPins.value = [];
        }
    }
});

const toggleMissingPin = (pinId) => {
    const index = selectedMissingPins.value.indexOf(pinId);
    if (index > -1) {
        selectedMissingPins.value.splice(index, 1);
    } else {
        selectedMissingPins.value.push(pinId);
    }
};

// Bulk Design Form
const bulkForm = useForm({
    pin_ids: [],
    headline_color: '#ffffff',
    subheadline_color: '#d4a574',
    headline_font: 'arial',
    subheadline_font: 'georgia',
    headline_font_size: 28,
    subheadline_font_size: 22,
    overlay_color: '#000000',
    overlay_opacity: 70,
    frame_design: 'simple_center',
    domain_name: props.currentWebsite?.domain || (props.currentWebsite?.slug ? props.currentWebsite.slug + '.com' : ''),
});

const openBulkDesignModal = () => {
    if (selectedMissingPins.value.length === 0) return;
    bulkForm.pin_ids = selectedMissingPins.value;
    showBulkDesignModal.value = true;
};

const closeBulkDesignModal = () => {
    showBulkDesignModal.value = false;
};

const submitBulkDesign = () => {
    isUpdatingDesigns.value = true;
    bulkForm.post(route('superadmin.pinterest-pins.bulk-update-designs', { website: props.currentWebsite.id }), {
        onSuccess: () => {
            showBulkDesignModal.value = false;
            selectedMissingPins.value = [];
            isUpdatingDesigns.value = false;
        },
        onError: () => {
            isUpdatingDesigns.value = false;
        }
    });
};

// Preview for Bulk Design
const previewPin = computed(() => {
    if (selectedMissingPins.value.length === 0) return null;
    return props.missingDesignPins.find(p => p.id === selectedMissingPins.value[0]);
});

// Preview logic shared between modals
const getPreviewStyles = (formData) => ({
    overlayBg: `rgba(${hexToRgb(formData.overlay_color)}, ${formData.overlay_opacity / 100})`,
    headlineColor: formData.headline_color,
    subheadlineColor: formData.subheadline_color,
    headlineFontSize: `${formData.headline_font_size / 2.2}px`,
    subheadlineFontSize: `${formData.subheadline_font_size / 2.2}px`,
    headlineFontFamily: getFontFamily(formData.headline_font),
    subheadlineFontFamily: getFontFamily(formData.subheadline_font),
});

const previewStyles = computed(() => getPreviewStyles(bulkForm));
const articlePreviewStyles = computed(() => getPreviewStyles(articleForm));

const hexToRgb = (hex) => {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result 
        ? `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}`
        : '0, 0, 0';
};

const getFontFamily = (fontId) => {
    switch (fontId) {
        case 'arial': return 'Arial, Helvetica, sans-serif';
        case 'montserrat': return '"Montserrat", sans-serif';
        case 'bebas-neue': return '"Bebas Neue", cursive';
        case 'poppins': return '"Poppins", sans-serif';
        case 'roboto': return '"Roboto", sans-serif';
        case 'open-sans': return '"Open Sans", sans-serif';
        case 'georgia': return 'Georgia, serif';
        case 'times': return '"Times New Roman", Times, serif';
        case 'playfair-display': return '"Playfair Display", serif';
        case 'dancing-script': return '"Dancing Script", cursive';
        case 'pacifico': return '"Pacifico", cursive';
        case 'great-vibes': return '"Great Vibes", cursive';
        default: return 'Arial, sans-serif';
    }
};

const fontOptions = {
    'sans-serif': [
        { id: 'arial', name: 'Arial Bold' },
        { id: 'montserrat', name: 'Montserrat' },
        { id: 'bebas-neue', name: 'Bebas Neue' },
        { id: 'poppins', name: 'Poppins' },
        { id: 'roboto', name: 'Roboto' },
        { id: 'open-sans', name: 'Open Sans' },
    ],
    'serif': [
        { id: 'georgia', name: 'Georgia' },
        { id: 'times', name: 'Times New Roman' },
        { id: 'playfair-display', name: 'Playfair Display' },
    ],
    'script': [
        { id: 'dancing-script', name: 'Dancing Script' },
        { id: 'pacifico', name: 'Pacifico' },
        { id: 'great-vibes', name: 'Great Vibes' },
    ]
};

const frameDesigns = [
    { id: 'simple_center', name: 'Simple Center', description: 'Classic text overlay on images', bgColor: '#000000', textColor: '#ffffff' },
    { id: 'black_christmas', name: 'Black Christmas', description: 'Elegant black with white lines', bgColor: '#000000', textColor: '#ffffff' },
    { id: 'green_dashed', name: 'Green Dashed', description: 'Vibrant green with dashed border', bgColor: '#22c55e', textColor: '#ffffff' },
    { id: 'ribbon_banner', name: 'Ribbon Banner', description: 'Cream banner with brown ribbon', bgColor: '#fef9e7', textColor: '#8b4513' },
    { id: 'star_rating', name: 'Star Rating', description: 'Orange banner with star rating', bgColor: '#eba13e', textColor: '#16120b' },
    { id: 'minimal_bold', name: 'Minimal Bold', description: 'White background with thick black borders', bgColor: '#ffffff', textColor: '#000000' },
    { id: 'crispy_orange', name: 'Crispy Orange', description: 'Orange banner with yellow accents', bgColor: '#e67e22', textColor: '#ffffff' },
    { id: 'torn_paper', name: 'Torn Paper', description: 'White background with torn edges', bgColor: '#ffffff', textColor: '#000000' },
];

// Copy Info Modal
const showCopyInfoModal = ref(false);
const copyInfoPin = ref(null);
const copyInfoData = ref(null);
const isGeneratingCopyInfo = ref(false);
const copyInfoError = ref('');
const copySuccess = ref('');

// Bulk generate modal (for articles without pins)
const showBulkGenerateModal = ref(false);
const selectedArticles = ref([]);
const isGenerating = ref(false);

const articleForm = useForm({
    article_ids: [],
    headline_color: '#ffffff',
    subheadline_color: '#d4a574',
    headline_font: 'arial',
    subheadline_font: 'georgia',
    headline_font_size: 28,
    subheadline_font_size: 22,
    overlay_color: '#000000',
    overlay_opacity: 70,
    frame_design: 'simple_center',
    domain_name: props.currentWebsite?.domain || (props.currentWebsite?.slug ? props.currentWebsite.slug + '.com' : ''),
});

const selectAll = computed({
    get: () => selectedArticles.value.length === props.articlesWithoutPins.length && props.articlesWithoutPins.length > 0,
    set: (value) => {
        if (value) {
            selectedArticles.value = props.articlesWithoutPins.map(a => a.id);
        } else {
            selectedArticles.value = [];
        }
    }
});

const toggleArticle = (articleId) => {
    const index = selectedArticles.value.indexOf(articleId);
    if (index > -1) {
        selectedArticles.value.splice(index, 1);
    } else {
        selectedArticles.value.push(articleId);
    }
};

const bulkGeneratePins = () => {
    if (selectedArticles.value.length === 0) return;
    
    isGenerating.value = true;
    articleForm.article_ids = selectedArticles.value;
    
    articleForm.post(route('superadmin.pinterest-pins.bulk-generate', { website: props.currentWebsite.id }), {
        onSuccess: () => {
            showBulkGenerateModal.value = false;
            selectedArticles.value = [];
            isGenerating.value = false;
        },
        onError: () => {
            isGenerating.value = false;
        }
    });
};

const closeBulkModal = () => {
    showBulkGenerateModal.value = false;
    selectedArticles.value = [];
};

const openDeleteModal = (pin) => {
    pinToDelete.value = pin;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    pinToDelete.value = null;
};

const deletePin = () => {
    if (!pinToDelete.value) return;
    
    router.delete(route('superadmin.pinterest-pins.destroy', {
        website: props.currentWebsite.id,
        pin: pinToDelete.value.id
    }), {
        onSuccess: () => closeDeleteModal()
    });
};

const regeneratePin = (pin) => {
    router.post(route('superadmin.pinterest-pins.regenerate', {
        website: props.currentWebsite.id,
        pin: pin.id
    }));
};

const downloadPin = (pin) => {
    window.location.href = route('superadmin.pinterest-pins.download', {
        website: props.currentWebsite.id,
        pin: pin.id
    });
};

const copyPinInfo = async (pin) => {
    copyInfoPin.value = pin;
    copyInfoData.value = null;
    copyInfoError.value = '';
    copySuccess.value = '';
    showCopyInfoModal.value = true;
    isGeneratingCopyInfo.value = true;
    
    try {
        const response = await axios.post(route('superadmin.pinterest-pins.generate-copy-info', {
            website: props.currentWebsite.id,
            pin: pin.id
        }));
        
        copyInfoData.value = response.data;
    } catch (error) {
        console.error('Failed to generate copy info:', error);
        copyInfoError.value = error.response?.data?.error || 'Failed to generate copy info. Please try again.';
    } finally {
        isGeneratingCopyInfo.value = false;
    }
};

const closeCopyInfoModal = () => {
    showCopyInfoModal.value = false;
    copyInfoPin.value = null;
    copyInfoData.value = null;
    copyInfoError.value = '';
    copySuccess.value = '';
};

const copyToClipboard = async (text, field) => {
    try {
        await navigator.clipboard.writeText(text);
        copySuccess.value = `${field} copied to clipboard!`;
        setTimeout(() => {
            copySuccess.value = '';
        }, 2000);
    } catch (error) {
        console.error('Failed to copy:', error);
    }
};

const copyAllInfo = async () => {
    if (!copyInfoData.value) return;
    
    const text = `Title: ${copyInfoData.value.title}\n\nDescription: ${copyInfoData.value.description}\n\nAlt Text: ${copyInfoData.value.alt_text}\n\nLink: ${copyInfoData.value.link}`;
    await copyToClipboard(text, 'All info');
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'generated':
            return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
        case 'pending':
            return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
        case 'failed':
            return 'bg-red-500/20 text-red-400 border-red-500/30';
        default:
            return 'bg-gray-500/20 text-gray-400 border-gray-500/30';
    }
};
</script>

<template>
    <Head title="Pinterest Designs" />

    <SuperAdminLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Pinterest Designs</h1>
                    <p class="text-gray-400 mt-1">Create and manage Pinterest pin images for your articles</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Bulk Design Button -->
                    <button
                        v-if="selectedMissingPins.length > 0"
                        @click="openBulkDesignModal"
                        class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-purple-500/40 animate-bounce-subtle"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Design {{ selectedMissingPins.length }} Selected
                    </button>
                    <!-- Generate Missing Pins Button -->
                    <button
                        v-if="articlesWithoutPins.length > 0"
                        @click="showBulkGenerateModal = true"
                        class="flex items-center gap-2 px-5 py-2.5 bg-[#252525] hover:bg-[#2a2a2a] text-white rounded-xl font-medium transition-all border border-[#3a3a3a]"
                    >
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Generate Missing ({{ articlesWithoutPins.length }})
                    </button>
                    <Link
                        :href="route('superadmin.pinterest-pins.create', { website: currentWebsite.id })"
                        class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white rounded-xl font-medium transition-all shadow-lg shadow-pink-500/20"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Pin Design
                    </Link>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-6 py-4 rounded-xl mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <div v-if="$page.props.errors?.error" class="bg-red-900/50 border border-red-500 text-red-200 px-6 py-4 rounded-xl mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $page.props.errors.error }}</span>
                </div>
            </div>

            <!-- Missing Designs Section -->
            <div v-if="missingDesignPins.length > 0" class="mb-12">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Missing Pin Designs ({{ missingDesignPins.length }})
                    </h2>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="selectAllMissing"
                                class="w-4 h-4 rounded bg-[#252525] border-[#3a3a3a] text-pink-500 focus:ring-pink-500"
                            />
                            Select All
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="pin in missingDesignPins"
                        :key="pin.id"
                        class="bg-[#1a1a1a] rounded-2xl border-2 overflow-hidden cursor-pointer transition-all"
                        :class="[
                            selectedMissingPins.includes(pin.id)
                                ? 'border-pink-500 bg-pink-500/5'
                                : 'border-[#2a2a2a] hover:border-[#3a3a3a]'
                        ]"
                        @click="toggleMissingPin(pin.id)"
                    >
                        <div class="relative aspect-[2/3] bg-[#0f0f0f]">
                            <!-- Show top image as placeholder since generated_image is missing -->
                            <img
                                v-if="pin.top_image_url"
                                :src="pin.top_image_url"
                                :alt="pin.title"
                                class="w-full h-full object-cover opacity-50"
                            />
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="bg-black/60 text-white text-xs px-3 py-1.5 rounded-full backdrop-blur-sm border border-white/10">
                                    Needs Design
                                </span>
                            </div>
                            <!-- Selection indicator -->
                            <div class="absolute top-3 left-3">
                                <div 
                                    class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                    :class="[
                                        selectedMissingPins.includes(pin.id)
                                            ? 'bg-pink-500 border-pink-500'
                                            : 'bg-black/40 border-white/30'
                                    ]"
                                >
                                    <svg v-if="selectedMissingPins.includes(pin.id)" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-white font-medium truncate flex-1">{{ pin.title }}</h3>
                                <button
                                    @click.stop="() => {
                                        selectedMissingPins = [pin.id];
                                        openBulkDesignModal();
                                    }"
                                    class="p-1.5 bg-pink-500/20 hover:bg-pink-500 text-pink-400 hover:text-white rounded-lg transition-all ml-2"
                                    title="Design this pin"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-gray-500 text-sm truncate">{{ pin.article?.title || 'Unknown article' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="pins.data.length === 0" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-pink-500/20 to-rose-500/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Pinterest Designs Yet</h3>
                <p class="text-gray-400 mb-6 max-w-md mx-auto">
                    Create beautiful Pinterest pin images from your articles to drive traffic from Pinterest to your website.
                </p>
                <Link
                    :href="route('superadmin.pinterest-pins.create', { website: currentWebsite.id })"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white rounded-xl font-medium transition-all"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Your First Pin
                </Link>
            </div>

            <!-- Pins Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="pin in pins.data"
                    :key="pin.id"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden group hover:border-pink-500/50 transition-all"
                >
                    <!-- Pin Image Preview -->
                    <div class="relative aspect-[2/3] bg-[#0f0f0f]">
                        <img
                            v-if="pin.generated_image_url"
                            :src="pin.generated_image_url"
                            :alt="pin.title"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <div v-if="pin.status === 'pending'" class="text-center">
                                <svg class="w-12 h-12 text-yellow-400 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-yellow-400 text-sm">Generating...</span>
                            </div>
                            <div v-else-if="pin.status === 'failed'" class="text-center px-4">
                                <svg class="w-12 h-12 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-red-400 text-sm">Generation Failed</span>
                                <p class="text-gray-500 text-xs mt-1">{{ pin.error_message }}</p>
                            </div>
                            <div v-else class="text-center">
                                <svg class="w-12 h-12 text-gray-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Hover Actions -->
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <button
                                v-if="pin.generated_image_url"
                                @click="downloadPin(pin)"
                                class="p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors"
                                title="Download"
                            >
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </button>
                            <button
                                @click="regeneratePin(pin)"
                                class="p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors"
                                title="Regenerate"
                            >
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            <button
                                @click="copyPinInfo(pin)"
                                class="p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors"
                                title="Copy Info"
                            >
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <button
                                @click="openDeleteModal(pin)"
                                class="p-3 bg-red-500/20 hover:bg-red-500/40 rounded-xl transition-colors"
                                title="Delete"
                            >
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-lg border', getStatusBadgeClass(pin.status)]">
                                {{ pin.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Pin Info -->
                    <div class="p-4">
                        <h3 class="text-white font-medium truncate mb-1">{{ pin.title }}</h3>
                        <p class="text-gray-500 text-sm truncate">{{ pin.article?.title || 'Unknown article' }}</p>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-[#2a2a2a]">
                            <span class="text-gray-500 text-xs">
                                {{ new Date(pin.created_at).toLocaleDateString() }}
                            </span>
                            <Link
                                v-if="pin.article"
                                :href="route('superadmin.articles.edit', { website: currentWebsite.id, article: pin.article.id })"
                                class="text-pink-400 hover:text-pink-300 text-xs font-medium"
                            >
                                View Article →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pins.links && pins.links.length > 3" class="mt-8 flex justify-center gap-2">
                <Link
                    v-for="link in pins.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm transition-colors',
                        link.active
                            ? 'bg-pink-500 text-white'
                            : link.url
                                ? 'bg-[#1a1a1a] text-gray-400 hover:text-white hover:bg-[#252525]'
                                : 'bg-[#1a1a1a] text-gray-600 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/70" @click="closeDeleteModal"></div>
            <div class="relative bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-semibold text-white mb-2">Delete Pinterest Pin</h3>
                <p class="text-gray-400 mb-6">
                    Are you sure you want to delete this Pinterest pin? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="closeDeleteModal"
                        class="px-4 py-2 bg-[#252525] hover:bg-[#2a2a2a] text-white rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deletePin"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors"
                    >
                        Delete Pin
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Generate Modal (Articles Without Pins) -->
        <div v-if="showBulkGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/90" @click="closeBulkModal"></div>
            <div class="relative bg-[#0f0f0f] rounded-2xl border border-[#2a2a2a] w-full max-w-[95vw] h-[90vh] mx-4 overflow-hidden flex flex-col">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-[#2a2a2a]">
                    <div>
                        <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Generate Pinterest Pins from Articles
                        </h3>
                        <p class="text-gray-400 mt-1">Select articles and choose a design to apply to all</p>
                    </div>
                    <button @click="closeBulkModal" class="p-2 hover:bg-[#252525] rounded-xl transition-colors group">
                        <svg class="w-6 h-6 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-hidden flex">
                    <!-- Left Side: Article Selection -->
                    <div class="w-full lg:w-1/3 overflow-y-auto p-6 border-r border-[#2a2a2a] bg-[#0a0a0a]">
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Select Articles</h4>
                            <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer hover:text-gray-300 transition-colors">
                                <input type="checkbox" v-model="selectAll" class="w-4 h-4 rounded bg-[#1a1a1a] border-[#2a2a2a] text-pink-500 focus:ring-pink-500" />
                                Select All
                            </label>
                        </div>
                        
                        <div class="space-y-2">
                            <div
                                v-for="article in articlesWithoutPins"
                                :key="article.id"
                                @click="toggleArticle(article.id)"
                                class="flex items-center gap-4 p-3 rounded-xl cursor-pointer transition-all border-2"
                                :class="[
                                    selectedArticles.includes(article.id)
                                        ? 'bg-pink-500/10 border-pink-500/50'
                                        : 'bg-[#1a1a1a] border-transparent hover:border-[#3a3a3a]'
                                ]"
                            >
                                <div 
                                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                    :class="[
                                        selectedArticles.includes(article.id)
                                            ? 'bg-pink-500 border-pink-500'
                                            : 'border-white/20'
                                    ]"
                                >
                                    <svg v-if="selectedArticles.includes(article.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <img v-if="article.featured_image" :src="article.featured_image" class="w-10 h-10 rounded-lg object-cover bg-gray-800" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-white text-sm font-medium truncate">{{ article.title }}</p>
                                    <p class="text-gray-500 text-[10px]">{{ new Date(article.created_at).toLocaleDateString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Middle: Design Settings -->
                    <div class="w-full lg:w-1/3 overflow-y-auto p-8 border-r border-[#2a2a2a] custom-scrollbar bg-[#0f0f0f]">
                        <div class="space-y-8">
                            <!-- Design Selector -->
                            <section>
                                <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-pink-500/20 text-pink-500 flex items-center justify-center text-[10px]">1</span>
                                    Choose Frame Design
                                </h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <button 
                                        v-for="design in frameDesigns" 
                                        :key="design.id"
                                        type="button"
                                        @click="articleForm.frame_design = design.id"
                                        class="relative p-3 rounded-xl border-2 transition-all text-left overflow-hidden group"
                                        :class="[
                                            articleForm.frame_design === design.id 
                                                ? 'border-pink-500 bg-pink-500/10' 
                                                : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#1a1a1a]'
                                        ]"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div 
                                                class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center text-[8px] font-bold"
                                                :style="{ backgroundColor: design.bgColor, color: design.textColor }"
                                            >
                                                Aa
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-white text-xs font-bold truncate">{{ design.name }}</p>
                                                <p class="text-gray-500 text-[9px] truncate">{{ design.description }}</p>
                                            </div>
                                        </div>
                                        <div v-if="articleForm.frame_design === design.id" class="absolute top-1 right-1 w-4 h-4 bg-pink-500 rounded-full flex items-center justify-center">
                                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </button>
                                </div>
                            </section>

                            <!-- Style Settings -->
                            <section>
                                <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-pink-500/20 text-pink-500 flex items-center justify-center text-[10px]">2</span>
                                    Colors & Fonts
                                </h4>
                                <div class="space-y-6">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] text-gray-500 mb-2 uppercase font-bold">Headline</label>
                                            <div class="flex items-center gap-3 bg-[#1a1a1a] p-2 rounded-xl border border-[#2a2a2a]">
                                                <input v-model="articleForm.headline_color" type="color" class="w-8 h-8 rounded-lg cursor-pointer border-0 bg-transparent" />
                                                <input v-model="articleForm.headline_color" type="text" class="bg-transparent border-0 text-white text-[10px] w-full focus:ring-0" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-gray-500 mb-2 uppercase font-bold">Subheadline</label>
                                            <div class="flex items-center gap-3 bg-[#1a1a1a] p-2 rounded-xl border border-[#2a2a2a]">
                                                <input v-model="articleForm.subheadline_color" type="color" class="w-8 h-8 rounded-lg cursor-pointer border-0 bg-transparent" />
                                                <input v-model="articleForm.subheadline_color" type="text" class="bg-transparent border-0 text-white text-[10px] w-full focus:ring-0" />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <label class="block text-[10px] text-gray-500 uppercase font-bold">Headline Size</label>
                                            <span class="text-pink-500 text-[10px] font-bold">{{ articleForm.headline_font_size }}px</span>
                                        </div>
                                        <input v-model="articleForm.headline_font_size" type="range" min="12" max="100" class="w-full h-2 bg-[#2a2a2a] rounded-lg appearance-none cursor-pointer accent-pink-500" />
                                    </div>

                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <label class="block text-[10px] text-gray-500 uppercase font-bold">Subheadline Size</label>
                                            <span class="text-pink-500 text-[10px] font-bold">{{ articleForm.subheadline_font_size }}px</span>
                                        </div>
                                        <input v-model="articleForm.subheadline_font_size" type="range" min="12" max="100" class="w-full h-2 bg-[#2a2a2a] rounded-lg appearance-none cursor-pointer accent-pink-500" />
                                    </div>

                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-2 uppercase font-bold">Domain Name</label>
                                        <input v-model="articleForm.domain_name" type="text" class="w-full bg-[#1a1a1a] border-[#2a2a2a] rounded-xl text-white text-sm focus:ring-pink-500 p-3" />
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- Right Side: Synchronized Preview -->
                    <div class="hidden lg:flex w-1/3 bg-[#0a0a0a] items-center justify-center p-8 relative">
                        <div class="absolute top-6 left-8">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Design Preview
                            </h4>
                        </div>
                        
                        <div v-if="selectedArticles.length > 0" class="relative bg-white rounded-2xl shadow-2xl overflow-hidden" style="aspect-ratio: 1/2; width: 100%; max-height: 600px;">
                            <!-- Top Image -->
                            <div class="absolute top-0 left-0 right-0 h-[40.2%] overflow-hidden bg-gray-100">
                                <img
                                    v-if="articlesWithoutPins.find(a => a.id === selectedArticles[0])?.featured_image"
                                    :src="articlesWithoutPins.find(a => a.id === selectedArticles[0]).featured_image"
                                    class="w-full h-full object-cover"
                                />
                            </div>

                            <!-- Dynamic Text Overlay based on frame_design -->
                            <div 
                                v-if="articleForm.frame_design === 'simple_center'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: articlePreviewStyles.overlayBg }"
                            >
                                <p class="text-center font-bold lowercase w-full truncate px-1" :style="{ color: articlePreviewStyles.headlineColor, fontSize: articlePreviewStyles.headlineFontSize, fontFamily: articlePreviewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.title || 'Title Preview' }}
                                </p>
                                <p class="text-center italic mt-1 w-full truncate px-1" :style="{ color: articlePreviewStyles.subheadlineColor, fontSize: articlePreviewStyles.subheadlineFontSize, fontFamily: articlePreviewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.excerpt || 'Subheadline Preview' }}
                                </p>
                            </div>

                            <div 
                                v-else-if="articleForm.frame_design === 'black_christmas'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: articlePreviewStyles.overlayBg }"
                            >
                                <div class="absolute top-2 left-0 right-0 h-0.5" :style="{ backgroundColor: articlePreviewStyles.headlineColor }"></div>
                                <div class="absolute bottom-2 left-0 right-0 h-0.5" :style="{ backgroundColor: articlePreviewStyles.headlineColor }"></div>
                                <p class="text-center font-bold capitalize w-full truncate px-1" :style="{ color: articlePreviewStyles.headlineColor, fontSize: articlePreviewStyles.headlineFontSize, fontFamily: articlePreviewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.title || 'Title Preview' }}
                                </p>
                                <p class="text-center mt-1 capitalize w-full truncate px-1" :style="{ color: articlePreviewStyles.subheadlineColor, fontSize: articlePreviewStyles.subheadlineFontSize, fontFamily: articlePreviewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.excerpt || 'Subheadline Preview' }}
                                </p>
                            </div>

                            <div 
                                v-else-if="articleForm.frame_design === 'green_dashed'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: articlePreviewStyles.overlayBg }"
                            >
                                <div class="absolute top-2 left-0 right-0 flex gap-1 px-1">
                                    <div v-for="i in 15" :key="'t-'+i" class="flex-1 h-0.5 bg-white rounded-full"></div>
                                </div>
                                <div class="absolute bottom-2 left-0 right-0 flex gap-1 px-1">
                                    <div v-for="i in 15" :key="'b-'+i" class="flex-1 h-0.5 bg-white rounded-full"></div>
                                </div>
                                <p class="text-center font-bold lowercase w-full truncate px-1" :style="{ color: articlePreviewStyles.headlineColor, fontSize: articlePreviewStyles.headlineFontSize, fontFamily: articlePreviewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.title || 'Title Preview' }}
                                </p>
                                <p class="text-center italic mt-1 w-full truncate px-1" :style="{ color: articlePreviewStyles.subheadlineColor, fontSize: articlePreviewStyles.subheadlineFontSize, fontFamily: articlePreviewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.excerpt || 'Subheadline Preview' }}
                                </p>
                            </div>

                            <div 
                                v-else-if="articleForm.frame_design === 'ribbon_banner'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-between py-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: articlePreviewStyles.overlayBg }"
                            >
                                <div class="absolute top-0 left-0 right-0 h-3" :style="{ backgroundColor: articlePreviewStyles.headlineColor }"></div>
                                <div class="flex-1 flex flex-col items-center justify-center w-full px-2 mt-2">
                                    <p class="text-center font-bold uppercase w-full truncate px-1" :style="{ color: articlePreviewStyles.headlineColor, fontSize: articlePreviewStyles.headlineFontSize, fontFamily: articlePreviewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                        {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.title || 'Title Preview' }}
                                    </p>
                                    <p class="text-center font-bold uppercase w-full mt-1 truncate px-1" :style="{ color: articlePreviewStyles.subheadlineColor, fontSize: articlePreviewStyles.subheadlineFontSize, fontFamily: articlePreviewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                        {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.excerpt || 'Subheadline Preview' }}
                                    </p>
                                </div>
                                <div class="relative w-[85%] flex items-center justify-center mb-1 h-6" :style="{ backgroundColor: articlePreviewStyles.headlineColor }">
                                    <div class="absolute left-0 top-0 bottom-0 w-2" :style="{ backgroundColor: articleForm.overlay_color, clipPath: 'polygon(0 0, 100% 50%, 0 100%)' }"></div>
                                    <div class="absolute right-0 top-0 bottom-0 w-2" :style="{ backgroundColor: articleForm.overlay_color, clipPath: 'polygon(100% 0, 0 50%, 100% 100%)' }"></div>
                                    <p class="text-center font-bold text-white uppercase text-[8px] tracking-widest px-2">{{ articleForm.domain_name }}</p>
                                </div>
                            </div>

                            <div 
                                v-else-if="articleForm.frame_design === 'star_rating'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: articlePreviewStyles.overlayBg }"
                            >
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 h-5 rounded-full flex items-center justify-center gap-0.5" :style="{ backgroundColor: articlePreviewStyles.headlineColor }">
                                    <div v-for="i in 5" :key="'s-'+i" class="w-2 h-2 bg-yellow-200" style="clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);"></div>
                                </div>
                                <p class="text-center font-bold uppercase w-full truncate px-1" :style="{ color: articlePreviewStyles.headlineColor, fontSize: articlePreviewStyles.headlineFontSize, fontFamily: articlePreviewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.title || 'Title Preview' }}
                                </p>
                                <p class="text-center font-bold uppercase w-full mt-1 truncate px-1" :style="{ color: articlePreviewStyles.subheadlineColor, fontSize: articlePreviewStyles.subheadlineFontSize, fontFamily: articlePreviewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.excerpt || 'Subheadline Preview' }}
                                </p>
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 px-4 h-5 rounded-full flex items-center justify-center min-w-[80px]" :style="{ backgroundColor: articlePreviewStyles.headlineColor }">
                                    <p class="text-center font-bold text-white uppercase text-[8px] tracking-wider">{{ articleForm.domain_name }}</p>
                                </div>
                            </div>

                            <div 
                                v-else-if="articleForm.frame_design === 'minimal_bold'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: articlePreviewStyles.overlayBg }"
                            >
                                <div class="absolute top-0 left-0 right-0 h-1" :style="{ backgroundColor: articlePreviewStyles.headlineColor }"></div>
                                <div class="absolute bottom-0 left-0 right-0 h-1" :style="{ backgroundColor: articlePreviewStyles.headlineColor }"></div>
                                <p class="text-center font-bold lowercase w-full truncate px-1" :style="{ color: articlePreviewStyles.headlineColor, fontSize: articlePreviewStyles.headlineFontSize, fontFamily: articlePreviewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.title || 'Title Preview' }}
                                </p>
                                <p class="text-center lowercase w-full mt-1 truncate px-1" :style="{ color: articlePreviewStyles.subheadlineColor, fontSize: articlePreviewStyles.subheadlineFontSize, fontFamily: articlePreviewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.excerpt || 'Subheadline Preview' }}
                                </p>
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 px-4 h-5 flex items-center justify-center min-w-[80px]" :style="{ backgroundColor: articlePreviewStyles.headlineColor }">
                                    <p class="text-center font-bold text-white lowercase text-[8px]">{{ articleForm.domain_name }}</p>
                                </div>
                            </div>

                            <div 
                                v-else-if="articleForm.frame_design === 'crispy_orange'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: articlePreviewStyles.overlayBg }"
                            >
                                <div class="absolute top-0 left-0 right-0 h-1" :style="{ backgroundColor: articlePreviewStyles.headlineColor }"></div>
                                <div class="absolute bottom-0 left-0 right-0 h-1" :style="{ backgroundColor: articlePreviewStyles.headlineColor }"></div>
                                <p class="text-center font-bold uppercase w-full truncate px-1" :style="{ color: articlePreviewStyles.headlineColor, fontSize: articlePreviewStyles.headlineFontSize, fontFamily: articlePreviewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.title || 'Title Preview' }}
                                </p>
                                <p class="text-center capitalize w-full mt-1 truncate px-1" :style="{ color: articlePreviewStyles.subheadlineColor, fontSize: articlePreviewStyles.subheadlineFontSize, fontFamily: articlePreviewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.excerpt || 'Subheadline Preview' }}
                                </p>
                            </div>

                            <div 
                                v-else-if="articleForm.frame_design === 'torn_paper'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: articlePreviewStyles.overlayBg }"
                            >
                                <div class="absolute top-0 left-0 right-0 h-3 -mt-1.5" :style="{ backgroundColor: articleForm.overlay_color, clipPath: 'polygon(0% 100%, 5% 20%, 10% 80%, 15% 10%, 20% 90%, 25% 30%, 30% 70%, 35% 0%, 40% 100%, 45% 20%, 50% 80%, 55% 10%, 60% 90%, 65% 30%, 70% 70%, 75% 0%, 80% 100%, 85% 20%, 90% 80%, 95% 10%, 100% 100%)' }"></div>
                                <div class="absolute bottom-0 left-0 right-0 h-3 -mb-1.5" :style="{ backgroundColor: articleForm.overlay_color, clipPath: 'polygon(0% 0%, 5% 80%, 10% 20%, 15% 90%, 20% 10%, 25% 70%, 30% 30%, 35% 100%, 40% 0%, 45% 80%, 50% 20%, 55% 90%, 60% 10%, 65% 70%, 70% 30%, 75% 100%, 80% 0%, 85% 80%, 90% 20%, 95% 90%, 100% 0%)' }"></div>
                                <p class="text-center font-bold w-full truncate px-1" :style="{ color: articlePreviewStyles.headlineColor, fontSize: articlePreviewStyles.headlineFontSize, fontFamily: articlePreviewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.title || 'Title Preview' }}
                                </p>
                                <p class="text-center font-bold w-full mt-1 truncate px-1" :style="{ color: articlePreviewStyles.subheadlineColor, fontSize: articlePreviewStyles.subheadlineFontSize, fontFamily: articlePreviewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ articlesWithoutPins.find(a => a.id === selectedArticles[0])?.excerpt || 'Subheadline Preview' }}
                                </p>
                            </div>

                            <!-- Bottom Image -->
                            <div class="absolute bottom-0 left-0 right-0 h-[40.2%] overflow-hidden bg-gray-50">
                                <img
                                    v-if="articlesWithoutPins.find(a => a.id === selectedArticles[0])?.featured_image"
                                    :src="articlesWithoutPins.find(a => a.id === selectedArticles[0]).featured_image"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                        </div>
                        <div v-else class="text-center">
                            <svg class="w-16 h-16 text-gray-800 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-gray-600 text-sm">Select an article to see preview</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 bg-[#1a1a1a] border-t border-[#2a2a2a] flex items-center justify-between">
                    <div class="text-sm text-gray-400">
                        {{ selectedArticles.length }} articles selected
                    </div>
                    <div class="flex items-center gap-4">
                        <button @click="closeBulkModal" class="px-6 py-2.5 bg-[#2a2a2a] hover:bg-[#353535] text-white rounded-xl font-medium transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="bulkGeneratePins" 
                            :disabled="selectedArticles.length === 0 || isGenerating"
                            class="px-10 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-3 disabled:opacity-50"
                        >
                            <svg v-if="isGenerating" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isGenerating ? 'Generating...' : 'Generate & Design All' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Copy Info Modal -->
        <div v-if="showCopyInfoModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/70" @click="closeCopyInfoModal"></div>
            <div class="relative bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            AI-Generated Pinterest Copy
                        </h3>
                        <p class="text-gray-400 text-sm mt-1">Copy this optimized content for your Pinterest pin</p>
                    </div>
                    <button @click="closeCopyInfoModal" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Success Message -->
                <div v-if="copySuccess" class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-4 py-3 rounded-xl mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ copySuccess }}
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="isGeneratingCopyInfo" class="py-12 text-center">
                    <svg class="w-12 h-12 text-purple-400 animate-spin mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-400">AI is generating optimized Pinterest copy...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="copyInfoError" class="bg-red-900/30 border border-red-500/50 text-red-300 px-4 py-6 rounded-xl text-center">
                    <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mb-4">{{ copyInfoError }}</p>
                    <button
                        @click="copyPinInfo(copyInfoPin)"
                        class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-colors"
                    >
                        Try Again
                    </button>
                </div>

                <!-- Copy Info Content -->
                <div v-else-if="copyInfoData" class="space-y-4">
                    <!-- Title -->
                    <div class="bg-[#252525] rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-400">Pin Title</label>
                            <button
                                @click="copyToClipboard(copyInfoData.title, 'Title')"
                                class="flex items-center gap-1 px-2 py-1 text-xs bg-[#1a1a1a] hover:bg-[#2a2a2a] text-gray-300 rounded transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copy
                            </button>
                        </div>
                        <p class="text-white">{{ copyInfoData.title }}</p>
                    </div>

                    <!-- Description -->
                    <div class="bg-[#252525] rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-400">Pin Description (with hashtags)</label>
                            <button
                                @click="copyToClipboard(copyInfoData.description, 'Description')"
                                class="flex items-center gap-1 px-2 py-1 text-xs bg-[#1a1a1a] hover:bg-[#2a2a2a] text-gray-300 rounded transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copy
                            </button>
                        </div>
                        <p class="text-white whitespace-pre-wrap">{{ copyInfoData.description }}</p>
                    </div>

                    <!-- Alt Text -->
                    <div class="bg-[#252525] rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-400">Alt Text (for accessibility)</label>
                            <button
                                @click="copyToClipboard(copyInfoData.alt_text, 'Alt text')"
                                class="flex items-center gap-1 px-2 py-1 text-xs bg-[#1a1a1a] hover:bg-[#2a2a2a] text-gray-300 rounded transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copy
                            </button>
                        </div>
                        <p class="text-white">{{ copyInfoData.alt_text }}</p>
                    </div>

                    <!-- Link -->
                    <div class="bg-[#252525] rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-400">Destination Link</label>
                            <button
                                @click="copyToClipboard(copyInfoData.link, 'Link')"
                                class="flex items-center gap-1 px-2 py-1 text-xs bg-[#1a1a1a] hover:bg-[#2a2a2a] text-gray-300 rounded transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copy
                            </button>
                        </div>
                        <a :href="copyInfoData.link" target="_blank" class="text-pink-400 hover:text-pink-300 underline break-all">{{ copyInfoData.link }}</a>
                    </div>

                    <!-- Hashtags -->
                    <div v-if="copyInfoData.hashtags && copyInfoData.hashtags.length > 0" class="bg-[#252525] rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-400">Suggested Hashtags</label>
                            <button
                                @click="copyToClipboard(copyInfoData.hashtags.map(h => '#' + h).join(' '), 'Hashtags')"
                                class="flex items-center gap-1 px-2 py-1 text-xs bg-[#1a1a1a] hover:bg-[#2a2a2a] text-gray-300 rounded transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copy All
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in copyInfoData.hashtags"
                                :key="tag"
                                class="px-2 py-1 bg-pink-500/20 text-pink-300 rounded-lg text-sm"
                            >
                                #{{ tag }}
                            </span>
                        </div>
                    </div>

                    <!-- Copy All Button -->
                    <div class="pt-4 border-t border-[#2a2a2a]">
                        <button
                            @click="copyAllInfo"
                            class="w-full px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white rounded-xl font-medium transition-all flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Copy All Information
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Design Modal -->
        <div v-if="showBulkDesignModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/90" @click="closeBulkDesignModal"></div>
            <div class="relative bg-[#0f0f0f] rounded-2xl border border-[#2a2a2a] w-full max-w-[95vw] h-[90vh] mx-4 overflow-hidden flex flex-col">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-[#2a2a2a]">
                    <div>
                        <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                            <span class="px-3 py-1 bg-pink-500 rounded-lg text-sm">{{ selectedMissingPins.length }}</span>
                            Bulk Design Pinterest Pins
                        </h3>
                        <p class="text-gray-400 mt-1">Changes below will affect all {{ selectedMissingPins.length }} selected pins</p>
                    </div>
                    <button @click="closeBulkDesignModal" class="p-2 hover:bg-[#252525] rounded-xl transition-colors group">
                        <svg class="w-6 h-6 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-hidden flex">
                    <!-- Design Settings -->
                    <div class="w-full lg:w-1/2 overflow-y-auto p-8 border-r border-[#2a2a2a] custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left Column: Colors & Fonts -->
                            <div class="space-y-8">
                                <!-- Colors -->
                                <section>
                                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Colors & Opacity</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-2 uppercase">Headline</label>
                                            <div class="flex items-center gap-3 bg-[#1a1a1a] p-2 rounded-xl border border-[#2a2a2a]">
                                                <input v-model="bulkForm.headline_color" type="color" class="w-8 h-8 rounded-lg cursor-pointer border-0 bg-transparent" />
                                                <input v-model="bulkForm.headline_color" type="text" class="bg-transparent border-0 text-white text-xs w-full focus:ring-0" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-2 uppercase">Subheadline</label>
                                            <div class="flex items-center gap-3 bg-[#1a1a1a] p-2 rounded-xl border border-[#2a2a2a]">
                                                <input v-model="bulkForm.subheadline_color" type="color" class="w-8 h-8 rounded-lg cursor-pointer border-0 bg-transparent" />
                                                <input v-model="bulkForm.subheadline_color" type="text" class="bg-transparent border-0 text-white text-xs w-full focus:ring-0" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-2 uppercase">Overlay</label>
                                            <div class="flex items-center gap-3 bg-[#1a1a1a] p-2 rounded-xl border border-[#2a2a2a]">
                                                <input v-model="bulkForm.overlay_color" type="color" class="w-8 h-8 rounded-lg cursor-pointer border-0 bg-transparent" />
                                                <input v-model="bulkForm.overlay_color" type="text" class="bg-transparent border-0 text-white text-xs w-full focus:ring-0" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-2 uppercase">Opacity: {{ bulkForm.overlay_opacity }}%</label>
                                            <input v-model="bulkForm.overlay_opacity" type="range" min="0" max="100" class="w-full h-2 bg-[#2a2a2a] rounded-lg appearance-none cursor-pointer accent-pink-500 mt-3" />
                                        </div>
                                    </div>
                                </section>

                                <!-- Fonts -->
                                <section>
                                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Typography</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-2 uppercase">Headline Font</label>
                                            <select v-model="bulkForm.headline_font" class="w-full bg-[#1a1a1a] border-[#2a2a2a] rounded-xl text-white text-sm focus:ring-pink-500">
                                                <optgroup v-for="(fonts, group) in fontOptions" :key="group" :label="group.toUpperCase()">
                                                    <option v-for="font in fonts" :key="font.id" :value="font.id">{{ font.name }}</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-2 uppercase">Headline Size: {{ bulkForm.headline_font_size }}px</label>
                                            <input v-model="bulkForm.headline_font_size" type="range" min="12" max="100" class="w-full h-2 bg-[#2a2a2a] rounded-lg appearance-none cursor-pointer accent-pink-500" />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-2 uppercase">Subheadline Font</label>
                                            <select v-model="bulkForm.subheadline_font" class="w-full bg-[#1a1a1a] border-[#2a2a2a] rounded-xl text-white text-sm focus:ring-pink-500">
                                                <optgroup v-for="(fonts, group) in fontOptions" :key="group" :label="group.toUpperCase()">
                                                    <option v-for="font in fonts" :key="font.id" :value="font.id">{{ font.name }}</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-2 uppercase">Subheadline Size: {{ bulkForm.subheadline_font_size }}px</label>
                                            <input v-model="bulkForm.subheadline_font_size" type="range" min="12" max="100" class="w-full h-2 bg-[#2a2a2a] rounded-lg appearance-none cursor-pointer accent-pink-500" />
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <!-- Right Column: Design & Domain -->
                            <div class="space-y-8">
                                <!-- Frame Designs -->
                                <section>
                                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Frame Design</h4>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button 
                                            v-for="design in frameDesigns" 
                                            :key="design.id"
                                            type="button"
                                            @click="bulkForm.frame_design = design.id"
                                            class="relative p-3 rounded-xl border-2 transition-all text-left overflow-hidden group"
                                            :class="[
                                                bulkForm.frame_design === design.id 
                                                    ? 'border-pink-500 bg-pink-500/10' 
                                                    : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#1a1a1a]'
                                            ]"
                                        >
                                            <div class="flex items-center gap-3">
                                                <!-- Mini Preview Circle -->
                                                <div 
                                                    class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center text-[8px] font-bold"
                                                    :style="{ backgroundColor: design.bgColor, color: design.textColor }"
                                                >
                                                    Aa
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-white text-xs font-bold truncate">{{ design.name }}</p>
                                                    <p class="text-gray-500 text-[9px] truncate line-clamp-1">{{ design.description }}</p>
                                                </div>
                                            </div>
                                            <!-- Selection Checkmark -->
                                            <div v-if="bulkForm.frame_design === design.id" class="absolute top-1 right-1 w-4 h-4 bg-pink-500 rounded-full flex items-center justify-center">
                                                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </button>
                                    </div>
                                </section>

                                <!-- Domain Name -->
                                <section>
                                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Website Info</h4>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-2 uppercase">Domain Name</label>
                                        <input 
                                            v-model="bulkForm.domain_name" 
                                            type="text" 
                                            class="w-full bg-[#1a1a1a] border-[#2a2a2a] rounded-xl text-white text-sm focus:ring-pink-500 p-3"
                                            placeholder="e.g., WWW.HADIK.COM"
                                        />
                                        <p class="text-[10px] text-gray-500 mt-2">Will be shown in Ribbon, Star, and Minimal designs.</p>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>

                    <!-- Synchronized Preview -->
                    <div class="hidden lg:flex w-1/2 bg-[#0a0a0a] items-center justify-center p-8 relative">
                        <div class="absolute top-6 left-8">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Live Preview (Example Pin)
                            </h4>
                        </div>
                        
                        <!-- The Preview Box (Synced with bulkForm) -->
                        <div v-if="previewPin" class="relative bg-white rounded-2xl shadow-2xl overflow-hidden" style="aspect-ratio: 1/2; height: 100%; max-height: 700px;">
                            <!-- Top Image -->
                            <div class="absolute top-0 left-0 right-0 h-[40.2%] overflow-hidden">
                                <img
                                    v-if="previewPin.top_image_url"
                                    :src="previewPin.top_image_url"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">Image</div>
                            </div>

                            <!-- Dynamic Text Overlay based on frame_design -->
                            <!-- simple_center -->
                            <div 
                                v-if="bulkForm.frame_design === 'simple_center'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <p class="text-center font-bold lowercase w-full truncate px-1" :style="{ color: previewStyles.headlineColor, fontSize: previewStyles.headlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ previewPin.headline_text || 'Title Preview' }}
                                </p>
                                <p class="text-center italic mt-1 w-full truncate px-1" :style="{ color: previewStyles.subheadlineColor, fontSize: previewStyles.subheadlineFontSize, fontFamily: previewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ previewPin.subheadline_text || 'Subheadline Preview' }}
                                </p>
                            </div>

                            <!-- black_christmas -->
                            <div 
                                v-else-if="bulkForm.frame_design === 'black_christmas'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <div class="absolute top-2 left-0 right-0 h-0.5" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <div class="absolute bottom-2 left-0 right-0 h-0.5" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <p class="text-center font-bold capitalize w-full truncate px-1" :style="{ color: previewStyles.headlineColor, fontSize: previewStyles.headlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ previewPin.headline_text || 'Title Preview' }}
                                </p>
                                <p class="text-center mt-1 capitalize w-full truncate px-1" :style="{ color: previewStyles.subheadlineColor, fontSize: previewStyles.subheadlineFontSize, fontFamily: previewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ previewPin.subheadline_text || 'Subheadline Preview' }}
                                </p>
                            </div>

                            <!-- green_dashed -->
                            <div 
                                v-else-if="bulkForm.frame_design === 'green_dashed'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <div class="absolute top-2 left-0 right-0 flex gap-1 px-1">
                                    <div v-for="i in 15" :key="'t-'+i" class="flex-1 h-0.5 bg-white rounded-full"></div>
                                </div>
                                <div class="absolute bottom-2 left-0 right-0 flex gap-1 px-1">
                                    <div v-for="i in 15" :key="'b-'+i" class="flex-1 h-0.5 bg-white rounded-full"></div>
                                </div>
                                <p class="text-center font-bold lowercase w-full truncate px-1" :style="{ color: previewStyles.headlineColor, fontSize: previewStyles.headlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ previewPin.headline_text || 'Title Preview' }}
                                </p>
                                <p class="text-center italic mt-1 w-full truncate px-1" :style="{ color: previewStyles.subheadlineColor, fontSize: previewStyles.subheadlineFontSize, fontFamily: previewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">
                                    {{ previewPin.subheadline_text || 'Subheadline Preview' }}
                                </p>
                            </div>

                            <!-- ribbon_banner -->
                            <div 
                                v-else-if="bulkForm.frame_design === 'ribbon_banner'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-between py-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <div class="absolute top-0 left-0 right-0 h-3" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <div class="flex-1 flex flex-col items-center justify-center w-full px-2 mt-2">
                                    <p class="text-center font-bold uppercase w-full truncate px-1" :style="{ color: previewStyles.headlineColor, fontSize: previewStyles.headlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.headline_text || 'Title Preview' }}</p>
                                    <p class="text-center font-bold uppercase w-full mt-1 truncate px-1" :style="{ color: previewStyles.subheadlineColor, fontSize: previewStyles.subheadlineFontSize, fontFamily: previewStyles.subheadlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.subheadline_text || 'Subheadline Preview' }}</p>
                                </div>
                                <div class="relative w-[85%] flex items-center justify-center mb-1 h-6" :style="{ backgroundColor: previewStyles.headlineColor }">
                                    <div class="absolute left-0 top-0 bottom-0 w-2" :style="{ backgroundColor: bulkForm.overlay_color, clipPath: 'polygon(0 0, 100% 50%, 0 100%)' }"></div>
                                    <div class="absolute right-0 top-0 bottom-0 w-2" :style="{ backgroundColor: bulkForm.overlay_color, clipPath: 'polygon(100% 0, 0 50%, 100% 100%)' }"></div>
                                    <p class="text-center font-bold text-white uppercase text-[8px] tracking-widest px-2">{{ bulkForm.domain_name }}</p>
                                </div>
                            </div>

                            <!-- star_rating -->
                            <div 
                                v-else-if="bulkForm.frame_design === 'star_rating'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 h-5 rounded-full flex items-center justify-center gap-0.5" :style="{ backgroundColor: previewStyles.headlineColor }">
                                    <div v-for="i in 5" :key="'s-'+i" class="w-2 h-2 bg-yellow-200" style="clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);"></div>
                                </div>
                                <p class="text-center font-bold uppercase w-full truncate px-1" :style="{ color: previewStyles.headlineColor, fontSize: previewStyles.headlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.headline_text || 'Title Preview' }}</p>
                                <p class="text-center font-bold uppercase w-full mt-1 truncate px-1" :style="{ color: previewStyles.subheadlineColor, fontSize: previewStyles.subheadlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.subheadline_text || 'Subheadline Preview' }}</p>
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 px-4 h-5 rounded-full flex items-center justify-center min-w-[80px]" :style="{ backgroundColor: previewStyles.headlineColor }">
                                    <p class="text-center font-bold text-white uppercase text-[8px] tracking-wider">{{ bulkForm.domain_name }}</p>
                                </div>
                            </div>

                            <!-- minimal_bold -->
                            <div 
                                v-else-if="bulkForm.frame_design === 'minimal_bold'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <div class="absolute top-0 left-0 right-0 h-1" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <div class="absolute bottom-0 left-0 right-0 h-1" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <p class="text-center font-bold lowercase w-full truncate px-1" :style="{ color: previewStyles.headlineColor, fontSize: previewStyles.headlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.headline_text || 'Title Preview' }}</p>
                                <p class="text-center lowercase w-full mt-1 truncate px-1" :style="{ color: previewStyles.subheadlineColor, fontSize: previewStyles.subheadlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.subheadline_text || 'Subheadline Preview' }}</p>
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 px-4 h-5 flex items-center justify-center min-w-[80px]" :style="{ backgroundColor: previewStyles.headlineColor }">
                                    <p class="text-center font-bold text-white lowercase text-[8px]">{{ bulkForm.domain_name }}</p>
                                </div>
                            </div>

                            <!-- crispy_orange -->
                            <div 
                                v-else-if="bulkForm.frame_design === 'crispy_orange'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <div class="absolute top-0 left-0 right-0 h-1" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <div class="absolute bottom-0 left-0 right-0 h-1" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <p class="text-center font-bold uppercase w-full truncate px-1" :style="{ color: previewStyles.headlineColor, fontSize: previewStyles.headlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.headline_text || 'Title Preview' }}</p>
                                <p class="text-center capitalize w-full mt-1 truncate px-1" :style="{ color: previewStyles.subheadlineColor, fontSize: previewStyles.subheadlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.subheadline_text || 'Subheadline Preview' }}</p>
                            </div>

                            <!-- torn_paper -->
                            <div 
                                v-else-if="bulkForm.frame_design === 'torn_paper'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <div class="absolute top-0 left-0 right-0 h-3 -mt-1.5" :style="{ backgroundColor: bulkForm.overlay_color, clipPath: 'polygon(0% 100%, 5% 20%, 10% 80%, 15% 10%, 20% 90%, 25% 30%, 30% 70%, 35% 0%, 40% 100%, 45% 20%, 50% 80%, 55% 10%, 60% 90%, 65% 30%, 70% 70%, 75% 0%, 80% 100%, 85% 20%, 90% 80%, 95% 10%, 100% 100%)' }"></div>
                                <div class="absolute bottom-0 left-0 right-0 h-3 -mb-1.5" :style="{ backgroundColor: bulkForm.overlay_color, clipPath: 'polygon(0% 0%, 5% 80%, 10% 20%, 15% 90%, 20% 10%, 25% 70%, 30% 30%, 35% 100%, 40% 0%, 45% 80%, 50% 20%, 55% 90%, 60% 10%, 65% 70%, 70% 30%, 75% 100%, 80% 0%, 85% 80%, 90% 20%, 95% 90%, 100% 0%)' }"></div>
                                <p class="text-center font-bold w-full truncate px-1" :style="{ color: previewStyles.headlineColor, fontSize: previewStyles.headlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.headline_text || 'Title Preview' }}</p>
                                <p class="text-center font-bold w-full mt-1 truncate px-1" :style="{ color: previewStyles.subheadlineColor, fontSize: previewStyles.subheadlineFontSize, fontFamily: previewStyles.headlineFontFamily, wordWrap: 'break-word', overflowWrap: 'break-word', lineHeight: '1.1' }">{{ previewPin.subheadline_text || 'Subheadline Preview' }}</p>
                            </div>

                            <!-- Bottom Image -->
                            <div class="absolute bottom-0 left-0 right-0 h-[40.2%] overflow-hidden">
                                <img
                                    v-if="previewPin.bottom_image_url || previewPin.top_image_url"
                                    :src="previewPin.bottom_image_url || previewPin.top_image_url"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-400">Image</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 bg-[#1a1a1a] border-t border-[#2a2a2a] flex items-center justify-between">
                    <div class="flex items-center gap-4 text-sm text-gray-400">
                        <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Headline and subheadline texts are unique for each pin, but style and frame will be synchronized.
                    </div>
                    <div class="flex items-center gap-4">
                        <button @click="closeBulkDesignModal" class="px-6 py-2.5 bg-[#2a2a2a] hover:bg-[#353535] text-white rounded-xl font-medium transition-colors">
                            Cancel
                        </button>
                        <button 
                            @click="submitBulkDesign" 
                            :disabled="isUpdatingDesigns"
                            class="px-10 py-2.5 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-pink-500/20 flex items-center gap-3 disabled:opacity-50"
                        >
                            <svg v-if="isUpdatingDesigns" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isUpdatingDesigns ? 'Generating All...' : 'Apply & Generate All Pins' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
@keyframes bounce-subtle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
.animate-bounce-subtle {
    animation: bounce-subtle 2s infinite;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0f0f0f;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #2a2a2a;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #3a3a3a;
}
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #ec4899;
    cursor: pointer;
    border: 3px solid #0f0f0f;
}
</style>
