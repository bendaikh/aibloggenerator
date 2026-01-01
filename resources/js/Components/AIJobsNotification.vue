<template>
    <div class="relative">
        <!-- Notification Bell Button -->
        <button 
            @click="toggleDropdown"
            class="relative p-2 text-gray-400 hover:text-white transition-colors rounded-lg hover:bg-[#1a1a1a]"
            :class="{ 'text-emerald-400': activeCount > 0 }"
        >
            <!-- Bell Icon with Animation -->
            <div :class="{ 'animate-pulse': activeCount > 0 }">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            
            <!-- Badge Counter -->
            <span 
                v-if="activeCount > 0"
                class="absolute -top-1 -right-1 w-5 h-5 bg-emerald-500 text-white text-xs font-bold rounded-full flex items-center justify-center animate-bounce"
            >
                {{ activeCount > 9 ? '9+' : activeCount }}
            </span>

            <!-- Processing Indicator -->
            <span 
                v-if="processingCount > 0"
                class="absolute bottom-0 right-0 w-3 h-3"
            >
                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
        </button>

        <!-- Dropdown Panel -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div 
                v-if="isOpen"
                class="absolute right-0 mt-2 w-96 bg-[#1a1a1a] border border-[#2a2a2a] rounded-xl shadow-2xl z-50 overflow-hidden"
            >
                <!-- Header -->
                <div class="px-4 py-3 border-b border-[#2a2a2a] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <h3 class="text-white font-semibold">AI Article Generation</h3>
                    </div>
                    <button 
                        v-if="completedJobs.length > 0"
                        @click="clearCompleted"
                        class="text-xs text-gray-400 hover:text-white transition-colors"
                    >
                        Clear completed
                    </button>
                </div>

                <!-- Jobs List -->
                <div class="max-h-96 overflow-y-auto">
                    <!-- Empty State -->
                    <div v-if="jobs.length === 0" class="px-4 py-8 text-center">
                        <div class="w-12 h-12 bg-[#252525] rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm">No recent AI generations</p>
                        <p class="text-gray-600 text-xs mt-1">Generate articles to see them here</p>
                    </div>

                    <!-- Job Items -->
                    <div v-else class="divide-y divide-[#2a2a2a]">
                        <div 
                            v-for="job in jobs" 
                            :key="job.id"
                            class="px-4 py-3 hover:bg-[#1f1f1f] transition-colors"
                        >
                            <div class="flex items-start gap-3">
                                <!-- Status Icon -->
                                <div class="mt-0.5">
                                    <!-- Pending -->
                                    <div v-if="job.status === 'pending'" class="w-8 h-8 bg-yellow-900/50 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <!-- Processing -->
                                    <div v-else-if="job.status === 'processing'" class="w-8 h-8 bg-blue-900/50 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <!-- Completed -->
                                    <div v-else-if="job.status === 'completed'" class="w-8 h-8 bg-emerald-900/50 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <!-- Failed -->
                                    <div v-else-if="job.status === 'failed'" class="w-8 h-8 bg-red-900/50 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Job Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-white text-sm font-medium truncate">{{ job.topic }}</p>
                                    <p class="text-gray-500 text-xs mt-0.5">{{ job.website_name }}</p>
                                    
                                    <!-- Status Text -->
                                    <div class="mt-1 flex items-center gap-2">
                                        <span 
                                            :class="[
                                                'text-xs px-2 py-0.5 rounded',
                                                job.status === 'pending' ? 'bg-yellow-900/50 text-yellow-300' : '',
                                                job.status === 'processing' ? 'bg-blue-900/50 text-blue-300' : '',
                                                job.status === 'completed' ? 'bg-emerald-900/50 text-emerald-300' : '',
                                                job.status === 'failed' ? 'bg-red-900/50 text-red-300' : ''
                                            ]"
                                        >
                                            {{ job.status === 'pending' ? 'Queued' : job.status.charAt(0).toUpperCase() + job.status.slice(1) }}
                                        </span>
                                        <span class="text-gray-600 text-xs">{{ job.created_at }}</span>
                                    </div>

                                    <!-- Error Message -->
                                    <p v-if="job.status === 'failed' && job.error_message" class="text-red-400 text-xs mt-1 line-clamp-2">
                                        {{ job.error_message }}
                                    </p>

                                    <!-- View Article Link -->
                                    <a 
                                        v-if="job.status === 'completed' && job.article_id"
                                        :href="`/superadmin/${job.website_id}/articles/${job.article_id}/edit`"
                                        class="inline-flex items-center gap-1 text-emerald-400 text-xs mt-1 hover:underline"
                                    >
                                        View article
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>

                                <!-- Dismiss Button -->
                                <button 
                                    v-if="job.status === 'completed' || job.status === 'failed'"
                                    @click.stop="dismissJob(job.id)"
                                    class="p-1 text-gray-500 hover:text-white transition-colors"
                                    title="Dismiss"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="activeCount > 0" class="px-4 py-3 border-t border-[#2a2a2a] bg-[#151515]">
                    <p class="text-gray-400 text-xs flex items-center gap-2">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        {{ activeCount }} article{{ activeCount > 1 ? 's' : '' }} generating...
                    </p>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const jobs = ref([]);
const loading = ref(false);
let pollInterval = null;

const activeCount = computed(() => {
    return jobs.value.filter(j => j.status === 'pending' || j.status === 'processing').length;
});

const processingCount = computed(() => {
    return jobs.value.filter(j => j.status === 'processing').length;
});

const completedJobs = computed(() => {
    return jobs.value.filter(j => j.status === 'completed' || j.status === 'failed');
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        fetchJobs();
    }
};

const fetchJobs = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/superadmin/api/generation-jobs');
        jobs.value = response.data.jobs;
    } catch (error) {
        console.error('Failed to fetch generation jobs:', error);
    } finally {
        loading.value = false;
    }
};

const dismissJob = async (jobId) => {
    try {
        await axios.delete(`/superadmin/api/generation-jobs/${jobId}`);
        jobs.value = jobs.value.filter(j => j.id !== jobId);
    } catch (error) {
        console.error('Failed to dismiss job:', error);
    }
};

const clearCompleted = async () => {
    try {
        await axios.delete('/superadmin/api/generation-jobs');
        jobs.value = jobs.value.filter(j => j.status === 'pending' || j.status === 'processing');
    } catch (error) {
        console.error('Failed to clear completed jobs:', error);
    }
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    const target = event.target;
    if (!target.closest('.relative')) {
        isOpen.value = false;
    }
};

onMounted(() => {
    // Initial fetch
    fetchJobs();
    
    // Poll every 5 seconds when there are active jobs
    pollInterval = setInterval(() => {
        if (activeCount.value > 0 || isOpen.value) {
            fetchJobs();
        }
    }, 5000);
    
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
    document.removeEventListener('click', handleClickOutside);
});
</script>

