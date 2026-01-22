<script setup>
import { ref, computed, watch } from 'vue';
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

const form = useForm({
    article_id: '',
    headline_text: '',
    subheadline_text: '',
    headline_color: '#ffffff',
    subheadline_color: '#d4a574',
    headline_font: 'sans-serif',
    subheadline_font: 'script',
    headline_font_size: 28,
    subheadline_font_size: 22,
    overlay_color: '#000000',
    overlay_opacity: 70,
    frame_design: 'simple_center',
    domain_name: props.currentWebsite?.domain || (props.currentWebsite?.slug ? props.currentWebsite.slug + '.com' : ''),
});

// Font options - Only include fonts that are available on Windows
// Sans-serif fonts (for headlines)
const fontOptions = {
    'sans-serif': [
        { id: 'sans-serif', name: 'Default Sans (Arial)' },
        { id: 'arial', name: 'Arial Bold' },
    ],
    'script': [
        { id: 'script', name: 'Default Script (Georgia)' },
        { id: 'georgia', name: 'Georgia' },
        { id: 'times', name: 'Times New Roman' },
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

// When article is selected, auto-generate AI headlines
watch(() => form.article_id, async (articleId) => {
    if (articleId) {
        selectedArticle.value = props.articles.find(a => a.id === parseInt(articleId));
        if (selectedArticle.value) {
            // Auto-generate AI headlines
            await generateAIHeadlines();
        }
    } else {
        selectedArticle.value = null;
    }
});

const previewStyles = computed(() => ({
    overlayBg: `rgba(${hexToRgb(form.overlay_color)}, ${form.overlay_opacity / 100})`,
    headlineColor: form.headline_color,
    subheadlineColor: form.subheadline_color,
    headlineFontSize: `${form.headline_font_size / 2}px`,
    subheadlineFontSize: `${form.subheadline_font_size / 2}px`,
    headlineFontFamily: getFontFamily(form.headline_font),
    subheadlineFontFamily: getFontFamily(form.subheadline_font),
}));

const getFontFamily = (fontId) => {
    // Map font IDs to CSS font families that match Windows system fonts
    switch (fontId) {
        case 'arial': return 'Arial, Helvetica, sans-serif';
        case 'sans-serif': return 'Arial, Helvetica, sans-serif';
        case 'georgia': return 'Georgia, "Times New Roman", serif';
        case 'times': return '"Times New Roman", Times, serif';
        case 'script': return 'Georgia, "Times New Roman", serif';
        default: return 'Arial, sans-serif';
    }
};

const hexToRgb = (hex) => {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result 
        ? `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}`
        : '0, 0, 0';
};

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
                                        <optgroup label="Serif / Script">
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
                                <button
                                    v-for="frame in frameDesigns"
                                    :key="frame.id"
                                    type="button"
                                    @click="form.frame_design = frame.id"
                                    :class="[
                                        'relative p-3 rounded-xl border-2 transition-all text-left',
                                        form.frame_design === frame.id 
                                            ? 'border-pink-500 bg-pink-500/10' 
                                            : 'border-[#2a2a2a] hover:border-[#3a3a3a] bg-[#0a0a0a]'
                                    ]"
                                >
                                    <!-- Frame Preview Mini -->
                                    <div 
                                        class="w-full aspect-[1/2] rounded-lg mb-2 overflow-hidden flex flex-col"
                                        :style="{ backgroundColor: frame.id === 'simple_center' ? '#1a1a1a' : frame.bgColor }"
                                    >
                                        <div class="flex-1 bg-gray-700"></div>
                                        <div 
                                            class="h-[15%] flex items-center justify-center"
                                            :style="{ backgroundColor: frame.bgColor }"
                                        >
                                            <div 
                                                class="w-8 h-0.5 rounded"
                                                :style="{ backgroundColor: frame.textColor }"
                                            ></div>
                                        </div>
                                        <div class="flex-1 bg-gray-600"></div>
                                    </div>
                                    <p class="text-white text-xs font-medium truncate">{{ frame.name }}</p>
                                    <p class="text-gray-500 text-[10px] truncate">{{ frame.description }}</p>
                                    
                                    <!-- Selected indicator -->
                                    <div 
                                        v-if="form.frame_design === frame.id"
                                        class="absolute top-2 right-2 w-5 h-5 bg-pink-500 rounded-full flex items-center justify-center"
                                    >
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
                            Live Preview
                        </h3>

                        <!-- Pin Preview (1:2 ratio = 512x1024) -->
                        <div class="relative bg-white rounded-xl overflow-hidden mx-auto" style="aspect-ratio: 1/2; max-width: 256px;">
                            <!-- Top Image -->
                            <div class="absolute top-0 left-0 right-0 h-[40.2%] overflow-hidden">
                                <img
                                    v-if="selectedArticle?.featured_image"
                                    :src="selectedArticle.featured_image.startsWith('http') ? selectedArticle.featured_image : '/' + selectedArticle.featured_image"
                                    alt="Top image"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-800 flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">Top Image</span>
                                </div>
                            </div>

                            <!-- Text Overlay - Simple Center -->
                            <div 
                                v-if="form.frame_design === 'simple_center'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-2 overflow-hidden"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <p 
                                    class="text-center font-bold lowercase tracking-wide leading-tight w-full px-1"
                                    :style="{ 
                                        color: previewStyles.headlineColor, 
                                        fontSize: previewStyles.headlineFontSize,
                                        fontFamily: previewStyles.headlineFontFamily,
                                        wordWrap: 'break-word',
                                        overflowWrap: 'break-word'
                                    }"
                                >
                                    {{ form.headline_text || 'cozy cinnamon' }}
                                </p>
                                <p 
                                    class="text-center italic leading-tight w-full px-1 mt-1"
                                    :style="{ 
                                        color: previewStyles.subheadlineColor, 
                                        fontSize: previewStyles.subheadlineFontSize,
                                        fontFamily: previewStyles.subheadlineFontFamily,
                                        wordWrap: 'break-word',
                                        overflowWrap: 'break-word'
                                    }"
                                >
                                    {{ form.subheadline_text || 'Sugar donut bread' }}
                                </p>
                            </div>

                            <!-- Text Overlay - Black Christmas -->
                            <div 
                                v-else-if="form.frame_design === 'black_christmas'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4 overflow-hidden"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <!-- Top decorative line -->
                                <div class="absolute top-2 left-0 right-0 h-0.5" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <!-- Bottom decorative line -->
                                <div class="absolute bottom-2 left-0 right-0 h-0.5" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <p 
                                    class="text-center font-bold tracking-wide capitalize w-full px-1"
                                    :style="{ 
                                        color: previewStyles.headlineColor,
                                        fontSize: previewStyles.headlineFontSize,
                                        fontFamily: previewStyles.headlineFontFamily,
                                        wordWrap: 'break-word',
                                        lineHeight: '1.1'
                                    }"
                                >
                                    {{ form.headline_text || 'White Christmas' }}
                                </p>
                                <p 
                                    class="text-center mt-1 capitalize w-full px-1"
                                    :style="{ 
                                        color: previewStyles.subheadlineColor,
                                        fontSize: previewStyles.subheadlineFontSize,
                                        fontFamily: previewStyles.subheadlineFontFamily,
                                        wordWrap: 'break-word',
                                        lineHeight: '1.1'
                                    }"
                                >
                                    {{ form.subheadline_text || 'Mojitos' }}
                                </p>
                            </div>

                            <!-- Text Overlay - Green Dashed -->
                            <div 
                                v-else-if="form.frame_design === 'green_dashed'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4 overflow-hidden"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <!-- Top dashed line -->
                                <div class="absolute top-2 left-0 right-0 flex gap-1.5 px-1">
                                    <div v-for="i in 20" :key="'top-'+i" class="flex-1 h-1 bg-white rounded-sm"></div>
                                </div>
                                <!-- Bottom dashed line -->
                                <div class="absolute bottom-2 left-0 right-0 flex gap-1.5 px-1">
                                    <div v-for="i in 20" :key="'bottom-'+i" class="flex-1 h-1 bg-white rounded-sm"></div>
                                </div>
                                <p 
                                    class="text-center font-bold lowercase tracking-wide w-full px-1"
                                    :style="{ 
                                        color: previewStyles.headlineColor,
                                        fontSize: previewStyles.headlineFontSize,
                                        fontFamily: previewStyles.headlineFontFamily,
                                        wordWrap: 'break-word',
                                        lineHeight: '1.1'
                                    }"
                                >
                                    {{ form.headline_text || 'chicken street tacos' }}
                                </p>
                                <p 
                                    class="text-center italic mt-1 w-full px-1"
                                    :style="{ 
                                        color: previewStyles.subheadlineColor,
                                        fontSize: previewStyles.subheadlineFontSize,
                                        fontFamily: previewStyles.subheadlineFontFamily,
                                        wordWrap: 'break-word',
                                        lineHeight: '1.1'
                                    }"
                                >
                                    {{ form.subheadline_text || 'easy to make' }}
                                </p>
                            </div>

                            <!-- Text Overlay - Ribbon Banner -->
                            <div 
                                v-else-if="form.frame_design === 'ribbon_banner'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-between py-2 overflow-hidden"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <!-- Top thick bar -->
                                <div class="absolute top-0 left-0 right-0 h-4" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                
                                <div class="flex-1 flex flex-col items-center justify-center w-full px-2 mt-4">
                                    <p 
                                        class="text-center font-bold uppercase w-full leading-tight"
                                        :style="{ 
                                            color: previewStyles.headlineColor,
                                            fontSize: previewStyles.headlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.headline_text || 'BISCOFF COOKIE BUTTER' }}
                                    </p>
                                    <p 
                                        class="text-center font-bold uppercase w-full mt-1 leading-tight"
                                        :style="{ 
                                            color: previewStyles.subheadlineColor,
                                            fontSize: previewStyles.subheadlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.subheadline_text || 'CINNAMON ROLLS' }}
                                    </p>
                                </div>

                                <!-- Ribbon -->
                                <div 
                                    class="relative w-[85%] flex items-center justify-center mb-2"
                                    style="height: 18%;"
                                    :style="{ backgroundColor: previewStyles.headlineColor }"
                                >
                                    <!-- Notch Left -->
                                    <div class="absolute left-0 top-0 bottom-0 w-3" :style="{ backgroundColor: form.overlay_color, clipPath: 'polygon(0 0, 100% 50%, 0 100%)' }"></div>
                                    <!-- Notch Right -->
                                    <div class="absolute right-0 top-0 bottom-0 w-3" :style="{ backgroundColor: form.overlay_color, clipPath: 'polygon(100% 0, 0 50%, 100% 100%)' }"></div>
                                    
                                    <p 
                                        class="text-center font-bold text-white uppercase tracking-wider px-4 text-[10px]"
                                        :style="{ fontFamily: previewStyles.headlineFontFamily }"
                                    >
                                        {{ form.domain_name || 'WWW.HADIK.COM' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Text Overlay - Star Rating -->
                            <div 
                                v-else-if="form.frame_design === 'star_rating'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4 overflow-hidden"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <!-- Top capsule with stars -->
                                <div 
                                    class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 h-6 rounded-full flex items-center justify-center gap-0.5 z-10"
                                    :style="{ backgroundColor: previewStyles.headlineColor }"
                                >
                                    <div v-for="i in 5" :key="'star-'+i" class="w-2 h-2 bg-yellow-200" style="clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);"></div>
                                </div>

                                <div class="flex flex-col items-center justify-center w-full px-2">
                                    <p 
                                        class="text-center font-bold uppercase w-full leading-tight"
                                        :style="{ 
                                            color: previewStyles.headlineColor,
                                            fontSize: previewStyles.headlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.headline_text || 'COZY CINNAMON' }}
                                    </p>
                                    <p 
                                        class="text-center font-bold uppercase w-full mt-1 leading-tight"
                                        :style="{ 
                                            color: previewStyles.subheadlineColor,
                                            fontSize: previewStyles.subheadlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.subheadline_text || 'SUGAR DONUT' }}
                                    </p>
                                </div>

                                <!-- Domain Name Capsule -->
                                <div 
                                    class="absolute -bottom-3 left-1/2 -translate-x-1/2 px-4 h-6 rounded-full flex items-center justify-center z-10 min-w-[100px]"
                                    :style="{ backgroundColor: previewStyles.headlineColor }"
                                >
                                    <p 
                                        class="text-center font-bold text-white uppercase text-[9px] tracking-wider"
                                        :style="{ fontFamily: previewStyles.headlineFontFamily }"
                                    >
                                        {{ form.domain_name || 'WWW.HADIK.COM' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Text Overlay - Minimal Bold -->
                            <div 
                                v-else-if="form.frame_design === 'minimal_bold'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center overflow-visible"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <!-- Top and Bottom thick lines -->
                                <div class="absolute top-0 left-0 right-0 h-1" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <div class="absolute bottom-0 left-0 right-0 h-1" :style="{ backgroundColor: previewStyles.headlineColor }"></div>

                                <div class="flex flex-col items-center justify-center w-full px-2">
                                    <p 
                                        class="text-center font-bold lowercase w-full leading-tight"
                                        :style="{ 
                                            color: previewStyles.headlineColor,
                                            fontSize: previewStyles.headlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.headline_text || 'yesy folder this' }}
                                    </p>
                                    <p 
                                        class="text-center lowercase w-full mt-1 leading-tight"
                                        :style="{ 
                                            color: previewStyles.subheadlineColor,
                                            fontSize: previewStyles.subheadlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.subheadline_text || '' }}
                                    </p>
                                </div>

                                <!-- Domain Name Bar -->
                                <div 
                                    class="absolute -bottom-3 left-1/2 -translate-x-1/2 px-4 h-6 flex items-center justify-center z-10 min-w-[100px]"
                                    :style="{ backgroundColor: previewStyles.headlineColor }"
                                >
                                    <p 
                                        class="text-center font-bold text-white lowercase text-[9px] tracking-wider"
                                        :style="{ fontFamily: previewStyles.headlineFontFamily }"
                                    >
                                        {{ form.domain_name || 'testteha.com' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Text Overlay - Crispy Orange -->
                            <div 
                                v-else-if="form.frame_design === 'crispy_orange'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center overflow-hidden"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <!-- Top and Bottom accent lines -->
                                <div class="absolute top-0 left-0 right-0 h-1" :style="{ backgroundColor: previewStyles.headlineColor }"></div>
                                <div class="absolute bottom-0 left-0 right-0 h-1" :style="{ backgroundColor: previewStyles.headlineColor }"></div>

                                <div class="flex flex-col items-center justify-center w-full px-2">
                                    <p 
                                        class="text-center font-bold uppercase w-full leading-tight"
                                        :style="{ 
                                            color: previewStyles.headlineColor,
                                            fontSize: previewStyles.headlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.headline_text || 'crispy oven roasted' }}
                                    </p>
                                    <p 
                                        class="text-center capitalize w-full mt-1 leading-tight"
                                        :style="{ 
                                            color: previewStyles.subheadlineColor,
                                            fontSize: previewStyles.subheadlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.subheadline_text || 'easy potatoes' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Text Overlay - Torn Paper -->
                            <div 
                                v-else-if="form.frame_design === 'torn_paper'"
                                class="absolute left-0 right-0 flex flex-col items-center justify-center"
                                style="top: 40.2%; height: 19.6%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <!-- Jagged Edge Top -->
                                <div class="absolute top-0 left-0 right-0 h-4 -mt-2" :style="{ backgroundColor: form.overlay_color, clipPath: 'polygon(0% 100%, 5% 20%, 10% 80%, 15% 10%, 20% 90%, 25% 30%, 30% 70%, 35% 0%, 40% 100%, 45% 20%, 50% 80%, 55% 10%, 60% 90%, 65% 30%, 70% 70%, 75% 0%, 80% 100%, 85% 20%, 90% 80%, 95% 10%, 100% 100%)' }"></div>
                                
                                <!-- Jagged Edge Bottom -->
                                <div class="absolute bottom-0 left-0 right-0 h-4 -mb-2" :style="{ backgroundColor: form.overlay_color, clipPath: 'polygon(0% 0%, 5% 80%, 10% 20%, 15% 90%, 20% 10%, 25% 70%, 30% 30%, 35% 100%, 40% 0%, 45% 80%, 50% 20%, 55% 90%, 60% 10%, 65% 70%, 70% 30%, 75% 100%, 80% 0%, 85% 80%, 90% 20%, 95% 90%, 100% 0%)' }"></div>

                                <div class="flex flex-col items-center justify-center w-full px-4 z-10">
                                    <p 
                                        class="text-center font-bold w-full leading-tight"
                                        :style="{ 
                                            color: previewStyles.headlineColor,
                                            fontSize: previewStyles.headlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.headline_text || 'Test Folder cd' }}
                                    </p>
                                    <p 
                                        class="text-center font-bold w-full mt-2 leading-tight"
                                        :style="{ 
                                            color: previewStyles.subheadlineColor,
                                            fontSize: previewStyles.subheadlineFontSize,
                                            fontFamily: previewStyles.headlineFontFamily,
                                            wordWrap: 'break-word'
                                        }"
                                    >
                                        {{ form.subheadline_text || 'Test Folder cd' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Bottom Image -->
                            <div class="absolute bottom-0 left-0 right-0 h-[40.2%] overflow-hidden">
                                <img
                                    v-if="selectedArticle?.secondary_image || selectedArticle?.featured_image"
                                    :src="(selectedArticle.secondary_image || selectedArticle.featured_image).startsWith('http') ? (selectedArticle.secondary_image || selectedArticle.featured_image) : '/' + (selectedArticle.secondary_image || selectedArticle.featured_image)"
                                    alt="Bottom image"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">Bottom Image</span>
                                </div>
                            </div>
                        </div>

                        <p class="text-center text-gray-500 text-sm mt-4">
                            Output: 512 x 1024px (Pinterest 1:2 ratio)
                        </p>
                        <p class="text-center text-gray-600 text-xs mt-2">
                            ⚡ Font sizes auto-scale if text is too long
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
</style>
