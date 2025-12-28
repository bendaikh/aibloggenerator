<template>
    <SuperAdminLayout>
        <Head title="Articles" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Articles</h1>
                    <Link
                        :href="route('superadmin.articles.create')"
                        class="inline-flex items-center px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create New Article
                    </Link>
                </div>

                <!-- Filter by Website -->
                <div class="mb-6">
                    <select
                        v-model="selectedWebsite"
                        @change="filterByWebsite"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                    >
                        <option value="">All Websites</option>
                        <option v-for="website in websites" :key="website.id" :value="website.id">
                            {{ website.name }}
                        </option>
                    </select>
                </div>

                <div v-if="articles.data.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Website</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="article in articles.data" :key="article.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12 rounded-lg overflow-hidden bg-gray-100">
                                            <img v-if="article.featured_image" :src="article.featured_image" :alt="article.title" class="h-full w-full object-cover" />
                                            <div v-else class="h-full w-full bg-gradient-to-br from-teal-400 to-cyan-500 flex items-center justify-center">
                                                <span class="text-white text-lg">📝</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ article.title }}</div>
                                            <div class="text-sm text-gray-500">{{ article.slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ article.website?.name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ article.category?.name || 'Uncategorized' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                        article.status === 'published' ? 'bg-green-100 text-green-800' : 
                                        article.status === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800'
                                    ]">
                                        {{ article.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ article.views || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link
                                        :href="route('superadmin.articles.edit', article.id)"
                                        class="text-teal-600 hover:text-teal-900 mr-4"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteArticle(article)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-12 bg-white rounded-lg shadow">
                    <div class="text-6xl mb-4">📝</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No articles yet</h3>
                    <p class="text-gray-600 mb-6">Create your first article to get started!</p>
                    <Link
                        :href="route('superadmin.articles.create')"
                        class="inline-flex items-center px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition"
                    >
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
                            link.active ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                            !link.url && 'opacity-50 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const props = defineProps({
    articles: Object,
    websites: Array
});

const selectedWebsite = ref('');

const filterByWebsite = () => {
    router.get(route('superadmin.articles.index'), {
        website_id: selectedWebsite.value || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const deleteArticle = (article) => {
    if (confirm(`Are you sure you want to delete "${article.title}"?`)) {
        router.delete(route('superadmin.articles.destroy', article.id));
    }
};
</script>

