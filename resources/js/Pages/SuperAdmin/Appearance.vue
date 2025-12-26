<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Appearance" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Appearance</h1>
                <p class="text-gray-400 mt-1">Customize your website's look and feel</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Theme Settings -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Theme Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Primary Color</label>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-emerald-500 cursor-pointer ring-2 ring-white ring-offset-2 ring-offset-[#1a1a1a]"></div>
                                <div class="w-10 h-10 rounded-lg bg-blue-500 cursor-pointer"></div>
                                <div class="w-10 h-10 rounded-lg bg-purple-500 cursor-pointer"></div>
                                <div class="w-10 h-10 rounded-lg bg-rose-500 cursor-pointer"></div>
                                <div class="w-10 h-10 rounded-lg bg-amber-500 cursor-pointer"></div>
                            </div>
                        </div>
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Font Family</label>
                            <select class="w-full bg-[#252525] border border-[#3a3a3a] text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option>Inter</option>
                                <option>Roboto</option>
                                <option>Open Sans</option>
                                <option>Lato</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Layout Settings -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Layout Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Blog Layout</label>
                            <div class="grid grid-cols-3 gap-3">
                                <button class="p-4 bg-[#252525] border-2 border-emerald-500 rounded-lg text-center">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                    <span class="text-white text-xs">List</span>
                                </button>
                                <button class="p-4 bg-[#252525] border border-[#3a3a3a] rounded-lg text-center hover:border-gray-500 transition-colors">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <span class="text-gray-400 text-xs">Grid</span>
                                </button>
                                <button class="p-4 bg-[#252525] border border-[#3a3a3a] rounded-lg text-center hover:border-gray-500 transition-colors">
                                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span class="text-gray-400 text-xs">Masonry</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

