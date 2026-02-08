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
    ads_txt: props.currentWebsite.ads_txt || '',
    pinterest_verification: props.currentWebsite.pinterest_verification || '',
    logo: null,
    favicon: null,
    remove_logo: false,
    remove_favicon: false,
});

const logoPreview = ref(props.currentWebsite.logo_url || '');
const faviconPreview = ref(props.currentWebsite.favicon_url || '');

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

                    <!-- Ads.txt Section -->
                    <div>
                        <label for="ads_txt" class="block text-sm font-medium text-gray-300 mb-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Ads.txt Content (Optional)
                            </div>
                        </label>
                        <textarea
                            id="ads_txt"
                            v-model="form.ads_txt"
                            rows="6"
                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                            placeholder="google.com, pub-xxxxxxxxxxxxxxxx, DIRECT, f08c47fec0942fa0"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Paste the content for your website's ads.txt file. This is required by many ad networks like Google Ads and HB Agency.</p>
                        <p v-if="form.errors.ads_txt" class="mt-1 text-sm text-red-500">{{ form.errors.ads_txt }}</p>
                    </div>

                    <!-- Pinterest Claim Website Section -->
                    <div>
                        <label for="pinterest_verification" class="block text-sm font-medium text-gray-300 mb-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 0a12 12 0 00-4.373 23.178c-.07-.627-.133-1.59.028-2.273.146-.622.937-3.977.937-3.977s-.239-.479-.239-1.188c0-1.113.645-1.944 1.448-1.944.683 0 1.013.513 1.013 1.128 0 .687-.437 1.713-.663 2.664-.189.797.4 1.447 1.185 1.447 1.422 0 2.515-1.5 2.515-3.664 0-1.915-1.377-3.254-3.342-3.254-2.276 0-3.612 1.707-3.612 3.471 0 .688.265 1.425.595 1.826a.24.24 0 01.056.23c-.061.252-.196.797-.222.908-.035.146-.115.177-.267.107-1-.465-1.624-1.927-1.624-3.1 0-2.523 1.834-4.84 5.286-4.84 2.775 0 4.932 1.977 4.932 4.62 0 2.757-1.739 4.976-4.151 4.976-.811 0-1.573-.421-1.834-.919l-.498 1.902c-.181.695-.669 1.566-.995 2.097A12 12 0 1012 0z"/>
                                </svg>
                                Pinterest Website Claim (Optional)
                            </div>
                        </label>
                        <input
                            id="pinterest_verification"
                            v-model="form.pinterest_verification"
                            type="text"
                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 font-mono text-sm"
                            placeholder="71443e52cd737e0a1bf625ef293124db"
                        />
                        <div class="mt-2 p-3 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                            <p class="text-xs text-gray-400 mb-2">
                                <strong class="text-gray-300">How to get this code:</strong>
                            </p>
                            <ol class="text-xs text-gray-500 space-y-1 list-decimal list-inside">
                                <li>Go to Pinterest Settings → Claim</li>
                                <li>Select "Add HTML tag" method</li>
                                <li>Copy only the <code class="text-amber-400 bg-[#1a1a1a] px-1 rounded">content</code> value from the meta tag</li>
                                <li>Paste it here (just the verification code, not the full tag)</li>
                            </ol>
                            <p class="text-xs text-gray-500 mt-2">
                                Example: From <code class="text-amber-400 bg-[#1a1a1a] px-1 rounded">&lt;meta name="p:domain_verify" content="71443e52cd..."&gt;</code>
                                <br/>
                                Paste only: <code class="text-emerald-400 bg-[#1a1a1a] px-1 rounded">71443e52cd...</code>
                            </p>
                        </div>
                        <p v-if="form.errors.pinterest_verification" class="mt-1 text-sm text-red-500">{{ form.errors.pinterest_verification }}</p>
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
