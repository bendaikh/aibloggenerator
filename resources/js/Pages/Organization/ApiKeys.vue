<script setup>
import { ref } from 'vue';
import axios from 'axios';
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
    gemini_api_key: '',
    ideogram_api_key: '',
    image_generation_provider: props.settings.image_generation_provider || 'openai',
    ai_model: props.settings.ai_model || 'gpt-4o',
    ai_default_tone: props.settings.ai_default_tone || 'conversational',
});

const testingConnection = ref(false);
const testResult = ref(null);
const testingGeminiConnection = ref(false);
const geminiTestResult = ref(null);
const testingIdeogramConnection = ref(false);
const ideogramTestResult = ref(null);

const submitForm = () => {
    form.post(route('organization.api-keys.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.openai_api_key = '';
            form.gemini_api_key = '';
            form.ideogram_api_key = '';
        }
    });
};

const testConnection = async () => {
    testingConnection.value = true;
    testResult.value = null;
    
    try {
        const response = await axios.post('/organization/settings/test');
        testResult.value = response.data;
    } catch (error) {
        testResult.value = {
            success: false,
            message: 'Failed to test connection: ' + (error.response?.data?.message || error.message)
        };
    } finally {
        testingConnection.value = false;
    }
};

const testGeminiConnection = async () => {
    testingGeminiConnection.value = true;
    geminiTestResult.value = null;
    
    try {
        const response = await axios.post('/organization/api-keys/test-gemini');
        geminiTestResult.value = response.data;
    } catch (error) {
        geminiTestResult.value = {
            success: false,
            message: 'Failed to test Gemini connection: ' + (error.response?.data?.message || error.message)
        };
    } finally {
        testingGeminiConnection.value = false;
    }
};

const testIdeogramConnection = async () => {
    testingIdeogramConnection.value = true;
    ideogramTestResult.value = null;
    
    try {
        const response = await axios.post('/organization/api-keys/test-ideogram');
        ideogramTestResult.value = response.data;
    } catch (error) {
        ideogramTestResult.value = {
            success: false,
            message: 'Failed to test Ideogram connection: ' + (error.response?.data?.message || error.message)
        };
    } finally {
        testingIdeogramConnection.value = false;
    }
};

</script>

