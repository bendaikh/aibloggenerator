<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="category.name + ' - ' + website.name">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
        </Head>

        <div class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <!-- Category Header -->
                <header class="text-center mb-12">
                    <div class="inline-block px-4 py-2 bg-emerald-100 text-emerald-600 rounded-full text-sm font-semibold uppercase tracking-wider mb-4">
                        Category
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">{{ category.name }}</h1>
                    <p v-if="category.description" class="text-xl text-gray-500 max-w-2xl mx-auto">
                        {{ category.description }}
                    </p>
                </header>

                <!-- Articles Grid -->
                <div v-if="articles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    <ArticleCard
                        v-for="article in articles.data"
                        :key="article.id"
                        :article="article"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16 bg-gray-50 rounded-2xl">
                    <div class="text-6xl mb-4">📝</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No recipes yet</h3>
                    <p class="text-gray-500">Check back soon for new content in this category!</p>
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

defineProps({
    website: Object,
    category: Object,
    articles: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>
