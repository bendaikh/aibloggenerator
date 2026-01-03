<template>
    <Head title="Global Article Generation" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Global Article Generator</h1>
                <p class="text-gray-400">Generate an article once and push it automatically to multiple websites</p>
            </div>

            <!-- API Key Warning -->
            <div v-if="!hasApiKey" class="bg-yellow-900/50 border border-yellow-500 text-yellow-200 px-6 py-4 rounded-lg mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span><strong>OpenAI API key not configured.</strong> Please add your API key in Global Settings to use AI generation.</span>
                    </div>
                    <Link :href="route('organization.settings')" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Go to Settings
                    </Link>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="form.errors.error" class="bg-red-900/50 border border-red-500 text-red-200 px-6 py-4 rounded-lg mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ form.errors.error }}</span>
                </div>
            </div>

            <!-- Success Message -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-6 py-4 rounded-lg mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Generation Form -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Topic -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Article Topic *
                                </label>
                                <input
                                    v-model="form.topic"
                                    type="text"
                                    required
                                    placeholder="e.g., 'Best Italian Pasta Recipes for Beginners'"
                                    class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500 text-lg"
                                />
                                <p v-if="form.errors.topic" class="mt-1 text-sm text-red-500">{{ form.errors.topic }}</p>
                                <p class="mt-1 text-xs text-gray-500">The AI will generate one article and push it to all selected websites.</p>
                            </div>

                            <!-- Settings Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Article Length
                                    </label>
                                    <select
                                        v-model="form.length"
                                        class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="short">Short (500-700 words)</option>
                                        <option value="medium">Medium (1000-1500 words)</option>
                                        <option value="long">Long (2000-3000 words)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Writing Tone
                                    </label>
                                    <select
                                        v-model="form.tone"
                                        class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="conversational">Conversational</option>
                                        <option value="professional">Professional</option>
                                        <option value="casual">Casual</option>
                                        <option value="friendly">Friendly</option>
                                        <option value="formal">Formal</option>
                                    </select>
                                </div>

                                <div class="flex items-end">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="form.auto_publish"
                                            type="checkbox"
                                            class="w-4 h-4 text-emerald-600 bg-[#0a0a0a] border-[#2a2a2a] rounded focus:ring-emerald-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-300">Auto-publish article</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Keywords -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Keywords (Optional)
                                </label>
                                <input
                                    v-model="form.keywords"
                                    type="text"
                                    placeholder="e.g., italian cuisine, pasta, cooking tips"
                                    class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">Separate keywords with commas</p>
                            </div>

                            <!-- Multiple Featured Images -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-gray-300">
                                        Featured Images (Optional)
                                    </label>
                                    <button 
                                        type="button"
                                        @click="addMoreImages"
                                        class="text-xs text-emerald-400 hover:text-emerald-300 font-medium flex items-center gap-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add another image
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500">These images will be distributed sequentially to the selected websites.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="(img, index) in form.featured_images" :key="index" class="relative group">
                                        <div class="absolute -top-2 -right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button 
                                                v-if="form.featured_images.length > 1"
                                                type="button"
                                                @click="removeImageAt(index)"
                                                class="w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <ImageUpload
                                            v-model="form.featured_images[index]"
                                            :label="`Image #${index + 1}`"
                                            type="article"
                                            hint="PNG, JPG, WebP"
                                            :allow-url="true"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-4 border-t border-[#2a2a2a]">
                                <button
                                    type="submit"
                                    :disabled="form.processing || !hasApiKey || selectedWebsites.length === 0"
                                    class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    {{ form.processing ? 'Queueing...' : 'Generate & Push to '+selectedWebsites.length+' Websites' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Website Selection -->
                <div class="lg:col-span-1">
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden sticky top-24">
                        <div class="p-6 border-b border-[#2a2a2a] bg-[#1f1f1f]">
                            <h3 class="text-white font-bold flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                Select Websites
                            </h3>
                            <div class="flex items-center justify-between mt-4">
                                <button @click="selectAllWebsites" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium">Select All</button>
                                <button @click="deselectAllWebsites" class="text-xs text-gray-500 hover:text-gray-400 font-medium">Clear All</button>
                            </div>
                        </div>

                        <div class="max-h-[500px] overflow-y-auto p-2">
                            <div v-if="websites.length === 0" class="p-8 text-center">
                                <p class="text-gray-500 text-sm">No websites found.</p>
                                <Link :href="route('organization.websites.create')" class="text-emerald-400 text-sm mt-2 inline-block">Create your first website</Link>
                            </div>

                            <div v-else class="space-y-1">
                                <div
                                    v-for="website in websites"
                                    :key="website.id"
                                    @click="toggleWebsite(website.id)"
                                    :class="[
                                        'flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all border',
                                        selectedWebsites.includes(website.id)
                                            ? 'bg-emerald-500/10 border-emerald-500/50 text-white'
                                            : 'bg-transparent border-transparent text-gray-400 hover:bg-[#252525] hover:text-white'
                                    ]"
                                >
                                    <div class="shrink-0">
                                        <div 
                                            v-if="selectedWebsites.includes(website.id)"
                                            class="w-5 h-5 bg-emerald-500 rounded flex items-center justify-center"
                                        >
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div v-else class="w-5 h-5 border-2 border-[#3a3a3a] rounded"></div>
                                    </div>
                                    
                                    <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-yellow-500 rounded flex items-center justify-center shrink-0">
                                        <span class="text-white text-xs font-bold">{{ website.name.charAt(0).toUpperCase() }}</span>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate">{{ website.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OrganizationLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import ImageUpload from '@/Components/ImageUpload.vue';

const page = usePage();

const props = defineProps({
    websites: {
        type: Array,
        default: () => []
    },
    hasApiKey: {
        type: Boolean,
        default: false
    },
    defaultTone: {
        type: String,
        default: 'conversational'
    }
});

const selectedWebsites = ref([]);

const form = useForm({
    topic: '',
    tone: props.defaultTone,
    length: 'medium',
    keywords: '',
    featured_images: [''], // Array of images
    auto_publish: false,
    website_ids: [],
});

const addMoreImages = () => {
    form.featured_images.push('');
};

const removeImageAt = (index) => {
    form.featured_images.splice(index, 1);
};

const toggleWebsite = (id) => {
    const index = selectedWebsites.value.indexOf(id);
    if (index === -1) {
        selectedWebsites.value.push(id);
    } else {
        selectedWebsites.value.splice(index, 1);
    }
};

const selectAllWebsites = () => {
    selectedWebsites.value = props.websites.map(w => w.id);
};

const deselectAllWebsites = () => {
    selectedWebsites.value = [];
};

const submitForm = () => {
    form.website_ids = selectedWebsites.value;
    form.post(route('organization.global-articles.generate'), {
        onSuccess: () => {
            form.reset('topic', 'keywords');
            // Flash message handled by inertia
        }
    });
};
</script>
