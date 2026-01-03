<script setup>
import { useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    currentWebsite: {
        type: Object,
        required: true
    }
});

const form = useForm({
    name: props.currentWebsite.name || '',
    logo: null,
    favicon: null,
    remove_logo: false,
    remove_favicon: false,
});

const logoPreview = ref(props.currentWebsite.logo_url || props.currentWebsite.logo || '');
const faviconPreview = ref(props.currentWebsite.favicon_url || props.currentWebsite.favicon || '');

const handleLogoChange = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        form.logo = file;
        form.remove_logo = false;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleFaviconChange = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        form.favicon = file;
        form.remove_favicon = false;
        const reader = new FileReader();
        reader.onload = (e) => {
            faviconPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    logoPreview.value = '';
    const input = document.getElementById('logo');
    if (input) input.value = '';
};

const removeFavicon = () => {
    form.favicon = null;
    form.remove_favicon = true;
    faviconPreview.value = '';
    const input = document.getElementById('favicon');
    if (input) input.value = '';
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('superadmin.settings.update', props.currentWebsite.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Website Settings" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Website Settings</h1>
                <p class="text-gray-400 mt-1">Manage your website name, logo, and favicon</p>
            </div>

            <form @submit.prevent="submit" class="max-w-3xl">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 space-y-6">
                    <!-- Website Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                            Website Name *
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="My Awesome Blog"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Logo
                        </label>
                        <div v-if="logoPreview" class="mb-3">
                            <div class="relative inline-block">
                                <img 
                                    :src="logoPreview" 
                                    alt="Logo preview"
                                    class="h-32 w-auto rounded-lg object-cover border border-[#3a3a3a]"
                                />
                                <button
                                    type="button"
                                    @click="removeLogo"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors"
                                    title="Remove logo"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="border-2 border-dashed border-[#3a3a3a] rounded-lg p-6 text-center hover:border-[#4a4a4a] transition-colors cursor-pointer" @click="$refs.logoInput?.click()">
                            <input
                                id="logo"
                                ref="logoInput"
                                type="file"
                                accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                                class="hidden"
                                @change="handleLogoChange"
                            />
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-[#252525] rounded-lg flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-white text-sm font-medium mb-1">
                                    {{ logoPreview ? 'Click to change logo' : 'Drop an image here or click to browse' }}
                                </p>
                                <p class="text-gray-500 text-xs">PNG, JPG, GIF, WebP, SVG up to 5MB</p>
                            </div>
                        </div>
                        <p v-if="form.errors.logo" class="mt-1 text-sm text-red-500">{{ form.errors.logo }}</p>
                        <p class="mt-1 text-xs text-gray-500">Your website logo will be displayed in the header and other prominent locations.</p>
                    </div>

                    <!-- Favicon Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Favicon
                        </label>
                        <div v-if="faviconPreview" class="mb-3">
                            <div class="relative inline-block">
                                <img 
                                    :src="faviconPreview" 
                                    alt="Favicon preview"
                                    class="h-16 w-16 rounded-lg object-cover border border-[#3a3a3a]"
                                />
                                <button
                                    type="button"
                                    @click="removeFavicon"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors"
                                    title="Remove favicon"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="border-2 border-dashed border-[#3a3a3a] rounded-lg p-6 text-center hover:border-[#4a4a4a] transition-colors cursor-pointer" @click="$refs.faviconInput?.click()">
                            <input
                                id="favicon"
                                ref="faviconInput"
                                type="file"
                                accept="image/jpeg,image/png,image/gif,image/webp,image/x-icon"
                                class="hidden"
                                @change="handleFaviconChange"
                            />
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-[#252525] rounded-lg flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-white text-sm font-medium mb-1">
                                    {{ faviconPreview ? 'Click to change favicon' : 'Drop an image here or click to browse' }}
                                </p>
                                <p class="text-gray-500 text-xs">PNG, JPG, GIF, WebP, ICO up to 2MB. Recommended: 32x32 or 16x16 pixels</p>
                            </div>
                        </div>
                        <p v-if="form.errors.favicon" class="mt-1 text-sm text-red-500">{{ form.errors.favicon }}</p>
                        <p class="mt-1 text-xs text-gray-500">The favicon appears in browser tabs and bookmarks. Square images work best.</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex items-center gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
    </SuperAdminLayout>
</template>
