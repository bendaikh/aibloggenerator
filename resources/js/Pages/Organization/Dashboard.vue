<script setup>
import { computed } from 'vue';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalWebsites: 0,
            totalArticles: 0,
            totalCategories: 0,
            totalPages: 0
        })
    },
    websites: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head title="Organization Dashboard" />

    <OrganizationLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Organization Dashboard</h1>
                <p class="text-gray-400 mt-1">Manage all your websites from one place</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Websites -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Total Websites</span>
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-white">{{ stats.totalWebsites }}</div>
                    <p class="text-gray-500 text-sm mt-2">Active websites</p>
                </div>

                <!-- Total Articles -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Total Articles</span>
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-white">{{ stats.totalArticles }}</div>
                    <p class="text-gray-500 text-sm mt-2">Across all websites</p>
                </div>

                <!-- Total Categories -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Total Categories</span>
                        <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-white">{{ stats.totalCategories }}</div>
                    <p class="text-gray-500 text-sm mt-2">Across all websites</p>
                </div>

                <!-- Total Pages -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Total Pages</span>
                        <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-pink-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-white">{{ stats.totalPages }}</div>
                    <p class="text-gray-500 text-sm mt-2">Across all websites</p>
                </div>
            </div>

            <!-- Websites List -->
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Your Websites</h2>
                        <p class="text-gray-400 text-sm mt-1">Click on a website to manage its content</p>
                    </div>
                    <Link
                        :href="route('organization.websites.create')"
                        class="inline-flex items-center px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Website
                    </Link>
                </div>

                <div v-if="websites.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link
                        v-for="website in websites"
                        :key="website.id"
                        :href="route('superadmin.dashboard', { website: website.id })"
                        class="block p-4 bg-[#252525] rounded-xl border border-[#3a3a3a] hover:border-emerald-500 transition-all group"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-xl flex items-center justify-center shrink-0">
                                <span class="text-white text-lg font-bold">{{ website.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-white font-semibold truncate group-hover:text-emerald-400 transition-colors">{{ website.name }}</h3>
                                <p class="text-gray-500 text-sm truncate">{{ website.domain || 'No domain set' }}</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                        <div class="flex items-center gap-4 mt-4 text-sm">
                            <span class="text-gray-400">
                                <span class="text-white font-medium">{{ website.articles_count || 0 }}</span> articles
                            </span>
                            <span class="text-gray-400">
                                <span class="text-white font-medium">{{ website.categories_count || 0 }}</span> categories
                            </span>
                            <span :class="[
                                'ml-auto px-2 py-1 rounded-full text-xs font-medium',
                                website.is_active ? 'bg-emerald-900/50 text-emerald-400' : 'bg-red-900/50 text-red-400'
                            ]">
                                {{ website.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </Link>
                </div>

                <div v-else class="text-center py-12">
                    <div class="w-16 h-16 bg-[#252525] rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">No websites yet</h3>
                    <p class="text-gray-400 mb-6">Create your first website to get started!</p>
                    <Link
                        :href="route('organization.websites.create')"
                        class="inline-flex items-center px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Your First Website
                    </Link>
                </div>
            </div>
        </div>
    </OrganizationLayout>
</template>

