<template>
    <Head title="Categories" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Categories</h1>
                    <p class="text-gray-400 mt-1">Organize articles for {{ currentWebsite?.name }}</p>
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
                    <div class="h-48 bg-[#0a0a0a] flex items-center justify-center border-b border-[#2a2a2a]">
                        <img v-if="category.image" :src="category.image" :alt="category.name" class="h-full w-full object-cover" />
                        <div v-else class="text-center">
                            <svg class="w-12 h-12 text-gray-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-gray-600 text-sm font-medium">{{ category.name }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-white mb-2">{{ category.name }}</h3>
                        <p v-if="category.description" class="text-gray-400 text-sm mb-3 line-clamp-2">
                            {{ category.description }}
                        </p>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-xs px-2 py-1 rounded bg-gray-800 text-gray-300">
                                {{ category.articles_count || 0 }} articles
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-6">
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
                            
                            <!-- Display Order -->
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
                        </div>

                        <div class="space-y-6">
                            <!-- Image Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Category Image
                                </label>
                                <div 
                                    @click="$refs.imageInput.click()"
                                    class="w-full h-40 bg-[#0a0a0a] border-2 border-dashed border-[#2a2a2a] rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-emerald-500 transition-colors group relative overflow-hidden"
                                >
                                    <template v-if="imagePreview">
                                        <img :src="imagePreview" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-30 transition-opacity" />
                                        <div class="relative z-10 text-center">
                                            <svg class="w-8 h-8 text-white mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            <span class="text-white text-sm font-medium">Change Image</span>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <svg class="w-10 h-10 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gray-500 text-sm">Upload PNG, JPG (Max 2MB)</span>
                                    </template>
                                    
                                    <input 
                                        type="file" 
                                        ref="imageInput" 
                                        class="hidden" 
                                        accept="image/*"
                                        @change="handleImageUpload"
                                    />
                                </div>
                                <p v-if="form.errors.image" class="mt-1 text-sm text-red-500">{{ form.errors.image }}</p>
                                <p v-if="form.errors.image_file" class="mt-1 text-sm text-red-500">{{ form.errors.image_file }}</p>
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
                        </div>
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
import { ref } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    },
    currentWebsite: {
        type: Object,
        default: null
    },
    websites: {
        type: Array,
        default: () => []
    }
});

const showModal = ref(false);
const editingCategory = ref(null);
const imagePreview = ref(null);

const form = useForm({
    name: '',
    slug: '',
    description: '',
    image: null,
    image_file: null, // Used for updates
    order: 0,
    is_active: true,
    _method: 'POST' // For method spoofing with multipart/form-data
});

const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (editingCategory.value) {
            form.image_file = file;
        } else {
            form.image = file;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    form._method = 'POST';
    imagePreview.value = null;
    showModal.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.slug = category.slug;
    form.description = category.description || '';
    form.image = null; 
    form.image_file = null;
    form.order = category.order;
    form.is_active = category.is_active;
    form._method = 'PUT';
    form.clearErrors();
    imagePreview.value = category.image;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
};

const submitForm = () => {
    if (editingCategory.value) {
        // Inertia.js doesn't support PUT with Files directly in some versions/configs,
        // so we use POST with _method spoofing
        form.post(route('superadmin.categories.update', { 
            website: props.currentWebsite?.id, 
            category: editingCategory.value.id 
        }), {
            onSuccess: () => {
                closeModal();
            },
            forceFormData: true
        });
    } else {
        form.post(route('superadmin.categories.store', { website: props.currentWebsite?.id }), {
            onSuccess: () => {
                closeModal();
            },
            forceFormData: true
        });
    }
};

const deleteCategory = (category) => {
    if (confirm(`Are you sure you want to delete "${category.name}"? This will also remove it from all articles.`)) {
        router.delete(route('superadmin.categories.destroy', { 
            website: props.currentWebsite?.id, 
            category: category.id 
        }), {
            preserveScroll: true
        });
    }
};
</script>

