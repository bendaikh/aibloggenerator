<template>
    <SuperAdminLayout>
        <Head title="Articles" />

        <div class="p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Articles</h1>
                    <p class="text-gray-400 mt-1">Manage articles for {{ currentWebsite?.name }}</p>
                </div>
                <Link
                    :href="route('superadmin.articles.create', { website: currentWebsite?.id })"
                    class="inline-flex items-center px-6 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Article
                </Link>
            </div>

            <div v-if="articles.data.length > 0" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden">
                <table class="min-w-full divide-y divide-[#2a2a2a]">
                    <thead class="bg-[#141414]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#2a2a2a]">
                        <tr v-for="article in articles.data" :key="article.id" class="hover:bg-[#252525]">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-lg overflow-hidden bg-[#252525]">
                                        <img v-if="article.featured_image" :src="article.featured_image" :alt="article.title" class="h-full w-full object-cover" />
                                        <div v-else class="h-full w-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                                            <span class="text-white text-lg">📝</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">{{ article.title }}</div>
                                        <div class="text-sm text-gray-500">{{ article.slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                {{ article.category?.name || 'Uncategorized' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'px-3 py-1 rounded-full text-xs font-semibold',
                                    article.status === 'published' ? 'bg-emerald-900/50 text-emerald-400' : 
                                    article.status === 'draft' ? 'bg-gray-700 text-gray-300' : 'bg-yellow-900/50 text-yellow-400'
                                ]">
                                    {{ article.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                {{ article.views || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a
                                        :href="article.url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-blue-400 hover:text-blue-300 transition"
                                        title="Visit Article"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                    <Link
                                        :href="route('superadmin.articles.edit', { website: currentWebsite?.id, article: article.id })"
                                        class="text-emerald-400 hover:text-emerald-300 transition"
                                        title="Edit Article"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="deleteArticle(article)"
                                        class="text-red-400 hover:text-red-300 transition"
                                        title="Delete Article"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="text-center py-16 bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a]">
                <div class="w-20 h-20 bg-[#252525] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">No articles yet</h3>
                <p class="text-gray-400 mb-8">Create your first article to get started!</p>
                <Link
                    :href="route('superadmin.articles.create', { website: currentWebsite?.id })"
                    class="inline-flex items-center px-8 py-4 bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 transition"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Article
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="articles.data.length > 0 && articles.links.length > 3" class="mt-6 flex justify-center gap-2">
                <Link
                    v-for="link in articles.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm',
                        link.active ? 'bg-emerald-500 text-white' : 'bg-[#252525] text-gray-300 hover:bg-[#303030]',
                        !link.url && 'opacity-50 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const page = usePage();

const props = defineProps({
    articles: Object,
    currentWebsite: Object,
    websites: Array
});

const deleteArticle = (article) => {
    if (confirm(`Are you sure you want to delete "${article.title}"?`)) {
        router.delete(route('superadmin.articles.destroy', { 
            website: props.currentWebsite?.id, 
            article: article.id 
        }));
    }
};
</script>
