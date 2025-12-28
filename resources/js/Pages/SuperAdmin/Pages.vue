<template>
    <Head title="Pages" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Pages</h1>
                    <p class="text-gray-400 mt-1">Manage your website pages</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Page
                </button>
            </div>

            <!-- Pages List -->
            <div v-if="pages.length > 0" class="space-y-4">
                <div
                    v-for="page in pages"
                    :key="page.id"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 hover:border-emerald-500 transition-colors"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-white mb-2">{{ page.title }}</h3>
                            <p v-if="page.excerpt" class="text-gray-400 text-sm mb-3 line-clamp-2">
                                {{ page.excerpt }}
                            </p>
                            <div class="flex items-center gap-3 mb-3">
                                <span class="text-xs px-2 py-1 rounded bg-gray-800 text-gray-300">
                                    {{ page.website.name }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    /{{ page.slug }}
                                </span>
                                <span :class="[
                                    'text-xs px-2 py-1 rounded',
                                    page.is_active ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300'
                                ]">
                                    {{ page.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span v-if="page.show_in_menu" class="text-xs px-2 py-1 rounded bg-blue-900 text-blue-300">
                                    In Menu
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <a
                                :href="page.url"
                                target="_blank"
                                class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition-colors"
                                title="View Page"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                            <button
                                @click="openEditModal(page)"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors"
                            >
                                Edit
                            </button>
                            <button
                                @click="deletePage(page)"
                                class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <p class="text-gray-400 text-lg">No pages yet</p>
                <p class="text-gray-500 text-sm mt-2">Create your first page to get started</p>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">
                        {{ editingPage ? 'Edit Page' : 'Create New Page' }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-6">
                    <!-- Website Selection -->
                    <div v-if="!editingPage">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Website *
                        </label>
                        <select
                            v-model="form.website_id"
                            required
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="">Select a website</option>
                            <option v-for="website in websites" :key="website.id" :value="website.id">
                                {{ website.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.website_id" class="mt-1 text-sm text-red-500">{{ form.errors.website_id }}</p>
                    </div>

                    <!-- Page Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Page Title *
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="e.g., About Us"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Slug (optional)
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="Auto-generated from title if empty"
                        />
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-500">{{ form.errors.slug }}</p>
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Excerpt
                        </label>
                        <textarea
                            v-model="form.excerpt"
                            rows="2"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="Brief summary of the page"
                        ></textarea>
                        <p v-if="form.errors.excerpt" class="mt-1 text-sm text-red-500">{{ form.errors.excerpt }}</p>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Content
                        </label>
                        <textarea
                            v-model="form.content"
                            rows="10"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500 font-mono text-sm"
                            placeholder="Page content (HTML supported)"
                        ></textarea>
                        <p v-if="form.errors.content" class="mt-1 text-sm text-red-500">{{ form.errors.content }}</p>
                    </div>

                    <!-- Featured Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Featured Image URL
                        </label>
                        <input
                            v-model="form.featured_image"
                            type="url"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="https://example.com/image.jpg"
                        />
                        <p v-if="form.errors.featured_image" class="mt-1 text-sm text-red-500">{{ form.errors.featured_image }}</p>
                    </div>

                    <!-- SEO Meta -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Meta Title
                            </label>
                            <input
                                v-model="form.meta_title"
                                type="text"
                                class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                placeholder="SEO title"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Display Order
                            </label>
                            <input
                                v-model.number="form.order"
                                type="number"
                                class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                placeholder="0"
                            />
                        </div>
                    </div>

                    <!-- Meta Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Meta Description
                        </label>
                        <textarea
                            v-model="form.meta_description"
                            rows="2"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="SEO description"
                        ></textarea>
                    </div>

                    <!-- Checkboxes -->
                    <div class="flex gap-6">
                        <div class="flex items-center">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                id="is_active"
                                class="w-4 h-4 text-emerald-600 bg-[#0a0a0a] border-[#2a2a2a] rounded focus:ring-emerald-500"
                            />
                            <label for="is_active" class="ml-2 text-sm text-gray-300">
                                Active (visible on website)
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input
                                v-model="form.show_in_menu"
                                type="checkbox"
                                id="show_in_menu"
                                class="w-4 h-4 text-emerald-600 bg-[#0a0a0a] border-[#2a2a2a] rounded focus:ring-emerald-500"
                            />
                            <label for="show_in_menu" class="ml-2 text-sm text-gray-300">
                                Show in navigation menu
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-[#2a2a2a]">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-gray-300 hover:text-white transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium transition-colors disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : (editingPage ? 'Update Page' : 'Create Page') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    pages: {
        type: Array,
        default: () => []
    },
    websites: {
        type: Array,
        default: () => []
    }
});

const showModal = ref(false);
const editingPage = ref(null);

const form = useForm({
    website_id: '',
    title: '',
    slug: '',
    content: '',
    excerpt: '',
    featured_image: '',
    meta_title: '',
    meta_description: '',
    order: 0,
    is_active: true,
    show_in_menu: true
});

const openCreateModal = () => {
    editingPage.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (page) => {
    editingPage.value = page;
    form.website_id = page.website_id;
    form.title = page.title;
    form.slug = page.slug;
    form.content = page.content || '';
    form.excerpt = page.excerpt || '';
    form.featured_image = page.featured_image || '';
    form.meta_title = page.meta_title || '';
    form.meta_description = page.meta_description || '';
    form.order = page.order;
    form.is_active = page.is_active;
    form.show_in_menu = page.show_in_menu;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingPage.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (editingPage.value) {
        form.put(route('superadmin.pages.update', editingPage.value.id), {
            onSuccess: () => {
                closeModal();
            }
        });
    } else {
        form.post(route('superadmin.pages.store'), {
            onSuccess: () => {
                closeModal();
            }
        });
    }
};

const deletePage = (page) => {
    if (confirm(`Are you sure you want to delete "${page.title}"? This action cannot be undone.`)) {
        router.delete(route('superadmin.pages.destroy', page.id), {
            preserveScroll: true
        });
    }
};
</script>
