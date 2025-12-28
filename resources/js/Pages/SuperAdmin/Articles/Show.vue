<template>
    <SuperAdminLayout>
        <Head :title="article.title" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <Link :href="route('superadmin.articles.index')" class="text-teal-600 hover:text-teal-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Articles
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Featured Image -->
                    <div v-if="article.featured_image" class="h-64 bg-gray-200">
                        <img :src="article.featured_image" :alt="article.title" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="h-64 bg-gradient-to-br from-teal-400 to-cyan-500 flex items-center justify-center">
                        <span class="text-white text-6xl">📝</span>
                    </div>

                    <div class="p-8">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                        article.status === 'published' ? 'bg-green-100 text-green-800' : 
                                        article.status === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800'
                                    ]">
                                        {{ article.status }}
                                    </span>
                                    <span v-if="article.category" class="px-3 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                                        {{ article.category.name }}
                                    </span>
                                </div>
                                <h1 class="text-3xl font-bold text-gray-900">{{ article.title }}</h1>
                            </div>
                            <div class="flex gap-3">
                                <a
                                    v-if="article.status === 'published'"
                                    :href="article.url"
                                    target="_blank"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    View Live
                                </a>
                                <Link
                                    :href="route('superadmin.articles.edit', article.id)"
                                    class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </Link>
                            </div>
                        </div>

                        <!-- Meta Info -->
                        <div class="flex flex-wrap gap-6 text-sm text-gray-500 mb-8 pb-6 border-b border-gray-200">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                {{ article.website?.name || 'N/A' }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ article.user?.name || 'Unknown' }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ article.views || 0 }} views
                            </div>
                            <div v-if="article.published_at" class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ formatDate(article.published_at) }}
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <div v-if="article.excerpt" class="mb-8 p-4 bg-gray-50 rounded-lg border-l-4 border-teal-500">
                            <p class="text-gray-700 italic">{{ article.excerpt }}</p>
                        </div>

                        <!-- Content Preview -->
                        <div class="prose max-w-none" v-html="article.content"></div>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

defineProps({
    article: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

