<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="`${product.name} - ${website.name}`">
            <link v-if="website.favicon_url" rel="icon" :href="website.favicon_url" />
            <meta name="description" :content="product.short_description || product.name" />
            <meta property="og:title" :content="product.name" />
            <meta property="og:description" :content="product.short_description || product.name" />
            <meta v-if="product.processed_featured_image || product.featured_image" property="og:image" :content="product.processed_featured_image || product.featured_image" />
        </Head>

        <!-- Breadcrumb -->
        <div class="bg-white py-4 border-b">
            <div class="container mx-auto px-4">
                <nav class="flex items-center gap-2 text-sm">
                    <a :href="website.url" class="text-gray-500 hover:text-amber-600 transition-colors">Home</a>
                    <span class="text-gray-300">/</span>
                    <a :href="`${website.url}/shop`" class="text-gray-500 hover:text-amber-600 transition-colors">Shop</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-900 font-medium">{{ product.name }}</span>
                </nav>
            </div>
        </div>

        <!-- Product Detail -->
        <section class="py-12">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden">
                            <img 
                                v-if="product.processed_featured_image || product.featured_image"
                                :src="product.processed_featured_image || product.featured_image"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Gallery Thumbnails -->
                        <div v-if="product.gallery_images?.length" class="flex gap-3 overflow-x-auto pb-2">
                            <div 
                                v-for="(image, index) in product.gallery_images" 
                                :key="index"
                                class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden border-2 border-transparent hover:border-amber-500 cursor-pointer transition-colors"
                            >
                                <img :src="image" :alt="`${product.name} - ${index + 1}`" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="space-y-6">
                        <!-- Badges -->
                        <div class="flex flex-wrap gap-2">
                            <span v-if="product.is_on_sale" class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full">Sale</span>
                            <span v-if="product.is_digital" class="px-3 py-1 bg-blue-500 text-white text-xs font-bold rounded-full">Digital Download</span>
                            <span v-if="product.is_featured" class="px-3 py-1 bg-amber-500 text-white text-xs font-bold rounded-full">Featured</span>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ product.name }}</h1>

                        <!-- Price -->
                        <div class="flex items-end gap-3">
                            <span v-if="product.is_on_sale" class="text-xl text-gray-400 line-through">
                                {{ formatPrice(product.price, product.currency) }}
                            </span>
                            <span class="text-3xl md:text-4xl font-bold text-amber-600">
                                {{ formatPrice(product.sale_price || product.price, product.currency) }}
                            </span>
                            <span v-if="product.is_on_sale" class="text-sm font-bold text-white bg-red-500 px-2 py-1 rounded">
                                -{{ Math.round((1 - product.sale_price / product.price) * 100) }}%
                            </span>
                        </div>

                        <!-- Short Description -->
                        <p v-if="product.short_description" class="text-gray-600 text-lg">
                            {{ product.short_description }}
                        </p>

                        <!-- Stock Status -->
                        <div v-if="product.track_stock">
                            <span v-if="product.stock_quantity > 0" class="inline-flex items-center gap-2 text-green-600 font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                In Stock ({{ product.stock_quantity }} available)
                            </span>
                            <span v-else class="inline-flex items-center gap-2 text-red-600 font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Out of Stock
                            </span>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <a 
                                v-if="product.external_url"
                                :href="product.external_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex-1 bg-amber-500 text-white px-6 py-4 rounded-lg font-bold text-center hover:bg-amber-600 transition-colors"
                            >
                                Buy Now
                            </a>
                            <a 
                                v-else-if="paymentsEnabled"
                                :href="`${website.url}/checkout/${product.slug}`"
                                class="flex-1 bg-amber-500 text-white px-6 py-4 rounded-lg font-bold text-center hover:bg-amber-600 transition-colors"
                                :class="{ 'opacity-50 pointer-events-none': product.track_stock && product.stock_quantity <= 0 }"
                            >
                                Buy Now
                            </a>
                            <a 
                                v-else-if="product.is_digital && product.digital_file_url"
                                :href="product.digital_file_url"
                                class="flex-1 bg-amber-500 text-white px-6 py-4 rounded-lg font-bold text-center hover:bg-amber-600 transition-colors"
                            >
                                Download Now
                            </a>
                            <button 
                                v-else
                                class="flex-1 bg-amber-500 text-white px-6 py-4 rounded-lg font-bold hover:bg-amber-600 transition-colors disabled:opacity-50"
                                :disabled="product.track_stock && product.stock_quantity <= 0"
                            >
                                Contact to Purchase
                            </button>
                            <a 
                                :href="`${website.url}/shop`"
                                class="flex-1 bg-gray-100 text-gray-700 px-6 py-4 rounded-lg font-bold text-center hover:bg-gray-200 transition-colors"
                            >
                                Continue Shopping
                            </a>
                        </div>

                        <!-- SKU -->
                        <div v-if="product.sku" class="text-sm text-gray-500 pt-4 border-t">
                            SKU: <span class="font-medium">{{ product.sku }}</span>
                        </div>
                    </div>
                </div>

                <!-- Full Description -->
                <div v-if="product.description" class="mt-12 max-w-4xl">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Product</h2>
                    <div class="bg-gray-50 rounded-xl p-6 prose prose-lg max-w-none" v-html="product.description"></div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <section v-if="relatedProducts.length > 0" class="py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">You May Also Like</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a 
                        v-for="relProduct in relatedProducts" 
                        :key="relProduct.id"
                        :href="relProduct.url"
                        class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-shadow group"
                    >
                        <div class="aspect-square relative overflow-hidden bg-gray-100">
                            <img 
                                v-if="relProduct.processed_featured_image || relProduct.featured_image"
                                :src="relProduct.processed_featured_image || relProduct.featured_image"
                                :alt="relProduct.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-amber-600 transition-colors line-clamp-2">{{ relProduct.name }}</h3>
                            <span class="text-lg font-bold text-amber-600">
                                {{ formatPrice(relProduct.sale_price || relProduct.price, relProduct.currency) }}
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    </PublicWebsiteLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PublicWebsiteLayout from '@/Layouts/PublicWebsiteLayout.vue';

const props = defineProps({
    website: Object,
    product: Object,
    relatedProducts: Array,
    paymentsEnabled: {
        type: Boolean,
        default: false,
    },
});

const formatPrice = (price, currency = 'USD') => {
    const symbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'CAD': 'C$',
        'AUD': 'A$',
    };
    const symbol = symbols[currency] || currency + ' ';
    return symbol + parseFloat(price).toFixed(2);
};
</script>
