<template>
    <a
        :href="article.url"
        class="group block h-full bg-white rounded-2xl overflow-hidden border-2 border-gray-100 hover:border-emerald-500 transition-all duration-300 shadow-md hover:shadow-xl"
    >
        <!-- Image Container -->
        <div class="relative aspect-[4/3] overflow-hidden">
            <img
                v-if="article.processed_featured_image || article.featured_image"
                :src="article.processed_featured_image || article.featured_image"
                :alt="article.title"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                <span class="text-white text-6xl">🍽️</span>
            </div>
            
            <!-- Category Badge -->
            <div v-if="article.category" class="absolute top-2 left-2 sm:top-4 sm:left-4">
                <span class="px-2 py-0.5 sm:px-3 sm:py-1 bg-white/90 backdrop-blur-sm text-emerald-600 text-[10px] sm:text-xs font-bold rounded-full shadow-sm uppercase tracking-wider">
                    {{ article.category.name }}
                </span>
            </div>
        </div>

        <!-- Content -->
        <div class="p-3 sm:p-4 md:p-6">
            <!-- Ratings (Static for now as requested by original design) -->
            <div class="flex justify-center text-yellow-400 text-xs sm:text-sm mb-2 sm:mb-3">
                <span v-for="i in 5" :key="i">★</span>
            </div>

            <!-- Title -->
            <h2 class="text-sm sm:text-base md:text-xl font-bold text-gray-900 mb-2 sm:mb-4 group-hover:text-emerald-600 transition-colors text-center line-clamp-2">
                {{ article.title }}
            </h2>

            <!-- Cook Times - Horizontal with vertical lines (hidden on very small screens for compact cards) -->
            <div v-if="showTime && hasTimeInfo" class="hidden sm:flex items-center justify-center gap-2 sm:gap-3 mb-2 sm:mb-4 py-2 sm:py-3 border-y border-emerald-100 bg-emerald-50/30">
                <div v-if="article.prep_time" class="flex items-center gap-1">
                    <span class="text-[8px] sm:text-[10px] uppercase text-gray-500 font-bold tracking-wider">Prep:</span>
                    <span class="text-xs sm:text-sm font-bold text-gray-900">{{ article.prep_time }}</span>
                </div>
                <div v-if="article.cook_time && article.prep_time" class="h-3 sm:h-4 w-px bg-gray-300"></div>
                <div v-if="article.cook_time" class="flex items-center gap-1">
                    <span class="text-[8px] sm:text-[10px] uppercase text-gray-500 font-bold tracking-wider">Cook:</span>
                    <span class="text-xs sm:text-sm font-bold text-gray-900">{{ article.cook_time }}</span>
                </div>
                <div v-if="article.total_time && (article.prep_time || article.cook_time)" class="h-3 sm:h-4 w-px bg-gray-300"></div>
                <div v-if="article.total_time" class="flex items-center gap-1">
                    <span class="text-[8px] sm:text-[10px] uppercase text-gray-500 font-bold tracking-wider">Total:</span>
                    <span class="text-xs sm:text-sm font-bold text-gray-900">{{ article.total_time }}</span>
                </div>
            </div>

            <!-- Tags (hidden on mobile for compact view) -->
            <div v-if="showTags && article.meta_tags && article.meta_tags.length > 0" class="hidden sm:flex flex-wrap justify-center gap-1.5 mb-2 sm:mb-4">
                <span 
                    v-for="(tag, index) in article.meta_tags.slice(0, 3)" 
                    :key="index"
                    class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-semibold rounded-md border border-emerald-100"
                >
                    #{{ tag }}
                </span>
            </div>

            <!-- Footer Info -->
            <div v-if="showDate" class="flex items-center justify-center text-[10px] sm:text-[11px] text-gray-400">
                <time :datetime="article.published_at">
                    {{ formatDate(article.published_at) }}
                </time>
            </div>
        </div>
    </a>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    article: {
        type: Object,
        required: true
    },
    showTime: {
        type: Boolean,
        default: true
    },
    showDate: {
        type: Boolean,
        default: false
    },
    showTags: {
        type: Boolean,
        default: true
    }
});

const hasTimeInfo = computed(() => {
    return props.article.prep_time || props.article.cook_time || props.article.rest_time || props.article.total_time;
});

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>
