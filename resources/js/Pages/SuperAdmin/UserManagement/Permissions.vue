<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';

const props = defineProps({
    permissions: Object,
    roles: Array,
    websites: Array,
});

const showPermissionModal = ref(false);
const showRolePermissionsModal = ref(false);
const editingPermission = ref(null);
const selectedRole = ref(null);

const permissionForm = useForm({
    name: '',
    display_name: '',
    description: '',
    group: '',
});

const rolePermissionsForm = useForm({
    permission_ids: [],
});

// Permission Management
const openCreatePermissionModal = () => {
    editingPermission.value = null;
    permissionForm.reset();
    permissionForm.clearErrors();
    showPermissionModal.value = true;
};

const openEditPermissionModal = (permission) => {
    editingPermission.value = permission;
    permissionForm.name = permission.name;
    permissionForm.display_name = permission.display_name;
    permissionForm.description = permission.description || '';
    permissionForm.group = permission.group || '';
    permissionForm.clearErrors();
    showPermissionModal.value = true;
};

const closePermissionModal = () => {
    showPermissionModal.value = false;
    editingPermission.value = null;
    permissionForm.reset();
};

const submitPermissionForm = () => {
    if (editingPermission.value) {
        permissionForm.put(route('organization.permissions.update', { 
            permission: editingPermission.value.id 
        }), {
            onSuccess: () => closePermissionModal(),
        });
    } else {
        permissionForm.post(route('organization.permissions.store'), {
            onSuccess: () => closePermissionModal(),
        });
    }
};

const deletePermission = (permission) => {
    if (confirm(`Are you sure you want to delete the permission "${permission.display_name}"?`)) {
        router.delete(route('organization.permissions.destroy', { 
            permission: permission.id 
        }));
    }
};

// Role Permissions Management
const openRolePermissionsModal = (role) => {
    selectedRole.value = role;
    rolePermissionsForm.permission_ids = role.permissions.map(p => p.id);
    rolePermissionsForm.clearErrors();
    showRolePermissionsModal.value = true;
};

const closeRolePermissionsModal = () => {
    showRolePermissionsModal.value = false;
    selectedRole.value = null;
    rolePermissionsForm.reset();
};

const submitRolePermissions = () => {
    rolePermissionsForm.put(route('organization.roles.permissions.update', { 
        role: selectedRole.value.id 
    }), {
        onSuccess: () => closeRolePermissionsModal(),
    });
};

const togglePermission = (permissionId) => {
    const index = rolePermissionsForm.permission_ids.indexOf(permissionId);
    if (index > -1) {
        rolePermissionsForm.permission_ids.splice(index, 1);
    } else {
        rolePermissionsForm.permission_ids.push(permissionId);
    }
};

const toggleGroupPermissions = (groupPermissions) => {
    const allSelected = groupPermissions.every(p => 
        rolePermissionsForm.permission_ids.includes(p.id)
    );
    
    if (allSelected) {
        // Deselect all in group
        groupPermissions.forEach(p => {
            const index = rolePermissionsForm.permission_ids.indexOf(p.id);
            if (index > -1) {
                rolePermissionsForm.permission_ids.splice(index, 1);
            }
        });
    } else {
        // Select all in group
        groupPermissions.forEach(p => {
            if (!rolePermissionsForm.permission_ids.includes(p.id)) {
                rolePermissionsForm.permission_ids.push(p.id);
            }
        });
    }
};

const isGroupFullySelected = (groupPermissions) => {
    return groupPermissions.every(p => 
        rolePermissionsForm.permission_ids.includes(p.id)
    );
};

const allPermissionsFlat = computed(() => {
    const flat = [];
    Object.values(props.permissions).forEach(group => {
        flat.push(...group);
    });
    return flat;
});
</script>

