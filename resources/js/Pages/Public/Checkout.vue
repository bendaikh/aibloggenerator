<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    website: Object,
    product: Object,
    paymentMethods: Object,
    stripePublishableKey: String,
    paypalClientId: String,
    paypalMode: String,
});

const form = useForm({
    customer_email: '',
    customer_name: '',
});

const selectedPaymentMethod = ref(null);
const processing = ref(false);
const error = ref(null);
const stripeLoaded = ref(false);
const paypalLoaded = ref(false);

const price = computed(() => {
    return props.product.is_on_sale ? props.product.sale_price : props.product.price;
});

const formatPrice = (amount, currency) => {
    const symbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'CAD': 'C$',
        'AUD': 'A$',
    };
    const symbol = symbols[currency] || currency + ' ';
    return symbol + parseFloat(amount).toFixed(2);
};

const loadStripe = () => {
    if (window.Stripe) {
        stripeLoaded.value = true;
        return;
    }
    
    const script = document.createElement('script');
    script.src = 'https://js.stripe.com/v3/';
    script.onload = () => {
        stripeLoaded.value = true;
    };
    document.head.appendChild(script);
};

const loadPayPal = () => {
    if (window.paypal) {
        paypalLoaded.value = true;
        initPayPalButtons();
        return;
    }
    
    const script = document.createElement('script');
    const clientId = props.paypalClientId;
    script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&currency=${props.product.currency}`;
    script.onload = () => {
        paypalLoaded.value = true;
        initPayPalButtons();
    };
    document.head.appendChild(script);
};

const initPayPalButtons = () => {
    if (!window.paypal || !document.getElementById('paypal-button-container')) return;
    
    document.getElementById('paypal-button-container').innerHTML = '';
    
    window.paypal.Buttons({
        style: {
            layout: 'vertical',
            color: 'blue',
            shape: 'rect',
            label: 'paypal',
        },
        createOrder: async () => {
            if (!form.customer_email) {
                error.value = 'Please enter your email address.';
                return;
            }
            
            try {
                const response = await axios.post(`/site/${props.website.slug}/checkout/${props.product.slug}/paypal/create`, {
                    customer_email: form.customer_email,
                    customer_name: form.customer_name,
                });
                return response.data.orderId;
            } catch (err) {
                error.value = err.response?.data?.error || 'Failed to create PayPal order.';
                throw err;
            }
        },
        onApprove: async (data) => {
            processing.value = true;
            try {
                const response = await axios.post(`/site/${props.website.slug}/checkout/${props.product.slug}/paypal/capture`, {
                    paypal_order_id: data.orderID,
                    order_number: data.orderID,
                });
                
                if (response.data.success) {
                    window.location.href = response.data.redirectUrl;
                }
            } catch (err) {
                error.value = err.response?.data?.error || 'Failed to capture payment.';
                processing.value = false;
            }
        },
        onError: (err) => {
            error.value = 'PayPal encountered an error. Please try again.';
            processing.value = false;
        },
        onCancel: () => {
            processing.value = false;
        },
    }).render('#paypal-button-container');
};

const handleStripeCheckout = async () => {
    if (!form.customer_email) {
        error.value = 'Please enter your email address.';
        return;
    }
    
    processing.value = true;
    error.value = null;
    
    try {
        const response = await axios.post(`/site/${props.website.slug}/checkout/${props.product.slug}/stripe`, {
            customer_email: form.customer_email,
            customer_name: form.customer_name,
        });
        
        window.location.href = response.data.url;
    } catch (err) {
        error.value = err.response?.data?.error || 'Failed to start checkout.';
        processing.value = false;
    }
};

onMounted(() => {
    if (props.paymentMethods.stripe) {
        loadStripe();
    }
    if (props.paymentMethods.paypal) {
        loadPayPal();
    }
    
    if (props.paymentMethods.stripe && !props.paymentMethods.paypal) {
        selectedPaymentMethod.value = 'stripe';
    } else if (props.paymentMethods.paypal && !props.paymentMethods.stripe) {
        selectedPaymentMethod.value = 'paypal';
    }
});
</script>

<template>
    <Head :title="`Checkout - ${product.name}`">
        <link v-if="website.favicon_url" rel="icon" :href="website.favicon_url" />
    </Head>

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
                <a :href="website.url" class="flex items-center gap-2">
                    <img 
                        v-if="website.logo_url" 
                        :src="website.logo_url" 
                        :alt="website.name" 
                        class="h-8 w-auto"
                    />
                    <span class="text-xl font-semibold text-gray-900">{{ website.name }}</span>
                </a>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Secure Checkout
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 py-8 md:py-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column - Customer Info & Payment -->
                <div class="space-y-6">
                    <h1 class="text-2xl font-bold text-gray-900">Checkout</h1>

                    <!-- Error Message -->
                    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        {{ error }}
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                <input 
                                    v-model="form.customer_email"
                                    type="email"
                                    required
                                    placeholder="you@example.com"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                />
                                <p class="mt-1 text-xs text-gray-500">Your order confirmation will be sent here</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name (Optional)</label>
                                <input 
                                    v-model="form.customer_name"
                                    type="text"
                                    placeholder="John Doe"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h2>
                        
                        <div class="space-y-3">
                            <!-- Stripe Option -->
                            <label 
                                v-if="paymentMethods.stripe"
                                :class="[
                                    'flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all',
                                    selectedPaymentMethod === 'stripe' 
                                        ? 'border-blue-500 bg-blue-50' 
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <input 
                                    type="radio" 
                                    v-model="selectedPaymentMethod" 
                                    value="stripe" 
                                    class="sr-only"
                                />
                                <div class="flex items-center gap-3 flex-1">
                                    <svg class="w-10 h-7 text-indigo-600" viewBox="0 0 60 25" fill="currentColor">
                                        <path d="M59.64 14.28c0-4.13-2-7.4-5.83-7.4-3.85 0-6.18 3.27-6.18 7.36 0 4.86 2.75 7.31 6.69 7.31 1.92 0 3.38-.44 4.48-1.05v-3.22c-1.1.55-2.36.89-3.96.89-1.57 0-2.96-.55-3.14-2.46h7.92c0-.21.02-1.04.02-1.43zm-8-1.55c0-1.83 1.12-2.59 2.14-2.59 1 0 2.04.76 2.04 2.59h-4.18zM40.95 6.88c-1.58 0-2.6.74-3.16 1.26l-.21-.99h-3.54v19.64l4.03-.86.01-4.77c.58.42 1.43 1.02 2.84 1.02 2.88 0 5.5-2.31 5.5-7.41-.01-4.67-2.67-7.89-5.47-7.89zm-.96 12.13c-.95 0-1.5-.34-1.89-.76l-.02-5.98c.42-.47.99-.79 1.91-.79 1.46 0 2.47 1.64 2.47 3.77 0 2.17-1 3.76-2.47 3.76zM28.24 5.57l4.05-.87V1.3l-4.05.86v3.41zM28.24 7.15h4.05v14.31h-4.05V7.15zM23.54 8.41l-.25-1.26h-3.47v14.31h4.03v-9.71c.95-1.24 2.56-1.01 3.07-.84V7.15c-.52-.19-2.42-.55-3.38 1.26zM15.43 2.91l-3.94.84-.02 13.12c0 2.42 1.82 4.21 4.24 4.21 1.34 0 2.32-.25 2.86-.54v-3.28c-.52.21-3.1.96-3.1-1.44V10.38h3.1V7.15h-3.1l-.04-4.24zM4.35 11.39c0-.63.52-.87 1.38-.87 1.23 0 2.79.37 4.02 1.04V7.89a10.74 10.74 0 00-4.02-.74C2.27 7.15 0 8.94 0 11.61c0 4.17 5.74 3.5 5.74 5.3 0 .75-.65 1-1.56 1-1.35 0-3.08-.56-4.45-1.31v3.72c1.51.65 3.04.93 4.45.93 3.55 0 5.99-1.76 5.99-4.47-.01-4.5-5.82-3.69-5.82-5.39z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">Card Payment</p>
                                        <p class="text-sm text-gray-500">Visa, Mastercard, Amex & more</p>
                                    </div>
                                </div>
                                <div v-if="selectedPaymentMethod === 'stripe'" class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </label>

                            <!-- PayPal Option -->
                            <label 
                                v-if="paymentMethods.paypal"
                                :class="[
                                    'flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all',
                                    selectedPaymentMethod === 'paypal' 
                                        ? 'border-blue-500 bg-blue-50' 
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <input 
                                    type="radio" 
                                    v-model="selectedPaymentMethod" 
                                    value="paypal" 
                                    class="sr-only"
                                />
                                <div class="flex items-center gap-3 flex-1">
                                    <svg class="w-10 h-7 text-blue-600" viewBox="0 0 101 32" fill="currentColor">
                                        <path d="M12.237 2.8h-7.8c-.5 0-1 .4-1.1.9L.3 25.1c-.1.4.2.8.7.8h3.7c.5 0 1-.4 1.1-.9l.8-5.4c.1-.5.5-.9 1.1-.9h2.5c5.1 0 8.1-2.5 8.8-7.4.3-2.1 0-3.8-1-5-1.1-1.3-3-2-5.5-2h-.3zm.9 7.3c-.4 2.8-2.6 2.8-4.7 2.8H7.4l.8-5.4c.1-.3.3-.5.6-.5h.5c1.4 0 2.8 0 3.5.8.4.5.6 1.2.4 2.3zm22.1-.1h-3.7c-.3 0-.6.2-.6.5l-.2 1-.3-.4c-.8-1.2-2.6-1.6-4.4-1.6-4.1 0-7.6 3.1-8.3 7.5-.4 2.2.1 4.3 1.4 5.7 1.1 1.3 2.8 1.9 4.7 1.9 3.3 0 5.2-2.1 5.2-2.1l-.2 1c-.1.4.2.8.7.8h3.4c.5 0 1-.4 1.1-.9l2-12.5c.1-.5-.3-.9-.8-.9zm-5.2 7.3c-.4 2.1-2.1 3.5-4.2 3.5-1.1 0-1.9-.3-2.5-1-.5-.6-.7-1.6-.5-2.6.3-2.1 2.1-3.5 4.2-3.5 1 0 1.9.4 2.5 1 .5.7.7 1.6.5 2.6zm27.3-7.3h-3.7c-.4 0-.7.2-.9.5l-5.3 7.8-2.2-7.5c-.2-.5-.6-.8-1.1-.8H40.4c-.5 0-.8.5-.7.9l4.2 12.2-3.9 5.5c-.3.5 0 1.1.6 1.1h3.7c.4 0 .7-.2.9-.5l12.8-18.4c.3-.5 0-1.1-.6-1.1zM68.4 2.8h-7.8c-.5 0-1 .4-1.1.9L56.4 25c-.1.4.2.8.7.8h4c.4 0 .7-.3.7-.6l.9-5.6c.1-.5.5-.9 1.1-.9h2.5c5.1 0 8.1-2.5 8.8-7.4.3-2.1 0-3.8-1-5-1.1-1.4-3-2-5.5-2h-.2zm.9 7.3c-.4 2.8-2.6 2.8-4.7 2.8h-1.2l.8-5.4c.1-.3.3-.5.6-.5h.5c1.4 0 2.8 0 3.5.8.5.5.6 1.2.5 2.3zm22.1-.1h-3.7c-.3 0-.6.2-.6.5l-.2 1-.3-.4c-.8-1.2-2.6-1.6-4.4-1.6-4.1 0-7.6 3.1-8.3 7.5-.4 2.2.1 4.3 1.4 5.7 1.1 1.3 2.8 1.9 4.7 1.9 3.3 0 5.2-2.1 5.2-2.1l-.2 1c-.1.4.2.8.7.8h3.4c.5 0 1-.4 1.1-.9l2-12.5c.1-.5-.2-.9-.8-.9zm-5.2 7.3c-.4 2.1-2.1 3.5-4.2 3.5-1.1 0-1.9-.3-2.5-1-.5-.6-.7-1.6-.5-2.6.3-2.1 2.1-3.5 4.2-3.5 1 0 1.9.4 2.5 1 .5.7.7 1.6.5 2.6zm10.9-14.4l-3.2 20.5c-.1.4.2.8.7.8h3.2c.5 0 1-.4 1.1-.9L101.1 3c.1-.4-.2-.8-.7-.8h-3.5c-.2 0-.5.2-.6.5z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">PayPal</p>
                                        <p class="text-sm text-gray-500">Pay with your PayPal account</p>
                                    </div>
                                </div>
                                <div v-if="selectedPaymentMethod === 'paypal'" class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </label>
                        </div>

                        <!-- Payment Buttons -->
                        <div class="mt-6">
                            <!-- Stripe Button -->
                            <button 
                                v-if="selectedPaymentMethod === 'stripe'"
                                @click="handleStripeCheckout"
                                :disabled="processing || !form.customer_email"
                                class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <svg v-if="processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                {{ processing ? 'Processing...' : `Pay ${formatPrice(price, product.currency)}` }}
                            </button>

                            <!-- PayPal Button Container -->
                            <div 
                                v-if="selectedPaymentMethod === 'paypal'"
                                id="paypal-button-container"
                                class="mt-2"
                            ></div>

                            <!-- No Payment Method Selected -->
                            <p v-if="!selectedPaymentMethod" class="text-center text-gray-500 py-4">
                                Please select a payment method above
                            </p>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="flex items-center gap-3 text-sm text-gray-500">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Your payment information is encrypted and secure</span>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                        
                        <!-- Product -->
                        <div class="flex gap-4 pb-4 border-b border-gray-200">
                            <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                <img 
                                    v-if="product.processed_featured_image || product.featured_image"
                                    :src="product.processed_featured_image || product.featured_image"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 truncate">{{ product.name }}</h3>
                                <p v-if="product.short_description" class="text-sm text-gray-500 line-clamp-2 mt-1">
                                    {{ product.short_description }}
                                </p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span v-if="product.is_digital" class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                                        Digital
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="py-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="text-gray-900">{{ formatPrice(price, product.currency) }}</span>
                            </div>
                            <div v-if="product.is_on_sale" class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount</span>
                                <span class="text-green-600">
                                    -{{ formatPrice(product.price - product.sale_price, product.currency) }}
                                </span>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total</span>
                                <span class="text-2xl font-bold text-gray-900">
                                    {{ formatPrice(price, product.currency) }}
                                </span>
                            </div>
                        </div>

                        <!-- Return Link -->
                        <a 
                            :href="`${website.url}/shop/${product.slug}`"
                            class="block mt-6 text-center text-sm text-blue-600 hover:text-blue-700"
                        >
                            ← Return to product page
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 py-6">
            <div class="max-w-4xl mx-auto px-4 text-center text-sm text-gray-500">
                <p>&copy; {{ new Date().getFullYear() }} {{ website.name }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
