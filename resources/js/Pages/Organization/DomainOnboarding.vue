<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';

const props = defineProps({
    domainRequests: Array,
    websites: Array,
});

const showAddModal = ref(false);
const domainToDelete = ref(null);

const form = useForm({
    domain: '',
});

const formatDate = (date) => {
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
        'pending': { bg: 'bg-yellow-900/50', text: 'text-yellow-300', label: 'Pending' },
        'parking': { bg: 'bg-blue-900/50', text: 'text-blue-300', label: 'Parking in Progress' },
        'parked': { bg: 'bg-purple-900/50', text: 'text-purple-300', label: 'Parked - Awaiting Approval' },
        'approved': { bg: 'bg-emerald-900/50', text: 'text-emerald-300', label: 'Approved' },
        'rejected': { bg: 'bg-red-900/50', text: 'text-red-300', label: 'Rejected' },
    };
    return badges[status] || badges['pending'];
};

const pendingDomains = computed(() => {
    return props.domainRequests.filter(d => ['pending', 'parking', 'parked'].includes(d.status));
});

const approvedDomains = computed(() => {
    return props.domainRequests.filter(d => d.status === 'approved');
});

const rejectedDomains = computed(() => {
    return props.domainRequests.filter(d => d.status === 'rejected');
});

const submitDomain = () => {
    form.post(route('organization.domains.store'), {
        onSuccess: () => {
            form.reset();
            showAddModal.value = false;
        },
    });
};

const cancelRequest = (domainRequest) => {
    if (confirm('Are you sure you want to cancel this domain request?')) {
        router.delete(route('organization.domains.destroy', domainRequest.id));
    }
};
</script>

<template>
    <Head title="Onboard Domain" />

    <OrganizationLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Onboard Domain</h1>
                    <p class="text-gray-400 mt-1">Submit your custom domain for parking and approval</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Domain
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Pending</p>
                            <p class="text-2xl font-bold text-white">{{ pendingDomains.length }}</p>
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
                            <p class="text-gray-400 text-sm">Approved</p>
                            <p class="text-2xl font-bold text-white">{{ approvedDomains.length }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-red-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Rejected</p>
                            <p class="text-2xl font-bold text-white">{{ rejectedDomains.length }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Domain Requests Table -->
            <div v-if="domainRequests.length > 0" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#0a0a0a] border-b border-[#2a2a2a]">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Domain</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Website</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Submitted</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#2a2a2a]">
                            <tr v-for="request in domainRequests" :key="request.id" class="hover:bg-[#252525] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-full flex items-center justify-center text-white font-bold">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ request.domain }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-xs px-3 py-1.5 rounded-full', getStatusBadge(request.status).bg, getStatusBadge(request.status).text]">
                                        {{ getStatusBadge(request.status).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="request.website" class="text-gray-300">{{ request.website.name }}</span>
                                    <span v-else class="text-gray-500">Not assigned</span>
                                </td>
                                <td class="px-6 py-4 text-gray-400 text-sm">
                                    {{ formatDate(request.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <button 
                                        v-if="request.status === 'pending'"
                                        @click="cancelRequest(request)"
                                        class="text-red-400 hover:text-red-300 text-sm transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <span v-else-if="request.status === 'rejected'" class="text-gray-500 text-sm">
                                        {{ request.rejection_reason || 'No reason provided' }}
                                    </span>
                                    <span v-else class="text-gray-500 text-sm">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <p class="text-gray-400 text-lg">No domain requests yet</p>
                <p class="text-gray-500 text-sm mt-2">Submit your first domain to get started</p>
                <button 
                    @click="showAddModal = true"
                    class="mt-4 bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-lg font-medium transition-colors"
                >
                    Add Domain
                </button>
            </div>

            <!-- How it Works Section -->
            <div class="mt-8 bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                <h2 class="text-xl font-bold text-white mb-4">How Domain Onboarding Works</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-[#252525] rounded-xl">
                        <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center mb-3">
                            <span class="text-yellow-400 font-bold">1</span>
                        </div>
                        <h3 class="text-white font-medium mb-1">Submit Domain</h3>
                        <p class="text-gray-500 text-sm">Enter your domain name and submit for review</p>
                    </div>
                    <div class="p-4 bg-[#252525] rounded-xl">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mb-3">
                            <span class="text-blue-400 font-bold">2</span>
                        </div>
                        <h3 class="text-white font-medium mb-1">Parking Process</h3>
                        <p class="text-gray-500 text-sm">Admin will park your domain with the hosting provider</p>
                    </div>
                    <div class="p-4 bg-[#252525] rounded-xl">
                        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mb-3">
                            <span class="text-purple-400 font-bold">3</span>
                        </div>
                        <h3 class="text-white font-medium mb-1">Approval</h3>
                        <p class="text-gray-500 text-sm">Once parked, your domain will be approved</p>
                    </div>
                    <div class="p-4 bg-[#252525] rounded-xl">
                        <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center mb-3">
                            <span class="text-emerald-400 font-bold">4</span>
                        </div>
                        <h3 class="text-white font-medium mb-1">Go Live</h3>
                        <p class="text-gray-500 text-sm">Domain is assigned to your website and goes live</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Domain Modal -->
        <Teleport to="body">
            <div v-if="showAddModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] w-full max-w-md">
                    <div class="p-6 border-b border-[#2a2a2a]">
                        <h2 class="text-xl font-bold text-white">Add New Domain</h2>
                        <p class="text-gray-400 text-sm mt-1">Enter the domain you want to onboard</p>
                    </div>
                    <form @submit.prevent="submitDomain" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Domain Name</label>
                            <input 
                                v-model="form.domain"
                                type="text"
                                placeholder="example.com"
                                class="w-full bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500 transition-colors"
                            />
                            <p v-if="form.errors.domain" class="text-red-400 text-sm mt-1">{{ form.errors.domain }}</p>
                            <p class="text-gray-500 text-sm mt-1">Enter without http:// or www. prefix</p>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button 
                                type="button"
                                @click="showAddModal = false; form.reset();"
                                class="flex-1 bg-[#252525] hover:bg-[#303030] text-white px-4 py-3 rounded-lg font-medium transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-3 rounded-lg font-medium transition-colors disabled:opacity-50"
                            >
                                {{ form.processing ? 'Submitting...' : 'Submit Domain' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </OrganizationLayout>
</template>