<template>
    <Head title="User Management - Permissions" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Permissions</h1>
                    <p class="text-gray-400 mt-1">Manage permissions and assign them to roles</p>
                </div>
                <button 
                    @click="openCreatePermissionModal"
                    class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Permission
                </button>
            </div>

            <!-- Roles with Permission Assignment -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-white mb-4">Assign Permissions to Roles</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="role in roles"
                        :key="role.id"
                        class="bg-[#1a1a1a] rounded-xl border border-[#2a2a2a] p-5 hover:border-emerald-500 transition-colors cursor-pointer"
                        @click="openRolePermissionsModal(role)"
                    >
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-white font-bold">{{ role.display_name }}</h3>
                                <p class="text-gray-500 text-xs">{{ role.name }}</p>
                            </div>
                        </div>
                        <div class="text-sm text-gray-400">
                            {{ role.permissions.length }} permission{{ role.permissions.length !== 1 ? 's' : '' }} assigned
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions List by Group -->
            <div>
                <h2 class="text-xl font-bold text-white mb-4">All Permissions</h2>
                <div v-if="Object.keys(permissions).length > 0" class="space-y-6">
                    <div v-for="(groupPermissions, groupName) in permissions" :key="groupName" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden">
                        <div class="px-6 py-4 bg-[#0a0a0a] border-b border-[#2a2a2a]">
                            <h3 class="text-lg font-bold text-white capitalize">{{ groupName || 'General' }}</h3>
                        </div>
                        <div class="divide-y divide-[#2a2a2a]">
                            <div
                                v-for="permission in groupPermissions"
                                :key="permission.id"
                                class="px-6 py-4 hover:bg-[#252525] transition-colors flex items-center justify-between"
                            >
                                <div class="flex-1">
                                    <h4 class="text-white font-medium">{{ permission.display_name }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">{{ permission.name }}</p>
                                    <p v-if="permission.description" class="text-sm text-gray-400 mt-1">
                                        {{ permission.description }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <button
                                        @click="openEditPermissionModal(permission)"
                                        class="px-3 py-1.5 bg-[#252525] hover:bg-[#303030] text-white rounded-lg text-sm transition-colors"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deletePermission(permission)"
                                        class="px-3 py-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white rounded-lg text-sm transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                    <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <p class="text-gray-400 text-lg">No permissions yet</p>
                    <p class="text-gray-500 text-sm mt-2">Create your first permission to get started</p>
                </div>
            </div>
        </div>

        <!-- Create/Edit Permission Modal -->
        <div v-if="showPermissionModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">
                        {{ editingPermission ? 'Edit Permission' : 'Add New Permission' }}
                    </h2>
                </div>
                <form @submit.prevent="submitPermissionForm" class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Permission Name (Slug) *
                        </label>
                        <input
                            v-model="permissionForm.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="articles.create"
                        />
                        <p class="mt-1 text-xs text-gray-500">Use dot notation (e.g., articles.create, users.edit)</p>
                        <p v-if="permissionForm.errors.name" class="mt-1 text-sm text-red-500">{{ permissionForm.errors.name }}</p>
                    </div>

                    <!-- Display Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Display Name *
                        </label>
                        <input
                            v-model="permissionForm.display_name"
                            type="text"
                            required
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="Create Articles"
                        />
                        <p class="mt-1 text-xs text-gray-500">Human-readable name shown to users</p>
                        <p v-if="permissionForm.errors.display_name" class="mt-1 text-sm text-red-500">{{ permissionForm.errors.display_name }}</p>
                    </div>

                    <!-- Group -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Group
                        </label>
                        <input
                            v-model="permissionForm.group"
                            type="text"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="articles"
                        />
                        <p class="mt-1 text-xs text-gray-500">Group permissions by category (e.g., articles, users, settings)</p>
                        <p v-if="permissionForm.errors.group" class="mt-1 text-sm text-red-500">{{ permissionForm.errors.group }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea
                            v-model="permissionForm.description"
                            rows="3"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="Describe what this permission allows..."
                        ></textarea>
                        <p v-if="permissionForm.errors.description" class="mt-1 text-sm text-red-500">{{ permissionForm.errors.description }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closePermissionModal"
                            class="px-4 py-2 bg-[#252525] hover:bg-[#303030] text-white rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="permissionForm.processing"
                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors disabled:opacity-50"
                        >
                            {{ permissionForm.processing ? 'Saving...' : (editingPermission ? 'Update Permission' : 'Create Permission') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role Permissions Modal -->
        <div v-if="showRolePermissionsModal && selectedRole" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">
                        Manage Permissions for {{ selectedRole.display_name }}
                    </h2>
                    <p class="text-gray-400 mt-1">Select permissions to assign to this role</p>
                </div>
                <form @submit.prevent="submitRolePermissions" class="p-6">
                    <div class="space-y-6 mb-6">
                        <div v-for="(groupPermissions, groupName) in permissions" :key="groupName" class="bg-[#0a0a0a] rounded-xl border border-[#2a2a2a] overflow-hidden">
                            <div class="px-4 py-3 bg-[#141414] border-b border-[#2a2a2a] flex items-center justify-between">
                                <h3 class="text-white font-bold capitalize">{{ groupName || 'General' }}</h3>
                                <button
                                    type="button"
                                    @click="toggleGroupPermissions(groupPermissions)"
                                    class="text-sm px-3 py-1 rounded-lg transition-colors"
                                    :class="isGroupFullySelected(groupPermissions) 
                                        ? 'bg-emerald-500 text-white hover:bg-emerald-600' 
                                        : 'bg-[#252525] text-gray-300 hover:bg-[#303030]'"
                                >
                                    {{ isGroupFullySelected(groupPermissions) ? 'Deselect All' : 'Select All' }}
                                </button>
                            </div>
                            <div class="p-4 space-y-2">
                                <label
                                    v-for="permission in groupPermissions"
                                    :key="permission.id"
                                    class="flex items-start gap-3 p-3 rounded-lg hover:bg-[#1a1a1a] cursor-pointer transition-colors"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="rolePermissionsForm.permission_ids.includes(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        class="mt-0.5 w-4 h-4 text-emerald-600 bg-[#0a0a0a] border-[#2a2a2a] rounded focus:ring-emerald-500"
                                    />
                                    <div class="flex-1">
                                        <p class="text-white font-medium text-sm">{{ permission.display_name }}</p>
                                        <p class="text-gray-500 text-xs">{{ permission.name }}</p>
                                        <p v-if="permission.description" class="text-gray-400 text-xs mt-1">
                                            {{ permission.description }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-[#2a2a2a]">
                        <div class="text-sm text-gray-400">
                            {{ rolePermissionsForm.permission_ids.length }} of {{ allPermissionsFlat.length }} permissions selected
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="closeRolePermissionsModal"
                                class="px-4 py-2 bg-[#252525] hover:bg-[#303030] text-white rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="rolePermissionsForm.processing"
                                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors disabled:opacity-50"
                            >
                                {{ rolePermissionsForm.processing ? 'Saving...' : 'Save Permissions' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </OrganizationLayout>
</template>
