<script setup>
import { ref } from 'vue';
import axios from 'axios';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    payments_enabled: props.settings.payments_enabled || false,
    stripe_publishable_key: '',
    stripe_secret_key: '',
    stripe_webhook_secret: '',
    paypal_client_id: '',
    paypal_client_secret: '',
    paypal_mode: props.settings.paypal_mode || 'sandbox',
});

const testingStripe = ref(false);
const stripeTestResult = ref(null);
const testingPaypal = ref(false);
const paypalTestResult = ref(null);

const submitForm = () => {
    form.post(route('organization.payments.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.stripe_publishable_key = '';
            form.stripe_secret_key = '';
            form.stripe_webhook_secret = '';
            form.paypal_client_id = '';
            form.paypal_client_secret = '';
        }
    });
};

const testStripeConnection = async () => {
    testingStripe.value = true;
    stripeTestResult.value = null;
    
    try {
        const response = await axios.post('/organization/payments/test-stripe');
        stripeTestResult.value = response.data;
    } catch (error) {
        stripeTestResult.value = {
            success: false,
            message: 'Failed to test Stripe connection: ' + (error.response?.data?.message || error.message)
        };
    } finally {
        testingStripe.value = false;
    }
};

const testPaypalConnection = async () => {
    testingPaypal.value = true;
    paypalTestResult.value = null;
    
    try {
        const response = await axios.post('/organization/payments/test-paypal');
        paypalTestResult.value = response.data;
    } catch (error) {
        paypalTestResult.value = {
            success: false,
            message: 'Failed to test PayPal connection: ' + (error.response?.data?.message || error.message)
        };
    } finally {
        testingPaypal.value = false;
    }
};

const disconnectStripe = () => {
    if (confirm('Are you sure you want to disconnect Stripe? This will remove all Stripe API credentials.')) {
        router.post(route('organization.payments.disconnect-stripe'), {}, {
            preserveScroll: true,
        });
    }
};

