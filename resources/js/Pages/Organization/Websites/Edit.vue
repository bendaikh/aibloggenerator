<script setup>
import { useForm } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    website: Object
});

const form = useForm({
    name: props.website.name,
    domain: props.website.domain || '',
    subdomain: props.website.subdomain || '',
    description: props.website.description || '',
    is_active: props.website.is_active,
});

const submit = () => {
    form.put(route('organization.websites.update', props.website.id));
};
</script>

<template>
    <Head :title="`Edit ${website.name}`" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <Link 
                    :href="route('organization.websites.index')" 
                    class="text-gray-400 hover:text-white text-sm flex items-center gap-2 mb-4"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Websites
                </Link>
                <h1 class="text-3xl font-bold text-white">Edit Website</h1>
                <p class="text-gray-400 mt-1">Update {{ website.name }} settings</p>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl">
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 space-y-6">
                    <!-- Website Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                            Website Name *
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="My Awesome Blog"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Subdomain -->
                    <div>
                        <label for="subdomain" class="block text-sm font-medium text-gray-300 mb-2">
                            Subdomain
                        </label>
                        <div class="flex items-center">
                            <input
                                id="subdomain"
                                v-model="form.subdomain"
                                type="text"
                                class="flex-1 bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="mysite"
                            />
                            <span class="bg-[#1f1f1f] border border-l-0 border-[#3a3a3a] text-gray-400 px-4 py-3 rounded-r-lg">
                                .websaasmanager.com
                            </span>
                        </div>
                        <p v-if="form.errors.subdomain" class="mt-1 text-sm text-red-500">{{ form.errors.subdomain }}</p>
                    </div>

                    <!-- Custom Domain -->
                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-300 mb-2">
                            Custom Domain (Optional)
                        </label>
                        <input
                            id="domain"
                            v-model="form.domain"
                            type="text"
                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="www.example.com"
                        />
                        <p class="mt-1 text-xs text-gray-500">Add a custom domain after DNS configuration</p>
                        <p v-if="form.errors.domain" class="mt-1 text-sm text-red-500">{{ form.errors.domain }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="A brief description of your website..."
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-[#3a3a3a] peer-focus:ring-2 peer-focus:ring-emerald-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                        <span class="text-gray-300">Active</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex items-center gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <Link
                        :href="route('organization.websites.index')"
                        class="px-6 py-3 text-gray-400 hover:text-white transition"
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </OrganizationLayout>
</template>

