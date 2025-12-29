<template>
    <Head title="Generate AI Article" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">AI Article Generator</h1>
                <p class="text-gray-400">Generate articles for {{ currentWebsite?.name }} using AI (GPT-4)</p>
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

            <!-- Generation Form -->
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8 mb-8">
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Category Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Category *
                        </label>
                        <select
                            v-model="form.category_id"
                            required
                            :disabled="!currentWebsite?.categories?.length"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500 disabled:opacity-50"
                        >
                            <option value="">Select a category</option>
                            <option v-for="category in currentWebsite?.categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-500">{{ form.errors.category_id }}</p>
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
                            v-model="form.topic"
                            type="text"
                            required
                            placeholder="e.g., 'Best Italian Pasta Recipes for Beginners'"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                        />
                        <p v-if="form.errors.topic" class="mt-1 text-sm text-red-500">{{ form.errors.topic }}</p>
                        <p class="mt-1 text-xs text-gray-500">Be specific for better results</p>
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
                                <option value="medium" selected>Medium (1000-1500 words)</option>
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
                                <option value="conversational" selected>Conversational</option>
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

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4 border-t border-[#2a2a2a]">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            {{ form.processing ? 'Generating...' : 'Generate Article with AI' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent AI Articles -->
            <div v-if="recentArticles.length > 0">
                <h2 class="text-2xl font-bold text-white mb-4">Recent AI-Generated Articles</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link
                        v-for="article in recentArticles"
                        :key="article.id"
                        :href="route('superadmin.articles.edit', { website: currentWebsite?.id, article: article.id })"
                        class="bg-[#1a1a1a] rounded-lg border border-[#2a2a2a] p-4 hover:border-emerald-500 transition-colors"
                    >
                        <h3 class="text-white font-semibold mb-2 line-clamp-2">{{ article.title }}</h3>
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            <span class="px-2 py-1 bg-emerald-900 text-emerald-300 rounded text-xs">AI</span>
                            <span>{{ article.category?.name }}</span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            {{ new Date(article.created_at).toLocaleDateString() }}
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: null
    },
    websites: {
        type: Array,
        default: () => []
    },
    recentArticles: {
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

const form = useForm({
    category_id: '',
    topic: '',
    tone: props.defaultTone,
    length: 'medium',
    keywords: '',
    auto_publish: false
});

const submitForm = () => {
    form.post(route('superadmin.ai-articles.generate', { website: props.currentWebsite?.id }), {
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>
