<script setup>
import { ref, computed } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: null
    },
    subscribers: {
        type: Object,
        default: () => ({ data: [] })
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, active: 0, this_month: 0 })
    }
});

const showDeleteModal = ref(false);
const subscriberToDelete = ref(null);
const isExporting = ref(false);

const openDeleteModal = (subscriber) => {
    subscriberToDelete.value = subscriber;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    subscriberToDelete.value = null;
};

const deleteSubscriber = () => {
    if (!subscriberToDelete.value) return;
    
    router.delete(route('superadmin.subscribers.destroy', {
        website: props.currentWebsite.id,
        subscriber: subscriberToDelete.value.id
    }), {
        onSuccess: () => closeDeleteModal()
    });
};

const exportSubscribers = () => {
    isExporting.value = true;
    window.location.href = route('superadmin.subscribers.export', { website: props.currentWebsite.id });
    setTimeout(() => {
        isExporting.value = false;
    }, 2000);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Email Subscribers" />

    <SuperAdminLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Email Subscribers</h1>
                    <p class="text-gray-400 mt-1">Manage email subscribers from your website</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        v-if="subscribers.data.length > 0"
                        @click="exportSubscribers"
                        :disabled="isExporting"
                        class="flex items-center gap-2 px-5 py-2.5 bg-[#252525] hover:bg-[#2a2a2a] text-white rounded-xl font-medium transition-all border border-[#3a3a3a] disabled:opacity-50"
                    >
                        <svg v-if="isExporting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-6 py-4 rounded-xl mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Total Subscribers</p>
                            <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Active Subscribers</p>
                            <p class="text-2xl font-bold text-white">{{ stats.active }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-500/20 to-rose-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">This Month</p>
                            <p class="text-2xl font-bold text-white">{{ stats.this_month }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="subscribers.data.length === 0" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Subscribers Yet</h3>
                <p class="text-gray-400 mb-6 max-w-md mx-auto">
                    When visitors subscribe to your newsletter on your website, their emails will appear here.
                </p>
            </div>

            <!-- Subscribers Table -->
            <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#2a2a2a]">
                            <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Email</th>
                            <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Source</th>
                            <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Status</th>
                            <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Subscribed</th>
                            <th class="text-right px-6 py-4 text-sm font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="subscriber in subscribers.data"
                            :key="subscriber.id"
                            class="border-b border-[#2a2a2a] hover:bg-[#252525] transition-colors"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white font-bold">
                                        {{ subscriber.email.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ subscriber.email }}</p>
                                        <p v-if="subscriber.name" class="text-gray-500 text-sm">{{ subscriber.name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-lg bg-[#252525] text-gray-300 capitalize">
                                    {{ subscriber.source }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-lg border',
                                    subscriber.is_active 
                                        ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' 
                                        : 'bg-gray-500/20 text-gray-400 border-gray-500/30'
                                ]">
                                    {{ subscriber.is_active ? 'Active' : 'Unsubscribed' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-400 text-sm">
                                {{ formatDate(subscriber.subscribed_at || subscriber.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="openDeleteModal(subscriber)"
                                    class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                    title="Delete"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="subscribers.links && subscribers.links.length > 3" class="mt-8 flex justify-center gap-2">
                <Link
                    v-for="link in subscribers.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm transition-colors',
                        link.active
                            ? 'bg-emerald-500 text-white'
                            : link.url
                                ? 'bg-[#1a1a1a] text-gray-400 hover:text-white hover:bg-[#252525]'
                                : 'bg-[#1a1a1a] text-gray-600 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/70" @click="closeDeleteModal"></div>
            <div class="relative bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-semibold text-white mb-2">Delete Subscriber</h3>
                <p class="text-gray-400 mb-6">
                    Are you sure you want to delete this subscriber? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="closeDeleteModal"
                        class="px-4 py-2 bg-[#252525] hover:bg-[#2a2a2a] text-white rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteSubscriber"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