const disconnectPaypal = () => {
    if (confirm('Are you sure you want to disconnect PayPal? This will remove all PayPal API credentials.')) {
        router.post(route('organization.payments.disconnect-paypal'), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Payments Integration" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Payments Integration</h1>
                <p class="text-gray-400 mt-1">Connect payment providers to accept payments for your products across all websites</p>
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
                    <!-- Enable Payments Toggle -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">Enable Payments</h3>
                                    <p class="text-gray-400 text-sm">Allow customers to purchase products from your websites</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    v-model="form.payments_enabled" 
                                    class="sr-only peer"
                                >
                                <div class="w-11 h-6 bg-[#3a3a3a] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                        
                        <div v-if="!settings.stripe_configured && !settings.paypal_configured" class="mt-4 bg-amber-900/30 border border-amber-600 text-amber-200 px-4 py-3 rounded-lg">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Please configure at least one payment provider (Stripe or PayPal) below to accept payments.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stripe Integration -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.594-7.305h.003z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">Stripe</h3>
                                    <p class="text-gray-400 text-sm">Accept credit/debit cards, Apple Pay, Google Pay, and more</p>
                                </div>
                            </div>
                            <div v-if="settings.stripe_configured" class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-indigo-900 text-indigo-300 text-sm rounded-full font-medium">Connected</span>
                                <button 
                                    type="button" 
                                    @click="disconnectStripe"
                                    class="text-red-400 hover:text-red-300 text-sm"
                                >
                                    Disconnect
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Publishable Key</label>
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <input 
                                            v-model="form.stripe_publishable_key"
                                            type="password" 
                                            :placeholder="settings.stripe_publishable_key_masked ? settings.stripe_publishable_key_masked + ' (saved)' : 'pk_live_... or pk_test_...'"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Secret Key</label>
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <input 
                                            v-model="form.stripe_secret_key"
                                            type="password" 
                                            :placeholder="settings.stripe_secret_key_masked ? settings.stripe_secret_key_masked + ' (saved)' : 'sk_live_... or sk_test_...'"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Webhook Secret (Optional)</label>
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <input 
                                            v-model="form.stripe_webhook_secret"
                                            type="password" 
                                            :placeholder="settings.stripe_webhook_secret_set ? 'whsec_**** (saved)' : 'whsec_...'"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        />
                                    </div>
                                </div>
                                <p class="text-gray-500 text-xs mt-1">Required for handling webhooks (payment confirmations, refunds, etc.)</p>
                            </div>

                            <div class="flex items-center gap-3">
                                <button 
                                    type="button"
                                    @click="testStripeConnection"
                                    :disabled="testingStripe || !settings.stripe_configured"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <svg v-if="testingStripe" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ testingStripe ? 'Testing...' : 'Test Connection' }}
                                </button>
                            </div>

                            <!-- Stripe Test Result -->
                            <div v-if="stripeTestResult" :class="[
                                'px-4 py-3 rounded-lg',
                                stripeTestResult.success ? 'bg-emerald-900/50 border border-emerald-500 text-emerald-200' : 'bg-red-900/50 border border-red-500 text-red-200'
                            ]">
                                <div class="flex items-center gap-2">
                                    <svg v-if="stripeTestResult.success" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>{{ stripeTestResult.message }}</span>
                                </div>
                            </div>

                            <!-- Stripe Documentation -->
                            <div class="bg-[#252525] border border-[#3a3a3a] rounded-lg p-5 space-y-4">
                                <h4 class="text-white font-medium flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    How to Get Your Stripe API Keys
                                </h4>
                                
                                <div class="space-y-3">
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold">1</div>
                                        <div>
                                            <p class="text-gray-300 text-sm font-medium">Create a Stripe Account</p>
                                            <p class="text-gray-500 text-xs">Go to <a href="https://stripe.com" target="_blank" class="text-indigo-400 hover:underline">stripe.com</a> and sign up for a free account. Complete the account verification process.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold">2</div>
                                        <div>
                                            <p class="text-gray-300 text-sm font-medium">Access the API Keys Page</p>
                                            <p class="text-gray-500 text-xs">In your Stripe Dashboard, go to <strong>Developers</strong> → <strong>API Keys</strong>, or visit <a href="https://dashboard.stripe.com/apikeys" target="_blank" class="text-indigo-400 hover:underline">dashboard.stripe.com/apikeys</a></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold">3</div>
                                        <div>
                                            <p class="text-gray-300 text-sm font-medium">Copy Your Keys</p>
                                            <p class="text-gray-500 text-xs"><strong>Publishable Key:</strong> Starts with <code class="bg-[#1a1a1a] px-1 rounded">pk_test_</code> or <code class="bg-[#1a1a1a] px-1 rounded">pk_live_</code></p>
                                            <p class="text-gray-500 text-xs mt-1"><strong>Secret Key:</strong> Starts with <code class="bg-[#1a1a1a] px-1 rounded">sk_test_</code> or <code class="bg-[#1a1a1a] px-1 rounded">sk_live_</code> (click "Reveal" to see it)</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold">4</div>
                                        <div>
                                            <p class="text-gray-300 text-sm font-medium">Webhook Secret (Optional)</p>
                                            <p class="text-gray-500 text-xs">Go to <strong>Developers</strong> → <strong>Webhooks</strong> → <strong>Add endpoint</strong>. Add your webhook URL and copy the signing secret that starts with <code class="bg-[#1a1a1a] px-1 rounded">whsec_</code></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-[#3a3a3a] pt-4 mt-4">
                                    <p class="text-amber-400 text-xs flex items-start gap-2">
                                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span><strong>Test vs Live Keys:</strong> Use test keys (<code class="bg-[#1a1a1a] px-1 rounded">pk_test_</code>, <code class="bg-[#1a1a1a] px-1 rounded">sk_test_</code>) during development. Switch to live keys when you're ready to accept real payments.</span>
                                    </p>
                                </div>

                                <div class="bg-[#1a1a1a] rounded-lg p-3">
                                    <p class="text-gray-400 text-xs font-medium mb-2">Supported Payment Methods:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">Visa</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">Mastercard</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">American Express</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">Apple Pay</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">Google Pay</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">Bank Transfers</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PayPal Integration -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72a.77.77 0 0 1 .76-.65h6.21c2.068 0 3.51.555 4.286 1.651.287.405.5.861.624 1.372.126.527.156 1.117.09 1.8v.478l.362.204c.31.168.583.37.818.606a3.85 3.85 0 0 1 .897 2.144c.058.462.058.946 0 1.456a6.434 6.434 0 0 1-.702 2.135c-.25.472-.562.883-.93 1.235a4.34 4.34 0 0 1-1.374.868c-.53.22-1.13.377-1.79.476-.673.1-1.423.15-2.236.15H9.804a.96.96 0 0 0-.949.817l-.042.212-.69 4.367-.034.185a.096.096 0 0 1-.095.082H7.076z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">PayPal</h3>
                                    <p class="text-gray-400 text-sm">Accept PayPal payments and PayPal Credit</p>
                                </div>
                            </div>
                            <div v-if="settings.paypal_configured" class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-blue-900 text-blue-300 text-sm rounded-full font-medium">Connected</span>
                                <button 
                                    type="button" 
                                    @click="disconnectPaypal"
                                    class="text-red-400 hover:text-red-300 text-sm"
                                >
                                    Disconnect
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Client ID</label>
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <input 
                                            v-model="form.paypal_client_id"
                                            type="password" 
                                            :placeholder="settings.paypal_client_id_masked ? settings.paypal_client_id_masked + ' (saved)' : 'AeA... (Client ID)'"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Client Secret</label>
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <input 
                                            v-model="form.paypal_client_secret"
                                            type="password" 
                                            :placeholder="settings.paypal_client_secret_set ? '**** (saved)' : 'EL... (Client Secret)'"
                                            class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-gray-400 text-sm block mb-2">Environment</label>
                                <select 
                                    v-model="form.paypal_mode"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="sandbox">Sandbox (Testing)</option>
                                    <option value="live">Live (Production)</option>
                                </select>
                                <p class="text-gray-500 text-xs mt-1">Use Sandbox for testing, switch to Live when ready to accept real payments</p>
                            </div>

                            <div class="flex items-center gap-3">
                                <button 
                                    type="button"
                                    @click="testPaypalConnection"
                                    :disabled="testingPaypal || !settings.paypal_configured"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <svg v-if="testingPaypal" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ testingPaypal ? 'Testing...' : 'Test Connection' }}
                                </button>
                            </div>

                            <!-- PayPal Test Result -->
                            <div v-if="paypalTestResult" :class="[
                                'px-4 py-3 rounded-lg',
                                paypalTestResult.success ? 'bg-emerald-900/50 border border-emerald-500 text-emerald-200' : 'bg-red-900/50 border border-red-500 text-red-200'
                            ]">
                                <div class="flex items-center gap-2">
                                    <svg v-if="paypalTestResult.success" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>{{ paypalTestResult.message }}</span>
                                </div>
                            </div>

                            <!-- PayPal Documentation -->
                            <div class="bg-[#252525] border border-[#3a3a3a] rounded-lg p-5 space-y-4">
                                <h4 class="text-white font-medium flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    How to Get Your PayPal API Credentials
                                </h4>
                                
                                <div class="space-y-3">
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">1</div>
                                        <div>
                                            <p class="text-gray-300 text-sm font-medium">Create a PayPal Developer Account</p>
                                            <p class="text-gray-500 text-xs">Go to <a href="https://developer.paypal.com" target="_blank" class="text-blue-400 hover:underline">developer.paypal.com</a> and log in with your PayPal account (or create one).</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">2</div>
                                        <div>
                                            <p class="text-gray-300 text-sm font-medium">Go to Apps & Credentials</p>
                                            <p class="text-gray-500 text-xs">In the Developer Dashboard, navigate to <strong>Apps & Credentials</strong> or visit <a href="https://developer.paypal.com/dashboard/applications/sandbox" target="_blank" class="text-blue-400 hover:underline">developer.paypal.com/dashboard/applications</a></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">3</div>
                                        <div>
                                            <p class="text-gray-300 text-sm font-medium">Create a New App</p>
                                            <p class="text-gray-500 text-xs">Click <strong>"Create App"</strong> button. Enter an app name (e.g., "My Store Payments") and select <strong>"Merchant"</strong> as the app type. Click <strong>"Create App"</strong>.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">4</div>
                                        <div>
                                            <p class="text-gray-300 text-sm font-medium">Copy Your Credentials</p>
                                            <p class="text-gray-500 text-xs"><strong>Client ID:</strong> Visible immediately after creating the app</p>
                                            <p class="text-gray-500 text-xs mt-1"><strong>Client Secret:</strong> Click <strong>"Show"</strong> under Secret to reveal it</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">5</div>
                                        <div>
                                            <p class="text-gray-300 text-sm font-medium">Switch to Live Mode</p>
                                            <p class="text-gray-500 text-xs">Toggle between <strong>"Sandbox"</strong> and <strong>"Live"</strong> at the top of the page to get the appropriate credentials for testing or production.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-[#3a3a3a] pt-4 mt-4">
                                    <p class="text-amber-400 text-xs flex items-start gap-2">
                                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span><strong>Sandbox vs Live:</strong> Use Sandbox credentials for testing (no real money). Switch to Live credentials when ready to accept real payments. Make sure to update the Environment setting above accordingly.</span>
                                    </p>
                                </div>

                                <div class="bg-[#1a1a1a] rounded-lg p-3">
                                    <p class="text-gray-400 text-xs font-medium mb-2">Supported Payment Methods:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">PayPal Balance</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">PayPal Credit</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">Pay in 4</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">Bank Account</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">Debit Card</span>
                                        <span class="px-2 py-1 bg-[#252525] text-gray-300 text-xs rounded">Credit Card</span>
                                    </div>
                                </div>

                                <div class="bg-blue-900/30 border border-blue-700 rounded-lg p-3">
                                    <p class="text-blue-300 text-xs flex items-start gap-2">
                                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span><strong>Tip:</strong> You can test sandbox payments using PayPal's test accounts. Go to <strong>Sandbox</strong> → <strong>Accounts</strong> in the Developer Dashboard to create test buyer/seller accounts.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods Summary -->
                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Payment Methods Status</h3>
                                <p class="text-gray-400 text-sm">Available payment options for your customers</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Stripe Status Card -->
                            <div :class="[
                                'p-4 rounded-lg border-2 transition-all',
                                settings.stripe_configured 
                                    ? 'border-indigo-500 bg-indigo-900/20' 
                                    : 'border-[#3a3a3a] bg-[#252525]'
                            ]">
                                <div class="flex items-center gap-3 mb-3">
                                    <svg class="w-8 h-8 text-indigo-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.594-7.305h.003z"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-white font-semibold">Stripe</h4>
                                        <p class="text-gray-400 text-xs">Cards, Apple Pay, Google Pay</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="settings.stripe_configured" class="px-2 py-1 bg-emerald-900 text-emerald-300 text-xs rounded font-medium">
                                        Ready to accept payments
                                    </span>
                                    <span v-else class="px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded">
                                        Not configured
                                    </span>
                                </div>
                            </div>

                            <!-- PayPal Status Card -->
                            <div :class="[
                                'p-4 rounded-lg border-2 transition-all',
                                settings.paypal_configured 
                                    ? 'border-blue-500 bg-blue-900/20' 
                                    : 'border-[#3a3a3a] bg-[#252525]'
                            ]">
                                <div class="flex items-center gap-3 mb-3">
                                    <svg class="w-8 h-8 text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72a.77.77 0 0 1 .76-.65h6.21c2.068 0 3.51.555 4.286 1.651.287.405.5.861.624 1.372.126.527.156 1.117.09 1.8v.478l.362.204c.31.168.583.37.818.606a3.85 3.85 0 0 1 .897 2.144c.058.462.058.946 0 1.456a6.434 6.434 0 0 1-.702 2.135c-.25.472-.562.883-.93 1.235a4.34 4.34 0 0 1-1.374.868c-.53.22-1.13.377-1.79.476-.673.1-1.423.15-2.236.15H9.804a.96.96 0 0 0-.949.817l-.042.212-.69 4.367-.034.185a.096.096 0 0 1-.095.082H7.076z"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-white font-semibold">PayPal</h4>
                                        <p class="text-gray-400 text-xs">PayPal balance, Credit, Banks</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="settings.paypal_configured" class="px-2 py-1 bg-emerald-900 text-emerald-300 text-xs rounded font-medium">
                                        Ready ({{ settings.paypal_mode === 'live' ? 'Live' : 'Sandbox' }})
                                    </span>
                                    <span v-else class="px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded">
                                        Not configured
                                    </span>
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
