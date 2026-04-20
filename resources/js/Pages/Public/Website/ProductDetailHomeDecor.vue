<template>
    <HomeDecorLayout :website="website">
        <Head :title="`${product.name} - ${website.name}`">
            <link v-if="website.favicon_url" rel="icon" :href="website.favicon_url" />
            <meta name="description" :content="product.short_description || product.name" />
            <meta property="og:title" :content="product.name" />
            <meta property="og:description" :content="product.short_description || product.name" />
            <meta v-if="product.processed_featured_image || product.featured_image" property="og:image" :content="product.processed_featured_image || product.featured_image" />
        </Head>

        <!-- Breadcrumb -->
        <div class="bg-white py-4 border-b border-[#E5D5C3]">
            <div class="container mx-auto px-4">
                <nav class="flex items-center gap-2 text-sm">
                    <a :href="website.url" class="text-[#5D5D5D] hover:text-[#FF6B4A] transition-colors">Home</a>
                    <span class="text-[#D4A574]">/</span>
                    <a :href="`${website.url}/shop`" class="text-[#5D5D5D] hover:text-[#FF6B4A] transition-colors">Shop</a>
                    <span class="text-[#D4A574]">/</span>
                    <span class="text-[#2D2D2D] font-medium">{{ product.name }}</span>
                </nav>
            </div>
        </div>

        <!-- Product Detail -->
        <section class="py-12 md:py-20 bg-[#F5F1ED]">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <div class="aspect-square bg-white rounded-3xl overflow-hidden shadow-lg">
                            <img 
                                v-if="product.processed_featured_image || product.featured_image"
                                :src="product.processed_featured_image || product.featured_image"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-32 h-32 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Gallery Thumbnails -->
                        <div v-if="product.gallery_images?.length" class="flex gap-3 overflow-x-auto pb-2">
                            <div 
                                v-for="(image, index) in product.gallery_images" 
                                :key="index"
                                class="w-24 h-24 flex-shrink-0 rounded-xl overflow-hidden border-2 border-transparent hover:border-[#FF6B4A] cursor-pointer transition-colors bg-white"
                            >
                                <img :src="image" :alt="`${product.name} - ${index + 1}`" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="space-y-8">
                        <!-- Badges -->
                        <div class="flex flex-wrap gap-2">
                            <span v-if="product.is_on_sale" class="px-4 py-2 bg-[#FF6B4A] text-white text-xs font-bold uppercase rounded-full">Sale</span>
                            <span v-if="product.is_digital" class="px-4 py-2 bg-[#2D2D2D] text-white text-xs font-bold uppercase rounded-full">Digital Download</span>
                            <span v-if="product.is_featured" class="px-4 py-2 bg-[#D4A574] text-white text-xs font-bold uppercase rounded-full">Featured</span>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-serif font-bold text-[#2D2D2D] leading-tight">{{ product.name }}</h1>

                        <!-- Price -->
                        <div class="flex items-end gap-4">
                            <span v-if="product.is_on_sale" class="text-2xl text-[#9D9D9D] line-through">
                                {{ formatPrice(product.price, product.currency) }}
                            </span>
                            <span class="text-4xl md:text-5xl font-bold text-[#FF6B4A]">
                                {{ formatPrice(product.sale_price || product.price, product.currency) }}
                            </span>
                            <span v-if="product.is_on_sale" class="text-sm font-bold text-white bg-[#FF6B4A] px-3 py-1 rounded-full">
                                Save {{ Math.round((1 - product.sale_price / product.price) * 100) }}%
                            </span>
                        </div>

                        <!-- Short Description -->
                        <p v-if="product.short_description" class="text-lg text-[#5D5D5D] leading-relaxed">
                            {{ product.short_description }}
                        </p>

                        <!-- Stock Status -->
                        <div v-if="product.track_stock" class="flex items-center gap-2">
                            <span v-if="product.stock_quantity > 0" class="flex items-center gap-2 text-green-600 font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                In Stock ({{ product.stock_quantity }} available)
                            </span>
                            <span v-else class="flex items-center gap-2 text-[#FF6B4A] font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Out of Stock
                            </span>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <!-- Buy Now - links to checkout if payments enabled, or external URL -->
                            <a 
                                v-if="product.external_url"
                                :href="product.external_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex-1 bg-[#FF6B4A] text-white px-8 py-4 rounded-full font-bold text-center hover:bg-[#E55A3A] transition-colors shadow-lg"
                            >
                                Buy Now →
                            </a>
                            <a 
                                v-else-if="paymentsEnabled"
                                :href="`${website.url}/checkout/${product.slug}`"
                                class="flex-1 bg-[#FF6B4A] text-white px-8 py-4 rounded-full font-bold text-center hover:bg-[#E55A3A] transition-colors shadow-lg"
                                :class="{ 'opacity-50 pointer-events-none': product.track_stock && product.stock_quantity <= 0 }"
                            >
                                Buy Now →
                            </a>
                            <a 
                                v-else-if="product.is_digital && product.digital_file_url"
                                :href="product.digital_file_url"
                                class="flex-1 bg-[#FF6B4A] text-white px-8 py-4 rounded-full font-bold text-center hover:bg-[#E55A3A] transition-colors shadow-lg"
                            >
                                Download Now →
                            </a>
                            <button 
                                v-else
                                class="flex-1 bg-[#FF6B4A] text-white px-8 py-4 rounded-full font-bold hover:bg-[#E55A3A] transition-colors shadow-lg"
                                :disabled="product.track_stock && product.stock_quantity <= 0"
                            >
                                Contact to Purchase
                            </button>
                            <a 
                                :href="`${website.url}/shop`"
                                class="flex-1 bg-white text-[#2D2D2D] px-8 py-4 rounded-full font-bold text-center border-2 border-[#E5D5C3] hover:border-[#D4A574] transition-colors"
                            >
                                Continue Shopping
                            </a>
                        </div>

                        <!-- SKU -->
                        <div v-if="product.sku" class="text-sm text-[#9D9D9D]">
                            SKU: <span class="font-medium">{{ product.sku }}</span>
                        </div>

                        <!-- Features -->
                        <div class="grid grid-cols-3 gap-4 pt-4 border-t border-[#E5D5C3]">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto text-[#D4A574] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                                <span class="text-xs text-[#5D5D5D]">Quality<br/>Packaging</span>
                            </div>
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto text-[#D4A574] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span class="text-xs text-[#5D5D5D]">Secure<br/>Payment</span>
                            </div>
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto text-[#D4A574] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                                <span class="text-xs text-[#5D5D5D]">Made with<br/>Love</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Full Description -->
                <div v-if="product.description" class="mt-20 max-w-4xl">
                    <h2 class="text-2xl font-serif font-bold text-[#2D2D2D] mb-6">About This Product</h2>
                    <div class="bg-white rounded-2xl p-8 shadow-sm prose prose-lg max-w-none text-[#5D5D5D]" v-html="product.description"></div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <section v-if="relatedProducts.length > 0" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl md:text-3xl font-serif font-bold text-[#2D2D2D] mb-10 text-center">You May Also Like</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <a 
                        v-for="relProduct in relatedProducts" 
                        :key="relProduct.id"
                        :href="relProduct.url"
                        class="group"
                    >
                        <div class="aspect-square bg-[#F5F1ED] rounded-2xl overflow-hidden mb-4 relative">
                            <img 
                                v-if="relProduct.processed_featured_image || relProduct.featured_image"
                                :src="relProduct.processed_featured_image || relProduct.featured_image"
                                :alt="relProduct.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-[#2D2D2D] mb-2 group-hover:text-[#FF6B4A] transition-colors line-clamp-2">{{ relProduct.name }}</h3>
                        <span class="text-xl font-bold text-[#FF6B4A]">
                            {{ formatPrice(relProduct.sale_price || relProduct.price, relProduct.currency) }}
                        </span>
                    </a>
                </div>
            </div>
        </section>
    </HomeDecorLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HomeDecorLayout from '@/Layouts/HomeDecorLayout.vue';

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
