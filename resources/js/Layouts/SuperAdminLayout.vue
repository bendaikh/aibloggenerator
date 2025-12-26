<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: () => ({ name: 'My Website', domain: 'mywebsite.com' })
    }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const contentManagementOpen = ref(true);

const isActive = (routeName) => {
    return route().current(routeName);
};

const isActivePrefix = (prefix) => {
    return route().current()?.startsWith(prefix);
};
</script>

<template>
    <div class="min-h-screen bg-[#0f0f0f] flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#141414] border-r border-[#2a2a2a] flex flex-col fixed h-full">
            <!-- Logo -->
            <div class="p-4 border-b border-[#2a2a2a]">
                <Link href="/" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-cyan-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-white font-semibold text-lg">AIBlogGen</span>
                </Link>
            </div>

            <!-- Organizations -->
            <div class="p-4 border-b border-[#2a2a2a]">
                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Organizations</span>
                <div class="mt-3 flex items-center gap-3 p-2 rounded-lg bg-[#1f1f1f] cursor-pointer hover:bg-[#252525] transition-colors">
                    <div class="w-8 h-8 bg-gradient-to-br from-rose-500 to-orange-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                        {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ user?.name || 'User' }}</p>
                        <p class="text-gray-500 text-xs truncate">{{ user?.email?.split('@')[0] || 'user' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </div>
            </div>

            <!-- Projects -->
            <div class="p-4 border-b border-[#2a2a2a]">
                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Projects</span>
                <div class="mt-3 flex items-center gap-3 p-2 rounded-lg bg-[#1f1f1f] cursor-pointer hover:bg-[#252525] transition-colors">
                    <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ currentWebsite.name }}</p>
                        <p class="text-gray-500 text-xs truncate">{{ currentWebsite.domain }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </div>

                <button class="mt-2 flex items-center gap-2 text-gray-400 text-sm hover:text-white transition-colors w-full p-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Organization
                </button>
            </div>

            <!-- Website Navigation -->
            <div class="flex-1 overflow-y-auto p-4">
                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Website</span>
                
                <nav class="mt-3 space-y-1">
                    <!-- Overview -->
                    <Link 
                        :href="route('superadmin.dashboard')" 
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

                    <!-- Websites -->
                    <Link 
                        :href="route('superadmin.websites.index')" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActivePrefix('superadmin.websites') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        Websites
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
                                :href="route('superadmin.articles.index')" 
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('superadmin.articles') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Articles
                            </Link>
                            <Link 
                                :href="route('superadmin.pages')" 
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActive('superadmin.pages') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Pages
                            </Link>
                            <Link 
                                :href="route('superadmin.authors')" 
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActive('superadmin.authors') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Authors
                            </Link>
                            <Link 
                                :href="route('superadmin.categories')" 
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActive('superadmin.categories') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Categories
                            </Link>
                        </div>
                    </div>

                    <!-- Appearance -->
                    <Link 
                        :href="route('superadmin.appearance')" 
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
                        :href="route('superadmin.social-media')" 
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
                        :href="route('superadmin.assets')" 
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
                        :href="route('superadmin.deployment')" 
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
                        :href="route('superadmin.ads')" 
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

                    <!-- Website Settings -->
                    <Link 
                        :href="route('superadmin.settings')" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('superadmin.settings') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Website Settings
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

