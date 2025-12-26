<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Ads Management" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Ads Management</h1>
                <p class="text-gray-400 mt-1">Configure and manage your advertising placements</p>
            </div>

            <!-- Ad Networks -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Google AdSense -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-white font-semibold">Google AdSense</h3>
                            <p class="text-gray-500 text-sm">Not configured</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Publisher ID</label>
                            <input 
                                type="text" 
                                placeholder="ca-pub-xxxxxxxxxx"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                        <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg font-medium transition-colors">
                            Save Configuration
                        </button>
                    </div>
                </div>

                <!-- Media.net -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-white font-semibold">Media.net</h3>
                            <p class="text-gray-500 text-sm">Not configured</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Site ID</label>
                            <input 
                                type="text" 
                                placeholder="Enter your site ID"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                        <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg font-medium transition-colors">
                            Save Configuration
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ad Placements -->
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Ad Placements</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-[#252525] rounded-lg">
                        <div>
                            <p class="text-white font-medium">Header Banner</p>
                            <p class="text-gray-500 text-sm">728x90 - Top of page</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-[#3a3a3a] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-[#252525] rounded-lg">
                        <div>
                            <p class="text-white font-medium">In-Article</p>
                            <p class="text-gray-500 text-sm">Responsive - Between paragraphs</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-[#3a3a3a] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-[#252525] rounded-lg">
                        <div>
                            <p class="text-white font-medium">Sidebar</p>
                            <p class="text-gray-500 text-sm">300x600 - Right sidebar</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-[#3a3a3a] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

