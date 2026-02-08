<template>
    <PublicWebsiteLayout :website="website" :search-query="searchQuery">
        <Head :title="searchQuery ? `Search results for '${searchQuery}' - ${website.name}` : `All Articles - ${website.name}`">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
            <meta v-if="website.pinterest_verification" name="p:domain_verify" :content="website.pinterest_verification" />
        </Head>

        <div class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <!-- Page Header -->
                <header class="text-center mb-12">
                    <div class="inline-block px-4 py-2 bg-emerald-100 text-emerald-600 rounded-full text-sm font-semibold uppercase tracking-wider mb-4">
                        {{ searchQuery ? 'Search Results' : 'All The Latest' }}
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        {{ searchQuery ? 'Search: ' + searchQuery : 'Browse All The Latest' }}
                    </h1>
                    <p class="text-xl text-gray-500 max-w-2xl mx-auto">
                        {{ searchQuery ? 'Showing ' + articles.total + ' results for your search.' : 'The newest breakfast ideas, dinners and desserts. These easy recipes are pretty much guaranteed winners!' }}
                    </p>
                </header>

                <!-- Articles Grid -->
                <div v-if="articles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    <ArticleCard
                        v-for="article in articles.data"
                        :key="article.id"
                        :article="article"
                        :title-font-family="articleTitleFontFamily"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16 bg-gray-50 rounded-2xl">
                    <div class="text-6xl mb-4">📝</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No articles yet</h3>
                    <p class="text-gray-500">Check back soon for new content!</p>
                </div>

                <!-- Pagination -->
                <div v-if="articles.data.length > 0 && (articles.prev_page_url || articles.next_page_url)" class="flex justify-center gap-4">
                    <a
                        v-if="articles.prev_page_url"
                        :href="articles.prev_page_url"
                        class="px-8 py-3 bg-gray-100 text-gray-700 rounded-full font-semibold hover:bg-gray-200 transition flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </a>
                    <a
                        v-if="articles.next_page_url"
                        :href="articles.next_page_url"
                        class="px-8 py-3 bg-emerald-500 text-white rounded-full font-semibold hover:bg-emerald-600 transition flex items-center gap-2"
                    >
                        Next
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </PublicWebsiteLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PublicWebsiteLayout from '@/Layouts/PublicWebsiteLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import { computed } from 'vue';

const props = defineProps({
    website: Object,
    articles: Object,
    searchQuery: String
});

const articleTitleFontFamily = computed(() => {
    const fontId = props.website?.theme_settings?.article_title_font_family || 'merriweather';
    const fontMap = {
        'default': "'Plus Jakarta Sans', sans-serif",
        'inter': "'Inter', sans-serif",
        'roboto': "'Roboto', sans-serif",
        'open-sans': "'Open Sans', sans-serif",
        'lato': "'Lato', sans-serif",
        'montserrat': "'Montserrat', sans-serif",
        'poppins': "'Poppins', sans-serif",
        'raleway': "'Raleway', sans-serif",
        'bebas-neue': "'Bebas Neue', cursive",
        'playfair-display': "'Playfair Display', serif",
        'merriweather': "'Merriweather', serif",
        'lora': "'Lora', serif",
        'dancing-script': "'Dancing Script', cursive",
        'pacifico': "'Pacifico', cursive",
        'great-vibes': "'Great Vibes', cursive",
    };
    return fontMap[fontId] || fontMap['merriweather'];
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

