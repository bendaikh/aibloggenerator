<script setup>
import { ref } from 'vue';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    openai_api_key: '',
    ai_model: props.settings.ai_model || 'gpt-4o',
    ai_default_tone: props.settings.ai_default_tone || 'conversational',
    article_generation_mode: props.settings.article_generation_mode || 'full_ai',
    max_variations: props.settings.max_variations || 5,
});

const testingConnection = ref(false);
const testResult = ref(null);

const submitForm = () => {
    form.post(route('organization.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.openai_api_key = '';
        }
    });
};

const testConnection = async () => {
    testingConnection.value = true;
    testResult.value = null;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                          document.head.querySelector('meta[name="csrf-token"]')?.content || '';
        
        const response = await fetch('/organization/settings/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        testResult.value = await response.json();
    } catch (error) {
        testResult.value = {
            success: false,
            message: 'Failed to test connection: ' + error.message
        };
    } finally {
        testingConnection.value = false;
    }
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
                    <!-- AI Settings -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">AI Content Generation</h3>
                                <p class="text-gray-400 text-sm">Connect your OpenAI account to generate articles with AI across all websites</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-gray-400 text-sm block mb-2">OpenAI API Key</label>
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <input 
                                            v-model="form.openai_api_key"
                                            type="password" 
                                            :placeholder="settings.openai_api_key_set ? settings.openai_api_key_masked + ' (saved - enter new key to update)' : 'sk-proj-...'"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        />
                                        <div v-if="settings.openai_api_key_set" class="absolute right-3 top-1/2 -translate-y-1/2">
                                            <span class="px-2 py-1 bg-emerald-900 text-emerald-300 text-xs rounded">Connected</span>
                                        </div>
                                    </div>
                                    <button 
                                        type="button"
                                        @click="testConnection"
                                        :disabled="testingConnection || !settings.openai_api_key_set"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                    >
                                        <svg v-if="testingConnection" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ testingConnection ? 'Testing...' : 'Test Connection' }}
                                    </button>
                                </div>
                                <p v-if="form.errors.openai_api_key" class="mt-1 text-sm text-red-500">{{ form.errors.openai_api_key }}</p>
                                <p class="text-gray-500 text-xs mt-2">
                                    Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank" class="text-emerald-400 hover:underline">OpenAI Dashboard</a>. Your key is encrypted and stored securely.
                                </p>

                                <!-- Test Result -->
                                <div v-if="testResult" :class="[
                                    'mt-3 px-4 py-3 rounded-lg',
                                    testResult.success ? 'bg-emerald-900/50 border border-emerald-500 text-emerald-200' : 'bg-red-900/50 border border-red-500 text-red-200'
                                ]">
                                    <div class="flex items-center gap-2">
                                        <svg v-if="testResult.success" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span>{{ testResult.message }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-gray-400 text-sm block mb-2">Default AI Model</label>
                                    <select 
                                        v-model="form.ai_model"
                                        class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="gpt-4o">GPT-4o (Recommended)</option>
                                        <option value="gpt-4-turbo">GPT-4 Turbo</option>
                                        <option value="gpt-3.5-turbo">GPT-3.5 Turbo (Faster, cheaper)</option>
                                    </select>
                                    <p class="text-gray-500 text-xs mt-1">GPT-4o provides the best quality for content generation</p>
                                </div>

                                <div>
                                    <label class="text-gray-400 text-sm block mb-2">Default Content Tone</label>
                                    <select 
                                        v-model="form.ai_default_tone"
                                        class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="conversational">Conversational</option>
                                        <option value="professional">Professional</option>
                                        <option value="casual">Casual</option>
                                        <option value="friendly">Friendly</option>
                                        <option value="formal">Formal</option>
                                    </select>
                                    <p class="text-gray-500 text-xs mt-1">This will be the default tone for new AI articles</p>
                                </div>
                            </div>
                        </div>
                    </div>

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

                            <!-- Max Variations -->
                            <div v-if="form.article_generation_mode === 'hybrid_rewrite'">
                                <label class="text-gray-400 text-sm block mb-2">Maximum Variations</label>
                                <div class="flex items-center gap-4">
                                    <input 
                                        v-model.number="form.max_variations"
                                        type="range"
                                        min="1"
                                        max="20"
                                        class="flex-1 h-2 bg-[#252525] rounded-lg appearance-none cursor-pointer accent-blue-500"
                                    />
                                    <div class="w-16 px-3 py-2 bg-[#252525] border border-[#3a3a3a] text-white rounded-lg text-center font-medium">
                                        {{ form.max_variations }}
                                    </div>
                                </div>
                                <p class="text-gray-500 text-xs mt-2">
                                    Generate up to {{ form.max_variations }} article variations from one master article when publishing to multiple websites
                                </p>
                                <p v-if="form.errors.max_variations" class="mt-1 text-sm text-red-500">{{ form.errors.max_variations }}</p>
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
                                            <li>Creates variations locally with advanced rewriting</li>
                                            <li>Each variation has different title, structure, and wording</li>
                                            <li>No additional AI API calls for variations</li>
                                            <li>Ideal for publishing same topic across multiple websites</li>
                                        </ul>
                                        <p class="text-blue-300 text-xs mt-2">
                                            <strong>Cost Example:</strong> 10 websites = 1 API call ($0.10) instead of 10 calls ($1.00)
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

