<script setup>
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({})
    }
});
</script>

<template>
    <Head title="Global Settings" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Global Settings</h1>
                <p class="text-gray-400 mt-1">Configure settings that apply to all your websites</p>
            </div>

            <!-- Info Message -->
            <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-6 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-blue-300 font-medium mb-2">Settings have been reorganized</p>
                        <p class="text-blue-200 text-sm">
                            AI and content generation settings have been moved to dedicated sections for better organization. 
                            Please use the sidebar navigation to access:
                        </p>
                        <ul class="text-blue-200 text-sm mt-3 space-y-2">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <strong>API Keys</strong> - Manage OpenAI API keys and AI content generation settings
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <strong>Agent Rewrite</strong> - Configure cost reduction strategies for multi-website generation
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- API Keys Card -->
                <Link 
                    :href="route('organization.api-keys')"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 hover:border-emerald-500/50 transition-all group"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white group-hover:text-emerald-400 transition-colors">API Keys</h3>
                            <p class="text-gray-400 text-sm">AI Content Generation Settings</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">
                        Configure your OpenAI API key, select AI models, and set default content tone for article generation.
                    </p>
                    <div class="mt-4 flex items-center gap-2 text-xs text-gray-600">
                        <span v-if="settings.openai_api_key_set" class="px-2 py-1 bg-emerald-900/50 text-emerald-400 rounded">
                            ✓ API Key Connected
                        </span>
                        <span v-else class="px-2 py-1 bg-red-900/50 text-red-400 rounded">
                            ⚠ API Key Not Set
                        </span>
                    </div>
                </Link>

                <!-- Agent Rewrite Card -->
                <Link 
                    :href="route('organization.agent-rewrite')"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 hover:border-blue-500/50 transition-all group"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white group-hover:text-blue-400 transition-colors">Agent Rewrite</h3>
                            <p class="text-gray-400 text-sm">Cost Reduction Strategy</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-blue-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">
                        Choose between Full AI mode or Hybrid Rewrite mode to save up to 90% on API costs for multi-website generation.
                    </p>
                    <div class="mt-4 flex items-center gap-2 text-xs text-gray-600">
                        <span v-if="settings.article_generation_mode === 'hybrid_rewrite'" class="px-2 py-1 bg-blue-900/50 text-blue-400 rounded">
                            Hybrid Mode (90% savings)
                        </span>
                        <span v-else class="px-2 py-1 bg-emerald-900/50 text-emerald-400 rounded">
                            Full AI Mode
                        </span>
                    </div>
                </Link>

                <!-- Security Notifications Card -->
                <Link 
                    :href="route('organization.security-notifications')"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6 hover:border-red-500/50 transition-all group"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white group-hover:text-red-400 transition-colors">Security Notifications</h3>
                            <p class="text-gray-400 text-sm">WhatsApp Login Alerts</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-red-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">
                        Get instant WhatsApp alerts when someone logs in as superadmin. Monitor login attempts with IP, location, and device details.
                    </p>
                    <div class="mt-4 flex items-center gap-2 text-xs text-gray-600">
                        <span class="px-2 py-1 bg-amber-900/50 text-amber-400 rounded">
                            🔒 Security Monitoring
                        </span>
                    </div>
                </Link>
            </div>
        </div>
    </OrganizationLayout>
</template>

