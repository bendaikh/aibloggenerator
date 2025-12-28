<template>
    <SuperAdminLayout>
        <Head title="Create Website" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <Link :href="route('superadmin.websites.index')" class="text-teal-600 hover:text-teal-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Websites
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-8">Create New Website</h1>

                    <form @submit.prevent="submit">
                        <!-- Website Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Website Name *
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Website Subdomain -->
                        <div class="mb-6">
                            <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-2">
                                Website Subdomain
                            </label>
                            <div class="flex items-center">
                                <input
                                    id="subdomain"
                                    v-model="form.subdomain"
                                    type="text"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                    placeholder="Auto-generated from name"
                                />
                                <span v-if="$page.props.app.base_domain !== 'localhost'" class="text-gray-500 ml-2">.{{ $page.props.app.base_domain }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Leave empty to auto-generate. Must be unique. 
                                <span v-if="$page.props.app.base_domain !== 'localhost'">Will be used as: http://yoursubdomain.{{ $page.props.app.base_domain }}</span>
                                <span v-else>Will be used as: {{ $page.props.app.url }}/site/your-slug</span>
                            </p>
                            <p v-if="form.errors.subdomain" class="mt-2 text-sm text-red-600">{{ form.errors.subdomain }}</p>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                placeholder="A brief description of your website..."
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <!-- Logo URL -->
                        <div class="mb-6">
                            <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                                Logo URL
                            </label>
                            <input
                                id="logo"
                                v-model="form.logo"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                placeholder="https://example.com/logo.png"
                            />
                            <p v-if="form.errors.logo" class="mt-2 text-sm text-red-600">{{ form.errors.logo }}</p>
                        </div>

                        <!-- Domain -->
                        <div class="mb-6">
                            <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">
                                Custom Domain (Optional)
                            </label>
                            <input
                                id="domain"
                                v-model="form.domain"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                placeholder="example.com"
                            />
                            <p class="mt-1 text-sm text-gray-500">Leave empty to use the default URL structure</p>
                            <p v-if="form.errors.domain" class="mt-2 text-sm text-red-600">{{ form.errors.domain }}</p>
                        </div>

                        <!-- Social Media -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Social Media Links</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="instagram" class="block text-sm font-medium text-gray-700 mb-2">
                                        Instagram
                                    </label>
                                    <input
                                        id="instagram"
                                        v-model="form.social_media.instagram"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                        placeholder="https://instagram.com/yourpage"
                                    />
                                </div>

                                <div>
                                    <label for="pinterest" class="block text-sm font-medium text-gray-700 mb-2">
                                        Pinterest
                                    </label>
                                    <input
                                        id="pinterest"
                                        v-model="form.social_media.pinterest"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                        placeholder="https://pinterest.com/yourpage"
                                    />
                                </div>

                                <div>
                                    <label for="facebook" class="block text-sm font-medium text-gray-700 mb-2">
                                        Facebook
                                    </label>
                                    <input
                                        id="facebook"
                                        v-model="form.social_media.facebook"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                        placeholder="https://facebook.com/yourpage"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Theme Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Theme
                            </label>
                            <div class="bg-gray-50 border-2 border-teal-500 rounded-lg p-4">
                                <div class="flex items-center">
                                    <input
                                        type="radio"
                                        id="solushcooks"
                                        value="solushcooks"
                                        v-model="form.theme"
                                        checked
                                        class="mr-3"
                                    />
                                    <label for="solushcooks" class="flex-1">
                                        <span class="font-semibold text-gray-900">Solushcooks Theme</span>
                                        <span class="block text-sm text-gray-600">Modern, clean design perfect for food blogs and recipe websites</span>
                                    </label>
                                    <span class="bg-teal-100 text-teal-800 text-xs font-semibold px-2 py-1 rounded">Default</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="route('superadmin.websites.index')"
                                class="px-6 py-2 text-gray-700 hover:text-gray-900 transition"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-3 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Website</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Preview Info -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">📝 What happens next?</h3>
                    <ul class="list-disc list-inside text-blue-800 space-y-2">
                        <li>Your website will be created with the Solushcooks theme</li>
                        <li>Default categories will be automatically generated (Comfort Classics, Party Appetizers, etc.)</li>
                        <li>You can start adding articles immediately after creation</li>
                        <li>Articles (both AI-generated and manual) will be published to this website</li>
                    </ul>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const form = useForm({
    name: '',
    subdomain: '',
    description: '',
    logo: '',
    domain: '',
    theme: 'solushcooks',
    social_media: {
        instagram: '',
        pinterest: '',
        facebook: ''
    }
});

const submit = () => {
    form.post(route('superadmin.websites.store'));
};
</script>

