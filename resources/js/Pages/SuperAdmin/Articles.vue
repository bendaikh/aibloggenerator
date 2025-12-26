<script setup>
import { ref } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    articles: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
</script>

<template>
    <Head title="Articles" />

    <SuperAdminLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Articles</h1>
                    <p class="text-gray-400 mt-1">Manage your blog articles</p>
                </div>
                <button class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Article
                </button>
            </div>

            <!-- Search and Filters -->
            <div class="flex items-center gap-4 mb-6">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search articles..."
                        class="w-full bg-[#1a1a1a] border border-[#2a2a2a] text-white pl-10 pr-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                </div>
                <select class="bg-[#1a1a1a] border border-[#2a2a2a] text-gray-400 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option>All Status</option>
                    <option>Published</option>
                    <option>Draft</option>
                </select>
                <select class="bg-[#1a1a1a] border border-[#2a2a2a] text-gray-400 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option>All Categories</option>
                </select>
            </div>

            <!-- Articles Table -->
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden">
                <table class="w-full">
                    <thead class="bg-[#141414]">
                        <tr>
                            <th class="text-left text-gray-400 text-sm font-medium px-6 py-4">Title</th>
                            <th class="text-left text-gray-400 text-sm font-medium px-6 py-4">Category</th>
                            <th class="text-left text-gray-400 text-sm font-medium px-6 py-4">Author</th>
                            <th class="text-left text-gray-400 text-sm font-medium px-6 py-4">Status</th>
                            <th class="text-left text-gray-400 text-sm font-medium px-6 py-4">Date</th>
                            <th class="text-left text-gray-400 text-sm font-medium px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#2a2a2a]">
                        <!-- Empty State -->
                        <tr v-if="articles.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-400">No articles yet</p>
                                <p class="text-gray-500 text-sm mt-1">Create your first article to get started</p>
                            </td>
                        </tr>
                        <!-- Sample Row -->
                        <tr v-for="article in articles" :key="article.id" class="hover:bg-[#1f1f1f] transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-white font-medium">{{ article.title }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-400">{{ article.category }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-400">{{ article.author }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="[
                                    'px-2 py-1 text-xs rounded-full',
                                    article.status === 'published' 
                                        ? 'bg-emerald-400/20 text-emerald-400' 
                                        : 'bg-amber-400/20 text-amber-400'
                                ]">
                                    {{ article.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-400">{{ article.date }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button class="p-2 text-gray-400 hover:text-white hover:bg-[#252525] rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </SuperAdminLayout>
</template>

