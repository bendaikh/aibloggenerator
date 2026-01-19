<script setup>
import { useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

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
        article_font_family: currentThemeSettings.article_font_family || 'default',
        // Newsletter CTA customization
        newsletter_cta_title: currentThemeSettings.newsletter_cta_title || 'GET NEW RECIPES',
        newsletter_cta_subtitle: currentThemeSettings.newsletter_cta_subtitle || 'IN YOUR INBOX',
        newsletter_cta_button: currentThemeSettings.newsletter_cta_button || 'SUBSCRIBE NOW',
        // Shop CTA customization
        shop_cta_title: currentThemeSettings.shop_cta_title || 'VISIT OUR SHOP',
        shop_cta_subtitle: currentThemeSettings.shop_cta_subtitle || 'FIND GREAT GIFT IDEAS!',
        shop_cta_button: currentThemeSettings.shop_cta_button || 'SHOP NOW',
        shop_cta_link: currentThemeSettings.shop_cta_link || '#shop',
        // Subscription popup customization
        show_subscription_popup: currentThemeSettings.show_subscription_popup !== false, // Default to true
        subscription_popup_desktop: currentThemeSettings.subscription_popup_desktop !== false, // Default to true
        subscription_popup_mobile: currentThemeSettings.subscription_popup_mobile !== false, // Default to true
        subscription_popup_delay: currentThemeSettings.subscription_popup_delay || 5, // Default 5 seconds
        subscription_popup_title: currentThemeSettings.subscription_popup_title || 'GET NEW RECIPES',
        subscription_popup_subtitle: currentThemeSettings.subscription_popup_subtitle || 'IN YOUR INBOX',
        subscription_popup_description: currentThemeSettings.subscription_popup_description || 'Join to receive our email series which contains a round-up of some of our quick and easy family favorite recipes.',
        subscription_popup_image: currentThemeSettings.subscription_popup_image || '',
        ...currentThemeSettings
    }
});

// Image upload handling
const isUploadingImage = ref(false);
const uploadImage = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    isUploadingImage.value = true;
    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', 'website');
    
    try {
        const response = await axios.post('/upload/image', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        
        if (response.data.success) {
            form.theme_settings.subscription_popup_image = response.data.url;
        }
    } catch (error) {
        console.error('Image upload failed:', error);
        alert('Failed to upload image. Please try again.');
    } finally {
        isUploadingImage.value = false;
        // Reset the input so the same file can be selected again
        event.target.value = '';
    }
};

const removeImage = () => {
    form.theme_settings.subscription_popup_image = '';
};

// Available fonts
const availableFonts = [
    { id: 'default', name: 'Default (Plus Jakarta Sans)', preview: 'Aa' },
    { id: 'inter', name: 'Inter', preview: 'Aa' },
    { id: 'roboto', name: 'Roboto', preview: 'Aa' },
    { id: 'open-sans', name: 'Open Sans', preview: 'Aa' },
    { id: 'lato', name: 'Lato', preview: 'Aa' },
    { id: 'montserrat', name: 'Montserrat', preview: 'Aa' },
    { id: 'poppins', name: 'Poppins', preview: 'Aa' },
    { id: 'raleway', name: 'Raleway', preview: 'Aa' },
    { id: 'merriweather', name: 'Merriweather (Serif)', preview: 'Aa' },
    { id: 'lora', name: 'Lora (Serif)', preview: 'Aa' },
];

const submit = () => {
    form.put(route('superadmin.appearance.update', props.currentWebsite.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Show success message
        }
    });
};

