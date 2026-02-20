<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AIJobsNotification from '@/Components/AIJobsNotification.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const websites = computed(() => page.props.websites || []);

const sidebarOpen = ref(false);
const userManagementOpen = ref(false);
const connectDomainsOpen = ref(false);
const userDropdownOpen = ref(false);

const isActive = (routeName) => {
    return route().current(routeName);
};

const isActivePrefix = (prefix) => {
    return route().current()?.startsWith(prefix);
};

const isUserManagementActive = () => {
    return isActivePrefix('organization.users') || 
           isActivePrefix('organization.roles') || 
           isActivePrefix('organization.permissions');
};

const isConnectDomainsActive = () => {
    return isActivePrefix('organization.domains') || 
           isActivePrefix('organization.pending-domains');
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

// Close sidebar when clicking a link (for mobile UX)
const handleNavClick = () => {
    if (window.innerWidth < 1024) {
        sidebarOpen.value = false;
    }
};

// Close sidebar on escape key
const handleKeydown = (e) => {
    if (e.key === 'Escape' && sidebarOpen.value) {
        sidebarOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div class="min-h-screen bg-[#0f0f0f] flex">
        <!-- Mobile Overlay -->
        <div 
            v-if="sidebarOpen" 
            @click="closeSidebar"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
        ></div>

        <!-- Sidebar -->
        <aside :class="[
            'w-64 bg-[#141414] border-r border-[#2a2a2a] flex flex-col fixed h-full z-50 transition-transform duration-300 ease-in-out',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]">
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
                        @click="handleNavClick"
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
                        @click="handleNavClick"
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
                        @click="handleNavClick"
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

                    <!-- Global Articles -->
                    <Link 
                        :href="route('organization.global-articles.index')" 
                        @click="handleNavClick"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('organization.global-articles.index') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        Global Articles
                    </Link>

                    <!-- Global Subscribers -->
                    <Link 
                        :href="route('organization.global-subscribers.index')" 
                        @click="handleNavClick"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('organization.global-subscribers.index') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Global Subscribers
                    </Link>

                    <!-- Connect Domains -->
                    <div>
                        <button 
                            @click="connectDomainsOpen = !connectDomainsOpen"
                            :class="[
                                'flex items-center justify-between w-full px-3 py-2 rounded-lg text-sm transition-colors',
                                isConnectDomainsActive()
                                    ? 'bg-[#1f1f1f] text-white' 
                                    : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                Connect Domains
                            </div>
                            <svg 
                                :class="['w-4 h-4 transition-transform', connectDomainsOpen ? 'rotate-180' : '']" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div v-show="connectDomainsOpen" class="mt-1 ml-8 space-y-1">
                            <Link 
                                :href="route('organization.domains.index')" 
                                @click="handleNavClick"
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('organization.domains') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Onboard Domain
                            </Link>
                            <Link 
                                v-if="user?.role === 'superadmin'"
                                :href="route('organization.pending-domains.index')" 
                                @click="handleNavClick"
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('organization.pending-domains') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Pending Domains
                            </Link>
                        </div>
                    </div>

                    <!-- API Keys -->
                    <Link 
                        :href="route('organization.api-keys')" 
                        @click="handleNavClick"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('organization.api-keys') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        API Keys
                    </Link>

                    <!-- Agent Rewrite -->
                    <Link 
                        :href="route('organization.agent-rewrite')" 
                        @click="handleNavClick"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('organization.agent-rewrite') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Agent Rewrite
                    </Link>

                    <!-- API Usage & Costs -->
                    <Link 
                        :href="route('organization.api-usage')" 
                        @click="handleNavClick"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('organization.api-usage') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        API Usage & Costs
                    </Link>

                    <!-- Themes -->
                    <Link 
                        :href="route('organization.themes')" 
                        @click="handleNavClick"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                            isActive('organization.themes') 
                                ? 'bg-[#1f1f1f] text-white' 
                                : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        Themes
                    </Link>

                    <!-- User Management -->
                    <div v-if="user?.role === 'superadmin'">
                        <button 
                            @click="userManagementOpen = !userManagementOpen"
                            :class="[
                                'flex items-center justify-between w-full px-3 py-2 rounded-lg text-sm transition-colors',
                                isUserManagementActive()
                                    ? 'bg-[#1f1f1f] text-white' 
                                    : 'text-gray-400 hover:bg-[#1a1a1a] hover:text-white'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                User Management
                            </div>
                            <svg 
                                :class="['w-4 h-4 transition-transform', userManagementOpen ? 'rotate-180' : '']" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div v-show="userManagementOpen" class="mt-1 ml-8 space-y-1">
                            <Link 
                                :href="route('organization.users.index')" 
                                @click="handleNavClick"
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('organization.users') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Users
                            </Link>
                            <Link 
                                :href="route('organization.roles.index')" 
                                @click="handleNavClick"
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('organization.roles') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Roles
                            </Link>
                            <Link 
                                :href="route('organization.permissions.index')" 
                                @click="handleNavClick"
                                :class="[
                                    'block px-3 py-2 rounded-lg text-sm transition-colors',
                                    isActivePrefix('organization.permissions') ? 'text-white bg-[#252525]' : 'text-gray-400 hover:text-white hover:bg-[#1a1a1a]'
                                ]"
                            >
                                Permissions
                            </Link>
                        </div>
                    </div>
                </nav>

                <!-- Websites List -->
                <div class="mt-6" v-if="websites.length > 0">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Your Websites</span>
                    <div class="mt-3 space-y-1">
                        <Link 
                            v-for="website in websites"
                            :key="website.id"
                            :href="route('superadmin.dashboard', { website: website.id })"
                            @click="handleNavClick"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors text-gray-400 hover:bg-[#1a1a1a] hover:text-white group"
                        >
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 overflow-hidden bg-[#252525]">
                                <img 
                                    v-if="website.favicon_url" 
                                    :src="website.favicon_url" 
                                    :alt="website.name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-white text-xs font-bold bg-gradient-to-br from-amber-500 to-yellow-500 w-full h-full flex items-center justify-center">{{ website.name.charAt(0).toUpperCase() }}</span>
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
                <div class="relative">
                    <button
                        @click="userDropdownOpen = !userDropdownOpen"
                        class="flex items-center gap-3 w-full hover:bg-[#1a1a1a] p-2 rounded-lg transition-colors"
                    >
                        <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                            {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                        </div>
                        <div class="flex-1 min-w-0 text-left">
                            <p class="text-white text-sm font-medium truncate">{{ user?.name || 'User' }}</p>
                            <p class="text-gray-500 text-xs truncate">{{ user?.email || 'user@example.com' }}</p>
                        </div>
                        <svg :class="['w-4 h-4 text-gray-500 transition-transform', userDropdownOpen ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- User Dropdown Menu -->
                    <div 
                        v-if="userDropdownOpen"
                        class="absolute bottom-full left-0 right-0 mb-2 bg-[#1a1a1a] border border-[#2a2a2a] rounded-lg shadow-xl z-50 py-1"
                    >
                        <Link
                            :href="route('profile.edit')"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-[#252525] transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile Settings
                        </Link>
                        <div class="border-t border-[#2a2a2a] my-1"></div>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-gray-400 hover:text-red-400 hover:bg-[#252525] transition-colors w-full"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-64">
            <!-- Top Header Bar -->
            <header class="sticky top-0 z-30 bg-[#0f0f0f]/95 backdrop-blur border-b border-[#2a2a2a]">
                <div class="flex items-center justify-between px-4 lg:px-8 py-3">
                    <!-- Left: Hamburger + Page Context -->
                    <div class="flex items-center gap-3">
                        <!-- Mobile Hamburger Button -->
                        <button 
                            @click="toggleSidebar"
                            class="lg:hidden p-2 text-gray-400 hover:text-white hover:bg-[#1a1a1a] rounded-lg transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <span class="text-gray-500 text-sm hidden sm:block">Organization Overview</span>
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

