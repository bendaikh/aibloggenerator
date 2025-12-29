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

                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8">
                    <h1 class="text-3xl font-bold text-white mb-8">Create New Article</h1>

                    <form @submit.prevent="submit">
                        <!-- Category Selection -->
                        <div class="mb-6">
                            <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">
                                Category
                            </label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <option value="">Select a category</option>
                                <option v-for="category in currentWebsite?.categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-500">{{ form.errors.category_id }}</p>
                        </div>

                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                                Title *
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                required
                            />
                            <p v-if="form.errors.title" class="mt-2 text-sm text-red-500">{{ form.errors.title }}</p>
                        </div>

                        <!-- Slug -->
                        <div class="mb-6">
                            <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">
                                Slug (URL)
                            </label>
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="Auto-generated from title"
                            />
                            <p v-if="form.errors.slug" class="mt-2 text-sm text-red-500">{{ form.errors.slug }}</p>
                        </div>

                        <!-- Featured Image -->
                        <div class="mb-6">
                            <label for="featured_image" class="block text-sm font-medium text-gray-300 mb-2">
                                Featured Image URL
                            </label>
                            <input
                                id="featured_image"
                                v-model="form.featured_image"
                                type="text"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="https://example.com/image.jpg"
                            />
                            <p v-if="form.errors.featured_image" class="mt-2 text-sm text-red-500">{{ form.errors.featured_image }}</p>
                        </div>

                        <!-- Excerpt -->
                        <div class="mb-6">
                            <label for="excerpt" class="block text-sm font-medium text-gray-300 mb-2">
                                Excerpt
                            </label>
                            <textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                rows="3"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="A brief summary of the article..."
                            ></textarea>
                            <p v-if="form.errors.excerpt" class="mt-2 text-sm text-red-500">{{ form.errors.excerpt }}</p>
                        </div>

                        <!-- Content -->
                        <div class="mb-6">
                            <label for="content" class="block text-sm font-medium text-gray-300 mb-2">
                                Content *
                            </label>
                            <textarea
                                id="content"
                                v-model="form.content"
                                rows="15"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-sm"
                                placeholder="Write your article content here... (HTML supported)"
                                required
                            ></textarea>
                            <p class="mt-1 text-sm text-gray-500">You can use HTML for formatting.</p>
                            <p v-if="form.errors.content" class="mt-2 text-sm text-red-500">{{ form.errors.content }}</p>
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Status *
                            </label>
                            <div class="flex gap-6">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" v-model="form.status" value="draft" class="mr-2 accent-emerald-500" />
                                    <span class="text-gray-300">Draft</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" v-model="form.status" value="published" class="mr-2 accent-emerald-500" />
                                    <span class="text-gray-300">Published</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" v-model="form.status" value="scheduled" class="mr-2 accent-emerald-500" />
                                    <span class="text-gray-300">Scheduled</span>
                                </label>
                            </div>
                            <p v-if="form.errors.status" class="mt-2 text-sm text-red-500">{{ form.errors.status }}</p>
                        </div>

                        <!-- Published At (for scheduled) -->
                        <div v-if="form.status === 'scheduled'" class="mb-6">
                            <label for="published_at" class="block text-sm font-medium text-gray-300 mb-2">
                                Publish Date
                            </label>
                            <input
                                id="published_at"
                                v-model="form.published_at"
                                type="datetime-local"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                            <p v-if="form.errors.published_at" class="mt-2 text-sm text-red-500">{{ form.errors.published_at }}</p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="route('superadmin.articles.index', { website: currentWebsite?.id })"
                                class="px-6 py-2 text-gray-400 hover:text-white transition"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Article</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const page = usePage();

const props = defineProps({
    currentWebsite: Object,
    websites: Array,
    preselectedWebsite: [String, Number]
});

const form = useForm({
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

const submit = () => {
    form.post(route('superadmin.articles.store', { website: props.currentWebsite?.id }));
};
</script>
