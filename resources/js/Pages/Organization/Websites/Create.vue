<script setup>
import { useForm } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showDomainInstructions = ref(false);

const form = useForm({
    name: '',
    domain: '',
    subdomain: '',
    description: '',
    is_active: true,
});

const submit = () => {
    form.post(route('organization.websites.store'));
};
</script>

<template>
    <Head title="Create Website" />

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
                <h1 class="text-3xl font-bold text-white">Create New Website</h1>
                <p class="text-gray-400 mt-1">Set up a new website for your organization</p>
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
                        <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate from name</p>
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
                        
                        <!-- Toggle Instructions Button -->
                        <button 
                            type="button"
                            @click="showDomainInstructions = !showDomainInstructions"
                            class="mt-2 text-sm text-emerald-400 hover:text-emerald-300 flex items-center gap-1"
                        >
                            <svg class="w-4 h-4" :class="{ 'rotate-180': showDomainInstructions }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            {{ showDomainInstructions ? 'Hide' : 'Show' }} Domain Setup Instructions
                        </button>
                    </div>

                    <!-- Custom Domain Instructions -->
                    <div v-if="showDomainInstructions" class="bg-gradient-to-r from-blue-900/30 to-indigo-900/30 border border-blue-800/50 rounded-xl p-5">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-lg">How to Connect Your Custom Domain</h4>
                                <p class="text-blue-200/70 text-sm">Follow these steps to point your domain to your website</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                    Log into your domain registrar
                                </h5>
                                <p class="text-gray-300 text-sm">Access the DNS management panel at your domain registrar (GoDaddy, Namecheap, Cloudflare, etc.)</p>
                            </div>

                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                    Add a CNAME Record
                                </h5>
                                <div class="text-gray-300 text-sm space-y-2">
                                    <p>Create a new CNAME record with these settings:</p>
                                    <div class="bg-[#1a1a1a] rounded-lg p-3 font-mono text-xs">
                                        <div class="grid grid-cols-2 gap-2">
                                            <span class="text-gray-500">Type:</span>
                                            <span class="text-white">CNAME</span>
                                            <span class="text-gray-500">Name/Host:</span>
                                            <span class="text-white">www</span>
                                            <span class="text-gray-500">Value/Target:</span>
                                            <span class="text-emerald-400">websaasmanager.com</span>
                                            <span class="text-gray-500">TTL:</span>
                                            <span class="text-white">Auto or 3600</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                    For Root Domain (Optional)
                                </h5>
                                <div class="text-gray-300 text-sm space-y-2">
                                    <p>To use your root domain (example.com without www), add an A Record:</p>
                                    <div class="bg-[#1a1a1a] rounded-lg p-3 font-mono text-xs">
                                        <div class="grid grid-cols-2 gap-2">
                                            <span class="text-gray-500">Type:</span>
                                            <span class="text-white">A</span>
                                            <span class="text-gray-500">Name/Host:</span>
                                            <span class="text-white">@</span>
                                            <span class="text-gray-500">Value/IP:</span>
                                            <span class="text-emerald-400">YOUR_SERVER_IP</span>
                                            <span class="text-gray-500">TTL:</span>
                                            <span class="text-white">Auto or 3600</span>
                                        </div>
                                    </div>
                                    <p class="text-yellow-400 text-xs mt-2">⚠️ Contact support for the exact IP address if using root domain.</p>
                                </div>
                            </div>

                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">4</span>
                                    Wait for DNS Propagation
                                </h5>
                                <p class="text-gray-300 text-sm">DNS changes can take up to 48 hours to propagate worldwide, but usually complete within 1-4 hours.</p>
                            </div>

                            <div class="bg-[#0a0a0a]/50 rounded-lg p-4">
                                <h5 class="text-emerald-400 font-medium mb-2 flex items-center gap-2">
                                    <span class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold">5</span>
                                    Enter Your Domain Here
                                </h5>
                                <p class="text-gray-300 text-sm">Once DNS is configured, enter your domain in the field above (e.g., www.example.com or example.com)</p>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-yellow-900/30 border border-yellow-800/50 rounded-lg">
                            <p class="text-yellow-200 text-xs flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span><strong>Need help?</strong> If you're using Cloudflare, make sure the proxy status is set to "DNS only" (gray cloud) for the initial setup.</span>
                            </p>
                        </div>
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
                        {{ form.processing ? 'Creating...' : 'Create Website' }}
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
