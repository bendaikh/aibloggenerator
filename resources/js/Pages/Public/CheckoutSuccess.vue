<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    website: Object,
    product: Object,
    order: Object,
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
</script>

<template>
    <Head :title="`Order Confirmed - ${website.name}`">
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
            </div>
        </header>

        <main class="max-w-2xl mx-auto px-4 py-12 md:py-20">
            <!-- Success Icon -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Thank You!</h1>
                <p class="text-lg text-gray-600">Your order has been confirmed.</p>
            </div>

            <!-- Order Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Order Number -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Order Number</span>
                        <span class="font-mono font-semibold text-gray-900">{{ order.order_number }}</span>
                    </div>
                </div>

                <!-- Product -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex gap-4">
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
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ product.name }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span v-if="product.is_digital" class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                    Digital Product
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold text-gray-900">
                                {{ formatPrice(order.amount, order.currency) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Info -->
                <div class="p-6 space-y-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Email</span>
                        <span class="text-gray-900">{{ order.customer_email }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Payment Method</span>
                        <span class="text-gray-900 capitalize">{{ order.payment_provider }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Status</span>
                        <span :class="[
                            'px-2 py-0.5 rounded-full text-xs font-medium',
                            order.status === 'paid' || order.status === 'fulfilled' 
                                ? 'bg-green-100 text-green-700' 
                                : 'bg-yellow-100 text-yellow-700'
                        ]">
                            {{ order.status === 'paid' ? 'Paid' : order.status === 'fulfilled' ? 'Fulfilled' : 'Processing' }}
                        </span>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Total Paid</span>
                            <span class="text-2xl font-bold text-gray-900">
                                {{ formatPrice(order.amount, order.currency) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Notice -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <div>
                        <p class="font-medium text-blue-900">Confirmation Email Sent</p>
                        <p class="text-sm text-blue-700 mt-1">
                            We've sent a confirmation email to <strong>{{ order.customer_email }}</strong>. 
                            Please check your inbox (and spam folder).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Digital Product Notice -->
            <div v-if="product.is_digital && product.digital_file_url" class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <div>
                        <p class="font-medium text-green-900">Download Your Product</p>
                        <p class="text-sm text-green-700 mt-1">
                            Your digital product is ready for download. Click the button below to get your files.
                        </p>
                        <a 
                            :href="product.digital_file_url"
                            target="_blank"
                            class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Now
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <a 
                    :href="`${website.url}/shop`"
                    class="flex-1 py-3 px-6 bg-gray-900 hover:bg-gray-800 text-white font-medium rounded-lg text-center transition-colors"
                >
                    Continue Shopping
                </a>
                <a 
                    :href="website.url"
                    class="flex-1 py-3 px-6 border border-gray-300 hover:border-gray-400 text-gray-700 font-medium rounded-lg text-center transition-colors"
                >
                    Return to Home
                </a>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 py-6 mt-auto">
            <div class="max-w-4xl mx-auto px-4 text-center text-sm text-gray-500">
                <p>&copy; {{ new Date().getFullYear() }} {{ website.name }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
