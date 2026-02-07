<script setup>
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Profile Information Form
const profileForm = useForm({
    name: user.value?.name || '',
    email: user.value?.email || '',
});

const updateProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            profileForm.reset('password');
        },
    });
};

// Password Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
            }
        },
    });
};

// Delete Account
const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const deleteForm = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    setTimeout(() => passwordInput.value?.focus(), 250);
};

const deleteUser = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => deleteForm.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    deleteForm.reset();
};
</script>

<template>
    <Head title="Profile Settings" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="max-w-4xl">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white">Profile Settings</h1>
                    <p class="text-gray-400 mt-1">Manage your account information and security settings</p>
                </div>

                <div class="space-y-6">
                    <!-- Profile Information -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-white">Profile Information</h2>
                            <p class="text-gray-400 text-sm mt-1">Update your account's profile information and email address</p>
                        </div>

                        <form @submit.prevent="updateProfile" class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                                <input
                                    v-model="profileForm.name"
                                    type="text"
                                    class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    required
                                />
                                <p v-if="profileForm.errors.name" class="mt-2 text-sm text-red-400">{{ profileForm.errors.name }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                                <input
                                    v-model="profileForm.email"
                                    type="email"
                                    class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    required
                                />
                                <p v-if="profileForm.errors.email" class="mt-2 text-sm text-red-400">{{ profileForm.errors.email }}</p>
                                
                                <div v-if="mustVerifyEmail && !user?.email_verified_at" class="mt-2">
                                    <p class="text-sm text-amber-400">
                                        Your email address is unverified.
                                        <Link :href="route('verification.send')" method="post" as="button" class="underline hover:text-amber-300">
                                            Click here to re-send the verification email.
                                        </Link>
                                    </p>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-lg transition-all disabled:opacity-50"
                                >
                                    Save Changes
                                </button>
                                <p v-if="profileForm.recentlySuccessful" class="text-sm text-emerald-400">Saved!</p>
                            </div>
                        </form>
                    </div>

                    <!-- Update Password -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-white">Update Password</h2>
                            <p class="text-gray-400 text-sm mt-1">Ensure your account is using a long, random password to stay secure</p>
                        </div>

                        <form @submit.prevent="updatePassword" class="space-y-6">
                            <!-- Current Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Current Password</label>
                                <input
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    autocomplete="current-password"
                                />
                                <p v-if="passwordForm.errors.current_password" class="mt-2 text-sm text-red-400">{{ passwordForm.errors.current_password }}</p>
                            </div>

                            <!-- New Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">New Password</label>
                                <input
                                    v-model="passwordForm.password"
                                    type="password"
                                    class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    autocomplete="new-password"
                                />
                                <p v-if="passwordForm.errors.password" class="mt-2 text-sm text-red-400">{{ passwordForm.errors.password }}</p>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Confirm Password</label>
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="w-full px-4 py-3 bg-[#0a0a0a] border border-[#2a2a2a] rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                    autocomplete="new-password"
                                />
                                <p v-if="passwordForm.errors.password_confirmation" class="mt-2 text-sm text-red-400">{{ passwordForm.errors.password_confirmation }}</p>
                            </div>

                            <!-- Save Button -->
                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                    class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-lg transition-all disabled:opacity-50"
                                >
                                    Update Password
                                </button>
                                <p v-if="passwordForm.recentlySuccessful" class="text-sm text-emerald-400">Password updated!</p>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </OrganizationLayout>
</template>
