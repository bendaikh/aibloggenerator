<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';

const props = defineProps({
    pendingDomains: Array,
    allDomains: Array,
    websites: Array,
});

const activeTab = ref('pending');
const selectedDomain = ref(null);
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showNotesModal = ref(false);

const approveForm = useForm({
    website_id: '',
});

const rejectForm = useForm({
    rejection_reason: '',
});

const notesForm = useForm({
    notes: '',
});

const parkedForm = useForm({
    notes: '',
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadge = (status) => {
    const badges = {
        'pending': { bg: 'bg-yellow-900/50', text: 'text-yellow-300', label: 'Pending', dot: 'bg-yellow-400' },
        'parking': { bg: 'bg-blue-900/50', text: 'text-blue-300', label: 'Parking', dot: 'bg-blue-400' },
        'parked': { bg: 'bg-purple-900/50', text: 'text-purple-300', label: 'Parked', dot: 'bg-purple-400' },
        'approved': { bg: 'bg-emerald-900/50', text: 'text-emerald-300', label: 'Approved', dot: 'bg-emerald-400' },
        'rejected': { bg: 'bg-red-900/50', text: 'text-red-300', label: 'Rejected', dot: 'bg-red-400' },
    };
    return badges[status] || badges['pending'];
};

const filteredDomains = computed(() => {
    if (activeTab.value === 'pending') {
        return props.pendingDomains;
    }
    return props.allDomains;
});

const stats = computed(() => {
    return {
        pending: props.allDomains.filter(d => d.status === 'pending').length,
        parking: props.allDomains.filter(d => d.status === 'parking').length,
        parked: props.allDomains.filter(d => d.status === 'parked').length,
        approved: props.allDomains.filter(d => d.status === 'approved').length,
        rejected: props.allDomains.filter(d => d.status === 'rejected').length,
    };
});

// Get websites that belong to the domain requester
const userWebsites = computed(() => {
    if (!selectedDomain.value) return [];
    // Filter websites owned by the user who requested the domain
    return props.websites.filter(w => w.user_id === selectedDomain.value.user_id);
});

const markAsParking = (domain) => {
    if (confirm('Mark this domain as parking in progress?')) {
        router.post(route('organization.domains.parking', domain.id));
    }
};

const openParkedModal = (domain) => {
    selectedDomain.value = domain;
    parkedForm.notes = domain.notes || '';
    showNotesModal.value = true;
};

const markAsParked = () => {
    parkedForm.post(route('organization.domains.parked', selectedDomain.value.id), {
        onSuccess: () => {
            showNotesModal.value = false;
            selectedDomain.value = null;
            parkedForm.reset();
        },
    });
};

const openApproveModal = (domain) => {
    selectedDomain.value = domain;
    approveForm.website_id = '';
    showApproveModal.value = true;
};

const approveDomain = () => {
    approveForm.post(route('organization.domains.approve', selectedDomain.value.id), {
        onSuccess: () => {
            showApproveModal.value = false;
            selectedDomain.value = null;
            approveForm.reset();
        },
    });
};

const openRejectModal = (domain) => {
    selectedDomain.value = domain;
    rejectForm.rejection_reason = '';
    showRejectModal.value = true;
};

const rejectDomain = () => {
    rejectForm.post(route('organization.domains.reject', selectedDomain.value.id), {
        onSuccess: () => {
            showRejectModal.value = false;
            selectedDomain.value = null;
            rejectForm.reset();
        },
    });
};

const updateNotes = (domain) => {
    selectedDomain.value = domain;
    notesForm.notes = domain.notes || '';
    showNotesModal.value = true;
};
</script>

<template>
    <Head title="Pending Domains" />

    <OrganizationLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Pending Domains</h1>
                    <p class="text-gray-400 mt-1">Manage domain requests from users</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                        <div>
                            <p class="text-gray-400 text-xs">Pending</p>
                            <p class="text-xl font-bold text-white">{{ stats.pending }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-blue-400 rounded-full"></div>
                        <div>
                            <p class="text-gray-400 text-xs">Parking</p>
                            <p class="text-xl font-bold text-white">{{ stats.parking }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-purple-400 rounded-full"></div>
                        <div>
                            <p class="text-gray-400 text-xs">Parked</p>
                            <p class="text-xl font-bold text-white">{{ stats.parked }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-emerald-400 rounded-full"></div>
                        <div>
                            <p class="text-gray-400 text-xs">Approved</p>
                            <p class="text-xl font-bold text-white">{{ stats.approved }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                        <div>
                            <p class="text-gray-400 text-xs">Rejected</p>
                            <p class="text-xl font-bold text-white">{{ stats.rejected }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 mb-6">
                <button 
                    @click="activeTab = 'pending'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        activeTab === 'pending' 
                            ? 'bg-emerald-500 text-white' 
                            : 'bg-[#252525] text-gray-400 hover:text-white'
                    ]"
                >
                    Action Required ({{ pendingDomains.length }})
                </button>
                <button 
                    @click="activeTab = 'all'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        activeTab === 'all' 
                            ? 'bg-emerald-500 text-white' 
                            : 'bg-[#252525] text-gray-400 hover:text-white'
                    ]"
                >
                    All Domains ({{ allDomains.length }})
                </button>
            </div>

            <!-- Domains Table -->
            <div v-if="filteredDomains.length > 0" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#0a0a0a] border-b border-[#2a2a2a]">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Domain</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Website</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Submitted</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#2a2a2a]">
                            <tr v-for="domain in filteredDomains" :key="domain.id" class="hover:bg-[#252525] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-full flex items-center justify-center text-white font-bold">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ domain.domain }}</p>
                                            <p v-if="domain.notes" class="text-gray-500 text-xs truncate max-w-xs">{{ domain.notes }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-gray-300">{{ domain.user?.name || 'Unknown' }}</p>
                                        <p class="text-gray-500 text-xs">{{ domain.user?.email || '' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span :class="['w-2 h-2 rounded-full', getStatusBadge(domain.status).dot]"></span>
                                        <span :class="['text-xs px-2 py-1 rounded', getStatusBadge(domain.status).bg, getStatusBadge(domain.status).text]">
                                            {{ getStatusBadge(domain.status).label }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="domain.website" class="text-gray-300">{{ domain.website.name }}</span>
                                    <span v-else class="text-gray-500">-</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-gray-400 text-sm">{{ formatDate(domain.created_at) }}</p>
                                        <p v-if="domain.parked_at" class="text-gray-500 text-xs">Parked: {{ formatDate(domain.parked_at) }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <!-- Pending: Show Park button -->
                                        <button 
                                            v-if="domain.status === 'pending'"
                                            @click="markAsParking(domain)"
                                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-lg transition-colors"
                                        >
                                            Start Parking
                                        </button>

                                        <!-- Parking: Show Parked button -->
                                        <button 
                                            v-if="domain.status === 'parking'"
                                            @click="openParkedModal(domain)"
                                            class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-lg transition-colors"
                                        >
                                            Mark Parked
                                        </button>

                                        <!-- Parked: Show Approve button -->
                                        <button 
                                            v-if="domain.status === 'parked'"
                                            @click="openApproveModal(domain)"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs rounded-lg transition-colors"
                                        >
                                            Approve
                                        </button>

                                        <!-- Reject button for pending/parking/parked -->
                                        <button 
                                            v-if="['pending', 'parking', 'parked'].includes(domain.status)"
                                            @click="openRejectModal(domain)"
                                            class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 text-xs rounded-lg transition-colors"
                                        >
                                            Reject
                                        </button>

                                        <!-- Approved/Rejected: Show info -->
                                        <span v-if="domain.status === 'approved'" class="text-emerald-400 text-xs">
                                            Approved {{ formatDate(domain.approved_at) }}
                                        </span>
                                        <span v-if="domain.status === 'rejected'" class="text-red-400 text-xs truncate max-w-xs" :title="domain.rejection_reason">
                                            {{ domain.rejection_reason || 'Rejected' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-400 text-lg">No domains require action</p>
                <p class="text-gray-500 text-sm mt-2">All domain requests have been processed</p>
            </div>

            <!-- Workflow Info -->
            <div class="mt-8 bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                <h2 class="text-xl font-bold text-white mb-4">Domain Approval Workflow</h2>
                <div class="flex items-center gap-4 overflow-x-auto pb-2">
                    <div class="flex items-center gap-2 min-w-max">
                        <div class="w-8 h-8 bg-yellow-500/20 rounded-full flex items-center justify-center">
                            <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                        </div>
                        <span class="text-gray-300 text-sm">Pending</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <div class="flex items-center gap-2 min-w-max">
                        <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center">
                            <div class="w-3 h-3 bg-blue-400 rounded-full"></div>
                        </div>
                        <span class="text-gray-300 text-sm">Parking (Hostinger)</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <div class="flex items-center gap-2 min-w-max">
                        <div class="w-8 h-8 bg-purple-500/20 rounded-full flex items-center justify-center">
                            <div class="w-3 h-3 bg-purple-400 rounded-full"></div>
                        </div>
                        <span class="text-gray-300 text-sm">Parked</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <div class="flex items-center gap-2 min-w-max">
                        <div class="w-8 h-8 bg-emerald-500/20 rounded-full flex items-center justify-center">
                            <div class="w-3 h-3 bg-emerald-400 rounded-full"></div>
                        </div>
                        <span class="text-gray-300 text-sm">Approved & Live</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mark as Parked Modal -->
        <Teleport to="body">
            <div v-if="showNotesModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] w-full max-w-md">
                    <div class="p-6 border-b border-[#2a2a2a]">
                        <h2 class="text-xl font-bold text-white">Mark Domain as Parked</h2>
                        <p class="text-gray-400 text-sm mt-1">{{ selectedDomain?.domain }}</p>
                    </div>
                    <form @submit.prevent="markAsParked" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Notes (optional)</label>
                            <textarea 
                                v-model="parkedForm.notes"
                                rows="3"
                                placeholder="Add any notes about the parking process..."
                                class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500 transition-colors resize-none"
                            ></textarea>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button 
                                type="button"
                                @click="showNotesModal = false; parkedForm.reset();"
                                class="flex-1 bg-[#252525] hover:bg-[#303030] text-white px-4 py-3 rounded-lg font-medium transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                :disabled="parkedForm.processing"
                                class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg font-medium transition-colors disabled:opacity-50"
                            >
                                {{ parkedForm.processing ? 'Saving...' : 'Mark as Parked' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Approve Modal -->
        <Teleport to="body">
            <div v-if="showApproveModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] w-full max-w-md">
                    <div class="p-6 border-b border-[#2a2a2a]">
                        <h2 class="text-xl font-bold text-white">Approve Domain</h2>
                        <p class="text-gray-400 text-sm mt-1">{{ selectedDomain?.domain }}</p>
                    </div>
                    <form @submit.prevent="approveDomain" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Assign to Website (optional)</label>
                            <select 
                                v-model="approveForm.website_id"
                                class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-4 py-3 text-white focus:outline-none focus:border-emerald-500 transition-colors"
                            >
                                <option value="">Don't assign yet</option>
                                <option v-for="website in userWebsites" :key="website.id" :value="website.id">
                                    {{ website.name }} ({{ website.user?.name || 'Unknown' }})
                                </option>
                            </select>
                            <p class="text-gray-500 text-sm mt-1">Showing websites owned by {{ selectedDomain?.user?.name }}. The user can also assign the domain later.</p>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button 
                                type="button"
                                @click="showApproveModal = false; approveForm.reset();"
                                class="flex-1 bg-[#252525] hover:bg-[#303030] text-white px-4 py-3 rounded-lg font-medium transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                :disabled="approveForm.processing"
                                class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg font-medium transition-colors disabled:opacity-50"
                            >
                                {{ approveForm.processing ? 'Approving...' : 'Approve Domain' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Reject Modal -->
        <Teleport to="body">
            <div v-if="showRejectModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] w-full max-w-md">
                    <div class="p-6 border-b border-[#2a2a2a]">
                        <h2 class="text-xl font-bold text-white">Reject Domain</h2>
                        <p class="text-gray-400 text-sm mt-1">{{ selectedDomain?.domain }}</p>
                    </div>
                    <form @submit.prevent="rejectDomain" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Rejection Reason</label>
                            <textarea 
                                v-model="rejectForm.rejection_reason"
                                rows="3"
                                placeholder="Explain why this domain is being rejected..."
                                class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-red-500 transition-colors resize-none"
                                required
                            ></textarea>
                            <p v-if="rejectForm.errors.rejection_reason" class="text-red-400 text-sm mt-1">{{ rejectForm.errors.rejection_reason }}</p>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button 
                                type="button"
                                @click="showRejectModal = false; rejectForm.reset();"
                                class="flex-1 bg-[#252525] hover:bg-[#303030] text-white px-4 py-3 rounded-lg font-medium transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                :disabled="rejectForm.processing"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium transition-colors disabled:opacity-50"
                            >
                                {{ rejectForm.processing ? 'Rejecting...' : 'Reject Domain' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </OrganizationLayout>
</template>
