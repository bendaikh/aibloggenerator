<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';

const props = defineProps({
    subscribers: Object,
    stats: Object,
    websites: Array,
});

const isExporting = ref(false);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const exportSubscribers = () => {
    isExporting.value = true;
    
    // Create a form and submit it to trigger download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('organization.global-subscribers.export');
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    setTimeout(() => {
        isExporting.value = false;
    }, 2000);
};

const getSourceBadgeColor = (source) => {
    if (!source) return 'bg-gray-700 text-gray-300';
    
    const sources = {
        'website': 'bg-blue-900 text-blue-300',
        'popup': 'bg-purple-900 text-purple-300',
        'footer': 'bg-emerald-900 text-emerald-300',
        'sidebar': 'bg-amber-900 text-amber-300',
        'homepage': 'bg-pink-900 text-pink-300',
    };
    
    return sources[source.toLowerCase()] || 'bg-gray-700 text-gray-300';
};
</script>

<template>
    <Head title="Global Subscribers" />

    <OrganizationLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Global Subscribers</h1>
                    <p class="text-gray-400 mt-1">All email subscribers from your websites</p>
                </div>
                <button 
                    @click="exportSubscribers"
                    :disabled="isExporting || subscribers.data.length === 0"
                    class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg v-if="isExporting" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    {{ isExporting ? 'Exporting...' : 'Export CSV' }}
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Total Subscribers</p>
                            <p class="text-2xl font-bold text-white">{{ stats.total.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Active Subscribers</p>
                            <p class="text-2xl font-bold text-white">{{ stats.active.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">This Month</p>
                            <p class="text-2xl font-bold text-white">{{ stats.this_month.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscribers Table -->
            <div v-if="subscribers.data.length > 0" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#0a0a0a] border-b border-[#2a2a2a]">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Subscriber</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Website</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Owner</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Source</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Subscribed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#2a2a2a]">
                            <tr v-for="subscriber in subscribers.data" :key="subscriber.id" class="hover:bg-[#252525] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-full flex items-center justify-center text-white font-bold">
                                            {{ subscriber.email.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ subscriber.email }}</p>
                                            <p v-if="subscriber.name" class="text-gray-500 text-sm">{{ subscriber.name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-[#252525] rounded flex items-center justify-center text-xs font-bold text-amber-400">
                                            {{ subscriber.website?.name?.charAt(0).toUpperCase() || '?' }}
                                        </div>
                                        <span class="text-gray-300">{{ subscriber.website?.name || 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-400">{{ subscriber.website?.user?.name || 'Unknown' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-xs px-2 py-1 rounded', getSourceBadgeColor(subscriber.source)]">
                                        {{ subscriber.source || 'website' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="subscriber.is_active" class="flex items-center gap-1 text-emerald-400 text-sm">
                                        <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                        Active
                                    </span>
                                    <span v-else class="flex items-center gap-1 text-gray-500 text-sm">
                                        <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                                        Inactive
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-400 text-sm">
                                    {{ formatDate(subscriber.subscribed_at || subscriber.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="subscribers.links.length > 3" class="px-6 py-4 border-t border-[#2a2a2a] flex items-center justify-between">
                    <div class="text-sm text-gray-400">
                        Showing {{ subscribers.from }} to {{ subscribers.to }} of {{ subscribers.total }} subscribers
                    </div>
                    <div class="flex gap-2">
                        <component 
                            v-for="(link, index) in subscribers.links" 
                            :key="index"
                            :is="link.url ? 'a' : 'span'"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-sm transition-colors',
                                link.active 
                                    ? 'bg-emerald-500 text-white' 
                                    : link.url 
                                        ? 'bg-[#252525] text-gray-400 hover:bg-[#303030] hover:text-white' 
                                        : 'text-gray-600 cursor-not-allowed'
                            ]"
                        />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-400 text-lg">No subscribers yet</p>
                <p class="text-gray-500 text-sm mt-2">Subscribers from all your websites will appear here</p>
            </div>
        </div>
    </OrganizationLayout>
</template>
