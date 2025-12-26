<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="category.name + ' - ' + website.name" />

        <div class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <!-- Category Header -->
                <header class="text-center mb-12">
                    <h1 class="text-5xl font-bold text-gray-900 mb-4">{{ category.name }}</h1>
                    <p v-if="category.description" class="text-xl text-gray-600 max-w-2xl mx-auto">
                        {{ category.description }}
                    </p>
                </header>

                <!-- Articles Grid -->
                <div v-if="articles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    <a
                        v-for="article in articles.data"
                        :key="article.id"
                        :href="article.url"
                        class="group bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden"
                    >
                        <div class="overflow-hidden">
                            <img
                                v-if="article.featured_image"
                                :src="article.featured_image"
                                :alt="article.title"
                                class="w-full aspect-[4/3] object-cover group-hover:scale-110 transition-transform duration-300"
                            />
                            <div v-else class="w-full aspect-[4/3] bg-gradient-to-br from-teal-400 to-cyan-500"></div>
                        </div>
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2 group-hover:text-teal-500 transition">
                                {{ article.title }}
                            </h2>
                            <p v-if="article.excerpt" class="text-gray-600 mb-4">
                                {{ article.excerpt.substring(0, 120) }}{{ article.excerpt.length > 120 ? '...' : '' }}
                            </p>
                            <div class="flex items-center text-sm text-gray-500">
                                <time :datetime="article.published_at">
                                    {{ formatDate(article.published_at) }}
                                </time>
                                <span class="mx-2">•</span>
                                <span>{{ article.views }} views</span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="text-6xl mb-4">📝</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No articles yet</h3>
                    <p class="text-gray-600">Check back soon for new content in this category!</p>
                </div>

                <!-- Pagination -->
                <div v-if="articles.data.length > 0 && (articles.prev_page_url || articles.next_page_url)" class="flex justify-center gap-4">
                    <Link
                        v-if="articles.prev_page_url"
                        :href="articles.prev_page_url"
                        class="px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="articles.next_page_url"
                        :href="articles.next_page_url"
                        class="px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </PublicWebsiteLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicWebsiteLayout from '@/Layouts/PublicWebsiteLayout.vue';

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

