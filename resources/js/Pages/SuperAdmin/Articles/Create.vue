<template>
    <SuperAdminLayout>
        <Head title="Create Article" />

        <div class="p-8">
            <div class="max-w-4xl">
                <div class="mb-8">
                    <Link 
                        :href="route('superadmin.articles.index', { website: currentWebsite?.id })" 
                        class="text-gray-400 hover:text-white flex items-center text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Articles
                    </Link>
                </div>

                <!-- Step 1: Choose Creation Method -->
                <div v-if="!creationMethod" class="space-y-6">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-white">Create New Article</h1>
                        <p class="text-gray-400 mt-1">Choose how you want to create your article</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Manual Creation -->
                        <button 
                            @click="creationMethod = 'manual'"
                            class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8 text-left hover:border-emerald-500 transition-all group"
                        >
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mb-6">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Write Manually</h3>
                            <p class="text-gray-400 text-sm">
                                Create your article from scratch. Write your own content with full creative control.
                            </p>
                            <div class="mt-6 flex items-center text-emerald-400 text-sm font-medium">
                                Start writing
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>

                        <!-- AI Generation -->
                        <button 
                            @click="creationMethod = 'ai'"
                            :disabled="!hasApiKey"
                            :class="[
                                'bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8 text-left transition-all group',
                                hasApiKey ? 'hover:border-emerald-500' : 'opacity-60 cursor-not-allowed'
                            ]"
                        >
                            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center mb-6">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Generate with AI</h3>
                            <p class="text-gray-400 text-sm">
                                Let AI write your article. Just provide a topic and our AI will create engaging content.
                            </p>
                            <div v-if="hasApiKey" class="mt-6 flex items-center text-emerald-400 text-sm font-medium">
                                Generate article
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                            <div v-else class="mt-6">
                                <Link 
                                    :href="route('organization.settings')" 
                                    class="text-yellow-400 text-sm font-medium hover:underline"
                                    @click.stop
                                >
                                    Configure API Key in Settings →
                                </Link>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Manual Creation Form -->
                <div v-else-if="creationMethod === 'manual'">
                    <div class="flex items-center gap-4 mb-8">
                        <button 
                            @click="creationMethod = null"
                            class="text-gray-400 hover:text-white p-2 rounded-lg hover:bg-[#1a1a1a] transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Write Article</h1>
                            <p class="text-gray-400 mt-1">Create your article manually</p>
                        </div>
                    </div>

                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8">
                        <form @submit.prevent="submitManual">
                            <!-- Category Selection -->
                            <div class="mb-6">
                                <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">
                                    Category
                                </label>
                                <select
                                    id="category_id"
                                    v-model="manualForm.category_id"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                >
                                    <option value="">Select a category</option>
                                    <option v-for="category in currentWebsite?.categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="manualForm.errors.category_id" class="mt-2 text-sm text-red-500">{{ manualForm.errors.category_id }}</p>
                            </div>

                            <!-- Title -->
                            <div class="mb-6">
                                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                                    Title *
                                </label>
                                <input
                                    id="title"
                                    v-model="manualForm.title"
                                    type="text"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    required
                                />
                                <p v-if="manualForm.errors.title" class="mt-2 text-sm text-red-500">{{ manualForm.errors.title }}</p>
                            </div>

                            <!-- Slug -->
                            <div class="mb-6">
                                <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">
                                    Slug (URL)
                                </label>
                                <input
                                    id="slug"
                                    v-model="manualForm.slug"
                                    type="text"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="Auto-generated from title"
                                />
                                <p v-if="manualForm.errors.slug" class="mt-2 text-sm text-red-500">{{ manualForm.errors.slug }}</p>
                            </div>

                            <!-- Featured Image -->
                            <div class="mb-6">
                                <label for="featured_image" class="block text-sm font-medium text-gray-300 mb-2">
                                    Featured Image URL
                                </label>
                                <input
                                    id="featured_image"
                                    v-model="manualForm.featured_image"
                                    type="text"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="https://example.com/image.jpg"
                                />
                                <p v-if="manualForm.errors.featured_image" class="mt-2 text-sm text-red-500">{{ manualForm.errors.featured_image }}</p>
                            </div>

                            <!-- Excerpt -->
                            <div class="mb-6">
                                <label for="excerpt" class="block text-sm font-medium text-gray-300 mb-2">
                                    Excerpt
                                </label>
                                <textarea
                                    id="excerpt"
                                    v-model="manualForm.excerpt"
                                    rows="3"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="A brief summary of the article..."
                                ></textarea>
                                <p v-if="manualForm.errors.excerpt" class="mt-2 text-sm text-red-500">{{ manualForm.errors.excerpt }}</p>
                            </div>

                            <!-- Content -->
                            <div class="mb-6">
                                <label for="content" class="block text-sm font-medium text-gray-300 mb-2">
                                    Content *
                                </label>
                                <textarea
                                    id="content"
                                    v-model="manualForm.content"
                                    rows="15"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-sm"
                                    placeholder="Write your article content here... (HTML supported)"
                                    required
                                ></textarea>
                                <p class="mt-1 text-sm text-gray-500">You can use HTML for formatting.</p>
                                <p v-if="manualForm.errors.content" class="mt-2 text-sm text-red-500">{{ manualForm.errors.content }}</p>
                            </div>

                            <!-- Status -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Status *
                                </label>
                                <div class="flex gap-6">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" v-model="manualForm.status" value="draft" class="mr-2 accent-emerald-500" />
                                        <span class="text-gray-300">Draft</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" v-model="manualForm.status" value="published" class="mr-2 accent-emerald-500" />
                                        <span class="text-gray-300">Published</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" v-model="manualForm.status" value="scheduled" class="mr-2 accent-emerald-500" />
                                        <span class="text-gray-300">Scheduled</span>
                                    </label>
                                </div>
                                <p v-if="manualForm.errors.status" class="mt-2 text-sm text-red-500">{{ manualForm.errors.status }}</p>
                            </div>

                            <!-- Published At (for scheduled) -->
                            <div v-if="manualForm.status === 'scheduled'" class="mb-6">
                                <label for="published_at" class="block text-sm font-medium text-gray-300 mb-2">
                                    Publish Date
                                </label>
                                <input
                                    id="published_at"
                                    v-model="manualForm.published_at"
                                    type="datetime-local"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                                <p v-if="manualForm.errors.published_at" class="mt-2 text-sm text-red-500">{{ manualForm.errors.published_at }}</p>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex items-center justify-end gap-4">
                                <button
                                    type="button"
                                    @click="creationMethod = null"
                                    class="px-6 py-2 text-gray-400 hover:text-white transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="manualForm.processing"
                                    class="px-6 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="manualForm.processing">Creating...</span>
                                    <span v-else>Create Article</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Step 2: AI Generation Form -->
                <div v-else-if="creationMethod === 'ai'">
                    <div class="flex items-center gap-4 mb-8">
                        <button 
                            @click="creationMethod = null"
                            class="text-gray-400 hover:text-white p-2 rounded-lg hover:bg-[#1a1a1a] transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Generate with AI</h1>
                            <p class="text-gray-400 mt-1">Let AI create your article</p>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="aiForm.errors.error" class="bg-red-900/50 border border-red-500 text-red-200 px-6 py-4 rounded-lg mb-6">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ aiForm.errors.error }}</span>
                        </div>
                    </div>

                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8">
                        <form @submit.prevent="submitAI" class="space-y-6">
                            <!-- Category Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Category *
                                </label>
                                <select
                                    v-model="aiForm.category_id"
                                    required
                                    :disabled="!currentWebsite?.categories?.length"
                                    class="w-full px-4 py-2 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500 disabled:opacity-50"
                                >
                                    <option value="">Select a category</option>
                                    <option v-for="category in currentWebsite?.categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="aiForm.errors.category_id" class="mt-1 text-sm text-red-500">{{ aiForm.errors.category_id }}</p>
                                <p v-if="!currentWebsite?.categories?.length" class="mt-1 text-sm text-yellow-500">
                                    No categories found. Please create categories for this website first.
                                </p>
                            </div>

                            <!-- Topic -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Article Topic *
                                </label>
                                <input
                                    v-model="aiForm.topic"
                                    type="text"
                                    required
                                    placeholder="e.g., 'Best Italian Pasta Recipes for Beginners'"
                                    class="w-full px-4 py-2 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                <p v-if="aiForm.errors.topic" class="mt-1 text-sm text-red-500">{{ aiForm.errors.topic }}</p>
                                <p class="mt-1 text-xs text-gray-500">Be specific for better results</p>
                            </div>

                            <!-- Settings Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Article Length
                                    </label>
                                    <select
                                        v-model="aiForm.length"
                                        class="w-full px-4 py-2 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
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
                                        v-model="aiForm.tone"
                                        class="w-full px-4 py-2 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
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
                                            v-model="aiForm.auto_publish"
                                            type="checkbox"
                                            class="w-4 h-4 text-emerald-600 bg-[#252525] border-[#3a3a3a] rounded focus:ring-emerald-500"
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
                                    v-model="aiForm.keywords"
                                    type="text"
                                    placeholder="e.g., italian cuisine, pasta, cooking tips"
                                    class="w-full px-4 py-2 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">Separate keywords with commas</p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-[#2a2a2a]">
                                <button
                                    type="button"
                                    @click="creationMethod = null"
                                    class="px-6 py-2 text-gray-400 hover:text-white transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="aiForm.processing"
                                    class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <svg v-if="aiForm.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    {{ aiForm.processing ? 'Generating...' : 'Generate Article' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const page = usePage();

const props = defineProps({
    currentWebsite: Object,
    websites: Array,
    preselectedWebsite: [String, Number],
    hasApiKey: {
        type: Boolean,
        default: false
    },
    defaultTone: {
        type: String,
        default: 'conversational'
    }
});

const creationMethod = ref(null);

// Manual form
const manualForm = useForm({
    category_id: '',
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    featured_image: '',
    status: 'draft',
    generation_type: 'manual',
    published_at: ''
});

// AI form
const aiForm = useForm({
    category_id: '',
    topic: '',
    tone: props.defaultTone,
    length: 'medium',
    keywords: '',
    auto_publish: false
});

const submitManual = () => {
    manualForm.post(route('superadmin.articles.store', { website: props.currentWebsite?.id }));
};

const submitAI = () => {
    aiForm.post(route('superadmin.ai-articles.generate', { website: props.currentWebsite?.id }));
};
</script>
