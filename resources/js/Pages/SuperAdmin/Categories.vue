<template>
    <Head title="Categories" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Categories</h1>
                    <p class="text-gray-400 mt-1">Organize your articles by category</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Category
                </button>
            </div>

            <!-- Categories List -->
            <div v-if="categories.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden hover:border-emerald-500 transition-colors"
                >
                    <div class="h-32 bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                        <img v-if="category.image" :src="category.image" :alt="category.name" class="h-full w-full object-cover" />
                        <span v-else class="text-4xl font-bold text-white">{{ category.name.charAt(0) }}</span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-white mb-2">{{ category.name }}</h3>
                        <p v-if="category.description" class="text-gray-400 text-sm mb-3 line-clamp-2">
                            {{ category.description }}
                        </p>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-xs px-2 py-1 rounded bg-gray-800 text-gray-300">
                                {{ category.website.name }}
                            </span>
                            <span :class="[
                                'text-xs px-2 py-1 rounded',
                                category.is_active ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300'
                            ]">
                                {{ category.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="openEditModal(category)"
                                class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteCategory(category)"
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <p class="text-gray-400 text-lg">No categories yet</p>
                <p class="text-gray-500 text-sm mt-2">Create your first category to organize articles</p>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">
                        {{ editingCategory ? 'Edit Category' : 'Add New Category' }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-6">
                    <!-- Website Selection -->
                    <div v-if="!editingCategory">
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

                    <!-- Category Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Category Name *
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="e.g., Comfort Classics"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
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
                            placeholder="Auto-generated from name if empty"
                        />
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-500">{{ form.errors.slug }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="Brief description of this category"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- Image URL -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Image URL
                        </label>
                        <input
                            v-model="form.image"
                            type="url"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="https://example.com/image.jpg"
                        />
                        <p v-if="form.errors.image" class="mt-1 text-sm text-red-500">{{ form.errors.image }}</p>
                    </div>

                    <!-- Order -->
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
                        <p v-if="form.errors.order" class="mt-1 text-sm text-red-500">{{ form.errors.order }}</p>
                    </div>

                    <!-- Is Active -->
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
                            {{ form.processing ? 'Saving...' : (editingCategory ? 'Update Category' : 'Create Category') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    },
    websites: {
        type: Array,
        default: () => []
    }
});

const showModal = ref(false);
const editingCategory = ref(null);

const form = useForm({
    website_id: '',
    name: '',
    slug: '',
    description: '',
    image: '',
    order: 0,
    is_active: true
});

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = category;
    form.website_id = category.website_id;
    form.name = category.name;
    form.slug = category.slug;
    form.description = category.description || '';
    form.image = category.image || '';
    form.order = category.order;
    form.is_active = category.is_active;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (editingCategory.value) {
        form.put(route('superadmin.categories.update', editingCategory.value.id), {
            onSuccess: () => {
                closeModal();
            }
        });
    } else {
        form.post(route('superadmin.categories.store'), {
            onSuccess: () => {
                closeModal();
            }
        });
    }
};

const deleteCategory = (category) => {
    if (confirm(`Are you sure you want to delete "${category.name}"? This will also remove it from all articles.`)) {
        router.delete(route('superadmin.categories.destroy', category.id), {
            preserveScroll: true
        });
    }
};
</script>
