<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Deployment" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Deployment</h1>
                <p class="text-gray-400 mt-1">Deploy and manage your website</p>
            </div>

            <!-- Deployment Status -->
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Production</h3>
                            <p class="text-gray-500 text-sm">Last deployed: Never</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-emerald-400/20 text-emerald-400 text-sm rounded-full">Ready</span>
                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Deploy Now
                        </button>
                    </div>
                </div>
            </div>

            <!-- Domain Settings -->
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 mb-6">
                <h3 class="text-lg font-semibold text-white mb-4">Domain Settings</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-gray-400 text-sm block mb-2">Custom Domain</label>
                        <div class="flex gap-3">
                            <input 
                                type="text" 
                                placeholder="yourdomain.com"
                                class="flex-1 bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                            <button class="bg-[#252525] hover:bg-[#303030] text-white px-4 py-2 rounded-lg font-medium transition-colors border border-[#3a3a3a]">
                                Add Domain
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deployment History -->
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Deployment History</h3>
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-400">No deployments yet</p>
                    <p class="text-gray-500 text-sm mt-1">Your deployment history will appear here</p>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

