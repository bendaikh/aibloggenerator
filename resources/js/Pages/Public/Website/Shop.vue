<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="`Shop - ${website.name}`">
            <link v-if="website.favicon_url" rel="icon" :href="website.favicon_url" />
            <meta name="description" :content="`Shop products from ${website.name}`" />
        </Head>

        <!-- Hero Section -->
        <section class="bg-gradient-to-b from-amber-50 to-white py-16 md:py-20">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Shop</h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Browse our collection of products
                </p>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="featuredProducts.length > 0" class="py-12">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Featured Products</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a 
                        v-for="product in featuredProducts" 
                        :key="product.id"
                        :href="product.url"
                        class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-shadow group"
                    >
                        <div class="aspect-square relative overflow-hidden bg-gray-100">
                            <img 
                                v-if="product.processed_featured_image || product.featured_image"
                                :src="product.processed_featured_image || product.featured_image"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div v-if="product.is_on_sale" class="absolute top-3 left-3">
                                <span class="px-2 py-1 bg-red-500 text-white text-xs font-bold rounded">Sale</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-amber-600 transition-colors line-clamp-2">{{ product.name }}</h3>
                            <div class="flex items-center gap-2">
                                <span v-if="product.is_on_sale" class="text-gray-400 line-through text-sm">
                                    {{ formatPrice(product.price, product.currency) }}
                                </span>
                                <span class="text-lg font-bold text-amber-600">
                                    {{ formatPrice(product.sale_price || product.price, product.currency) }}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- All Products -->
        <section class="py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">All Products</h2>
                    <span class="text-gray-600">{{ products.total }} items</span>
                </div>
                
                <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <a 
                        v-for="product in products.data" 
                        :key="product.id"
                        :href="product.url"
                        class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-shadow group"
                    >
                        <div class="aspect-square relative overflow-hidden bg-gray-100">
                            <img 
                                v-if="product.processed_featured_image || product.featured_image"
                                :src="product.processed_featured_image || product.featured_image"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="absolute top-3 left-3 flex flex-wrap gap-2">
                                <span v-if="product.is_on_sale" class="px-2 py-1 bg-red-500 text-white text-xs font-bold rounded">Sale</span>
                                <span v-if="product.is_digital" class="px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded">Digital</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-amber-600 transition-colors line-clamp-2">{{ product.name }}</h3>
                            <p v-if="product.short_description" class="text-sm text-gray-500 mb-3 line-clamp-2">{{ product.short_description }}</p>
                            <div class="flex items-center gap-2">
                                <span v-if="product.is_on_sale" class="text-gray-400 line-through text-sm">
                                    {{ formatPrice(product.price, product.currency) }}
                                </span>
                                <span class="text-lg font-bold text-amber-600">
                                    {{ formatPrice(product.sale_price || product.price, product.currency) }}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-xl p-12 text-center shadow-sm">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Products Yet</h3>
                    <p class="text-gray-600">Check back soon for new items!</p>
                </div>

                <!-- Pagination -->
                <div v-if="products.last_page > 1" class="flex justify-center gap-2 mt-10">
                    <a 
                        v-for="page in products.last_page"
                        :key="page"
                        :href="`${website.url}/shop?page=${page}`"
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center font-medium text-sm transition-colors',
                            products.current_page === page 
                                ? 'bg-amber-500 text-white' 
                                : 'bg-white text-gray-700 hover:bg-amber-50 border'
                        ]"
                    >
                        {{ page }}
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
    products: Object,
    featuredProducts: Array,
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
