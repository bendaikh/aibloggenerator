<script setup>
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    themes: Array,
});
</script>

<template>
    <Head title="Themes" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Available Themes</h1>
                <p class="text-gray-400 mt-1">View available theme options for your websites</p>
            </div>

            <!-- Info Box -->
            <div class="mb-8 bg-blue-500/10 border border-blue-500/50 rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-2">How to Select a Theme</h4>
                        <ul class="text-gray-300 text-sm space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 flex-shrink-0">•</span>
                                <span>Each website can have its <strong>own theme</strong> - themes are selected per website, not organization-wide</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 flex-shrink-0">•</span>
                                <span>To select a theme: Go to <strong>Websites → Create New Website</strong> or edit an existing website</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 flex-shrink-0">•</span>
                                <span>You'll see a theme selector during website creation/editing</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Themes Grid (Read-Only Display) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div 
                    v-for="theme in themes" 
                    :key="theme.id"
                    class="relative rounded-2xl border-2 border-[#2a2a2a] bg-[#1a1a1a] overflow-hidden"
                >
                    <div class="p-6">
                        <!-- Theme Icon -->
                        <div class="mb-4">
                            <div :class="[
                                'w-16 h-16 rounded-xl flex items-center justify-center',
                                theme.slug === 'recipe' ? 'bg-gradient-to-br from-emerald-400 to-teal-500' : 'bg-gradient-to-br from-rose-400 to-pink-500'
                            ]">
                                <svg v-if="theme.slug === 'recipe'" class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <svg v-else class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                        </div>

                        <!-- Theme Name -->
                        <h3 class="text-xl font-bold text-white mb-2">{{ theme.name }}</h3>
                        
                        <!-- Theme Description -->
                        <p class="text-gray-400 text-sm mb-4">{{ theme.description }}</p>

                        <!-- Theme Features -->
                        <div class="space-y-2">
                            <div class="flex items-start gap-2 text-sm">
                                <svg 
                                    :class="[
                                        'w-5 h-5 flex-shrink-0 mt-0.5',
                                        theme.show_recipe_sections ? 'text-emerald-400' : 'text-gray-500'
                                    ]" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path 
                                        stroke-linecap="round" 
                                        stroke-linejoin="round" 
                                        stroke-width="2" 
                                        :d="theme.show_recipe_sections ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'" 
                                    />
                                </svg>
                                <span :class="theme.show_recipe_sections ? 'text-gray-300' : 'text-gray-500'">
                                    {{ theme.show_recipe_sections ? 'Shows' : 'Hides' }} ingredient cards
                                </span>
                            </div>
                            <div class="flex items-start gap-2 text-sm">
                                <svg 
                                    :class="[
                                        'w-5 h-5 flex-shrink-0 mt-0.5',
                                        theme.show_recipe_sections ? 'text-emerald-400' : 'text-gray-500'
                                    ]" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path 
                                        stroke-linecap="round" 
                                        stroke-linejoin="round" 
                                        stroke-width="2" 
                                        :d="theme.show_recipe_sections ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'" 
                                    />
                                </svg>
                                <span :class="theme.show_recipe_sections ? 'text-gray-300' : 'text-gray-500'">
                                    {{ theme.show_recipe_sections ? 'Shows' : 'Hides' }} instruction steps
                                </span>
                            </div>
                            <div class="flex items-start gap-2 text-sm">
                                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-300">Full article content</span>
                            </div>
                        </div>

                        <!-- Best For -->
                        <div class="mt-4 pt-4 border-t border-[#2a2a2a]">
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Best For:</p>
                            <p class="text-sm text-gray-300">
                                {{ theme.slug === 'recipe' ? 'Food blogs, cooking websites, recipe collections' : 'Home decor, lifestyle blogs, general content' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom CTA -->
            <div class="mt-8 bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-500/50 rounded-xl p-6 text-center">
                <h4 class="text-white font-semibold text-lg mb-2">Ready to create a website with a theme?</h4>
                <p class="text-gray-400 mb-4">You can select a theme when creating or editing your websites</p>
                <a 
                    :href="route('organization.websites.create')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-purple-500 text-white font-semibold rounded-lg hover:bg-purple-600 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Website
                </a>
            </div>
        </div>
    </OrganizationLayout>
</template>
