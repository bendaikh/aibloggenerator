<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AIJobsNotification from '@/Components/AIJobsNotification.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const websites = computed(() => page.props.websites || []);

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

            <!-- Organization -->
            <div class="p-4 border-b border-[#2a2a2a]">
                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Organization</span>
                <div class="mt-3 flex items-center gap-3 p-2 rounded-lg bg-[#1f1f1f]">
                    <div class="w-8 h-8 bg-gradient-to-br from-rose-500 to-orange-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                        {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ user?.name || 'User' }}</p>
                        <p class="text-gray-500 text-xs truncate">{{ user?.email?.split('@')[0] || 'user' }}</p>
                    </div>
                </div>
            </div>

            <!-- Organization Navigation -->
            <div class="flex-1 overflow-y-auto p-4">
                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Menu</span>
                
                <nav class="mt-3 space-y-1">
                    <!-- Dashboard -->
                    <Link 
                        :href="route('organization.dashboard')" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('organization.dashboard') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Dashboard
                    </Link>

                    <!-- Websites -->
                    <Link 
                        :href="route('organization.websites.index')" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActivePrefix('organization.websites') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        Websites
                    </Link>

                    <!-- Global Settings -->
                    <Link 
                        :href="route('organization.settings')" 
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('organization.settings') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Global Settings
                    </Link>
                </nav>

                <!-- Websites List -->
                <div class="mt-6" v-if="websites.length > 0">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Your Websites</span>
                    <div class="mt-3 space-y-1">
                        <Link 
                            v-for="website in websites"
                            :key="website.id"
                            :href="route('superadmin.dashboard', { website: website.id })"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors text-gray-400 hover:bg-[#1a1a1a] hover:text-white group"
                        >
                            <div class="w-7 h-7 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-lg flex items-center justify-center shrink-0">
                                <span class="text-white text-xs font-bold">{{ website.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate">{{ website.name }}</p>
                            </div>
                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>
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
            <!-- Top Header Bar -->
            <header class="sticky top-0 z-40 bg-[#0f0f0f]/95 backdrop-blur border-b border-[#2a2a2a]">
                <div class="flex items-center justify-between px-8 py-3">
                    <!-- Left: Page Context -->
                    <div class="flex items-center gap-3">
                        <span class="text-gray-500 text-sm">Organization Overview</span>
                    </div>
                    
                    <!-- Right: Actions -->
                    <div class="flex items-center gap-4">
                        <!-- AI Jobs Notification -->
                        <AIJobsNotification />
                    </div>
                </div>
            </header>
            
            <slot />
        </main>
    </div>
</template>

