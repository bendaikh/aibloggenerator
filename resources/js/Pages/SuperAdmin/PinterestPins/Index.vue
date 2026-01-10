<script setup>
import { ref, computed, onMounted } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: null
    },
    pins: {
        type: Object,
        default: () => ({ data: [] })
    },
    articlesWithoutPins: {
        type: Array,
        default: () => []
    }
});

const showDeleteModal = ref(false);
const pinToDelete = ref(null);
const selectedPins = ref([]);
const showBulkActions = computed(() => selectedPins.value.length > 0);

// Bulk generate modal
const showBulkGenerateModal = ref(false);
const selectedArticles = ref([]);
const isGenerating = ref(false);
const selectAll = computed({
    get: () => selectedArticles.value.length === props.articlesWithoutPins.length && props.articlesWithoutPins.length > 0,
    set: (value) => {
        if (value) {
            selectedArticles.value = props.articlesWithoutPins.map(a => a.id);
        } else {
            selectedArticles.value = [];
        }
    }
});

const toggleArticle = (articleId) => {
    const index = selectedArticles.value.indexOf(articleId);
    if (index > -1) {
        selectedArticles.value.splice(index, 1);
    } else {
        selectedArticles.value.push(articleId);
    }
};

const bulkGeneratePins = () => {
    if (selectedArticles.value.length === 0) return;
    
    isGenerating.value = true;
    router.post(route('superadmin.pinterest-pins.bulk-generate', { website: props.currentWebsite.id }), {
        article_ids: selectedArticles.value
    }, {
        onSuccess: () => {
            showBulkGenerateModal.value = false;
            selectedArticles.value = [];
            isGenerating.value = false;
        },
        onError: () => {
            isGenerating.value = false;
        }
    });
};

const closeBulkModal = () => {
    showBulkGenerateModal.value = false;
    selectedArticles.value = [];
};

const openDeleteModal = (pin) => {
    pinToDelete.value = pin;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    pinToDelete.value = null;
};

const deletePin = () => {
    if (!pinToDelete.value) return;
    
    router.delete(route('superadmin.pinterest-pins.destroy', {
        website: props.currentWebsite.id,
        pin: pinToDelete.value.id
    }), {
        onSuccess: () => closeDeleteModal()
    });
};

const regeneratePin = (pin) => {
    router.post(route('superadmin.pinterest-pins.regenerate', {
        website: props.currentWebsite.id,
        pin: pin.id
    }));
};

const downloadPin = (pin) => {
    window.location.href = route('superadmin.pinterest-pins.download', {
        website: props.currentWebsite.id,
        pin: pin.id
    });
};

