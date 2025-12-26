<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Website Settings" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Website Settings</h1>
                <p class="text-gray-400 mt-1">Configure your website settings</p>
            </div>

            <div class="space-y-6">
                <!-- General Settings -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">General Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Website Name</label>
                            <input 
                                type="text" 
                                placeholder="My Awesome Blog"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Website Description</label>
                            <textarea 
                                rows="3"
                                placeholder="A brief description of your website"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                            ></textarea>
                        </div>
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Website URL</label>
                            <input 
                                type="url" 
                                placeholder="https://mywebsite.com"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">SEO Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Meta Title</label>
                            <input 
                                type="text" 
                                placeholder="Default meta title for pages"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Meta Description</label>
                            <textarea 
                                rows="2"
                                placeholder="Default meta description"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                            ></textarea>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="indexing" class="w-4 h-4 rounded bg-[#252525] border-[#3a3a3a] text-emerald-500 focus:ring-emerald-500">
                            <label for="indexing" class="text-gray-400 text-sm">Allow search engines to index this site</label>
                        </div>
                    </div>
                </div>

                <!-- AI Settings -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">AI Content Generation</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">OpenAI API Key</label>
                            <input 
                                type="password" 
                                placeholder="sk-..."
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Default AI Model</label>
                            <select class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option>GPT-4</option>
                                <option>GPT-4 Turbo</option>
                                <option>GPT-3.5 Turbo</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Content Tone</label>
                            <select class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option>Professional</option>
                                <option>Casual</option>
                                <option>Friendly</option>
                                <option>Authoritative</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

