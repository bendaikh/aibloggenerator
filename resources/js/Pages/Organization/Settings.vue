<script setup>
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({})
    },
    isSuperAdmin: {
        type: Boolean,
        default: false
    },
    globalUsers: {
        type: Array,
        default: () => []
    },
    assignableUsers: {
        type: Array,
        default: () => []
    },
    usersForGlobalToggle: {
        type: Array,
        default: () => []
    }
});

const cloneUsers = (users) => users.map((u) => ({ ...u }));

const localGlobalUsers = ref(cloneUsers(props.globalUsers));
const localAssignableUsers = ref(cloneUsers(props.assignableUsers));
const localToggleUsers = ref(cloneUsers(props.usersForGlobalToggle));
const togglingUserId = ref(null);
const savingAssignments = ref(false);

watch(
    () => props.usersForGlobalToggle,
    (users) => {
        localToggleUsers.value = cloneUsers(users);
    },
    { deep: true }
);

watch(
    () => props.globalUsers,
    (users) => {
        localGlobalUsers.value = cloneUsers(users);
    },
    { deep: true }
);

watch(
    () => props.assignableUsers,
    (users) => {
        localAssignableUsers.value = cloneUsers(users);
    },
    { deep: true }
);

const applyGlobalUsersPayload = (payload) => {
    console.log('Applying payload:', payload);
    localGlobalUsers.value = cloneUsers(payload.globalUsers ?? []);
    localAssignableUsers.value = cloneUsers(payload.assignableUsers ?? []);
    localToggleUsers.value = cloneUsers(payload.usersForGlobalToggle ?? []);
};

const displayGlobalUsers = computed(() => localGlobalUsers.value);

const showAssignmentSection = computed(() => displayGlobalUsers.value.length > 0);

const selectedGlobalUserId = ref(displayGlobalUsers.value[0]?.id ?? null);

watch(displayGlobalUsers, (users) => {
    if (!users.length) {
        selectedGlobalUserId.value = null;
        return;
    }

    if (!users.some((u) => u.id === selectedGlobalUserId.value)) {
        selectedGlobalUserId.value = users[0].id;
    }
}, { deep: true });

const selectedGlobalUser = computed(() =>
    displayGlobalUsers.value.find((u) => u.id === selectedGlobalUserId.value) ?? null
);

const secondaryUserIds = ref([]);

watch(selectedGlobalUser, (user) => {
    secondaryUserIds.value = user
        ? user.secondary_users?.map((u) => u.id) ?? []
        : [];
}, { immediate: true });

const toggleGlobalUser = (user, event) => {
    const isGlobal = event.target.checked;
    togglingUserId.value = user.id;

    router.put(
        route('organization.settings.global-users.status', { user: user.id }),
        { is_global_user: isGlobal },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['globalUsers', 'assignableUsers', 'usersForGlobalToggle'],
            onSuccess: () => {
                // Success
            },
            onError: () => {
                user.is_global_user = !isGlobal;
            },
            onFinish: () => {
                togglingUserId.value = null;
            }
        }
    );
};

const saveAssignments = () => {
    if (!selectedGlobalUserId.value) return;

    savingAssignments.value = true;

    router.put(
        route('organization.settings.global-users.assignments', {
            user: selectedGlobalUserId.value,
        }),
        { secondary_user_ids: secondaryUserIds.value },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['globalUsers', 'assignableUsers', 'usersForGlobalToggle'],
            onFinish: () => {
                savingAssignments.value = false;
            }
        }
    );
};
</script>

