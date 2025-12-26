<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="article.title + ' - ' + website.name" />

        <article class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto">
                    <!-- Article Header -->
                    <header class="mb-8">
                        <div class="mb-4">
                            <Link
                                v-if="article.category"
                                :href="route('website.category', { website: website.slug, category: article.category.slug })"
                                class="inline-block text-teal-500 hover:text-teal-600 font-semibold text-sm uppercase tracking-wide"
                            >
                                {{ article.category.name }}
                            </Link>
                        </div>
                        <h1 class="text-5xl font-bold text-gray-900 mb-6">{{ article.title }}</h1>
                        
                        <div class="flex items-center text-gray-600 text-sm space-x-4">
                            <span>By {{ article.user?.name || 'Admin' }}</span>
                            <span>•</span>
                            <time :datetime="article.published_at">
                                {{ formatDate(article.published_at) }}
                            </time>
                            <span>•</span>
                            <span>{{ article.views }} views</span>
                        </div>
                    </header>

                    <!-- Featured Image -->
                    <div v-if="article.featured_image" class="mb-12 rounded-lg overflow-hidden">
                        <img
                            :src="article.featured_image"
                            :alt="article.title"
                            class="w-full h-auto"
                        />
                    </div>

                    <!-- Article Excerpt -->
                    <div v-if="article.excerpt" class="text-xl text-gray-700 mb-8 p-6 bg-gray-50 rounded-lg border-l-4 border-teal-500">
                        {{ article.excerpt }}
                    </div>

                    <!-- Article Content -->
                    <div class="prose prose-lg max-w-none mb-12" v-html="article.content"></div>

                    <!-- Share Buttons -->
                    <div class="border-t border-b border-gray-200 py-6 mb-12">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 font-semibold">Share this recipe:</span>
                            <div class="flex gap-4">
                                <a href="#" class="text-gray-600 hover:text-blue-600 transition">
                                    <i class="fab fa-facebook text-2xl"></i>
                                </a>
                                <a href="#" class="text-gray-600 hover:text-red-600 transition">
                                    <i class="fab fa-pinterest text-2xl"></i>
                                </a>
                                <a href="#" class="text-gray-600 hover:text-blue-400 transition">
                                    <i class="fab fa-twitter text-2xl"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Author Bio -->
                    <div v-if="article.user" class="bg-gray-50 rounded-lg p-8 mb-12">
                        <div class="flex items-start gap-6">
                            <div class="flex-shrink-0">
                                <div class="w-24 h-24 rounded-full bg-teal-500 flex items-center justify-center text-white text-3xl font-bold">
                                    {{ article.user.name.charAt(0) }}
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">About {{ article.user.name }}</h3>
                                <p class="text-gray-600">
                                    I love to cook and share delicious recipes! Here you will find deliciously simple tried and true recipes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Related Articles -->
                    <div v-if="relatedArticles.length > 0" class="mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-8">You Might Also Like</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <Link
                                v-for="related in relatedArticles"
                                :key="related.id"
                                :href="route('article.show', { website: website.slug, article: related.slug })"
                                class="group"
                            >
                                <div class="overflow-hidden rounded-lg mb-4">
                                    <img
                                        v-if="related.featured_image"
                                        :src="related.featured_image"
                                        :alt="related.title"
                                        class="w-full aspect-[4/3] object-cover group-hover:scale-110 transition-transform duration-300"
                                    />
                                    <div v-else class="w-full aspect-[4/3] bg-gradient-to-br from-teal-400 to-cyan-500"></div>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-teal-500 transition">
                                    {{ related.title }}
                                </h3>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </PublicWebsiteLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicWebsiteLayout from '@/Layouts/PublicWebsiteLayout.vue';

defineProps({
    website: Object,
    article: Object,
    relatedArticles: Array
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<style>
.prose {
    color: #374151;
}

.prose h2 {
    font-size: 2em;
    font-weight: 700;
    margin-top: 2em;
    margin-bottom: 1em;
}

.prose h3 {
    font-size: 1.5em;
    font-weight: 600;
    margin-top: 1.5em;
    margin-bottom: 0.75em;
}

.prose p {
    margin-bottom: 1.25em;
    line-height: 1.75;
}

.prose ul, .prose ol {
    margin-bottom: 1.25em;
    padding-left: 1.5em;
}

.prose li {
    margin-bottom: 0.5em;
}
</style>