// Get font family CSS value
const getFontFamily = (fontId) => {
    const fontMap = {
        'default': "'Plus Jakarta Sans', sans-serif",
        'inter': "'Inter', sans-serif",
        'roboto': "'Roboto', sans-serif",
        'open-sans': "'Open Sans', sans-serif",
        'lato': "'Lato', sans-serif",
        'montserrat': "'Montserrat', sans-serif",
        'poppins': "'Poppins', sans-serif",
        'raleway': "'Raleway', sans-serif",
        'merriweather': "'Merriweather', serif",
        'lora': "'Lora', serif",
    };
    return fontMap[fontId] || fontMap['default'];
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
                    <h3 class="text-lg font-semibold text-white mb-4">CTA Blocks Customization</h3>
                    <p class="text-gray-400 text-sm mb-6">Customize the Call-to-Action blocks on your homepage</p>
                    
                    <div class="space-y-6">
                        <!-- Newsletter CTA Section -->
                        <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                        <span class="text-white text-xl">💌</span>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-semibold">Newsletter Subscription CTA</h4>
                                        <p class="text-gray-400 text-sm">Collect email subscribers</p>
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
                            
                            <!-- Newsletter CTA Fields -->
                            <div v-if="form.theme_settings.show_newsletter_cta" class="space-y-3 mt-4 pt-4 border-t border-[#3a3a3a]">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-gray-400 text-xs mb-1">Title</label>
                                        <input
                                            v-model="form.theme_settings.newsletter_cta_title"
                                            type="text"
                                            placeholder="GET NEW RECIPES"
                                            class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-gray-400 text-xs mb-1">Subtitle</label>
                                        <input
                                            v-model="form.theme_settings.newsletter_cta_subtitle"
                                            type="text"
                                            placeholder="IN YOUR INBOX"
                                            class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-400 text-xs mb-1">Button Text</label>
                                    <input
                                        v-model="form.theme_settings.newsletter_cta_button"
                                        type="text"
                                        placeholder="SUBSCRIBE NOW"
                                        class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    />
                                </div>
                                <!-- Preview -->
                                <div class="mt-4 p-4 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl text-white text-center">
                                    <h3 class="text-lg font-bold mb-0.5">{{ form.theme_settings.newsletter_cta_title || 'GET NEW RECIPES' }}</h3>
                                    <h4 class="text-sm mb-3 text-emerald-100">{{ form.theme_settings.newsletter_cta_subtitle || 'IN YOUR INBOX' }}</h4>
                                    <span class="inline-block bg-white text-emerald-600 px-6 py-2 rounded-full font-bold text-sm shadow-md">
                                        {{ form.theme_settings.newsletter_cta_button || 'SUBSCRIBE NOW' }} 💌
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Shop CTA Section -->
                        <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                                        <span class="text-white text-xl">🛍️</span>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-semibold">Shop CTA</h4>
                                        <p class="text-gray-400 text-sm">Link to your shop or store</p>
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
                            
                            <!-- Shop CTA Fields -->
                            <div v-if="form.theme_settings.show_shop_cta" class="space-y-3 mt-4 pt-4 border-t border-[#3a3a3a]">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-gray-400 text-xs mb-1">Title</label>
                                        <input
                                            v-model="form.theme_settings.shop_cta_title"
                                            type="text"
                                            placeholder="VISIT OUR SHOP"
                                            class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-gray-400 text-xs mb-1">Subtitle</label>
                                        <input
                                            v-model="form.theme_settings.shop_cta_subtitle"
                                            type="text"
                                            placeholder="FIND GREAT GIFT IDEAS!"
                                            class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-gray-400 text-xs mb-1">Button Text</label>
                                        <input
                                            v-model="form.theme_settings.shop_cta_button"
                                            type="text"
                                            placeholder="SHOP NOW"
                                            class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-gray-400 text-xs mb-1">Button Link (URL)</label>
                                        <input
                                            v-model="form.theme_settings.shop_cta_link"
                                            type="text"
                                            placeholder="https://yourshop.com"
                                            class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                    </div>
                                </div>
                                <!-- Preview -->
                                <div class="mt-4 p-4 bg-gradient-to-br from-pink-400 to-rose-500 rounded-xl text-white text-center">
                                    <h3 class="text-lg font-bold mb-0.5">{{ form.theme_settings.shop_cta_title || 'VISIT OUR SHOP' }}</h3>
                                    <h4 class="text-sm mb-3 text-pink-100">{{ form.theme_settings.shop_cta_subtitle || 'FIND GREAT GIFT IDEAS!' }}</h4>
                                    <span class="inline-block bg-white text-pink-600 px-6 py-2 rounded-full font-bold text-sm shadow-md">
                                        {{ form.theme_settings.shop_cta_button || 'SHOP NOW' }} 🛍️
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Popup Settings -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Article Subscription Popup</h3>
                    <p class="text-gray-400 text-sm mb-6">Customize the subscription popup that appears when visitors read articles</p>
                    
                    <div class="p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center">
                                    <span class="text-white text-xl">🎁</span>
                                </div>
                                <div>
                                    <h4 class="text-white font-semibold">Show Subscription Popup in Articles</h4>
                                    <p class="text-gray-400 text-sm">Display a popup to collect email subscribers while reading articles</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.theme_settings.show_subscription_popup"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-[#3a3a3a] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                        
                        <!-- Popup Settings Fields -->
                        <div v-if="form.theme_settings.show_subscription_popup" class="space-y-4 mt-4 pt-4 border-t border-[#3a3a3a]">
                            <!-- Desktop/Mobile Toggles -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center justify-between p-3 bg-[#1a1a1a] rounded-lg border border-[#3a3a3a]">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-white text-sm font-medium">Show on Desktop</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="form.theme_settings.subscription_popup_desktop"
                                            class="sr-only peer"
                                        />
                                        <div class="w-11 h-6 bg-[#3a3a3a] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-[#1a1a1a] rounded-lg border border-[#3a3a3a]">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-white text-sm font-medium">Show on Mobile</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="form.theme_settings.subscription_popup_mobile"
                                            class="sr-only peer"
                                        />
                                        <div class="w-11 h-6 bg-[#3a3a3a] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Delay Setting -->
                            <div>
                                <label class="block text-gray-400 text-xs mb-1">Delay Before Showing Popup (seconds)</label>
                                <input
                                    v-model.number="form.theme_settings.subscription_popup_delay"
                                    type="number"
                                    min="1"
                                    max="60"
                                    placeholder="5"
                                    class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                                <p class="text-gray-500 text-xs mt-1">How many seconds to wait before showing the popup (1-60 seconds)</p>
                            </div>
                            
                            <!-- Title and Subtitle -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-gray-400 text-xs mb-1">Title</label>
                                    <input
                                        v-model="form.theme_settings.subscription_popup_title"
                                        type="text"
                                        placeholder="GET NEW RECIPES"
                                        class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-gray-400 text-xs mb-1">Subtitle</label>
                                    <input
                                        v-model="form.theme_settings.subscription_popup_subtitle"
                                        type="text"
                                        placeholder="IN YOUR INBOX"
                                        class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    />
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div>
                                <label class="block text-gray-400 text-xs mb-1">Description</label>
                                <textarea
                                    v-model="form.theme_settings.subscription_popup_description"
                                    rows="3"
                                    placeholder="Join to receive our email series..."
                                    class="w-full px-3 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                                ></textarea>
                            </div>
                            
                            <!-- Prize Image Upload -->
                            <div>
                                <label class="block text-gray-400 text-xs mb-1">Prize/Incentive Image</label>
                                <p class="text-gray-500 text-xs mb-2">Upload an image of the prize or incentive subscribers will receive (e.g., a book, ebook, etc.)</p>
                                
                                <div v-if="form.theme_settings.subscription_popup_image" class="mb-3">
                                    <div class="relative inline-block">
                                        <img 
                                            :src="form.theme_settings.subscription_popup_image" 
                                            alt="Prize image" 
                                            class="max-w-xs max-h-48 rounded-lg border border-[#3a3a3a]"
                                        />
                                        <button
                                            @click="removeImage"
                                            type="button"
                                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <label class="inline-flex items-center gap-2 px-4 py-2 bg-[#1a1a1a] border border-[#3a3a3a] rounded-lg text-white text-sm cursor-pointer hover:bg-[#252525] transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span v-if="!isUploadingImage">{{ form.theme_settings.subscription_popup_image ? 'Change Image' : 'Upload Image' }}</span>
                                    <span v-else class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Uploading...
                                    </span>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="uploadImage"
                                        class="hidden"
                                        :disabled="isUploadingImage"
                                    />
                                </label>
                            </div>
                            
                            <!-- Preview -->
                            <div class="mt-4 p-4 bg-gradient-to-br from-purple-400 via-pink-400 to-rose-500 rounded-xl text-white">
                                <p class="text-gray-400 text-xs mb-3 uppercase tracking-wider">Preview:</p>
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                    <div class="flex flex-col md:flex-row gap-4 md:gap-6 items-center md:items-start">
                                        <!-- Image on left (desktop) / top (mobile) - Takes more space -->
                                        <div v-if="form.theme_settings.subscription_popup_image" class="flex-shrink-0 w-full md:w-2/5 flex justify-center md:justify-start">
                                            <img 
                                                :src="form.theme_settings.subscription_popup_image" 
                                                alt="Prize preview" 
                                                class="w-full max-w-xs md:max-w-none md:w-full h-auto md:h-48 rounded-lg shadow-lg"
                                            />
                                        </div>
                                        <!-- Content -->
                                        <div class="flex-1 md:w-3/5 text-center md:text-left">
                                            <h3 class="text-xl font-bold mb-1">{{ form.theme_settings.subscription_popup_title || 'GET NEW RECIPES' }}</h3>
                                            <h4 class="text-sm mb-2 text-purple-100">{{ form.theme_settings.subscription_popup_subtitle || 'IN YOUR INBOX' }}</h4>
                                            <p class="text-xs mb-3 text-purple-50">{{ form.theme_settings.subscription_popup_description || 'Join to receive our email series...' }}</p>
                                            <span class="inline-block bg-white text-purple-600 px-6 py-2 rounded-full font-bold text-sm shadow-md">
                                                SUBSCRIBE NOW 💌
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Typography Settings -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Typography</h3>
                    <p class="text-gray-400 text-sm mb-6">Choose the font family for article text</p>
                    
                    <div class="space-y-4">
                        <label class="block">
                            <span class="text-white text-sm font-medium mb-2 block">Article Font Family</span>
                            <select
                                v-model="form.theme_settings.article_font_family"
                                class="w-full px-4 py-3 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            >
                                <option v-for="font in availableFonts" :key="font.id" :value="font.id">
                                    {{ font.name }}
                                </option>
                            </select>
                            <p class="text-gray-500 text-xs mt-2">This font will be applied to all article text content</p>
                        </label>
                        
                        <!-- Font Preview -->
                        <div class="p-6 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                            <p class="text-gray-400 text-xs mb-3">PREVIEW:</p>
                            <div :style="{ fontFamily: getFontFamily(form.theme_settings.article_font_family) }">
                                <p class="text-white text-2xl font-bold mb-2">The Quick Brown Fox</p>
                                <p class="text-gray-300 text-base leading-relaxed">
                                    This is how your article text will look. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
                                    Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                                </p>
                            </div>
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
