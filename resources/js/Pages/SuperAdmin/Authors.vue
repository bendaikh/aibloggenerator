<template>
    <Head title="Authors" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Authors</h1>
                    <p class="text-gray-400 mt-1">Manage article authors for {{ currentWebsite?.name }}</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Author
                </button>
            </div>

            <!-- Authors List -->
            <div v-if="authors.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="author in authors"
                    :key="author.id"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden hover:border-emerald-500 transition-colors"
                >
                    <div class="h-48 bg-[#0a0a0a] flex items-center justify-center border-b border-[#2a2a2a]">
                        <img v-if="author.image" :src="author.image" :alt="author.name" class="h-full w-full object-cover" />
                        <div v-else class="text-center">
                            <div class="w-20 h-20 bg-emerald-500/10 rounded-full flex items-center justify-center mx-auto mb-2">
                                <span class="text-3xl font-bold text-emerald-500">{{ author.name.charAt(0) }}</span>
                            </div>
                            <span class="text-gray-600 text-sm font-medium">{{ author.name }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-white mb-1">{{ author.name }}</h3>
                        <p v-if="author.email" class="text-emerald-500 text-sm mb-3 truncate">{{ author.email }}</p>
                        <p v-if="author.description" class="text-gray-400 text-sm mb-4 line-clamp-2">
                            {{ author.description }}
                        </p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <span :class="[
                                'text-xs px-2 py-1 rounded',
                                author.is_active ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300'
                            ]">
                                {{ author.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        
                        <div class="flex gap-2">
                            <button
                                @click="openEditModal(author)"
                                class="flex-1 px-3 py-2 bg-[#252525] hover:bg-[#303030] text-white rounded-lg text-sm transition-colors"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteAuthor(author)"
                                class="px-3 py-2 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white rounded-lg text-sm transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-gray-400 text-lg">No authors yet</p>
                <p class="text-gray-500 text-sm mt-2">Add your first author to get started</p>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">
                        {{ editingAuthor ? 'Edit Author' : 'Add New Author' }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-6">
                            <!-- Author Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Full Name *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                    placeholder="John Doe"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Email Address
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                    placeholder="john@example.com"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                            </div>
                            
                            <!-- Is Active -->
                            <div class="flex items-center pt-2">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    id="is_active"
                                    class="w-4 h-4 text-emerald-600 bg-[#0a0a0a] border-[#2a2a2a] rounded focus:ring-emerald-500"
                                />
                                <label for="is_active" class="ml-2 text-sm text-gray-300">
                                    Active Author
                                </label>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Image Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Author Profile Photo
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
                                            <span class="text-white text-sm font-medium">Change Photo</span>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <svg class="w-10 h-10 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
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
                        </div>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Biography / Brief Description
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="Tell us something about the author..."
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
                            {{ form.processing ? 'Saving...' : (editingAuthor ? 'Update Author' : 'Create Author') }}
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
    authors: {
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
const editingAuthor = ref(null);
const imagePreview = ref(null);

const form = useForm({
    name: '',
    email: '',
    description: '',
    image: null,
    image_file: null,
    is_active: true,
    _method: 'POST'
});

const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (editingAuthor.value) {
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
    editingAuthor.value = null;
    form.reset();
    form.clearErrors();
    form._method = 'POST';
    imagePreview.value = null;
    showModal.value = true;
};

const openEditModal = (author) => {
    editingAuthor.value = author;
    form.name = author.name;
    form.email = author.email || '';
    form.description = author.description || '';
    form.image = null;
    form.image_file = null;
    form.is_active = author.is_active;
    form._method = 'PUT';
    form.clearErrors();
    imagePreview.value = author.image;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingAuthor.value = null;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
};

const submitForm = () => {
    if (editingAuthor.value) {
        form.post(route('superadmin.authors.update', { 
            website: props.currentWebsite?.id, 
            author: editingAuthor.value.id 
        }), {
            onSuccess: () => {
                closeModal();
            },
            forceFormData: true
        });
    } else {
        form.post(route('superadmin.authors.store', { website: props.currentWebsite?.id }), {
            onSuccess: () => {
                closeModal();
            },
            forceFormData: true
        });
    }
};

const deleteAuthor = (author) => {
    if (confirm(`Are you sure you want to delete author "${author.name}"?`)) {
        router.delete(route('superadmin.authors.destroy', { 
            website: props.currentWebsite?.id, 
            author: author.id 
        }), {
            preserveScroll: true
        });
    }
};
</script>
