<script setup>
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({})
    }
});

console.log('AgentRewrite component loaded with settings:', props.settings);

const form = useForm({
    article_generation_mode: props.settings.article_generation_mode || 'full_ai',
    max_variations: props.settings.max_variations || 5,
});

// Watch for changes in props to detect when settings are updated
watch(() => props.settings, (newSettings, oldSettings) => {
    console.log('Settings props changed:', { old: oldSettings, new: newSettings });
    // Update form values when props change
    form.article_generation_mode = newSettings.article_generation_mode || 'full_ai';
    form.max_variations = newSettings.max_variations || 5;
}, { deep: true });

const submitForm = () => {
    console.log('Submitting form with data:', form.data());
    form.post(route('organization.agent-rewrite.update'), {
        preserveScroll: false,
        onSuccess: () => {
            console.log('Form submitted successfully');
            // Force a complete page reload to get fresh data
            window.location.reload();
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
    });
};
</script>

<template>
    <Head title="Agent Rewrite" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Agent Rewrite</h1>
                <p class="text-gray-400 mt-1">Configure cost reduction strategies for multi-website article generation</p>
            </div>

            <!-- Success Message -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-6 py-4 rounded-lg mb-6">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <form @submit.prevent="submitForm">
                <div class="space-y-6">
                    <!-- Cost Reduction Settings -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Cost Reduction Strategy</h3>
                                <p class="text-gray-400 text-sm">Save up to 90% on API costs for multi-website article generation</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Generation Mode -->
                            <div>
                                <label class="text-gray-400 text-sm block mb-3">Article Generation Mode</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Full AI Mode -->
                                    <label :class="[
                                        'relative flex items-start p-4 rounded-lg border-2 cursor-pointer transition-all',
                                        form.article_generation_mode === 'full_ai' 
                                            ? 'border-emerald-500 bg-emerald-900/20' 
                                            : 'border-[#3a3a3a] bg-[#252525] hover:border-[#4a4a4a]'
                                    ]">
                                        <input 
                                            type="radio" 
                                            v-model="form.article_generation_mode" 
                                            value="full_ai"
                                            class="mt-1 text-emerald-500 focus:ring-emerald-500"
                                        />
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-white font-medium">Full AI Mode</span>
                                                <span class="px-2 py-0.5 bg-emerald-900 text-emerald-300 text-xs rounded">Default</span>
                                            </div>
                                            <p class="text-gray-400 text-sm mb-2">Every article generated via OpenAI API</p>
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Maximum uniqueness
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Premium quality
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-red-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Higher API costs
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Hybrid Mode -->
                                    <label :class="[
                                        'relative flex items-start p-4 rounded-lg border-2 cursor-pointer transition-all',
                                        form.article_generation_mode === 'hybrid_rewrite' 
                                            ? 'border-blue-500 bg-blue-900/20' 
                                            : 'border-[#3a3a3a] bg-[#252525] hover:border-[#4a4a4a]'
                                    ]">
                                        <input 
                                            type="radio" 
                                            v-model="form.article_generation_mode" 
                                            value="hybrid_rewrite"
                                            class="mt-1 text-blue-500 focus:ring-blue-500"
                                        />
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-white font-medium">Hybrid Rewrite Mode</span>
                                                <span class="px-2 py-0.5 bg-blue-900 text-blue-300 text-xs rounded">90% savings</span>
                                            </div>
                                            <p class="text-gray-400 text-sm mb-2">1 master AI article + local variations</p>
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    90% cost reduction
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    8x faster generation
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    40%+ uniqueness guaranteed
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <p v-if="form.errors.article_generation_mode" class="mt-1 text-sm text-red-500">{{ form.errors.article_generation_mode }}</p>
                            </div>

                            <!-- Unlimited Variations Info -->
                            <div v-if="form.article_generation_mode === 'hybrid_rewrite'" class="p-3 bg-emerald-900/20 border border-emerald-500/30 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-emerald-300 text-sm font-medium">Unlimited Websites</span>
                                </div>
                                <p class="text-emerald-200/70 text-xs mt-1">
                                    All selected websites will receive unique article variations - no limits!
                                </p>
                            </div>

                            <!-- Info Box -->
                            <div class="mt-4 p-4 bg-blue-900/20 border border-blue-500/30 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="text-sm space-y-2">
                                        <p class="text-blue-300 font-medium">How Hybrid Mode Works:</p>
                                        <ul class="text-blue-200 space-y-1 list-disc list-inside">
                                            <li>Generates 1 high-quality master article via AI</li>
                                            <li>Creates unlimited variations locally with advanced rewriting</li>
                                            <li>Each variation has different structure and wording</li>
                                            <li>No additional AI API calls for variations</li>
                                            <li>Works with any number of websites - no limits!</li>
                                        </ul>
                                        <p class="text-blue-300 text-xs mt-2">
                                            <strong>Cost Example:</strong> 25 websites = 1 API call ($0.10) instead of 25 calls ($2.50)
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-lg font-medium transition-colors disabled:opacity-50 flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </OrganizationLayout>
</template>
