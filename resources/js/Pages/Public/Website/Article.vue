<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="article.title + ' - ' + website.name" />

        <article class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto">
                    <!-- Article Header -->
                    <header class="mb-8 text-center">
                        <div class="mb-4">
                            <a
                                v-if="article.category"
                                :href="article.category.url"
                                class="inline-block text-emerald-500 hover:text-emerald-600 font-semibold text-sm uppercase tracking-wider"
                            >
                                {{ article.category.name }}
                            </a>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">{{ article.title }}</h1>
                        
                        <div class="flex items-center justify-center gap-4 text-gray-500 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                    <span class="text-white text-xs font-bold">{{ (article.user?.name || 'A').charAt(0) }}</span>
                                </div>
                                <span>By {{ article.user?.name || 'Admin' }}</span>
                            </div>
                            <span>•</span>
                            <time :datetime="article.published_at">
                                {{ formatDate(article.published_at) }}
                            </time>
                            <span>•</span>
                            <span>{{ article.views }} views</span>
                        </div>

                        <!-- Star Rating -->
                        <div class="flex justify-center mt-4">
                            <div class="flex text-yellow-400 text-xl">
                                <span v-for="i in 5" :key="i">★</span>
                            </div>
                        </div>
                    </header>

                    <!-- Featured Image -->
                    <div v-if="article.featured_image" class="mb-12 rounded-2xl overflow-hidden shadow-lg">
                        <img
                            :src="article.featured_image"
                            :alt="article.title"
                            class="w-full h-auto"
                        />
                    </div>

                    <!-- Article Excerpt -->
                    <div v-if="article.excerpt" class="text-xl text-gray-600 mb-8 p-6 bg-emerald-50 rounded-2xl border-l-4 border-emerald-500">
                        {{ article.excerpt }}
                    </div>

                    <!-- Article Content -->
                    <div class="prose prose-lg max-w-none mb-12" v-html="article.content"></div>

                    <!-- Share Buttons -->
                    <div class="border-t border-b border-gray-200 py-6 mb-12">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                            <span class="text-gray-700 font-semibold">Share this recipe:</span>
                            <div class="flex gap-3">
                                <a href="#" class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white hover:bg-blue-700 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center text-white hover:bg-red-700 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-sky-500 rounded-full flex items-center justify-center text-white hover:bg-sky-600 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Author Bio -->
                    <div class="bg-gray-50 rounded-2xl p-8 mb-12">
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                            <div class="flex-shrink-0">
                                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-3xl font-bold ring-4 ring-emerald-200">
                                    {{ (article.user?.name || 'A').charAt(0) }}
                                </div>
                            </div>
                            <div class="text-center md:text-left">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">About {{ article.user?.name || 'the Author' }}</h3>
                                <p class="text-gray-600">
                                    I love to cook and share delicious recipes! Here you will find deliciously simple tried and true recipes.
                                    Thanks so much for stopping by!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Related Articles -->
                    <div v-if="relatedArticles.length > 0" class="mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">You Might Also Like</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <a
                                v-for="related in relatedArticles"
                                :key="related.id"
                                :href="related.url"
                                class="group"
                            >
                                <div class="overflow-hidden rounded-2xl mb-4 shadow-md group-hover:shadow-xl transition">
                                    <img
                                        v-if="related.featured_image"
                                        :src="related.featured_image"
                                        :alt="related.title"
                                        class="w-full aspect-[4/3] object-cover group-hover:scale-105 transition-transform duration-500"
                                    />
                                    <div v-else class="w-full aspect-[4/3] bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                        <span class="text-white text-4xl">🍽️</span>
                                    </div>
                                </div>
                                <div class="flex justify-center text-yellow-400 text-sm mb-1">
                                    <span v-for="i in 5" :key="i">★</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-500 transition text-center">
                                    {{ related.title }}
                                </h3>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </PublicWebsiteLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
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
    color: #111827;
}

.prose h3 {
    font-size: 1.5em;
    font-weight: 600;
    margin-top: 1.5em;
    margin-bottom: 0.75em;
    color: #111827;
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

.prose img {
    border-radius: 1rem;
    margin: 2rem 0;
}

.prose a {
    color: #10b981;
    text-decoration: underline;
}

.prose a:hover {
    color: #059669;
}
</style>
