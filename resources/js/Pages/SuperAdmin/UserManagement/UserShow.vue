<script setup>
import { Head, Link } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';

const props = defineProps({
    user: Object,
    userWebsites: Array,
    userArticlesCount: Number,
});

const getRoleBadgeColor = (roleName) => {
    if (!roleName) return 'bg-gray-700 text-gray-300';
    
    const hash = roleName.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    const colors = [
        'bg-purple-900 text-purple-300',
        'bg-blue-900 text-blue-300',
        'bg-emerald-900 text-emerald-300',
        'bg-amber-900 text-amber-300',
        'bg-pink-900 text-pink-300',
        'bg-indigo-900 text-indigo-300',
    ];
    return colors[hash % colors.length];
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`User Profile - ${user.name}`" />

    <OrganizationLayout>
        <div class="p-8">
            <!-- Back Button -->
            <div class="mb-6">
                <Link 
                    :href="route('organization.users.index')"
                    class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Users
                </Link>
            </div>

            <!-- User Profile Header -->
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-violet-600/20 to-purple-600/20 px-8 py-12">
                    <div class="flex items-center gap-6">
                        <div class="w-24 h-24 bg-gradient-to-br from-violet-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-lg">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">{{ user.name }}</h1>
                            <p class="text-gray-400 mt-1">{{ user.email }}</p>
                            <div class="mt-3">
                                <span 
                                    v-if="user.role_relation" 
                                    :class="['text-sm px-3 py-1 rounded-full', getRoleBadgeColor(user.role_relation.name)]"
                                >
                                    {{ user.role_relation.display_name }}
                                </span>
                                <span v-else class="text-sm px-3 py-1 rounded-full bg-gray-700 text-gray-300">
                                    No Role
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- User Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Info Card -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <h2 class="text-xl font-bold text-white mb-6">User Information</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-500 mb-1">Full Name</label>
                                    <p class="text-white">{{ user.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-500 mb-1">Email Address</label>
                                    <p class="text-white">{{ user.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-500 mb-1">Role</label>
                                    <p class="text-white">{{ user.role_relation?.display_name || 'No Role' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-500 mb-1">Member Since</label>
                                    <p class="text-white">{{ formatDate(user.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User's Websites -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <h2 class="text-xl font-bold text-white mb-6">Websites</h2>
                        
                        <div v-if="userWebsites && userWebsites.length > 0" class="space-y-3">
                            <div 
                                v-for="website in userWebsites" 
                                :key="website.id"
                                class="flex items-center justify-between p-4 bg-[#252525] rounded-xl hover:bg-[#303030] transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 overflow-hidden bg-[#1a1a1a]">
                                        <img 
                                            v-if="website.favicon_url" 
                                            :src="website.favicon_url" 
                                            :alt="website.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <span v-else class="text-white text-sm font-bold bg-gradient-to-br from-amber-500 to-yellow-500 w-full h-full flex items-center justify-center">
                                            {{ website.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ website.name }}</p>
                                        <p class="text-gray-500 text-sm">{{ website.subdomain }}.{{ website.domain || 'example.com' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-400">
                                    <span>{{ website.articles_count }} articles</span>
                                    <span>{{ website.categories_count }} categories</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <p class="text-gray-500">No websites yet</p>
                        </div>
                    </div>
                </div>

                <!-- Stats Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <h2 class="text-lg font-bold text-white mb-4">Quick Stats</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-[#252525] rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-400">Websites</span>
                                </div>
                                <span class="text-2xl font-bold text-white">{{ userWebsites?.length || 0 }}</span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-[#252525] rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-400">Articles</span>
                                </div>
                                <span class="text-2xl font-bold text-white">{{ userArticlesCount || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Account Status -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <h2 class="text-lg font-bold text-white mb-4">Account Status</h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Email Verified</span>
                                <span v-if="user.email_verified_at" class="flex items-center gap-1 text-emerald-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Verified
                                </span>
                                <span v-else class="flex items-center gap-1 text-amber-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Pending
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Last Updated</span>
                                <span class="text-white text-sm">{{ formatDate(user.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OrganizationLayout>
</template>