const copyPinInfo = async (pin) => {
    const text = `Title: ${pin.title}\nDescription: ${pin.description || ''}\nLink: ${pin.link || ''}`;
    await navigator.clipboard.writeText(text);
    // Show toast or feedback
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
    <Head title="Pinterest Designs" />

    <SuperAdminLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Pinterest Designs</h1>
                    <p class="text-gray-400 mt-1">Create and manage Pinterest pin images for your articles</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Generate Missing Pins Button -->
                    <button
                        v-if="articlesWithoutPins.length > 0"
                        @click="showBulkGenerateModal = true"
                        class="flex items-center gap-2 px-5 py-2.5 bg-[#252525] hover:bg-[#2a2a2a] text-white rounded-xl font-medium transition-all border border-[#3a3a3a]"
                    >
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Generate Missing ({{ articlesWithoutPins.length }})
                    </button>
                    <Link
                        :href="route('superadmin.pinterest-pins.create', { website: currentWebsite.id })"
                        class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white rounded-xl font-medium transition-all shadow-lg shadow-pink-500/20"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Pin Design
                    </Link>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-6 py-4 rounded-xl mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <div v-if="$page.props.errors?.error" class="bg-red-900/50 border border-red-500 text-red-200 px-6 py-4 rounded-xl mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $page.props.errors.error }}</span>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="pins.data.length === 0" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-pink-500/20 to-rose-500/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Pinterest Designs Yet</h3>
                <p class="text-gray-400 mb-6 max-w-md mx-auto">
                    Create beautiful Pinterest pin images from your articles to drive traffic from Pinterest to your website.
                </p>
                <Link
                    :href="route('superadmin.pinterest-pins.create', { website: currentWebsite.id })"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white rounded-xl font-medium transition-all"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Your First Pin
                </Link>
            </div>

            <!-- Pins Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="pin in pins.data"
                    :key="pin.id"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden group hover:border-pink-500/50 transition-all"
                >
                    <!-- Pin Image Preview -->
                    <div class="relative aspect-[2/3] bg-[#0f0f0f]">
                        <img
                            v-if="pin.generated_image_url"
                            :src="pin.generated_image_url"
                            :alt="pin.title"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <div v-if="pin.status === 'pending'" class="text-center">
                                <svg class="w-12 h-12 text-yellow-400 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-yellow-400 text-sm">Generating...</span>
                            </div>
                            <div v-else-if="pin.status === 'failed'" class="text-center px-4">
                                <svg class="w-12 h-12 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-red-400 text-sm">Generation Failed</span>
                                <p class="text-gray-500 text-xs mt-1">{{ pin.error_message }}</p>
                            </div>
                            <div v-else class="text-center">
                                <svg class="w-12 h-12 text-gray-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Hover Actions -->
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <button
                                v-if="pin.generated_image_url"
                                @click="downloadPin(pin)"
                                class="p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors"
                                title="Download"
                            >
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </button>
                            <button
                                @click="regeneratePin(pin)"
                                class="p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors"
                                title="Regenerate"
                            >
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            <button
                                @click="copyPinInfo(pin)"
                                class="p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors"
                                title="Copy Info"
                            >
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <button
                                @click="openDeleteModal(pin)"
                                class="p-3 bg-red-500/20 hover:bg-red-500/40 rounded-xl transition-colors"
                                title="Delete"
                            >
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-lg border', getStatusBadgeClass(pin.status)]">
                                {{ pin.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Pin Info -->
                    <div class="p-4">
                        <h3 class="text-white font-medium truncate mb-1">{{ pin.title }}</h3>
                        <p class="text-gray-500 text-sm truncate">{{ pin.article?.title || 'Unknown article' }}</p>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-[#2a2a2a]">
                            <span class="text-gray-500 text-xs">
                                {{ new Date(pin.created_at).toLocaleDateString() }}
                            </span>
                            <Link
                                v-if="pin.article"
                                :href="route('superadmin.articles.edit', { website: currentWebsite.id, article: pin.article.id })"
                                class="text-pink-400 hover:text-pink-300 text-xs font-medium"
                            >
                                View Article →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pins.links && pins.links.length > 3" class="mt-8 flex justify-center gap-2">
                <Link
                    v-for="link in pins.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm transition-colors',
                        link.active
                            ? 'bg-pink-500 text-white'
                            : link.url
                                ? 'bg-[#1a1a1a] text-gray-400 hover:text-white hover:bg-[#252525]'
                                : 'bg-[#1a1a1a] text-gray-600 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/70" @click="closeDeleteModal"></div>
            <div class="relative bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-semibold text-white mb-2">Delete Pinterest Pin</h3>
                <p class="text-gray-400 mb-6">
                    Are you sure you want to delete this Pinterest pin? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="closeDeleteModal"
                        class="px-4 py-2 bg-[#252525] hover:bg-[#2a2a2a] text-white rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deletePin"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors"
                    >
                        Delete Pin
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Generate Modal -->
        <div v-if="showBulkGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/70" @click="closeBulkModal"></div>
            <div class="relative bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 max-w-2xl w-full mx-4 max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-white">Generate Pinterest Pins</h3>
                        <p class="text-gray-400 text-sm mt-1">Select articles to generate Pinterest pins for</p>
                    </div>
                    <button @click="closeBulkModal" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Select All -->
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-[#2a2a2a]">
                    <input
                        type="checkbox"
                        v-model="selectAll"
                        class="w-5 h-5 rounded bg-[#252525] border-[#3a3a3a] text-pink-500 focus:ring-pink-500"
                    />
                    <span class="text-white font-medium">Select All ({{ articlesWithoutPins.length }} articles)</span>
                </div>

                <!-- Articles List -->
                <div class="flex-1 overflow-y-auto space-y-2 mb-4">
                    <div
                        v-for="article in articlesWithoutPins"
                        :key="article.id"
                        class="flex items-center gap-4 p-3 bg-[#252525] rounded-xl hover:bg-[#2a2a2a] transition-colors cursor-pointer"
                        @click="toggleArticle(article.id)"
                    >
                        <input
                            type="checkbox"
                            :checked="selectedArticles.includes(article.id)"
                            @click.stop
                            @change="toggleArticle(article.id)"
                            class="w-5 h-5 rounded bg-[#1a1a1a] border-[#3a3a3a] text-pink-500 focus:ring-pink-500"
                        />
                        <img
                            v-if="article.featured_image"
                            :src="article.featured_image"
                            :alt="article.title"
                            class="w-12 h-12 rounded-lg object-cover"
                        />
                        <div v-else class="w-12 h-12 rounded-lg bg-[#1a1a1a] flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-white font-medium truncate">{{ article.title }}</h4>
                            <p class="text-gray-500 text-sm">{{ article.status }} • {{ new Date(article.created_at).toLocaleDateString() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-[#2a2a2a]">
                    <span class="text-gray-400 text-sm">{{ selectedArticles.length }} articles selected</span>
                    <div class="flex gap-3">
                        <button
                            @click="closeBulkModal"
                            class="px-4 py-2 bg-[#252525] hover:bg-[#2a2a2a] text-white rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="bulkGeneratePins"
                            :disabled="selectedArticles.length === 0 || isGenerating"
                            class="px-5 py-2 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="isGenerating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ isGenerating ? 'Generating...' : 'Generate Pins' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
