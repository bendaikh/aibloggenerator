<script setup>
import { ref, watch } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: null
    },
    articles: {
        type: Array,
        default: () => []
    }
});

const selectedArticle = ref(null);
const isGeneratingHeadlines = ref(false);
const aiError = ref('');
const previewImage = ref(null);
const isGeneratingPreview = ref(false);
const previewError = ref('');

const form = useForm({
    article_id: '',
    headline_text: '',
    subheadline_text: '',
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

// Font options - Including standard and Google Fonts
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

// Available frame designs
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

// Generate AI headlines
const generateAIHeadlines = async () => {
    if (!form.article_id) return;
    
    isGeneratingHeadlines.value = true;
    aiError.value = '';
    
    try {
        const response = await axios.post(route('superadmin.pinterest-pins.generate-headlines', { 
            website: props.currentWebsite.id 
        }), {
            article_id: form.article_id
        });
        
        if (response.data.headline) {
            form.headline_text = response.data.headline;
        }
        if (response.data.subheadline) {
            form.subheadline_text = response.data.subheadline;
        }
    } catch (error) {
        console.error('Failed to generate headlines:', error);
        aiError.value = error.response?.data?.error || 'Failed to generate headlines. Please try again.';
    } finally {
        isGeneratingHeadlines.value = false;
    }
};

let previewDebounceTimer = null;

const generatePreview = async () => {
    if (!form.article_id || !form.headline_text || !form.subheadline_text) return;
    
    isGeneratingPreview.value = true;
    previewError.value = '';
    
    try {
        const response = await axios.post(route('superadmin.pinterest-pins.preview', {
            website: props.currentWebsite.id
        }), {
            article_id: form.article_id,
            headline_text: form.headline_text,
            subheadline_text: form.subheadline_text,
            headline_color: form.headline_color,
            subheadline_color: form.subheadline_color,
            headline_font: form.headline_font,
            subheadline_font: form.subheadline_font,
            headline_font_size: form.headline_font_size,
            subheadline_font_size: form.subheadline_font_size,
            overlay_color: form.overlay_color,
            overlay_opacity: form.overlay_opacity,
            frame_design: form.frame_design,
            domain_name: form.domain_name,
        });
        
        if (response.data.image) {
            previewImage.value = response.data.image;
        }
    } catch (error) {
        console.error('Failed to generate preview:', error);
        previewError.value = error.response?.data?.error || 'Failed to generate preview.';
    } finally {
        isGeneratingPreview.value = false;
    }
};

const debouncedPreview = () => {
    if (previewDebounceTimer) clearTimeout(previewDebounceTimer);
    previewDebounceTimer = setTimeout(() => {
        generatePreview();
    }, 50);
};

// When article is selected, auto-generate AI headlines
watch(() => form.article_id, async (articleId) => {
    if (articleId) {
        selectedArticle.value = props.articles.find(a => a.id === parseInt(articleId));
        if (selectedArticle.value) {
            await generateAIHeadlines();
        }
    } else {
        selectedArticle.value = null;
        previewImage.value = null;
    }
});

// Auto-generate preview when any design setting changes
watch(
    () => [
        form.article_id, form.headline_text, form.subheadline_text,
        form.headline_color, form.subheadline_color,
        form.headline_font, form.subheadline_font,
        form.headline_font_size, form.subheadline_font_size,
        form.overlay_color, form.overlay_opacity,
        form.frame_design, form.domain_name,
    ],
    () => { debouncedPreview(); }
);

const submitForm = () => {
    form.post(route('superadmin.pinterest-pins.store', { website: props.currentWebsite.id }));
};
</script>

<template>
    <Head title="Create Pinterest Pin" />

    <SuperAdminLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <Link
                    :href="route('superadmin.pinterest-pins.index', { website: currentWebsite.id })"
                    class="p-2 bg-[#1a1a1a] hover:bg-[#252525] rounded-xl transition-colors"
                >
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-3xl font-bold text-white">Create Pinterest Pin</h1>
                    <p class="text-gray-400 mt-1">Design a Pinterest pin image from an article (512 x 1024px)</p>
                </div>
            </div>

            <!-- Error Messages -->
            <div v-if="form.errors.error" class="bg-red-900/50 border border-red-500 text-red-200 px-6 py-4 rounded-xl mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ form.errors.error }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Form -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Article Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Select Article *
                            </label>
                            <select
                                v-model="form.article_id"
                                required
                                class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-xl text-white focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                                <option value="">Choose an article...</option>
                                <option v-for="article in articles" :key="article.id" :value="article.id">
                                    {{ article.title }}
                                </option>
                            </select>
                            <p v-if="form.errors.article_id" class="mt-1 text-sm text-red-500">{{ form.errors.article_id }}</p>
                            <p class="mt-1 text-xs text-gray-500">Only published articles with featured images are shown</p>
                        </div>

                        <!-- Text Content -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    Text Overlay
                                </h3>
                                <button
                                    v-if="form.article_id"
                                    type="button"
                                    @click="generateAIHeadlines"
                                    :disabled="isGeneratingHeadlines"
                                    class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-xs font-medium rounded-lg transition-all disabled:opacity-50"
                                >
                                    <svg v-if="isGeneratingHeadlines" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    {{ isGeneratingHeadlines ? 'Generating...' : 'AI Generate' }}
                                </button>
                            </div>

                            <!-- AI Error Message -->
                            <div v-if="aiError" class="bg-red-900/30 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg text-sm">
                                {{ aiError }}
                            </div>

                            <!-- AI Loading Indicator -->
                            <div v-if="isGeneratingHeadlines" class="bg-purple-900/30 border border-purple-500/50 text-purple-300 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                AI is generating attractive headlines...
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Headline Text *
                                </label>
                                <input
                                    v-model="form.headline_text"
                                    type="text"
                                    required
                                    placeholder="e.g., cozy cinnamon"
                                    class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-xl text-white focus:ring-2 focus:ring-pink-500"
                                />
                                <p v-if="form.errors.headline_text" class="mt-1 text-sm text-red-500">{{ form.errors.headline_text }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Subheadline Text *
                                </label>
                                <input
                                    v-model="form.subheadline_text"
                                    type="text"
                                    required
                                    placeholder="e.g., Sugar donut bread"
                                    class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-xl text-white focus:ring-2 focus:ring-pink-500"
                                />
                                <p v-if="form.errors.subheadline_text" class="mt-1 text-sm text-red-500">{{ form.errors.subheadline_text }}</p>
                            </div>

                            <!-- Domain Name Field (Only for designs that use it) -->
                            <div v-if="['ribbon_banner', 'star_rating', 'minimal_bold'].includes(form.frame_design)" class="bg-pink-500/5 border border-pink-500/20 rounded-xl p-4 mt-2">
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Domain Name (displayed in design)
                                </label>
                                <input
                                    v-model="form.domain_name"
                                    type="text"
                                    placeholder="e.g., WWW.HADIK.COM"
                                    class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-xl text-white focus:ring-2 focus:ring-pink-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">This will be shown in the capsule or ribbon of the design</p>
                            </div>
                        </div>

                        <!-- Color Settings -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                Colors & Style
                            </h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Headline Color
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <input
                                            v-model="form.headline_color"
                                            type="color"
                                            class="w-12 h-12 rounded-lg cursor-pointer border-2 border-[#2a2a2a]"
                                        />
                                        <input
                                            v-model="form.headline_color"
                                            type="text"
                                            class="flex-1 px-3 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white text-sm"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Subheadline Color
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <input
                                            v-model="form.subheadline_color"
                                            type="color"
                                            class="w-12 h-12 rounded-lg cursor-pointer border-2 border-[#2a2a2a]"
                                        />
                                        <input
                                            v-model="form.subheadline_color"
                                            type="text"
                                            class="flex-1 px-3 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white text-sm"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Font Settings -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Headline Font
                                    </label>
                                    <select
                                        v-model="form.headline_font"
                                        class="w-full px-3 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white text-sm focus:ring-2 focus:ring-pink-500"
                                    >
                                        <optgroup label="Sans Serif">
                                            <option v-for="font in fontOptions['sans-serif']" :key="font.id" :value="font.id">{{ font.name }}</option>
                                        </optgroup>
                                        <optgroup label="Serif">
                                            <option v-for="font in fontOptions['serif']" :key="font.id" :value="font.id">{{ font.name }}</option>
                                        </optgroup>
                                        <optgroup label="Script / Decorative">
                                            <option v-for="font in fontOptions['script']" :key="font.id" :value="font.id">{{ font.name }}</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Subheadline Font
                                    </label>
                                    <select
                                        v-model="form.subheadline_font"
                                        class="w-full px-3 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white text-sm focus:ring-2 focus:ring-pink-500"
                                    >
                                        <optgroup label="Sans Serif">
                                            <option v-for="font in fontOptions['sans-serif']" :key="font.id" :value="font.id">{{ font.name }}</option>
                                        </optgroup>
                                        <optgroup label="Serif">
                                            <option v-for="font in fontOptions['serif']" :key="font.id" :value="font.id">{{ font.name }}</option>
                                        </optgroup>
                                        <optgroup label="Script / Decorative">
                                            <option v-for="font in fontOptions['script']" :key="font.id" :value="font.id">{{ font.name }}</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Headline Size ({{ form.headline_font_size }}px)
                                    </label>
                                    <input
                                        v-model="form.headline_font_size"
                                        type="range"
                                        min="12"
                                        max="60"
                                        class="w-full h-3 bg-[#2a2a2a] rounded-lg appearance-none cursor-pointer accent-pink-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Subheadline Size ({{ form.subheadline_font_size }}px)
                                    </label>
                                    <input
                                        v-model="form.subheadline_font_size"
                                        type="range"
                                        min="12"
                                        max="60"
                                        class="w-full h-3 bg-[#2a2a2a] rounded-lg appearance-none cursor-pointer accent-pink-500"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Overlay Color
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <input
                                            v-model="form.overlay_color"
                                            type="color"
                                            class="w-12 h-12 rounded-lg cursor-pointer border-2 border-[#2a2a2a]"
                                        />
                                        <input
                                            v-model="form.overlay_color"
                                            type="text"
                                            class="flex-1 px-3 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white text-sm"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Overlay Opacity: {{ form.overlay_opacity }}%
                                    </label>
                                    <input
                                        v-model="form.overlay_opacity"
                                        type="range"
                                        min="0"
                                        max="100"
                                        class="w-full h-3 bg-[#2a2a2a] rounded-lg appearance-none cursor-pointer accent-pink-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                                Frame Design
                            </h3>

                            <div class="grid grid-cols-3 gap-3">
                                <!-- Simple Center -->
                                <button
                                    type="button"
                                    @click="form.frame_design = 'simple_center'"
                                    :class="[
                                        'relative p-3 rounded-xl border-2 transition-all text-left',
                                        form.frame_design === 'simple_center' 
                                            ? 'border-pink-500 bg-pink-500/10' 
                                            : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#0a0a0a]'
                                    ]"
                                >
                                    <div class="w-full aspect-[1/2] rounded-lg mb-2 overflow-hidden flex flex-col bg-[#1a1a1a]">
                                        <!-- Top image placeholder -->
                                        <div class="h-[38%] bg-gradient-to-br from-amber-700 to-amber-900"></div>
                                        <!-- Text overlay with dark overlay -->
                                        <div class="h-[24%] bg-black/70 flex flex-col items-center justify-center px-1">
                                            <p class="text-white text-[9px] font-bold lowercase leading-tight">cozy cinnamon</p>
                                            <p class="text-amber-300 text-[7px] italic leading-tight">Sugar donut bread</p>
                                        </div>
                                        <!-- Bottom image placeholder -->
                                        <div class="h-[38%] bg-gradient-to-br from-amber-800 to-amber-950"></div>
                                    </div>
                                    <p class="text-white text-xs font-medium truncate">Simple Center</p>
                                    <p class="text-gray-500 text-[10px] truncate">Classic text overlay</p>
                                    <div v-if="form.frame_design === 'simple_center'" class="absolute top-2 right-2 w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Black Christmas -->
                                <button
                                    type="button"
                                    @click="form.frame_design = 'black_christmas'"
                                    :class="[
                                        'relative p-3 rounded-xl border-2 transition-all text-left',
                                        form.frame_design === 'black_christmas' 
                                            ? 'border-pink-500 bg-pink-500/10' 
                                            : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#0a0a0a]'
                                    ]"
                                >
                                    <div class="w-full aspect-[1/2] rounded-lg mb-2 overflow-hidden flex flex-col bg-black">
                                        <div class="h-[38%] bg-gradient-to-br from-gray-600 to-gray-800"></div>
                                        <div class="h-[24%] bg-black flex flex-col items-center justify-center px-1 relative">
                                            <div class="absolute top-1 left-0 right-0 h-[1px] bg-white"></div>
                                            <div class="absolute bottom-1 left-0 right-0 h-[1px] bg-white"></div>
                                            <p class="text-white text-[9px] font-bold capitalize leading-tight">White Christmas</p>
                                            <p class="text-amber-300 text-[7px] capitalize leading-tight">Mojitos</p>
                                        </div>
                                        <div class="h-[38%] bg-gradient-to-br from-gray-700 to-gray-900"></div>
                                    </div>
                                    <p class="text-white text-xs font-medium truncate">Black Christmas</p>
                                    <p class="text-gray-500 text-[10px] truncate">Elegant black with lines</p>
                                    <div v-if="form.frame_design === 'black_christmas'" class="absolute top-2 right-2 w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Green Dashed -->
                                <button
                                    type="button"
                                    @click="form.frame_design = 'green_dashed'"
                                    :class="[
                                        'relative p-3 rounded-xl border-2 transition-all text-left',
                                        form.frame_design === 'green_dashed' 
                                            ? 'border-pink-500 bg-pink-500/10' 
                                            : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#0a0a0a]'
                                    ]"
                                >
                                    <div class="w-full aspect-[1/2] rounded-lg mb-2 overflow-hidden flex flex-col bg-[#22c55e]">
                                        <div class="h-[38%] bg-gradient-to-br from-orange-400 to-orange-600"></div>
                                        <div class="h-[24%] bg-[#22c55e] flex flex-col items-center justify-center px-1 relative">
                                            <!-- Top dashed line -->
                                            <div class="absolute top-1 left-1 right-1 flex gap-[2px]">
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                            </div>
                                            <!-- Bottom dashed line -->
                                            <div class="absolute bottom-1 left-1 right-1 flex gap-[2px]">
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                                <div class="flex-1 h-[2px] bg-white rounded-sm"></div>
                                            </div>
                                            <p class="text-white text-[8px] font-bold lowercase leading-tight">street tacos</p>
                                            <p class="text-white text-[6px] italic leading-tight">easy to make</p>
                                        </div>
                                        <div class="h-[38%] bg-gradient-to-br from-orange-500 to-orange-700"></div>
                                    </div>
                                    <p class="text-white text-xs font-medium truncate">Green Dashed</p>
                                    <p class="text-gray-500 text-[10px] truncate">Vibrant green border</p>
                                    <div v-if="form.frame_design === 'green_dashed'" class="absolute top-2 right-2 w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Ribbon Banner -->
                                <button
                                    type="button"
                                    @click="form.frame_design = 'ribbon_banner'"
                                    :class="[
                                        'relative p-3 rounded-xl border-2 transition-all text-left',
                                        form.frame_design === 'ribbon_banner' 
                                            ? 'border-pink-500 bg-pink-500/10' 
                                            : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#0a0a0a]'
                                    ]"
                                >
                                    <div class="w-full aspect-[1/2] rounded-lg mb-2 overflow-hidden flex flex-col bg-[#fef9e7]">
                                        <div class="h-[36%] bg-gradient-to-br from-amber-600 to-amber-800"></div>
                                        <div class="h-[28%] bg-[#fef9e7] flex flex-col items-center justify-center px-1 relative">
                                            <!-- Top brown bar -->
                                            <div class="absolute top-0 left-0 right-0 h-[4px] bg-[#8b4513]"></div>
                                            <p class="text-[#8b4513] text-[8px] font-bold uppercase mt-1 leading-tight">BISCOFF</p>
                                            <p class="text-[#8b4513] text-[6px] uppercase leading-tight">CINNAMON ROLLS</p>
                                            <!-- Ribbon -->
                                            <div class="absolute bottom-1 bg-[#8b4513] h-[8px] w-[85%] flex items-center justify-center">
                                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#fef9e7]" style="clip-path: polygon(0 0, 100% 50%, 0 100%);"></div>
                                                <div class="absolute right-0 top-0 bottom-0 w-1 bg-[#fef9e7]" style="clip-path: polygon(100% 0, 0 50%, 100% 100%);"></div>
                                                <p class="text-white text-[5px] font-bold">HADIK.COM</p>
                                            </div>
                                        </div>
                                        <div class="h-[36%] bg-gradient-to-br from-amber-700 to-amber-900"></div>
                                    </div>
                                    <p class="text-white text-xs font-medium truncate">Ribbon Banner</p>
                                    <p class="text-gray-500 text-[10px] truncate">Cream with brown ribbon</p>
                                    <div v-if="form.frame_design === 'ribbon_banner'" class="absolute top-2 right-2 w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Star Rating -->
                                <button
                                    type="button"
                                    @click="form.frame_design = 'star_rating'"
                                    :class="[
                                        'relative p-3 rounded-xl border-2 transition-all text-left',
                                        form.frame_design === 'star_rating' 
                                            ? 'border-pink-500 bg-pink-500/10' 
                                            : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#0a0a0a]'
                                    ]"
                                >
                                    <div class="w-full aspect-[1/2] rounded-lg mb-2 overflow-hidden flex flex-col bg-[#eba13e]">
                                        <div class="h-[36%] bg-gradient-to-br from-amber-500 to-amber-700"></div>
                                        <div class="h-[28%] bg-[#eba13e] flex flex-col items-center justify-center px-1 relative">
                                            <!-- Top capsule with stars -->
                                            <div class="absolute -top-1 bg-[#16120b] h-[8px] px-2 rounded-full flex items-center justify-center gap-[2px]">
                                                <div class="w-[6px] h-[6px] bg-yellow-200" style="clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);"></div>
                                                <div class="w-[6px] h-[6px] bg-yellow-200" style="clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);"></div>
                                                <div class="w-[6px] h-[6px] bg-yellow-200" style="clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);"></div>
                                            </div>
                                            <p class="text-[#16120b] text-[8px] font-bold uppercase leading-tight">CINNAMON</p>
                                            <p class="text-[#16120b] text-[6px] uppercase leading-tight">SUGAR DONUT</p>
                                            <!-- Bottom capsule -->
                                            <div class="absolute -bottom-1 bg-[#16120b] h-[8px] px-2 rounded-full flex items-center justify-center">
                                                <p class="text-white text-[5px] font-bold">HADIK.COM</p>
                                            </div>
                                        </div>
                                        <div class="h-[36%] bg-gradient-to-br from-amber-600 to-amber-800"></div>
                                    </div>
                                    <p class="text-white text-xs font-medium truncate">Star Rating</p>
                                    <p class="text-gray-500 text-[10px] truncate">Orange with star rating</p>
                                    <div v-if="form.frame_design === 'star_rating'" class="absolute top-2 right-2 w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Minimal Bold -->
                                <button
                                    type="button"
                                    @click="form.frame_design = 'minimal_bold'"
                                    :class="[
                                        'relative p-3 rounded-xl border-2 transition-all text-left',
                                        form.frame_design === 'minimal_bold' 
                                            ? 'border-pink-500 bg-pink-500/10' 
                                            : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#0a0a0a]'
                                    ]"
                                >
                                    <div class="w-full aspect-[1/2] rounded-lg mb-2 overflow-hidden flex flex-col bg-white">
                                        <div class="h-[36%] bg-gradient-to-br from-gray-600 to-gray-800"></div>
                                        <div class="h-[28%] bg-white flex flex-col items-center justify-center px-1 relative">
                                            <!-- Top thick line -->
                                            <div class="absolute top-0 left-0 right-0 h-[2px] bg-black"></div>
                                            <!-- Bottom thick line -->
                                            <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-black"></div>
                                            <p class="text-black text-[8px] font-bold lowercase leading-tight">minimal bold</p>
                                            <!-- Domain bar -->
                                            <div class="absolute -bottom-[6px] bg-black h-[8px] px-2 flex items-center justify-center">
                                                <p class="text-white text-[5px] font-bold lowercase">domain.com</p>
                                            </div>
                                        </div>
                                        <div class="h-[36%] bg-gradient-to-br from-gray-700 to-gray-900"></div>
                                    </div>
                                    <p class="text-white text-xs font-medium truncate">Minimal Bold</p>
                                    <p class="text-gray-500 text-[10px] truncate">White with black borders</p>
                                    <div v-if="form.frame_design === 'minimal_bold'" class="absolute top-2 right-2 w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Crispy Orange -->
                                <button
                                    type="button"
                                    @click="form.frame_design = 'crispy_orange'"
                                    :class="[
                                        'relative p-3 rounded-xl border-2 transition-all text-left',
                                        form.frame_design === 'crispy_orange' 
                                            ? 'border-pink-500 bg-pink-500/10' 
                                            : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#0a0a0a]'
                                    ]"
                                >
                                    <div class="w-full aspect-[1/2] rounded-lg mb-2 overflow-hidden flex flex-col bg-[#e67e22]">
                                        <div class="h-[38%] bg-gradient-to-br from-yellow-500 to-yellow-700"></div>
                                        <div class="h-[24%] bg-[#e67e22] flex flex-col items-center justify-center px-1 relative">
                                            <!-- Top accent line -->
                                            <div class="absolute top-0 left-0 right-0 h-[2px] bg-white"></div>
                                            <!-- Bottom accent line -->
                                            <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-white"></div>
                                            <p class="text-white text-[8px] font-bold uppercase leading-tight">crispy oven</p>
                                            <p class="text-white text-[6px] capitalize leading-tight">easy potatoes</p>
                                        </div>
                                        <div class="h-[38%] bg-gradient-to-br from-yellow-600 to-yellow-800"></div>
                                    </div>
                                    <p class="text-white text-xs font-medium truncate">Crispy Orange</p>
                                    <p class="text-gray-500 text-[10px] truncate">Orange with yellow accents</p>
                                    <div v-if="form.frame_design === 'crispy_orange'" class="absolute top-2 right-2 w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Torn Paper -->
                                <button
                                    type="button"
                                    @click="form.frame_design = 'torn_paper'"
                                    :class="[
                                        'relative p-3 rounded-xl border-2 transition-all text-left',
                                        form.frame_design === 'torn_paper' 
                                            ? 'border-pink-500 bg-pink-500/10' 
                                            : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#0a0a0a]'
                                    ]"
                                >
                                    <div class="w-full aspect-[1/2] rounded-lg mb-2 overflow-hidden flex flex-col bg-white">
                                        <div class="h-[38%] bg-gradient-to-br from-gray-500 to-gray-700"></div>
                                        <div class="h-[24%] bg-white flex flex-col items-center justify-center px-1 relative">
                                            <!-- Jagged top edge -->
                                            <div class="absolute top-0 left-0 right-0 h-2 bg-white" style="clip-path: polygon(0% 100%, 10% 20%, 20% 80%, 30% 10%, 40% 90%, 50% 30%, 60% 70%, 70% 0%, 80% 100%, 90% 20%, 100% 80%);"></div>
                                            <!-- Jagged bottom edge -->
                                            <div class="absolute bottom-0 left-0 right-0 h-2 bg-white" style="clip-path: polygon(0% 0%, 10% 80%, 20% 20%, 30% 90%, 40% 10%, 50% 70%, 60% 30%, 70% 100%, 80% 0%, 90% 80%, 100% 20%);"></div>
                                            <p class="text-black text-[8px] font-bold leading-tight">Torn Paper</p>
                                            <p class="text-black text-[6px] font-bold leading-tight">Recipe Card</p>
                                        </div>
                                        <div class="h-[38%] bg-gradient-to-br from-gray-600 to-gray-800"></div>
                                    </div>
                                    <p class="text-white text-xs font-medium truncate">Torn Paper</p>
                                    <p class="text-gray-500 text-[10px] truncate">White with torn edges</p>
                                    <div v-if="form.frame_design === 'torn_paper'" class="absolute top-2 right-2 w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end pt-6 border-t border-[#2a2a2a]">
                            <button
                                type="submit"
                                :disabled="form.processing || !form.article_id"
                                class="px-8 py-3 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white rounded-xl font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ form.processing ? 'Generating...' : 'Generate Pin Image' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Preview -->
                <div class="space-y-6">
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Design Preview
                        </h3>

                        <!-- Preview Error -->
                        <div v-if="previewError" class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-xl">
                            <p class="text-red-400 text-sm">{{ previewError }}</p>
                        </div>

                        <!-- Server-Rendered Preview Image -->
                        <div class="rounded-xl overflow-y-auto overflow-x-hidden mx-auto relative max-h-[800px] scrollbar-thin scrollbar-thumb-pink-500 scrollbar-track-[#2a2a2a]" style="max-width: 512px;">
                            <div v-if="previewImage" class="w-full">
                                <img
                                    :src="previewImage"
                                    alt="Pin Preview"
                                    class="w-full h-auto rounded-xl"
                                />
                            </div>
                            <div v-else-if="isGeneratingPreview" class="bg-[#111] rounded-xl flex flex-col items-center justify-center p-8" style="aspect-ratio: 1/2;">
                                <svg class="animate-spin h-10 w-10 text-pink-400 mb-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-gray-400 text-sm">Generating preview...</p>
                            </div>
                            <div v-else class="bg-[#111] rounded-xl flex flex-col items-center justify-center p-8" style="aspect-ratio: 1/2;">
                                <svg class="w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-gray-500 text-sm text-center">Select an article and enter text to<br/>see your pin preview</p>
                            </div>
                        </div>

                        <p class="text-center text-gray-500 text-sm mt-4">
                            Output: 512 x 1024px (Pinterest 1:2 ratio)
                        </p>
                        <p class="text-center text-gray-600 text-xs mt-2">
                            Preview updates automatically as you change settings
                        </p>
                    </div>

                    <!-- Selected Article Info -->
                    <div v-if="selectedArticle" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <h4 class="text-sm font-medium text-gray-400 mb-3">Selected Article</h4>
                        <h3 class="text-white font-semibold mb-2">{{ selectedArticle.title }}</h3>
                        <p class="text-gray-400 text-sm line-clamp-3">{{ selectedArticle.meta_description || selectedArticle.excerpt }}</p>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #ec4899;
    cursor: pointer;
}

/* Custom scrollbar styling for preview */
.scrollbar-thin::-webkit-scrollbar {
    width: 8px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: #2a2a2a;
    border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #ec4899;
    border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #db2777;
}
</style>
