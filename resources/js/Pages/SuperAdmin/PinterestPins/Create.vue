<script setup>
import { ref, computed, watch } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

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
const showPreview = ref(false);

const form = useForm({
    article_id: '',
    headline_text: '',
    subheadline_text: '',
    headline_color: '#ffffff',
    subheadline_color: '#d4a574',
    overlay_color: '#000000',
    overlay_opacity: 70,
});

// When article is selected, auto-populate headline/subheadline
watch(() => form.article_id, (articleId) => {
    if (articleId) {
        selectedArticle.value = props.articles.find(a => a.id === parseInt(articleId));
        if (selectedArticle.value) {
            const title = selectedArticle.value.title;
            const words = title.split(' ');
            const midPoint = Math.ceil(words.length / 2);
            form.headline_text = words.slice(0, midPoint).join(' ').toLowerCase();
            form.subheadline_text = words.slice(midPoint).join(' ');
        }
    } else {
        selectedArticle.value = null;
    }
});

const previewStyles = computed(() => ({
    overlayBg: `rgba(${hexToRgb(form.overlay_color)}, ${form.overlay_opacity / 100})`,
    headlineColor: form.headline_color,
    subheadlineColor: form.subheadline_color,
}));

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
                    <p class="text-gray-400 mt-1">Design a Pinterest pin image from an article</p>
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
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                                Text Overlay
                            </h3>
                            
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

                        <!-- Pin Preview (2:3 ratio) -->
                        <div class="relative bg-[#0a0a0a] rounded-xl overflow-hidden" style="aspect-ratio: 2/3;">
                            <!-- Top Image -->
                            <div class="absolute top-0 left-0 right-0 h-[40%] overflow-hidden">
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

                            <!-- Text Overlay -->
                            <div 
                                class="absolute left-0 right-0 flex flex-col items-center justify-center px-4"
                                style="top: 40%; height: 15%;"
                                :style="{ backgroundColor: previewStyles.overlayBg }"
                            >
                                <p 
                                    class="text-center text-xl font-bold lowercase tracking-wide"
                                    :style="{ color: previewStyles.headlineColor }"
                                >
                                    {{ form.headline_text || 'cozy cinnamon' }}
                                </p>
                                <p 
                                    class="text-center text-2xl italic"
                                    style="font-family: 'Georgia', serif;"
                                    :style="{ color: previewStyles.subheadlineColor }"
                                >
                                    {{ form.subheadline_text || 'Sugar donut bread' }}
                                </p>
                            </div>

                            <!-- Bottom Image -->
                            <div class="absolute bottom-0 left-0 right-0 h-[45%] overflow-hidden">
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
                            Preview dimensions: 1000 × 1500px (Pinterest 2:3 ratio)
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
