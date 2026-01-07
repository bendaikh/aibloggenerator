<script setup>
import { useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    currentWebsite: {
        type: Object,
        required: true
    }
});

// Available themes
const availableThemes = [
    {
        id: 'solushcooks',
        name: 'Solushcooks',
        description: 'Modern, clean theme perfect for food blogs and recipe websites',
        preview: '🍽️'
    }
];

// Initialize form with current theme settings
const currentThemeSettings = props.currentWebsite.theme_settings || {};
const form = useForm({
    theme: props.currentWebsite.theme || 'solushcooks',
    theme_settings: {
        show_newsletter_cta: currentThemeSettings.show_newsletter_cta !== false, // Default to true
        show_shop_cta: currentThemeSettings.show_shop_cta !== false, // Default to true
        ...currentThemeSettings
    }
});

const submit = () => {
    form.put(route('superadmin.appearance.update', props.currentWebsite.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Show success message
        }
    });
};
</script>

<template>
    <Head title="Appearance" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Appearance</h1>
                <p class="text-gray-400 mt-1">Customize your website's look and feel</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Available Themes -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Available Themes</h3>
                    <p class="text-gray-400 text-sm mb-4">Choose a theme for your website</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            v-for="theme in availableThemes"
                            :key="theme.id"
                            @click="form.theme = theme.id"
                            :class="[
                                'relative p-4 rounded-xl border-2 cursor-pointer transition-all',
                                form.theme === theme.id
                                    ? 'border-emerald-500 bg-emerald-500/10'
                                    : 'border-[#3a3a3a] bg-[#252525] hover:border-[#4a4a4a]'
                            ]"
                        >
                            <div class="flex items-center gap-3 mb-2">
                                <div class="text-3xl">{{ theme.preview }}</div>
                                <div>
                                    <h4 class="text-white font-semibold">{{ theme.name }}</h4>
                                </div>
                            </div>
                            <p class="text-gray-400 text-xs">{{ theme.description }}</p>
                            <div v-if="form.theme === theme.id" class="absolute top-2 right-2">
                                <div class="w-5 h-5 bg-emerald-500 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Theme Customization -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Theme Customization</h3>
                    <p class="text-gray-400 text-sm mb-6">Enable or disable sections on your website</p>
                    
                    <div class="space-y-6">
                        <!-- Newsletter CTA Section -->
                        <div class="flex items-start justify-between p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                        <span class="text-white text-xl">💌</span>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-semibold">Newsletter Subscription CTA</h4>
                                        <p class="text-gray-400 text-sm">"GET NEW RECIPES IN YOUR INBOX" section</p>
                                    </div>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.theme_settings.show_newsletter_cta"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-[#3a3a3a] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <!-- Shop CTA Section -->
                        <div class="flex items-start justify-between p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                                        <span class="text-white text-xl">🛍️</span>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-semibold">Shop CTA</h4>
                                        <p class="text-gray-400 text-sm">"VISIT OUR SHOP" section</p>
                                    </div>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.theme_settings.show_shop_cta"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-[#3a3a3a] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </SuperAdminLayout>
</template>
