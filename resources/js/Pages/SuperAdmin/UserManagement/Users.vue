<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';

const props = defineProps({
    users: Object,
    websites: Array,
    roles: Array,
});

const showModal = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: null,
});

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.role_id = user.role_id;
    form.password = '';
    form.password_confirmation = '';
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingUser.value = null;
    form.reset();
};

const submitForm = () => {
    if (editingUser.value) {
        form.put(route('organization.users.update', { 
            user: editingUser.value.id 
        }), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('organization.users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = (user) => {
    if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        router.delete(route('organization.users.destroy', { 
            user: user.id 
        }));
    }
};

const getRoleBadgeColor = (roleName) => {
    // Generate colors based on role name
    if (!roleName) return 'bg-gray-700 text-gray-300';
    
    const hash = roleName.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    const colors = [
        'bg-purple-900 text-purple-300',
        'bg-blue-900 text-blue-300',
        'bg-emerald-900 text-emerald-300',
        'bg-amber-900 text-amber-300',
        'bg-pink-900 text-pink-300',
        'bg-indigo-900 text-indigo-300',
    ];
    return colors[hash % colors.length];
};
</script>

<template>
    <Head title="User Management - Users" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Users</h1>
                    <p class="text-gray-400 mt-1">Manage system users and their access levels</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add User
                </button>
            </div>

            <!-- Users Table -->
            <div v-if="users.data.length > 0" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#0a0a0a] border-b border-[#2a2a2a]">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#2a2a2a]">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-[#252525] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="text-white font-medium">{{ user.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-400">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span v-if="user.role_relation" :class="['text-xs px-2 py-1 rounded', getRoleBadgeColor(user.role_relation.name)]">
                                        {{ user.role_relation.display_name }}
                                    </span>
                                    <span v-else class="text-xs px-2 py-1 rounded bg-gray-700 text-gray-300">
                                        No Role
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-400 text-sm">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEditModal(user)"
                                            class="px-3 py-1.5 bg-[#252525] hover:bg-[#303030] text-white rounded-lg text-sm transition-colors"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteUser(user)"
                                            class="px-3 py-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white rounded-lg text-sm transition-colors"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links.length > 3" class="px-6 py-4 border-t border-[#2a2a2a] flex items-center justify-between">
                    <div class="text-sm text-gray-400">
                        Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
                    </div>
                    <div class="flex gap-2">
                        <component 
                            v-for="(link, index) in users.links" 
                            :key="index"
                            :is="link.url ? 'Link' : 'span'"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-sm transition-colors',
                                link.active 
                                    ? 'bg-emerald-500 text-white' 
                                    : link.url 
                                        ? 'bg-[#252525] text-gray-400 hover:bg-[#303030] hover:text-white' 
                                        : 'text-gray-600 cursor-not-allowed'
                            ]"
                        />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="text-gray-400 text-lg">No users yet</p>
                <p class="text-gray-500 text-sm mt-2">Add your first user to get started</p>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#2a2a2a]">
                    <h2 class="text-2xl font-bold text-white">
                        {{ editingUser ? 'Edit User' : 'Add New User' }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-6">
                    <!-- Name -->
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
                            Email Address *
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="john@example.com"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Role *
                        </label>
                        <select
                            v-model="form.role_id"
                            required
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="">Select a role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                {{ role.display_name }}
                            </option>
                        </select>
                        <p v-if="form.errors.role_id" class="mt-1 text-sm text-red-500">{{ form.errors.role_id }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Password {{ editingUser ? '' : '*' }}
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            :required="!editingUser"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="••••••••"
                        />
                        <p v-if="editingUser" class="mt-1 text-xs text-gray-500">Leave blank to keep current password</p>
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Confirm Password {{ editingUser ? '' : '*' }}
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            :required="!editingUser"
                            class="w-full px-4 py-2 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                            placeholder="••••••••"
                        />
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
                            {{ form.processing ? 'Saving...' : (editingUser ? 'Update User' : 'Create User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </OrganizationLayout>
</template>
