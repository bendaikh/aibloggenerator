<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';

const props = defineProps({
    roles: Array,
    websites: Array,
});

const showModal = ref(false);
const editingRole = ref(null);

const form = useForm({
    name: '',
    display_name: '',
    description: '',
});

const openCreateModal = () => {
    editingRole.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (role) => {
    editingRole.value = role;
    form.name = role.name;
    form.display_name = role.display_name;
    form.description = role.description || '';
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingRole.value = null;
    form.reset();
};

const submitForm = () => {
    if (editingRole.value) {
        form.put(route('organization.roles.update', { 
            role: editingRole.value.id 
        }), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('organization.roles.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteRole = (role) => {
    if (confirm(`Are you sure you want to delete the role "${role.display_name}"?`)) {
        router.delete(route('organization.roles.destroy', { 
            role: role.id 
        }));
    }
};
</script>

<template>
    <Head title="User Management - Roles" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Roles</h1>
                    <p class="text-gray-400 mt-1">Create and manage user roles</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Role
                </button>
            </div>

            <!-- Roles Grid -->
            <div v-if="roles.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 hover:border-emerald-500 transition-colors"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ role.display_name }}</h3>
                                <p class="text-sm text-gray-500">{{ role.name }}</p>
                            </div>
                        </div>
                    </div>

                    <p v-if="role.description" class="text-gray-400 text-sm mb-4 line-clamp-2">
                        {{ role.description }}
                    </p>
                    <p v-else class="text-gray-500 text-sm mb-4 italic">No description</p>

                    <div class="flex items-center gap-4 mb-4 text-sm">
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>{{ role.users_count || 0 }} users</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <span>{{ role.permissions_count || 0 }} permissions</span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="openEditModal(role)"
                            class="flex-1 px-3 py-2 bg-[#252525] hover:bg-[#303030] text-white rounded-lg text-sm transition-colors"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteRole(role)"
                            class="px-3 py-2 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white rounded-lg text-sm transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <p class="text-gray-400 text-lg">No roles yet</p>
                <p class="text-gray-500 text-sm mt-2">Create your first role to get started</p>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">
                        {{ editingRole ? 'Edit Role' : 'Add New Role' }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Role Name (Slug) *
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="editor"
                        />
                        <p class="mt-1 text-xs text-gray-500">Lowercase, no spaces (e.g., editor, content-manager)</p>
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Display Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Display Name *
                        </label>
                        <input
                            v-model="form.display_name"
                            type="text"
                            required
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="Editor"
                        />
                        <p class="mt-1 text-xs text-gray-500">Human-readable name shown to users</p>
                        <p v-if="form.errors.display_name" class="mt-1 text-sm text-red-500">{{ form.errors.display_name }}</p>
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
                            placeholder="Describe what this role can do..."
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 bg-[#252525] hover:bg-[#303030] text-white rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : (editingRole ? 'Update Role' : 'Create Role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </OrganizationLayout>
</template>
