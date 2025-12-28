<template>
    <SuperAdminLayout>
        <Head title="Create Article" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <Link :href="route('superadmin.articles.index')" class="text-teal-600 hover:text-teal-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Articles
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-8">Create New Article</h1>

                    <form @submit.prevent="submit">
                        <!-- Website Selection -->
                        <div class="mb-6">
                            <label for="website_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Website *
                            </label>
                            <select
                                id="website_id"
                                v-model="form.website_id"
                                @change="onWebsiteChange"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                required
                            >
                                <option value="">Select a website</option>
                                <option v-for="website in websites" :key="website.id" :value="website.id">
                                    {{ website.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.website_id" class="mt-2 text-sm text-red-600">{{ form.errors.website_id }}</p>
                        </div>

                        <!-- Category Selection -->
                        <div class="mb-6">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Category
                            </label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            >
                                <option value="">Select a category</option>
                                <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-600">{{ form.errors.category_id }}</p>
                        </div>

                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Title *
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                required
                            />
                            <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <!-- Slug -->
                        <div class="mb-6">
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                Slug (URL)
                            </label>
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                placeholder="Auto-generated from title"
                            />
                            <p v-if="form.errors.slug" class="mt-2 text-sm text-red-600">{{ form.errors.slug }}</p>
                        </div>

                        <!-- Featured Image -->
                        <div class="mb-6">
                            <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
                                Featured Image URL
                            </label>
                            <input
                                id="featured_image"
                                v-model="form.featured_image"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                placeholder="https://example.com/image.jpg"
                            />
                            <p v-if="form.errors.featured_image" class="mt-2 text-sm text-red-600">{{ form.errors.featured_image }}</p>
                        </div>

                        <!-- Excerpt -->
                        <div class="mb-6">
                            <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
                                Excerpt
                            </label>
                            <textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                placeholder="A brief summary of the article..."
                            ></textarea>
                            <p v-if="form.errors.excerpt" class="mt-2 text-sm text-red-600">{{ form.errors.excerpt }}</p>
                        </div>

                        <!-- Content -->
                        <div class="mb-6">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                Content *
                            </label>
                            <textarea
                                id="content"
                                v-model="form.content"
                                rows="15"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent font-mono text-sm"
                                placeholder="Write your article content here... (HTML supported)"
                                required
                            ></textarea>
                            <p class="mt-1 text-sm text-gray-500">You can use HTML for formatting.</p>
                            <p v-if="form.errors.content" class="mt-2 text-sm text-red-600">{{ form.errors.content }}</p>
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status *
                            </label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" v-model="form.status" value="draft" class="mr-2" />
                                    <span class="text-gray-700">Draft</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" v-model="form.status" value="published" class="mr-2" />
                                    <span class="text-gray-700">Published</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" v-model="form.status" value="scheduled" class="mr-2" />
                                    <span class="text-gray-700">Scheduled</span>
                                </label>
                            </div>
                            <p v-if="form.errors.status" class="mt-2 text-sm text-red-600">{{ form.errors.status }}</p>
                        </div>

                        <!-- Published At (for scheduled) -->
                        <div v-if="form.status === 'scheduled'" class="mb-6">
                            <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">
                                Publish Date
                            </label>
                            <input
                                id="published_at"
                                v-model="form.published_at"
                                type="datetime-local"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            />
                            <p v-if="form.errors.published_at" class="mt-2 text-sm text-red-600">{{ form.errors.published_at }}</p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="route('superadmin.articles.index')"
                                class="px-6 py-2 text-gray-700 hover:text-gray-900 transition"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
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
import { Head, Link, useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const props = defineProps({
    websites: Array,
    preselectedWebsite: [String, Number]
});

const form = useForm({
    website_id: props.preselectedWebsite || '',
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

const availableCategories = computed(() => {
    if (!form.website_id) return [];
    const website = props.websites.find(w => w.id === parseInt(form.website_id));
    return website?.categories || [];
});

const onWebsiteChange = () => {
    form.category_id = '';
};

const submit = () => {
    form.post(route('superadmin.articles.store'));
};
</script>

