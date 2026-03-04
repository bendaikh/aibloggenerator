<script setup>
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    themes: Array,
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingTheme = ref(null);
const deletingTheme = ref(null);

const createForm = useForm({
    name: '',
    slug: '',
    description: '',
    show_recipe_sections: true,
    is_active: true,
    is_public: true,
});

const editForm = useForm({
    name: '',
    slug: '',
    description: '',
    show_recipe_sections: true,
    is_active: true,
    is_public: true,
});

const openCreateModal = () => {
    createForm.reset();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
};

const submitCreate = () => {
    createForm.post(route('organization.themes.store'), {
        onSuccess: () => {
            closeCreateModal();
        },
    });
};

const openEditModal = (theme) => {
    editingTheme.value = theme;
    editForm.name = theme.name;
    editForm.slug = theme.slug;
    editForm.description = theme.description;
    editForm.show_recipe_sections = theme.show_recipe_sections;
    editForm.is_active = theme.is_active;
    editForm.is_public = theme.is_public;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingTheme.value = null;
    editForm.reset();
};

const submitEdit = () => {
    editForm.put(route('organization.themes.update', editingTheme.value.id), {
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const openDeleteModal = (theme) => {
    deletingTheme.value = theme;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deletingTheme.value = null;
};

const confirmDelete = () => {
    if (deletingTheme.value) {
        useForm({}).delete(route('organization.themes.destroy', deletingTheme.value.id), {
            onSuccess: () => {
                closeDeleteModal();
            },
        });
    }
};

const generateSlug = (name) => {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
};
</script>

<template>
    <Head title="Theme Management" />

    <OrganizationLayout>
        <div class="p-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Theme Management</h1>
                    <p class="text-gray-400 mt-1">Manage themes and their visibility for users</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-pink-600 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Theme
                </button>
            </div>

            <!-- Info Box -->
            <div class="mb-8 bg-blue-500/10 border border-blue-500/50 rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-2">Theme Visibility</h4>
                        <ul class="text-gray-300 text-sm space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 flex-shrink-0">•</span>
                                <span><strong>Public Themes:</strong> Available to all users when creating websites</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 flex-shrink-0">•</span>
                                <span><strong>Private Themes:</strong> Only visible to superadmins (useful for testing or exclusive themes)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 flex-shrink-0">•</span>
                                <span><strong>Active Status:</strong> Inactive themes are hidden from everyone</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Themes Table -->
            <div class="bg-[#1a1a1a] rounded-2xl border-2 border-[#2a2a2a] overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#2a2a2a]">
                            <th class="text-left p-4 text-gray-400 font-semibold">Theme</th>
                            <th class="text-left p-4 text-gray-400 font-semibold">Recipe Sections</th>
                            <th class="text-center p-4 text-gray-400 font-semibold">Visibility</th>
                            <th class="text-center p-4 text-gray-400 font-semibold">Status</th>
                            <th class="text-center p-4 text-gray-400 font-semibold">Websites</th>
                            <th class="text-right p-4 text-gray-400 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="theme in themes" 
                            :key="theme.id"
                            class="border-b border-[#2a2a2a] hover:bg-[#222222] transition"
                        >
                            <td class="p-4">
                                <div>
                                    <div class="text-white font-semibold">{{ theme.name }}</div>
                                    <div class="text-gray-400 text-sm">{{ theme.slug }}</div>
                                    <div v-if="theme.description" class="text-gray-500 text-xs mt-1">{{ theme.description }}</div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span 
                                    :class="[
                                        'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium',
                                        theme.show_recipe_sections 
                                            ? 'bg-emerald-500/10 text-emerald-400' 
                                            : 'bg-gray-500/10 text-gray-400'
                                    ]"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path 
                                            stroke-linecap="round" 
                                            stroke-linejoin="round" 
                                            stroke-width="2" 
                                            :d="theme.show_recipe_sections ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'" 
                                        />
                                    </svg>
                                    {{ theme.show_recipe_sections ? 'Shown' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span 
                                    :class="[
                                        'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium',
                                        theme.is_public 
                                            ? 'bg-blue-500/10 text-blue-400' 
                                            : 'bg-orange-500/10 text-orange-400'
                                    ]"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path 
                                            v-if="theme.is_public"
                                            stroke-linecap="round" 
                                            stroke-linejoin="round" 
                                            stroke-width="2" 
                                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                        <path 
                                            v-else
                                            stroke-linecap="round" 
                                            stroke-linejoin="round" 
                                            stroke-width="2" 
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        />
                                    </svg>
                                    {{ theme.is_public ? 'Public' : 'Private' }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span 
                                    :class="[
                                        'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium',
                                        theme.is_active 
                                            ? 'bg-green-500/10 text-green-400' 
                                            : 'bg-red-500/10 text-red-400'
                                    ]"
                                >
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    {{ theme.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="text-white font-medium">{{ theme.websites_count || 0 }}</span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEditModal(theme)"
                                        class="p-2 text-blue-400 hover:bg-blue-500/10 rounded-lg transition"
                                        title="Edit theme"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="openDeleteModal(theme)"
                                        class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition"
                                        title="Delete theme"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border-2 border-[#2a2a2a] max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">Create New Theme</h2>
                </div>
                <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                    <div>
                        <label class="block text-gray-300 font-medium mb-2">Theme Name *</label>
                        <input
                            v-model="createForm.name"
                            @input="createForm.slug = generateSlug(createForm.name)"
                            type="text"
                            required
                            class="w-full px-4 py-3 bg-[#0f0f0f] border border-[#2a2a2a] rounded-lg text-white focus:border-purple-500 focus:outline-none"
                            placeholder="Recipe Theme"
                        />
                    </div>
                    <div>
                        <label class="block text-gray-300 font-medium mb-2">Slug *</label>
                        <input
                            v-model="createForm.slug"
                            type="text"
                            required
                            class="w-full px-4 py-3 bg-[#0f0f0f] border border-[#2a2a2a] rounded-lg text-white focus:border-purple-500 focus:outline-none"
                            placeholder="recipe-theme"
                        />
                    </div>
                    <div>
                        <label class="block text-gray-300 font-medium mb-2">Description</label>
                        <textarea
                            v-model="createForm.description"
                            rows="3"
                            class="w-full px-4 py-3 bg-[#0f0f0f] border border-[#2a2a2a] rounded-lg text-white focus:border-purple-500 focus:outline-none"
                            placeholder="A beautiful theme for recipe websites..."
                        ></textarea>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-[#0f0f0f] rounded-lg">
                        <input
                            v-model="createForm.show_recipe_sections"
                            type="checkbox"
                            id="create-show-recipe"
                            class="w-5 h-5 text-purple-500 bg-[#1a1a1a] border-[#2a2a2a] rounded focus:ring-purple-500 focus:ring-2"
                        />
                        <label for="create-show-recipe" class="text-gray-300">Show Recipe Sections (ingredients & instructions)</label>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-[#0f0f0f] rounded-lg">
                        <input
                            v-model="createForm.is_active"
                            type="checkbox"
                            id="create-is-active"
                            class="w-5 h-5 text-green-500 bg-[#1a1a1a] border-[#2a2a2a] rounded focus:ring-green-500 focus:ring-2"
                        />
                        <label for="create-is-active" class="text-gray-300">Active (visible when enabled)</label>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                        <input
                            v-model="createForm.is_public"
                            type="checkbox"
                            id="create-is-public"
                            class="w-5 h-5 text-blue-500 bg-[#1a1a1a] border-[#2a2a2a] rounded focus:ring-blue-500 focus:ring-2"
                        />
                        <label for="create-is-public" class="text-blue-300">
                            <strong>Public</strong> - Available to all users
                            <span class="block text-xs text-blue-400 mt-1">Uncheck to make this theme private (superadmins only)</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeCreateModal"
                            class="px-6 py-3 bg-[#2a2a2a] text-white rounded-lg hover:bg-[#333333] transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition disabled:opacity-50"
                        >
                            {{ createForm.processing ? 'Creating...' : 'Create Theme' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border-2 border-[#2a2a2a] max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">Edit Theme</h2>
                </div>
                <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-gray-300 font-medium mb-2">Theme Name *</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            required
                            class="w-full px-4 py-3 bg-[#0f0f0f] border border-[#2a2a2a] rounded-lg text-white focus:border-purple-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-gray-300 font-medium mb-2">Slug *</label>
                        <input
                            v-model="editForm.slug"
                            type="text"
                            required
                            class="w-full px-4 py-3 bg-[#0f0f0f] border border-[#2a2a2a] rounded-lg text-white focus:border-purple-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-gray-300 font-medium mb-2">Description</label>
                        <textarea
                            v-model="editForm.description"
                            rows="3"
                            class="w-full px-4 py-3 bg-[#0f0f0f] border border-[#2a2a2a] rounded-lg text-white focus:border-purple-500 focus:outline-none"
                        ></textarea>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-[#0f0f0f] rounded-lg">
                        <input
                            v-model="editForm.show_recipe_sections"
                            type="checkbox"
                            id="edit-show-recipe"
                            class="w-5 h-5 text-purple-500 bg-[#1a1a1a] border-[#2a2a2a] rounded focus:ring-purple-500 focus:ring-2"
                        />
                        <label for="edit-show-recipe" class="text-gray-300">Show Recipe Sections (ingredients & instructions)</label>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-[#0f0f0f] rounded-lg">
                        <input
                            v-model="editForm.is_active"
                            type="checkbox"
                            id="edit-is-active"
                            class="w-5 h-5 text-green-500 bg-[#1a1a1a] border-[#2a2a2a] rounded focus:ring-green-500 focus:ring-2"
                        />
                        <label for="edit-is-active" class="text-gray-300">Active (visible when enabled)</label>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                        <input
                            v-model="editForm.is_public"
                            type="checkbox"
                            id="edit-is-public"
                            class="w-5 h-5 text-blue-500 bg-[#1a1a1a] border-[#2a2a2a] rounded focus:ring-blue-500 focus:ring-2"
                        />
                        <label for="edit-is-public" class="text-blue-300">
                            <strong>Public</strong> - Available to all users
                            <span class="block text-xs text-blue-400 mt-1">Uncheck to make this theme private (superadmins only)</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="px-6 py-3 bg-[#2a2a2a] text-white rounded-lg hover:bg-[#333333] transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition disabled:opacity-50"
                        >
                            {{ editForm.processing ? 'Updating...' : 'Update Theme' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border-2 border-red-500/50 max-w-md w-full">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">Delete Theme</h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-300 mb-4">
                        Are you sure you want to delete <strong class="text-white">{{ deletingTheme?.name }}</strong>?
                    </p>
                    <p class="text-sm text-red-400">
                        This action cannot be undone. Themes currently in use by websites cannot be deleted.
                    </p>
                </div>
                <div class="p-6 border-t border-[#2a2a2a] flex justify-end gap-3">
                    <button
                        @click="closeDeleteModal"
                        class="px-6 py-3 bg-[#2a2a2a] text-white rounded-lg hover:bg-[#333333] transition"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmDelete"
                        class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
                    >
                        Delete Theme
                    </button>
                </div>
            </div>
        </div>
    </OrganizationLayout>
</template>
