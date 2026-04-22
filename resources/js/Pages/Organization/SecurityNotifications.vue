<script setup>
import { ref } from 'vue';
import axios from 'axios';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    twilio_sid: '',
    twilio_auth_token: '',
    twilio_whatsapp_from: props.settings.twilio_whatsapp_from || '+14155238886',
    admin_whatsapp_number: props.settings.admin_whatsapp_number || '+212634741761',
    security_alerts_enabled: props.settings.security_alerts_enabled ?? true,
});

const testingConnection = ref(false);
const testResult = ref(null);

const submitForm = () => {
    form.post(route('organization.security-notifications.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.twilio_sid = '';
            form.twilio_auth_token = '';
        }
    });
};

const testConnection = async () => {
    testingConnection.value = true;
    testResult.value = null;
    
    try {
        const response = await axios.post('/organization/security-notifications/test-twilio');
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
</script>

<template>
    <Head title="Security Notifications" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Security Notifications</h1>
                <p class="text-gray-400 mt-1">Configure WhatsApp alerts for superadmin login attempts</p>
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

            <!-- Info Box -->
            <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-6 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-blue-300 font-medium mb-2">🔒 SuperAdmin Login Monitoring</p>
                        <p class="text-blue-200 text-sm">
                            Receive instant WhatsApp alerts whenever someone logs in as a superadmin. Each notification includes:
                        </p>
                        <ul class="text-blue-200 text-sm mt-2 space-y-1 list-disc list-inside">
                            <li>User name and email</li>
                            <li>IP address and geographic location</li>
                            <li>Browser and device information</li>
                            <li>Exact timestamp of login</li>
                        </ul>
                        <p class="text-blue-200 text-sm mt-3">
                            This helps you detect unauthorized access attempts and keep your application secure.
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitForm">
                <div class="space-y-6">
                    <!-- Twilio WhatsApp Configuration -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Twilio WhatsApp Setup</h3>
                                <p class="text-gray-400 text-sm">Connect your Twilio account to send WhatsApp notifications</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Twilio Account SID</label>
                                <div class="relative">
                                    <input 
                                        v-model="form.twilio_sid"
                                        type="password" 
                                        :placeholder="settings.twilio_sid_set ? settings.twilio_sid_masked + ' (saved - enter new SID to update)' : 'AC...'"
                                        class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                    <div v-if="settings.twilio_sid_set" class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <span class="px-2 py-1 bg-green-900 text-green-300 text-xs rounded">Configured</span>
                                    </div>
                                </div>
                                <p v-if="form.errors.twilio_sid" class="mt-1 text-sm text-red-500">{{ form.errors.twilio_sid }}</p>
                                <p class="text-gray-500 text-xs mt-2">
                                    Your Account SID from <a href="https://console.twilio.com/" target="_blank" class="text-green-400 hover:underline">Twilio Console</a>
                                </p>
                            </div>

                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Twilio Auth Token</label>
                                <input 
                                    v-model="form.twilio_auth_token"
                                    type="password" 
                                    :placeholder="settings.twilio_auth_token_set ? settings.twilio_auth_token_masked + ' (saved - enter new token to update)' : 'Your Twilio Auth Token'"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                                <p v-if="form.errors.twilio_auth_token" class="mt-1 text-sm text-red-500">{{ form.errors.twilio_auth_token }}</p>
                                <p class="text-gray-500 text-xs mt-2">
                                    Your Auth Token from Twilio Console (kept encrypted)
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-gray-400 text-sm block mb-2">Twilio WhatsApp From Number</label>
                                    <input 
                                        v-model="form.twilio_whatsapp_from"
                                        type="text" 
                                        placeholder="+14155238886"
                                        class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                    <p v-if="form.errors.twilio_whatsapp_from" class="mt-1 text-sm text-red-500">{{ form.errors.twilio_whatsapp_from }}</p>
                                    <p class="text-gray-500 text-xs mt-1">Twilio sandbox number (default: +1 415 523 8886)</p>
                                </div>

                                <div>
                                    <label class="text-gray-400 text-sm block mb-2">Your WhatsApp Number *</label>
                                    <input 
                                        v-model="form.admin_whatsapp_number"
                                        type="text" 
                                        placeholder="+212634741761"
                                        required
                                        class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    />
                                    <p v-if="form.errors.admin_whatsapp_number" class="mt-1 text-sm text-red-500">{{ form.errors.admin_whatsapp_number }}</p>
                                    <p class="text-gray-500 text-xs mt-1">Where to receive security alerts</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 p-4 bg-[#252525] rounded-lg border border-[#3a3a3a]">
                                <label class="flex items-center cursor-pointer flex-1">
                                    <input 
                                        v-model="form.security_alerts_enabled"
                                        type="checkbox" 
                                        class="w-5 h-5 bg-[#1a1a1a] border-[#3a3a3a] rounded text-green-500 focus:ring-2 focus:ring-green-500"
                                    />
                                    <span class="ml-3 text-white font-medium">Enable Security Alerts</span>
                                </label>
                                <div class="text-xs text-gray-500">
                                    {{ form.security_alerts_enabled ? '✓ Enabled' : '✗ Disabled' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Setup Instructions -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Quick Setup Guide
                        </h3>
                        <div class="space-y-3 text-sm text-gray-300">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center font-semibold text-xs">1</div>
                                <div>
                                    <p class="font-medium text-white mb-1">Sign up for Twilio</p>
                                    <p class="text-gray-400">Visit <a href="https://www.twilio.com/try-twilio" target="_blank" class="text-green-400 hover:underline">Twilio.com</a> and create a free account</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center font-semibold text-xs">2</div>
                                <div>
                                    <p class="font-medium text-white mb-1">Connect WhatsApp Sandbox</p>
                                    <p class="text-gray-400">In Twilio Console: Messaging → Try it out → Send a WhatsApp message</p>
                                    <p class="text-gray-400 mt-1">Send the join code from YOUR WhatsApp (+212634741761) to their number</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center font-semibold text-xs">3</div>
                                <div>
                                    <p class="font-medium text-white mb-1">Get Your Credentials</p>
                                    <p class="text-gray-400">Copy Account SID and Auth Token from your Twilio Dashboard</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-6 h-6 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center font-semibold text-xs">4</div>
                                <div>
                                    <p class="font-medium text-white mb-1">Save & Test</p>
                                    <p class="text-gray-400">Paste your credentials above, save, and use the Test button to verify</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                        </button>

                        <button 
                            type="button"
                            @click="testConnection"
                            :disabled="testingConnection || !settings.twilio_sid_set || !settings.twilio_auth_token_set"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="testingConnection" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ testingConnection ? 'Testing...' : 'Send Test Alert' }}
                        </button>
                    </div>

                    <!-- Test Result -->
                    <div v-if="testResult" :class="[
                        'px-6 py-4 rounded-lg',
                        testResult.success ? 'bg-green-900/50 border border-green-500 text-green-200' : 'bg-red-900/50 border border-red-500 text-red-200'
                    ]">
                        <div class="flex items-center gap-3">
                            <svg v-if="testResult.success" class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>{{ testResult.message }}</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </OrganizationLayout>
</template>