<template>
    <Head title="Global Settings" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Global Settings</h1>
                <p class="text-gray-400 mt-1">Configure settings that apply to all your websites</p>
            </div>

            <div v-if="isSuperAdmin" class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 mb-6">
                <div class="flex items-start gap-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-white">Global Users</h2>
                        <p class="text-gray-400 text-sm mt-1">
                            Mark users as global managers. They can generate articles and publish to websites owned by assigned secondary users only.
                        </p>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Mark as Global User</h3>
                    <p v-if="localToggleUsers.length === 0" class="text-gray-500 text-sm py-4">No other users available.</p>
                    <div v-else class="max-h-48 overflow-y-auto space-y-2 border border-[#2a2a2a] rounded-xl p-2">
                        <label
                            v-for="user in localToggleUsers"
                            :key="user.id"
                            class="flex items-center justify-between gap-3 p-3 rounded-lg hover:bg-[#252525] cursor-pointer"
                        >
                            <div class="min-w-0">
                                <p class="text-white text-sm font-medium truncate">{{ user.name }}</p>
                                <p class="text-gray-500 text-xs truncate">{{ user.email }}</p>
                            </div>
                            <input
                                type="checkbox"
                                :checked="user.is_global_user"
                                :disabled="togglingUserId === user.id"
                                @change="toggleGlobalUser(user, $event)"
                                class="w-4 h-4 rounded bg-[#1a1a1a] border-[#3a3a3a] text-violet-500 focus:ring-violet-500 disabled:opacity-50"
                            />
                        </label>
                    </div>
                </div>

                <div v-if="showAssignmentSection">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Assign Secondary Users</h3>
                    <div class="mb-4">
                        <label class="block text-xs text-gray-500 mb-2">Select Global User</label>
                        <select
                            v-model="selectedGlobalUserId"
                            class="w-full max-w-md bg-[#0f0f0f] border border-[#2a2a2a] rounded-lg text-white text-sm p-3 focus:ring-violet-500 focus:border-violet-500"
                        >
                            <option v-for="gu in displayGlobalUsers" :key="gu.id" :value="gu.id">
                                {{ gu.name }} ({{ gu.email }})
                            </option>
                        </select>
                    </div>

                    <p class="text-gray-500 text-sm mb-3">Secondary users whose websites this global user can access:</p>
                    <div class="max-h-56 overflow-y-auto space-y-2 border border-[#2a2a2a] rounded-xl p-2 mb-4">
                        <label
                            v-for="user in localAssignableUsers.filter((u) => u.id !== selectedGlobalUserId)"
                            :key="'sec-' + user.id"
                            class="flex items-center gap-3 p-3 rounded-lg hover:bg-[#252525] cursor-pointer"
                        >
                            <input
                                v-model="secondaryUserIds"
                                type="checkbox"
                                :value="user.id"
                                class="w-4 h-4 rounded bg-[#1a1a1a] border-[#3a3a3a] text-violet-500 focus:ring-violet-500"
                            />
                            <div class="min-w-0">
                                <p class="text-white text-sm font-medium truncate">{{ user.name }}</p>
                                <p class="text-gray-500 text-xs truncate">{{ user.email }}</p>
                            </div>
                        </label>
                    </div>

                    <button
                        type="button"
                        @click="saveAssignments"
                        :disabled="savingAssignments || !selectedGlobalUserId"
                        class="px-5 py-2.5 bg-violet-600 hover:bg-violet-500 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                    >
                        {{ savingAssignments ? 'Saving...' : 'Save Assignments' }}
                    </button>
                </div>
                <p v-else class="text-gray-500 text-sm">Enable at least one global user above to assign secondary users.</p>
            </div>

            <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-6 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-blue-300 font-medium mb-2">Settings have been reorganized</p>
                        <p class="text-blue-200 text-sm">
                            AI and content generation settings have been moved to dedicated sections. Use the sidebar to access API Keys and Agent Rewrite.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Link
                    :href="route('organization.api-keys')"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 hover:border-emerald-500/50 transition-all group"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white group-hover:text-emerald-400 transition-colors">API Keys</h3>
                            <p class="text-gray-400 text-sm">AI Content Generation Settings</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs">
                        <span v-if="settings.openai_api_key_set" class="px-2 py-1 bg-emerald-900/50 text-emerald-400 rounded">✓ API Key Connected</span>
                        <span v-else class="px-2 py-1 bg-red-900/50 text-red-400 rounded">⚠ API Key Not Set</span>
                    </div>
                </Link>

                <Link
                    :href="route('organization.agent-rewrite')"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 hover:border-blue-500/50 transition-all group"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Agent Rewrite</h3>
                            <p class="text-gray-400 text-sm">Cost Reduction Strategy</p>
                        </div>
                    </div>
                </Link>

                <Link
                    :href="route('organization.security-notifications')"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 hover:border-red-500/50 transition-all group"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Security Notifications</h3>
                            <p class="text-gray-400 text-sm">WhatsApp Login Alerts</p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </OrganizationLayout>
</template>
