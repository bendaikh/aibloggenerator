<script setup>
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    usage: {
        type: Object,
        required: true
    },
    logs: {
        type: Object,
        required: true
    }
});

const formatCost = (cost) => {
    return '$' + parseFloat(cost).toFixed(4);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US').format(num);
};

const getModeColor = (mode) => {
    return mode === 'hybrid_rewrite' ? 'blue' : 'emerald';
};

const getModeLabel = (mode) => {
    return mode === 'hybrid_rewrite' ? 'Hybrid Mode' : 'Full AI Mode';
};

const savingsPercentage = computed(() => {
    if (!props.usage.total_cost || props.usage.total_cost === 0) return 0;
    
    const fullAICost = props.usage.full_ai_cost || 0;
    const hybridCost = props.usage.hybrid_cost || 0;
    
    if (fullAICost === 0) return 0;
    
    return ((fullAICost - hybridCost) / fullAICost * 100).toFixed(1);
});
</script>

<template>
    <Head title="API Usage & Costs" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">API Usage & Costs</h1>
                <p class="text-gray-400 mt-1">Track your OpenAI API usage and monitor cost savings with Hybrid Mode</p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Cost -->
                <div class="bg-gradient-to-br from-emerald-900/50 to-emerald-800/30 rounded-2xl border border-emerald-500/30 p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div class="text-gray-400 text-sm">Total Cost</div>
                    </div>
                    <div class="text-3xl font-bold text-white">{{ formatCost(usage.total_cost) }}</div>
                    <p class="text-emerald-400 text-xs mt-1">All time</p>
                </div>

                <!-- Total Tokens -->
                <div class="bg-gradient-to-br from-blue-900/50 to-blue-800/30 rounded-2xl border border-blue-500/30 p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="text-gray-400 text-sm">Total Tokens</div>
                    </div>
                    <div class="text-3xl font-bold text-white">{{ formatNumber(usage.total_tokens) }}</div>
                    <p class="text-blue-400 text-xs mt-1">{{ formatNumber(usage.total_requests) }} requests</p>
                </div>

                <!-- Hybrid Savings -->
                <div class="bg-gradient-to-br from-purple-900/50 to-purple-800/30 rounded-2xl border border-purple-500/30 p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="text-gray-400 text-sm">Hybrid Savings</div>
                    </div>
                    <div class="text-3xl font-bold text-white">{{ formatCost(usage.estimated_savings) }}</div>
                    <p class="text-purple-400 text-xs mt-1">{{ savingsPercentage }}% saved</p>
                </div>

                <!-- Articles Generated -->
                <div class="bg-gradient-to-br from-orange-900/50 to-orange-800/30 rounded-2xl border border-orange-500/30 p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 bg-orange-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="text-gray-400 text-sm">Articles Generated</div>
                    </div>
                    <div class="text-3xl font-bold text-white">{{ formatNumber(usage.total_articles) }}</div>
                    <p class="text-orange-400 text-xs mt-1">Via AI generation</p>
                </div>
            </div>

            <!-- Mode Comparison -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Full AI Mode Stats -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Full AI Mode</h3>
                            <p class="text-gray-400 text-sm">Unique AI article for each website</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-[#2a2a2a]">
                            <span class="text-gray-400">Total Cost</span>
                            <span class="text-white font-semibold">{{ formatCost(usage.full_ai_cost) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-[#2a2a2a]">
                            <span class="text-gray-400">API Calls</span>
                            <span class="text-white font-semibold">{{ formatNumber(usage.full_ai_requests) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="text-gray-400">Avg Cost/Article</span>
                            <span class="text-white font-semibold">
                                {{ usage.full_ai_requests > 0 ? formatCost(usage.full_ai_cost / usage.full_ai_requests) : '$0.0000' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Hybrid Mode Stats -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Hybrid Rewrite Mode</h3>
                            <p class="text-gray-400 text-sm">1 master + local variations</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-[#2a2a2a]">
                            <span class="text-gray-400">Total Cost</span>
                            <span class="text-white font-semibold">{{ formatCost(usage.hybrid_cost) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-[#2a2a2a]">
                            <span class="text-gray-400">API Calls</span>
                            <span class="text-white font-semibold">{{ formatNumber(usage.hybrid_requests) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="text-gray-400">Avg Cost/Article</span>
                            <span class="text-white font-semibold">
                                {{ usage.hybrid_articles > 0 ? formatCost(usage.hybrid_cost / usage.hybrid_articles) : '$0.0000' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-blue-900/20 border border-blue-500/30 rounded-lg">
                        <p class="text-blue-300 text-sm">
                            <strong>💰 Savings:</strong> {{ formatCost(usage.estimated_savings) }} saved compared to Full AI mode
                        </p>
                    </div>
                </div>
            </div>

            <!-- Recent API Usage Logs -->
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Recent API Usage</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-[#2a2a2a]">
                                <th class="text-left text-gray-400 text-sm font-medium pb-3">Date</th>
                                <th class="text-left text-gray-400 text-sm font-medium pb-3">Mode</th>
                                <th class="text-left text-gray-400 text-sm font-medium pb-3">Model</th>
                                <th class="text-right text-gray-400 text-sm font-medium pb-3">Tokens</th>
                                <th class="text-right text-gray-400 text-sm font-medium pb-3">Cost</th>
                                <th class="text-left text-gray-400 text-sm font-medium pb-3">Topic</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs.data" :key="log.id" class="border-b border-[#2a2a2a] hover:bg-[#252525] transition-colors">
                                <td class="py-4 text-gray-300 text-sm">{{ formatDate(log.created_at) }}</td>
                                <td class="py-4">
                                    <span :class="[
                                        'px-2 py-1 rounded text-xs font-medium',
                                        getModeColor(log.generation_mode) === 'blue' ? 'bg-blue-900/50 text-blue-300' : 'bg-emerald-900/50 text-emerald-300'
                                    ]">
                                        {{ getModeLabel(log.generation_mode) }}
                                    </span>
                                </td>
                                <td class="py-4 text-gray-300 text-sm">{{ log.model }}</td>
                                <td class="py-4 text-gray-300 text-sm text-right">{{ formatNumber(log.total_tokens) }}</td>
                                <td class="py-4 text-white font-semibold text-sm text-right">{{ formatCost(log.estimated_cost) }}</td>
                                <td class="py-4 text-gray-400 text-sm truncate max-w-xs">
                                    {{ log.metadata?.topic || 'N/A' }}
                                    <span v-if="log.metadata?.is_master_article" class="ml-2 text-blue-400 text-xs">(Master)</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.links && logs.links.length > 3" class="mt-6 flex justify-center gap-2">
                    <a
                        v-for="(link, index) in logs.links"
                        :key="index"
                        :href="link.url"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm transition-colors',
                            link.active 
                                ? 'bg-emerald-500 text-white' 
                                : link.url 
                                    ? 'bg-[#252525] text-gray-300 hover:bg-[#2a2a2a]' 
                                    : 'bg-[#1a1a1a] text-gray-600 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </OrganizationLayout>
</template>
