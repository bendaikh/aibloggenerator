<script setup>
import { ref } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: null
    },
    pin: {
        type: Object,
        required: true
    }
});

const showEditModal = ref(false);
const copied = ref(false);

const form = useForm({
    headline_text: props.pin.headline_text,
    subheadline_text: props.pin.subheadline_text,
    headline_color: props.pin.headline_color,
    subheadline_color: props.pin.subheadline_color,
    overlay_color: props.pin.overlay_color,
    overlay_opacity: props.pin.overlay_opacity,
});

const regeneratePin = () => {
    form.post(route('superadmin.pinterest-pins.regenerate', {
        website: props.currentWebsite.id,
        pin: props.pin.id
    }), {
        onSuccess: () => {
            showEditModal.value = false;
        }
    });
};

const downloadPin = () => {
    window.location.href = route('superadmin.pinterest-pins.download', {
        website: props.currentWebsite.id,
        pin: props.pin.id
    });
};

const copyPinInfo = async () => {
    const text = `Title: ${props.pin.title}
Description: ${props.pin.description || ''}
Link: ${props.pin.link || ''}
Image: ${props.pin.generated_image_url || ''}`;
    
    await navigator.clipboard.writeText(text);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'generated':
            return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
        case 'pending':
            return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
        case 'failed':
            return 'bg-red-500/20 text-red-400 border-red-500/30';
        default:
            return 'bg-gray-500/20 text-gray-400 border-gray-500/30';
    }
};
</script>

<template>
    <Head :title="pin.title" />

    <SuperAdminLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <Link
                    :href="route('superadmin.pinterest-pins.index', { website: currentWebsite.id })"
                    class="p-2 bg-[#1a1a1a] hover:bg-[#252525] rounded-xl transition-colors"
                >
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white">{{ pin.title }}</h1>
                    <p class="text-gray-400 mt-1">Pinterest Pin Details</p>
                </div>
                <span :class="['px-3 py-1 text-sm font-medium rounded-lg border', getStatusBadgeClass(pin.status)]">
                    {{ pin.status }}
                </span>
            </div>

            <!-- Success Message -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-6 py-4 rounded-xl mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Pin Image -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">Pin Image</h3>
                        <div class="flex items-center gap-2">
                            <button
                                v-if="pin.generated_image_url"
                                @click="downloadPin"
                                class="p-2 bg-[#252525] hover:bg-[#2a2a2a] rounded-lg transition-colors"
                                title="Download"
                            >
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </button>
                            <button
                                @click="showEditModal = true"
                                class="p-2 bg-[#252525] hover:bg-[#2a2a2a] rounded-lg transition-colors"
                                title="Edit & Regenerate"
                            >
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="relative bg-[#0a0a0a] rounded-xl overflow-hidden" style="aspect-ratio: 2/3;">
                        <img
                            v-if="pin.generated_image_url"
                            :src="pin.generated_image_url"
                            :alt="pin.title"
                            class="w-full h-full object-contain"
                        />
                        <div v-else-if="pin.status === 'pending'" class="w-full h-full flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-16 h-16 text-yellow-400 animate-spin mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-yellow-400">Generating...</span>
                            </div>
                        </div>
                        <div v-else-if="pin.status === 'failed'" class="w-full h-full flex items-center justify-center">
                            <div class="text-center px-8">
                                <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-red-400 block mb-2">Generation Failed</span>
                                <p class="text-gray-500 text-sm">{{ pin.error_message }}</p>
                                <button
                                    @click="showEditModal = true"
                                    class="mt-4 px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg text-sm"
                                >
                                    Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pin Details -->
                <div class="space-y-6">
                    <!-- Pinterest Info -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-white">Pinterest Info</h3>
                            <button
                                @click="copyPinInfo"
                                class="flex items-center gap-2 px-3 py-1.5 bg-[#252525] hover:bg-[#2a2a2a] rounded-lg transition-colors text-sm"
                            >
                                <svg v-if="copied" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg v-else class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                {{ copied ? 'Copied!' : 'Copy All' }}
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Title</label>
                                <p class="text-white">{{ pin.title }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Description</label>
                                <p class="text-white text-sm">{{ pin.description || 'No description' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Link</label>
                                <a :href="pin.link" target="_blank" class="text-pink-400 hover:text-pink-300 text-sm break-all">
                                    {{ pin.link }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Design Settings -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Design Settings</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Headline</label>
                                <p class="text-white text-sm">{{ pin.headline_text }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Subheadline</label>
                                <p class="text-white text-sm">{{ pin.subheadline_text }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Headline Color</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded border border-[#3a3a3a]" :style="{ backgroundColor: pin.headline_color }"></div>
                                    <span class="text-white text-sm">{{ pin.headline_color }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Subheadline Color</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded border border-[#3a3a3a]" :style="{ backgroundColor: pin.subheadline_color }"></div>
                                    <span class="text-white text-sm">{{ pin.subheadline_color }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Overlay Color</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded border border-[#3a3a3a]" :style="{ backgroundColor: pin.overlay_color }"></div>
                                    <span class="text-white text-sm">{{ pin.overlay_color }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Overlay Opacity</label>
                                <span class="text-white text-sm">{{ pin.overlay_opacity }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Article Link -->
                    <div v-if="pin.article" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Linked Article</h3>
                        <Link
                            :href="route('superadmin.articles.edit', { website: currentWebsite.id, article: pin.article.id })"
                            class="flex items-center gap-4 p-4 bg-[#252525] hover:bg-[#2a2a2a] rounded-xl transition-colors"
                        >
                            <div class="flex-1">
                                <h4 class="text-white font-medium">{{ pin.article.title }}</h4>
                                <p class="text-gray-500 text-sm mt-1">Click to edit article</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit & Regenerate Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/70" @click="showEditModal = false"></div>
            <div class="relative bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-semibold text-white mb-6">Edit & Regenerate Pin</h3>
                
                <form @submit.prevent="regeneratePin" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Headline Text</label>
                        <input
                            v-model="form.headline_text"
                            type="text"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Subheadline Text</label>
                        <input
                            v-model="form.subheadline_text"
                            type="text"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Headline Color</label>
                            <input v-model="form.headline_color" type="color" class="w-full h-10 rounded cursor-pointer" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Subheadline Color</label>
                            <input v-model="form.subheadline_color" type="color" class="w-full h-10 rounded cursor-pointer" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Overlay Color</label>
                            <input v-model="form.overlay_color" type="color" class="w-full h-10 rounded cursor-pointer" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Opacity: {{ form.overlay_opacity }}%</label>
                            <input v-model="form.overlay_opacity" type="range" min="0" max="100" class="w-full" />
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="px-4 py-2 bg-[#252525] hover:bg-[#2a2a2a] text-white rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg transition-colors flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ form.processing ? 'Regenerating...' : 'Regenerate Pin' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SuperAdminLayout>
</template>