<template>
    <Head title="API Keys" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">API Keys</h1>
                <p class="text-gray-400 mt-1">Manage your API keys and AI content generation settings</p>
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
                    <!-- AI Content Generation -->
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

                    <!-- Gemini AI Integration -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Google Gemini AI</h3>
                                <p class="text-gray-400 text-sm">Connect Gemini for AI image generation (Home Decor theme)</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Gemini API Key</label>
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <input 
                                            v-model="form.gemini_api_key"
                                            type="password" 
                                            :placeholder="settings.gemini_api_key_set ? settings.gemini_api_key_masked + ' (saved - enter new key to update)' : 'AIzaSy...'"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                        <div v-if="settings.gemini_api_key_set" class="absolute right-3 top-1/2 -translate-y-1/2">
                                            <span class="px-2 py-1 bg-blue-900 text-blue-300 text-xs rounded">Connected</span>
                                        </div>
                                    </div>
                                    <button 
                                        type="button"
                                        @click="testGeminiConnection"
                                        :disabled="testingGeminiConnection || !settings.gemini_api_key_set"
                                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                    >
                                        <svg v-if="testingGeminiConnection" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ testingGeminiConnection ? 'Testing...' : 'Test Connection' }}
                                    </button>
                                </div>
                                <p v-if="form.errors.gemini_api_key" class="mt-1 text-sm text-red-500">{{ form.errors.gemini_api_key }}</p>
                                <p class="text-gray-500 text-xs mt-2">
                                    Get your API key from <a href="https://makersuite.google.com/app/apikey" target="_blank" class="text-blue-400 hover:underline">Google AI Studio</a>. Your key is encrypted and stored securely.
                                </p>

                                <!-- Gemini Test Result -->
                                <div v-if="geminiTestResult" :class="[
                                    'mt-3 px-4 py-3 rounded-lg',
                                    geminiTestResult.success ? 'bg-blue-900/50 border border-blue-500 text-blue-200' : 'bg-red-900/50 border border-red-500 text-red-200'
                                ]">
                                    <div class="flex items-center gap-2">
                                        <svg v-if="geminiTestResult.success" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span>{{ geminiTestResult.message }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[#252525] border border-[#3a3a3a] rounded-lg p-4">
                                <h4 class="text-white font-medium mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    What is Gemini used for?
                                </h4>
                                <ul class="text-gray-400 text-sm space-y-1 list-disc list-inside">
                                    <li>AI-powered image generation for articles</li>
                                    <li>Create custom visuals for Home Decor theme</li>
                                    <li>Generate room design images and decor ideas</li>
                                    <li>Enhance articles with unique AI-generated imagery</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Ideogram AI Integration -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Ideogram AI</h3>
                                <p class="text-gray-400 text-sm">Cost-effective AI image generation perfect for Home Decor theme</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Ideogram API Key</label>
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <input 
                                            v-model="form.ideogram_api_key"
                                            type="password" 
                                            :placeholder="settings.ideogram_api_key_set ? settings.ideogram_api_key_masked + ' (saved - enter new key to update)' : 'idg_...'"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                        />
                                        <div v-if="settings.ideogram_api_key_set" class="absolute right-3 top-1/2 -translate-y-1/2">
                                            <span class="px-2 py-1 bg-orange-900 text-orange-300 text-xs rounded">Connected</span>
                                        </div>
                                    </div>
                                    <button 
                                        type="button"
                                        @click="testIdeogramConnection"
                                        :disabled="testingIdeogramConnection || !settings.ideogram_api_key_set"
                                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                    >
                                        <svg v-if="testingIdeogramConnection" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ testingIdeogramConnection ? 'Testing...' : 'Test Connection' }}
                                    </button>
                                </div>
                                <p v-if="form.errors.ideogram_api_key" class="mt-1 text-sm text-red-500">{{ form.errors.ideogram_api_key }}</p>
                                <p class="text-gray-500 text-xs mt-2">
                                    Get your API key from <a href="https://ideogram.ai/api" target="_blank" class="text-orange-400 hover:underline">Ideogram API Dashboard</a>. Your key is encrypted and stored securely.
                                </p>

                                <!-- Ideogram Test Result -->
                                <div v-if="ideogramTestResult" :class="[
                                    'mt-3 px-4 py-3 rounded-lg',
                                    ideogramTestResult.success ? 'bg-orange-900/50 border border-orange-500 text-orange-200' : 'bg-red-900/50 border border-red-500 text-red-200'
                                ]">
                                    <div class="flex items-center gap-2">
                                        <svg v-if="ideogramTestResult.success" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span>{{ ideogramTestResult.message }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[#252525] border border-[#3a3a3a] rounded-lg p-4">
                                <h4 class="text-white font-medium mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Why use Ideogram for Home Decor?
                                </h4>
                                <ul class="text-gray-400 text-sm space-y-1 list-disc list-inside">
                                    <li>75% cheaper than DALL-E for similar quality</li>
                                    <li>Excellent for realistic interior design images</li>
                                    <li>Specialized in home decor, architecture, and design</li>
                                    <li>Fast generation times with high-quality results</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Image Generation Provider Selection -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Image Generation Provider</h3>
                                <p class="text-gray-400 text-sm">Choose which AI to use for generating images (Home Decor theme)</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Select Provider</label>
                                <select 
                                    v-model="form.image_generation_provider"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                >
                                    <option value="openai">OpenAI DALL-E</option>
                                    <option value="ideogram">Ideogram (Recommended for Home Decor)</option>
                                    <option value="gemini">Google Gemini (Experimental)</option>
                                </select>
                                <p class="text-gray-500 text-xs mt-2">
                                    This setting determines which AI service will be used to generate images for your Home Decor articles.
                                </p>
                            </div>

                            <!-- Provider Status Cards -->
                            <div class="grid grid-cols-3 gap-4">
                                <!-- OpenAI Status -->
                                <div :class="[
                                    'p-4 rounded-lg border-2 transition-all',
                                    form.image_generation_provider === 'openai' 
                                        ? 'border-emerald-500 bg-emerald-900/20' 
                                        : 'border-[#3a3a3a] bg-[#252525]'
                                ]">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span class="text-white font-medium">OpenAI</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span v-if="settings.openai_api_key_set" class="px-2 py-1 bg-emerald-900 text-emerald-300 text-xs rounded">
                                            ✓ Connected
                                        </span>
                                        <span v-else class="px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded">
                                            Not configured
                                        </span>
                                    </div>
                                </div>

                                <!-- Ideogram Status -->
                                <div :class="[
                                    'p-4 rounded-lg border-2 transition-all',
                                    form.image_generation_provider === 'ideogram' 
                                        ? 'border-orange-500 bg-orange-900/20' 
                                        : 'border-[#3a3a3a] bg-[#252525]'
                                ]">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-white font-medium">Ideogram</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span v-if="settings.ideogram_api_key_set" class="px-2 py-1 bg-orange-900 text-orange-300 text-xs rounded">
                                            ✓ Connected
                                        </span>
                                        <span v-else class="px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded">
                                            Not configured
                                        </span>
                                    </div>
                                </div>

                                <!-- Gemini Status -->
                                <div :class="[
                                    'p-4 rounded-lg border-2 transition-all',
                                    form.image_generation_provider === 'gemini' 
                                        ? 'border-blue-500 bg-blue-900/20' 
                                        : 'border-[#3a3a3a] bg-[#252525]'
                                ]">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        <span class="text-white font-medium">Gemini</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span v-if="settings.gemini_api_key_set" class="px-2 py-1 bg-blue-900 text-blue-300 text-xs rounded">
                                            ✓ Connected
                                        </span>
                                        <span v-else class="px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded">
                                            Not configured
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning if provider not configured -->
                            <div v-if="form.image_generation_provider === 'openai' && !settings.openai_api_key_set" class="bg-amber-900/50 border border-amber-500 text-amber-200 px-4 py-3 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>OpenAI API key is not configured. Please add your API key above.</span>
                                </div>
                            </div>
                            <div v-if="form.image_generation_provider === 'ideogram' && !settings.ideogram_api_key_set" class="bg-amber-900/50 border border-amber-500 text-amber-200 px-4 py-3 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Ideogram API key is not configured. Please add your API key above.</span>
                                </div>
                            </div>
                            <div v-if="form.image_generation_provider === 'gemini' && !settings.gemini_api_key_set" class="bg-amber-900/50 border border-amber-500 text-amber-200 px-4 py-3 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Gemini API key is not configured. Please add your API key above.</span>
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
