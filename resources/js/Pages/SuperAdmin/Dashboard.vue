<script setup>
import { ref, onMounted, computed } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: null
    },
    stats: {
        type: Object,
        default: () => ({
            totalArticles: 0,
            articlesGrowth: 0,
            publishedArticles: 0,
            draftArticles: 0,
            aiUsage: '0',
            aiUsageGrowth: 0,
            aiCost: 0,
            aiEvents: 0,
            adRevenue: 0,
            revenueGrowth: 0,
            adImpressions: 0,
            impressionsGrowth: 0
        })
    },
    chartData: {
        type: Array,
        default: () => []
    }
});

// Chart data for visualization
const performanceData = ref([
    { date: 'Nov 24', revenue: 0, impressions: 0 },
    { date: 'Nov 27', revenue: 0, impressions: 0 },
    { date: 'Nov 30', revenue: 0, impressions: 0 },
    { date: 'Dec 3', revenue: 0, impressions: 0 },
    { date: 'Dec 6', revenue: 0, impressions: 0 },
    { date: 'Dec 9', revenue: 0, impressions: 0 },
    { date: 'Dec 12', revenue: 0, impressions: 0 },
    { date: 'Dec 15', revenue: 0.02, impressions: 15 },
    { date: 'Dec 18', revenue: 0.08, impressions: 80 },
    { date: 'Dec 21', revenue: 0.28, impressions: 420 },
    { date: 'Dec 24', revenue: 0.34, impressions: 545 },
]);

const selectedPeriod = ref('Last 30 days');

// Calculate chart path for SVG
const chartWidth = 900;
const chartHeight = 200;
const padding = 40;

const maxRevenue = computed(() => Math.max(...performanceData.value.map(d => d.revenue), 0.4));
const maxImpressions = computed(() => Math.max(...performanceData.value.map(d => d.impressions), 600));

const revenuePath = computed(() => {
    const points = performanceData.value.map((d, i) => {
        const x = padding + (i / (performanceData.value.length - 1)) * (chartWidth - padding * 2);
        const y = chartHeight - padding - (d.revenue / maxRevenue.value) * (chartHeight - padding * 2);
        return `${x},${y}`;
    });
    return `M ${points.join(' L ')}`;
});

const impressionsPath = computed(() => {
    const points = performanceData.value.map((d, i) => {
        const x = padding + (i / (performanceData.value.length - 1)) * (chartWidth - padding * 2);
        const y = chartHeight - padding - (d.impressions / maxImpressions.value) * (chartHeight - padding * 2);
        return `${x},${y}`;
    });
    return `M ${points.join(' L ')}`;
});

const revenueAreaPath = computed(() => {
    const points = performanceData.value.map((d, i) => {
        const x = padding + (i / (performanceData.value.length - 1)) * (chartWidth - padding * 2);
        const y = chartHeight - padding - (d.revenue / maxRevenue.value) * (chartHeight - padding * 2);
        return `${x},${y}`;
    });
    const startX = padding;
    const endX = padding + ((performanceData.value.length - 1) / (performanceData.value.length - 1)) * (chartWidth - padding * 2);
    const bottomY = chartHeight - padding;
    return `M ${startX},${bottomY} L ${points.join(' L ')} L ${endX},${bottomY} Z`;
});

const impressionsAreaPath = computed(() => {
    const points = performanceData.value.map((d, i) => {
        const x = padding + (i / (performanceData.value.length - 1)) * (chartWidth - padding * 2);
        const y = chartHeight - padding - (d.impressions / maxImpressions.value) * (chartHeight - padding * 2);
        return `${x},${y}`;
    });
    const startX = padding;
    const endX = padding + ((performanceData.value.length - 1) / (performanceData.value.length - 1)) * (chartWidth - padding * 2);
    const bottomY = chartHeight - padding;
    return `M ${startX},${bottomY} L ${points.join(' L ')} L ${endX},${bottomY} Z`;
});
</script>

