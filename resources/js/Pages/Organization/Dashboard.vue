<script setup>
import { ref, computed, watch } from 'vue';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalWebsites: 0,
            totalArticles: 0,
            totalVisitors: 0,
            totalPages: 0,
            totalSubscribers: 0
        })
    },
    websites: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({
            date_from: null,
            date_to: null,
            preset: 'all_time'
        })
    }
});

const showDatePicker = ref(false);
const selectedPreset = ref(props.filters.preset || 'all_time');
const customDateFrom = ref(props.filters.date_from || '');
const customDateTo = ref(props.filters.date_to || '');

const datePresets = [
    { value: 'all_time', label: 'All Time' },
    { value: 'today', label: 'Today' },
    { value: 'yesterday', label: 'Yesterday' },
    { value: 'last_7_days', label: 'Last 7 Days' },
    { value: 'last_30_days', label: 'Last 30 Days' },
    { value: 'this_month', label: 'This Month' },
    { value: 'last_month', label: 'Last Month' },
    { value: 'this_year', label: 'This Year' },
    { value: 'custom', label: 'Custom Range' },
];

const currentPresetLabel = computed(() => {
    if (selectedPreset.value === 'custom' && customDateFrom.value && customDateTo.value) {
        return `${formatDateShort(customDateFrom.value)} - ${formatDateShort(customDateTo.value)}`;
    }
    return datePresets.find(p => p.value === selectedPreset.value)?.label || 'All Time';
});

const formatDateShort = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const applyPreset = (preset) => {
    selectedPreset.value = preset;
    if (preset !== 'custom') {
        showDatePicker.value = false;
        router.get(route('organization.dashboard'), {
            preset: preset
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const applyCustomDates = () => {
    if (customDateFrom.value && customDateTo.value) {
        selectedPreset.value = 'custom';
        showDatePicker.value = false;
        router.get(route('organization.dashboard'), {
            date_from: customDateFrom.value,
            date_to: customDateTo.value,
            preset: 'custom'
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    const dropdown = document.getElementById('date-picker-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        showDatePicker.value = false;
    }
};

// Add click listener
if (typeof window !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
}
</script>

<template>
    <Head title="Organization Dashboard" />

    <OrganizationLayout>
        <div class="p-8">
            <!-- Header with Date Filter -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Organization Dashboard</h1>
                    <p class="text-gray-400 mt-1">Manage all your websites from one place</p>
                </div>
                
                <!-- Date Filter -->
                <div class="relative" id="date-picker-dropdown">
                    <button 
                        @click.stop="showDatePicker = !showDatePicker"
                        class="flex items-center gap-2 bg-[#1a1a1a] border border-[#2a2a2a] hover:border-[#3a3a3a] text-white px-4 py-2.5 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm font-medium">{{ currentPresetLabel }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Dropdown -->
                    <div 
                        v-if="showDatePicker"
                        class="absolute right-0 mt-2 w-72 bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl shadow-2xl z-50 overflow-hidden"
                    >
                        <!-- Presets -->
                        <div class="p-2 border-b border-[#2a2a2a]">
                            <p class="text-xs text-gray-500 uppercase tracking-wider px-2 py-1">Quick Presets</p>
                            <div class="grid grid-cols-2 gap-1">
                                <button
                                    v-for="preset in datePresets.filter(p => p.value !== 'custom')"
                                    :key="preset.value"
                                    @click="applyPreset(preset.value)"
                                    :class="[
                                        'px-3 py-2 rounded-lg text-sm text-left transition-colors',
                                        selectedPreset === preset.value 
                                            ? 'bg-emerald-500/20 text-emerald-400' 
                                            : 'text-gray-300 hover:bg-[#252525]'
                                    ]"
                                >
                                    {{ preset.label }}
                                </button>
                            </div>
                        </div>
                        
                        <!-- Custom Date Range -->
                        <div class="p-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Custom Range</p>
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs text-gray-400 mb-1 block">From</label>
                                    <input 
                                        type="date" 
                                        v-model="customDateFrom"
                                        class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500"
                                    />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-400 mb-1 block">To</label>
                                    <input 
                                        type="date" 
                                        v-model="customDateTo"
                                        class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500"
                                    />
                                </div>
                                <button 
                                    @click="applyCustomDates"
                                    :disabled="!customDateFrom || !customDateTo"
                                    class="w-full bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white py-2 rounded-lg text-sm font-medium transition-colors"
                                >
                                    Apply Range
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
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
                        <span class="text-gray-400 text-sm">Articles</span>
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-white">{{ stats.totalArticles }}</div>
                    <p class="text-gray-500 text-sm mt-2">In selected period</p>
                </div>

                <!-- Total Visitors -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Views</span>
                        <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-white">{{ stats.totalVisitors.toLocaleString() }}</div>
                    <p class="text-gray-500 text-sm mt-2">Article views</p>
                </div>

                <!-- Total Pages -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Pages</span>
                        <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-pink-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-white">{{ stats.totalPages }}</div>
                    <p class="text-gray-500 text-sm mt-2">In selected period</p>
                </div>

                <!-- Total Subscribers -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Subscribers</span>
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-4xl font-bold text-white">{{ stats.totalSubscribers || 0 }}</div>
                    <p class="text-gray-500 text-sm mt-2">In selected period</p>
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
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 overflow-hidden bg-[#252525]">
                                <img 
                                    v-if="website.favicon_url" 
                                    :src="website.favicon_url" 
                                    :alt="website.name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-white text-lg font-bold bg-gradient-to-br from-amber-500 to-yellow-500 w-full h-full flex items-center justify-center">{{ website.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-white font-semibold truncate group-hover:text-emerald-400 transition-colors">{{ website.name }}</h3>
                                <p class="text-gray-500 text-sm truncate">{{ website.domain || 'No domain set' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a 
                                    :href="website.url" 
                                    target="_blank"
                                    @click.stop
                                    class="p-2 text-gray-500 hover:text-emerald-400 transition-colors"
                                    title="Visit website"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                                <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
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
