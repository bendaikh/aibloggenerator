<template>
    <Head title="Select Theme - Global Articles" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Select Theme for Article Generation</h1>
                <p class="text-gray-400">Choose which theme you want to work with. Only websites from the selected theme will be available.</p>
            </div>

            <!-- Theme Selection Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl">
                <!-- Recipe Theme -->
                <Link 
                    :href="route('organization.global-articles.index', { theme: 'recipe' })"
                    class="group relative bg-[#1a1a1a] rounded-2xl border-2 border-[#2a2a2a] hover:border-emerald-500 transition-all overflow-hidden cursor-pointer"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative p-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Recipe Theme</h3>
                        <p class="text-gray-400 text-sm mb-4">Generate recipe articles with ingredients, instructions, and cooking tips</p>
                        <div class="flex items-center text-emerald-400 text-sm font-medium">
                            <span>Select Theme</span>
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </Link>

                <!-- Home Decor Theme -->
                <Link 
                    :href="route('organization.global-articles.index', { theme: 'home-decor' })"
                    class="group relative bg-[#1a1a1a] rounded-2xl border-2 border-[#2a2a2a] hover:border-amber-500 transition-all overflow-hidden cursor-pointer"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative p-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Home Decor Theme</h3>
                        <p class="text-gray-400 text-sm mb-4">Create home decor articles with multiple images and detailed descriptions</p>
                        <div class="flex items-center text-amber-400 text-sm font-medium">
                            <span>Select Theme</span>
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </Link>

                <!-- Crochet Theme -->
                <Link 
                    :href="route('organization.global-articles.index', { theme: 'crochet' })"
                    class="group relative bg-[#1a1a1a] rounded-2xl border-2 border-[#2a2a2a] hover:border-green-500 transition-all overflow-hidden cursor-pointer"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative p-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Crochet Theme</h3>
                        <p class="text-gray-400 text-sm mb-4">Generate crochet pattern articles with instructions and stitch guides</p>
                        <div class="flex items-center text-green-400 text-sm font-medium">
                            <span>Select Theme</span>
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Info Section -->
            <div class="mt-8 max-w-5xl">
                <div class="bg-blue-900/20 border border-blue-500/50 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-white font-semibold mb-2">How Theme Selection Works</h4>
                            <ul class="text-blue-200 text-sm space-y-2">
                                <li>• When you select a theme, only websites using that theme will be shown in the website selector</li>
                                <li>• Each theme has a customized article generation experience tailored to its content type</li>
                                <li>• Home Decor theme includes special multi-image support with individual titles for each image</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OrganizationLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
</script>
