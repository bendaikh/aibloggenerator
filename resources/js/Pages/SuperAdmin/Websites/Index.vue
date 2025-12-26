<template>
    <SuperAdminLayout>
        <Head title="Websites" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">My Websites</h1>
                    <Link
                        :href="route('superadmin.websites.create')"
                        class="inline-flex items-center px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create New Website
                    </Link>
                </div>

                <div v-if="websites.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="website in websites"
                        :key="website.id"
                        class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden"
                    >
                        <div class="h-48 bg-gradient-to-br from-teal-400 to-cyan-500 flex items-center justify-center">
                            <img v-if="website.logo" :src="website.logo" :alt="website.name" class="max-h-32 max-w-full" />
                            <span v-else class="text-4xl font-bold text-white">{{ website.name.charAt(0) }}</span>
                        </div>
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ website.name }}</h2>
                            <p v-if="website.description" class="text-gray-600 mb-4 line-clamp-2">
                                {{ website.description }}
                            </p>
                            <div class="flex items-center gap-4 mb-4 text-sm text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ website.articles_count }} articles
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    {{ website.categories_count }} categories
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span :class="[
                                    'px-3 py-1 rounded-full text-xs font-semibold',
                                    website.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]">
                                    {{ website.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <Link
                                    :href="route('superadmin.websites.show', website.id)"
                                    class="flex-1 text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                                >
                                    View
                                </Link>
                                <Link
                                    :href="route('superadmin.websites.edit', website.id)"
                                    class="flex-1 text-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition"
                                >
                                    Edit
                                </Link>
                                <a
                                    :href="route('website.show', website.slug)"
                                    target="_blank"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-white rounded-lg shadow">
                    <div class="text-6xl mb-4">🌐</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No websites yet</h3>
                    <p class="text-gray-600 mb-6">Create your first website to get started!</p>
                    <Link
                        :href="route('superadmin.websites.create')"
                        class="inline-flex items-center px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition"
                    >
                        Create Website
                    </Link>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

defineProps({
    websites: Array
});
</script>