<template>
    <Head title="Dashboard" />

    <SuperAdminLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Dashboard</h1>
                <p class="text-gray-400 mt-1">Welcome back!</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Articles -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Total Articles</span>
                        <span class="flex items-center gap-1 text-emerald-400 text-sm font-medium bg-emerald-400/10 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            +{{ stats.articlesGrowth }}%
                        </span>
                    </div>
                    <div class="text-4xl font-bold text-white mb-4">{{ stats.totalArticles }}</div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-emerald-400/20 text-emerald-400 text-xs rounded-full">Active</span>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-gray-400 text-sm">
                        <span class="font-medium text-white">{{ stats.publishedArticles }} published</span>
                        <span>•</span>
                        <span>{{ stats.draftArticles }} drafts</span>
                        <button class="ml-auto p-1 hover:bg-[#252525] rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">Articles for this website</p>
                </div>

                <!-- AI Usage -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">AI Usage</span>
                        <span class="flex items-center gap-1 text-emerald-400 text-sm font-medium bg-emerald-400/10 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            +{{ stats.aiUsageGrowth }}%
                        </span>
                    </div>
                    <div class="text-4xl font-bold text-white mb-4">{{ stats.aiUsage }}</div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-emerald-400/20 text-emerald-400 text-xs rounded-full">Active</span>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-gray-400 text-sm">
                        <span class="font-medium text-white">${{ stats.aiCost }} total cost</span>
                        <span>•</span>
                        <span>{{ stats.aiEvents }} events</span>
                        <button class="ml-auto p-1 hover:bg-[#252525] rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">AI tokens used this month</p>
                </div>

                <!-- Ad Revenue -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Ad Revenue</span>
                        <span class="flex items-center gap-1 text-emerald-400 text-sm font-medium bg-emerald-400/10 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            +{{ stats.revenueGrowth }}%
                        </span>
                    </div>
                    <div class="text-4xl font-bold text-white mb-4">€{{ stats.adRevenue }}</div>
                    <div class="mt-3 flex items-center gap-2 text-gray-400 text-sm">
                        <span class="font-medium text-white">Current month revenue</span>
                        <button class="ml-auto p-1 hover:bg-[#252525] rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 15.536c-1.171 1.952-3.07 1.952-4.242 0-1.172-1.953-1.172-5.119 0-7.072 1.171-1.952 3.07-1.952 4.242 0M8 10.5h4m-4 3h4m9-1.5a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">Updated 1 day ago</p>
                </div>

                <!-- Ad Impressions -->
                <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-400 text-sm">Ad Impressions</span>
                        <span class="flex items-center gap-1 text-emerald-400 text-sm font-medium bg-emerald-400/10 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            +{{ stats.impressionsGrowth }}%
                        </span>
                    </div>
                    <div class="text-4xl font-bold text-white mb-4">{{ stats.adImpressions }}</div>
                    <div class="mt-3 flex items-center gap-2 text-gray-400 text-sm">
                        <span class="font-medium text-white">Current month impressions</span>
                        <button class="ml-auto p-1 hover:bg-[#252525] rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">Updated 1 day ago</p>
                </div>
            </div>

            <!-- Advertising Performance Chart -->
            <div class="bg-[#1a1a1a] rounded-2xl p-6 border border-[#2a2a2a]">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Advertising Performance</h2>
                        <p class="text-gray-400 text-sm mt-1">Showing revenue (€) and impressions</p>
                    </div>
                    <div class="relative">
                        <select 
                            v-model="selectedPeriod"
                            class="bg-[#252525] border border-[#3a3a3a] text-white text-sm rounded-lg px-4 py-2 pr-10 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                            <option>This year</option>
                        </select>
                        <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Chart -->
                <div class="relative h-64">
                    <svg viewBox="0 0 900 200" class="w-full h-full" preserveAspectRatio="xMidYMid meet">
                        <!-- Grid lines -->
                        <g class="text-[#2a2a2a]">
                            <line x1="40" y1="40" x2="860" y2="40" stroke="currentColor" stroke-dasharray="4" />
                            <line x1="40" y1="80" x2="860" y2="80" stroke="currentColor" stroke-dasharray="4" />
                            <line x1="40" y1="120" x2="860" y2="120" stroke="currentColor" stroke-dasharray="4" />
                            <line x1="40" y1="160" x2="860" y2="160" stroke="currentColor" stroke-dasharray="4" />
                        </g>

                        <!-- Y-axis labels (Revenue) -->
                        <g class="text-gray-400 text-xs">
                            <text x="35" y="44" text-anchor="end" fill="currentColor">€0.32</text>
                            <text x="35" y="84" text-anchor="end" fill="currentColor">€0.24</text>
                            <text x="35" y="124" text-anchor="end" fill="currentColor">€0.16</text>
                            <text x="35" y="164" text-anchor="end" fill="currentColor">€0.08</text>
                        </g>

                        <!-- Y-axis labels (Impressions) -->
                        <g class="text-gray-400 text-xs">
                            <text x="865" y="44" text-anchor="start" fill="currentColor">600</text>
                            <text x="865" y="84" text-anchor="start" fill="currentColor">450</text>
                            <text x="865" y="124" text-anchor="start" fill="currentColor">300</text>
                            <text x="865" y="164" text-anchor="start" fill="currentColor">150</text>
                        </g>

                        <!-- X-axis labels -->
                        <g class="text-gray-400 text-xs">
                            <text v-for="(d, i) in performanceData" :key="i" 
                                :x="40 + (i / (performanceData.length - 1)) * 820" 
                                y="195" 
                                text-anchor="middle" 
                                fill="currentColor"
                            >{{ d.date }}</text>
                        </g>

                        <!-- Area fills -->
                        <defs>
                            <linearGradient id="revenueGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.3" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                            </linearGradient>
                            <linearGradient id="impressionsGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#06b6d4" stop-opacity="0.3" />
                                <stop offset="100%" stop-color="#06b6d4" stop-opacity="0" />
                            </linearGradient>
                        </defs>

                        <path :d="revenueAreaPath" fill="url(#revenueGradient)" />
                        <path :d="impressionsAreaPath" fill="url(#impressionsGradient)" />

                        <!-- Lines -->
                        <path :d="revenuePath" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path :d="impressionsPath" fill="none" stroke="#06b6d4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                        <!-- Data points -->
                        <g v-for="(d, i) in performanceData" :key="'point-' + i">
                            <circle 
                                :cx="40 + (i / (performanceData.length - 1)) * 820"
                                :cy="160 - (d.revenue / 0.4) * 120"
                                r="4"
                                fill="#10b981"
                                class="opacity-0 hover:opacity-100 transition-opacity cursor-pointer"
                            />
                            <circle 
                                :cx="40 + (i / (performanceData.length - 1)) * 820"
                                :cy="160 - (d.impressions / 600) * 120"
                                r="4"
                                fill="#06b6d4"
                                class="opacity-0 hover:opacity-100 transition-opacity cursor-pointer"
                            />
                        </g>
                    </svg>
                </div>

                <!-- Legend -->
                <div class="flex items-center justify-center gap-6 mt-4">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span class="text-gray-400 text-sm">Revenue (€)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-cyan-500"></div>
                        <span class="text-gray-400 text-sm">Impressions</span>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

