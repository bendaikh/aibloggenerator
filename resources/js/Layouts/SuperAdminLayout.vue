<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: () => null
    }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const currentWebsite = computed(() => page.props.currentWebsite || props.currentWebsite);
const websites = computed(() => page.props.websites || []);

const contentManagementOpen = ref(true);
const websiteDropdownOpen = ref(false);

const isActive = (routeName) => {
    return route().current(routeName);
};

const isActivePrefix = (prefix) => {
    return route().current()?.startsWith(prefix);
};

const switchWebsite = (website) => {
    websiteDropdownOpen.value = false;
    router.get(route('superadmin.dashboard', { website: website.id }));
};
</script>

<template>
    <div class="min-h-screen bg-[#0f0f0f] flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#141414] border-r border-[#2a2a2a] flex flex-col fixed h-full">
            <!-- Logo -->
            <div class="p-4 border-b border-[#2a2a2a]">
                <Link :href="route('organization.dashboard')" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-cyan-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-white font-semibold text-lg">AIBlogGen</span>
                </Link>
            </div>

            <!-- Projects / Website Selector -->
            <div class="p-4 border-b border-[#2a2a2a]">
                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Projects</span>
                
                <!-- Website Selector Dropdown -->
                <div class="relative mt-3">
                    <button 
                        @click="websiteDropdownOpen = !websiteDropdownOpen"
                        class="flex items-center gap-3 p-2 rounded-lg bg-[#1f1f1f] cursor-pointer hover:bg-[#252525] transition-colors w-full"
                    >
                        <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-lg flex items-center justify-center">
                            <svg v-if="!currentWebsite" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                            </svg>
                            <span v-else class="text-white text-sm font-bold">{{ currentWebsite.name?.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="flex-1 min-w-0 text-left">
                            <p class="text-white text-sm font-medium truncate">{{ currentWebsite?.name || 'Select Website' }}</p>
                            <p class="text-gray-500 text-xs truncate">{{ currentWebsite?.domain || 'No website selected' }}</p>
                        </div>
                        <svg :class="['w-4 h-4 text-gray-500 transition-transform', websiteDropdownOpen ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div 
                        v-if="websiteDropdownOpen"
                        class="absolute top-full left-0 right-0 mt-1 bg-[#1a1a1a] border border-[#2a2a2a] rounded-lg shadow-xl z-50 py-1 max-h-64 overflow-y-auto"
                    >
                        <button
                            v-for="website in websites"
                            :key="website.id"
                            @click="switchWebsite(website)"
                            :class="[
                                'flex items-center gap-3 px-3 py-2 w-full hover:bg-[#252525] transition-colors',
                                currentWebsite?.id === website.id ? 'bg-[#252525]' : ''
                            ]"
                        >
                            <div class="w-7 h-7 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-lg flex items-center justify-center shrink-0">
                                <span class="text-white text-xs font-bold">{{ website.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="flex-1 min-w-0 text-left">
                                <p class="text-white text-sm truncate">{{ website.name }}</p>
                            </div>
                            <svg v-if="currentWebsite?.id === website.id" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        
                        <div class="border-t border-[#2a2a2a] mt-1 pt-1">
                            <Link
                                :href="route('organization.websites.create')"
                                class="flex items-center gap-3 px-3 py-2 w-full hover:bg-[#252525] transition-colors text-gray-400 hover:text-white"
                            >
                                <div class="w-7 h-7 border border-dashed border-[#3a3a3a] rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <span class="text-sm">Create New Website</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Back to Organization -->
                <Link 
                    :href="route('organization.dashboard')"
                    class="mt-3 flex items-center gap-2 text-gray-400 text-sm hover:text-white transition-colors w-full p-2 rounded-lg hover:bg-[#1a1a1a]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Organization
                </Link>
            </div>

            <!-- Website Navigation -->
            <div class="flex-1 overflow-y-auto p-4">
                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Website</span>
                
                <nav class="mt-3 space-y-1">
                    <!-- Overview -->
                    <Link 
                        :href="currentWebsite ? route('superadmin.dashboard', { website: currentWebsite.id }) : '#'" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('superadmin.dashboard') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Overview
                    </Link>

                    <!-- Content Management -->
                    <div>
                        <button 
                            @click="contentManagementOpen = !contentManagementOpen"
                            :class="[
                                'flex items-center justify-between w-full px-3 py-2 rounded-lg text-sm transition-colors',
                                isActivePrefix('superadmin.articles') || isActivePrefix('superadmin.pages') || isActivePrefix('superadmin.authors') || isActivePrefix('superadmin.categories')
                                    ? 'bg-[#1f1f1f] text-white' 
                                    : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Content Management
                            </div>
                            <svg 
                                :class="['w-4 h-4 transition-transform', contentManagementOpen ? 'rotate-180' : '']" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div v-show="contentManagementOpen" class="mt-1 ml-8 space-y-1">
                            <Link 
                                :href="currentWebsite ? route('superadmin.articles.index', { website: currentWebsite.id }) : '#'" 
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('superadmin.articles') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Articles
                            </Link>
                            <Link 
                                :href="currentWebsite ? route('superadmin.ai-articles.index', { website: currentWebsite.id }) : '#'" 
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('superadmin.ai-articles') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    AI Article Generator
                                </div>
                            </Link>
                            <Link 
                                :href="currentWebsite ? route('superadmin.pages.index', { website: currentWebsite.id }) : '#'" 
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('superadmin.pages') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Pages
                            </Link>
                            <Link 
                                :href="currentWebsite ? route('superadmin.authors', { website: currentWebsite.id }) : '#'" 
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActive('superadmin.authors') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Authors
                            </Link>
                            <Link 
                                :href="currentWebsite ? route('superadmin.categories.index', { website: currentWebsite.id }) : '#'" 
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('superadmin.categories') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Categories
                            </Link>
                        </div>
                    </div>

                    <!-- Appearance -->
                    <Link 
                        :href="currentWebsite ? route('superadmin.appearance', { website: currentWebsite.id }) : '#'" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('superadmin.appearance') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        Appearance
                    </Link>

                    <!-- Social Media -->
                    <Link 
                        :href="currentWebsite ? route('superadmin.social-media', { website: currentWebsite.id }) : '#'" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('superadmin.social-media') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        Social Media
                    </Link>

                    <!-- Assets -->
                    <Link 
                        :href="currentWebsite ? route('superadmin.assets', { website: currentWebsite.id }) : '#'" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('superadmin.assets') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Assets
                    </Link>

                    <!-- Deployment -->
                    <Link 
                        :href="currentWebsite ? route('superadmin.deployment', { website: currentWebsite.id }) : '#'" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('superadmin.deployment') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Deployment
                    </Link>

                    <!-- Ads Management -->
                    <Link 
                        :href="currentWebsite ? route('superadmin.ads', { website: currentWebsite.id }) : '#'" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('superadmin.ads') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                        Ads Management
                    </Link>
                </nav>
            </div>

            <!-- User Profile -->
            <div class="p-4 border-t border-[#2a2a2a]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ user?.name || 'User' }}</p>
                        <p class="text-gray-500 text-xs truncate">{{ user?.email || 'user@example.com' }}</p>
                    </div>
                    <Link 
                        :href="route('profile.edit')" 
                        class="p-2 text-gray-400 hover:text-white transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <slot />
        </main>
    </div>
</template>
