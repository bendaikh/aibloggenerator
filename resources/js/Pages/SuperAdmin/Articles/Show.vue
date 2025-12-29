<template>
    <SuperAdminLayout>
        <Head :title="article.title" />

        <div class="p-8">
            <div class="max-w-4xl">
                <div class="mb-8">
                    <Link 
                        :href="route('superadmin.articles.index', { website: currentWebsite?.id })" 
                        class="text-gray-400 hover:text-white flex items-center text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Articles
                    </Link>
                </div>

                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden">
                    <!-- Featured Image -->
                    <div v-if="article.featured_image" class="h-64 bg-[#252525]">
                        <img :src="article.featured_image" :alt="article.title" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="h-64 bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                        <span class="text-white text-6xl">📝</span>
                    </div>

                    <div class="p-8">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                        article.status === 'published' ? 'bg-emerald-900/50 text-emerald-400' : 
                                        article.status === 'draft' ? 'bg-gray-700 text-gray-300' : 'bg-yellow-900/50 text-yellow-400'
                                    ]">
                                        {{ article.status }}
                                    </span>
                                    <span v-if="article.category" class="px-3 py-1 rounded-full text-xs font-semibold bg-[#252525] text-gray-300">
                                        {{ article.category.name }}
                                    </span>
                                </div>
                                <h1 class="text-3xl font-bold text-white">{{ article.title }}</h1>
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
                                    :href="route('superadmin.articles.edit', { website: currentWebsite?.id, article: article.id })"
                                    class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </Link>
                            </div>
                        </div>

                        <!-- Meta Info -->
                        <div class="flex flex-wrap gap-6 text-sm text-gray-400 mb-8 pb-6 border-b border-[#2a2a2a]">
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
                        <div v-if="article.excerpt" class="mb-8 p-4 bg-[#252525] rounded-lg border-l-4 border-emerald-500">
                            <p class="text-gray-300 italic">{{ article.excerpt }}</p>
                        </div>

                        <!-- Content Preview -->
                        <div class="prose prose-invert max-w-none" v-html="article.content"></div>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const page = usePage();

defineProps({
    article: Object,
    currentWebsite: Object,
    websites: Array
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>
