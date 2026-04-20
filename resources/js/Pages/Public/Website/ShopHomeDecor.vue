<template>
    <HomeDecorLayout :website="website">
        <Head :title="`Shop - ${website.name}`">
            <link v-if="website.favicon_url" rel="icon" :href="website.favicon_url" />
            <meta name="description" :content="`Shop curated home decor products from ${website.name}`" />
        </Head>

        <!-- Hero Section -->
        <section class="bg-gradient-to-b from-[#FFF8F3] to-white py-16 md:py-24">
            <div class="container mx-auto px-4 text-center">
                <span class="inline-block px-4 py-2 bg-[#FF6B4A]/10 text-[#FF6B4A] text-sm font-bold rounded-full mb-6">Shop Our Collection</span>
                <h1 class="text-4xl md:text-6xl font-serif font-bold text-[#2D2D2D] mb-6 leading-tight">
                    Curated Home Décor<br/>& Artisan Finds
                </h1>
                <p class="text-lg md:text-xl text-[#5D5D5D] max-w-2xl mx-auto">
                    Discover beautiful pieces handpicked to transform your space into something extraordinary.
                </p>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="featuredProducts.length > 0" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-10">
                    <h2 class="text-2xl md:text-3xl font-serif font-bold text-[#2D2D2D]">Featured Picks</h2>
                    <span class="text-[#FF6B4A] font-medium">Handpicked for you</span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <a 
                        v-for="product in featuredProducts" 
                        :key="product.id"
                        :href="product.url"
                        class="group"
                    >
                        <div class="aspect-square bg-[#F5F1ED] rounded-2xl overflow-hidden mb-4 relative">
                            <img 
                                v-if="product.processed_featured_image || product.featured_image"
                                :src="product.processed_featured_image || product.featured_image"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div v-if="product.is_on_sale" class="absolute top-4 left-4">
                                <span class="px-3 py-1 bg-[#FF6B4A] text-white text-xs font-bold uppercase rounded-full shadow">Sale</span>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-[#2D2D2D] mb-2 group-hover:text-[#FF6B4A] transition-colors line-clamp-2">{{ product.name }}</h3>
                        <div class="flex items-center gap-2">
                            <span v-if="product.is_on_sale" class="text-[#9D9D9D] line-through text-sm">
                                {{ formatPrice(product.price, product.currency) }}
                            </span>
                            <span class="text-xl font-bold text-[#FF6B4A]">
                                {{ formatPrice(product.sale_price || product.price, product.currency) }}
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- All Products -->
        <section class="py-16 bg-[#F5F1ED]">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-10">
                    <h2 class="text-2xl md:text-3xl font-serif font-bold text-[#2D2D2D]">All Products</h2>
                    <span class="text-[#5D5D5D]">{{ products.total }} items</span>
                </div>
                
                <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <a 
                        v-for="product in products.data" 
                        :key="product.id"
                        :href="product.url"
                        class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group"
                    >
                        <div class="aspect-square relative overflow-hidden">
                            <img 
                                v-if="product.processed_featured_image || product.featured_image"
                                :src="product.processed_featured_image || product.featured_image"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full bg-[#F5F1ED] flex items-center justify-center">
                                <svg class="w-20 h-20 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                                <span v-if="product.is_on_sale" class="px-3 py-1 bg-[#FF6B4A] text-white text-xs font-bold uppercase rounded-full shadow">Sale</span>
                                <span v-if="product.is_digital" class="px-3 py-1 bg-[#2D2D2D] text-white text-xs font-bold uppercase rounded-full shadow">Digital</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-[#2D2D2D] mb-2 group-hover:text-[#FF6B4A] transition-colors line-clamp-2">{{ product.name }}</h3>
                            <p v-if="product.short_description" class="text-sm text-[#5D5D5D] mb-4 line-clamp-2">{{ product.short_description }}</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span v-if="product.is_on_sale" class="text-[#9D9D9D] line-through text-sm">
                                        {{ formatPrice(product.price, product.currency) }}
                                    </span>
                                    <span class="text-xl font-bold text-[#FF6B4A]">
                                        {{ formatPrice(product.sale_price || product.price, product.currency) }}
                                    </span>
                                </div>
                                <span class="text-sm font-medium text-[#5D5D5D] group-hover:text-[#FF6B4A] transition-colors">
                                    View →
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl p-16 text-center shadow-sm">
                    <div class="w-24 h-24 bg-[#FFF8F3] rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-serif font-bold text-[#2D2D2D] mb-2">Coming Soon</h3>
                    <p class="text-[#5D5D5D]">We're curating something special for you. Check back soon!</p>
                </div>

                <!-- Pagination -->
                <div v-if="products.last_page > 1" class="flex justify-center gap-2 mt-12">
                    <a 
                        v-for="page in products.last_page"
                        :key="page"
                        :href="`${website.url}/shop?page=${page}`"
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors',
                            products.current_page === page 
                                ? 'bg-[#FF6B4A] text-white' 
                                : 'bg-white text-[#2D2D2D] hover:bg-[#FFF8F3]'
                        ]"
                    >
                        {{ page }}
                    </a>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-r from-[#D4A574] to-[#C4956D]">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-white mb-4">Can't Find What You're Looking For?</h2>
                <p class="text-white/90 text-lg mb-8 max-w-2xl mx-auto">
                    We're always adding new items to our collection. Subscribe to be the first to know about new arrivals!
                </p>
                <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    <input type="email" placeholder="Your email address" class="flex-1 px-6 py-4 rounded-full border-2 border-white/20 bg-white/10 text-white placeholder:text-white/60 focus:outline-none focus:border-white/40" />
                    <button class="bg-white text-[#D4A574] px-8 py-4 rounded-full font-bold hover:bg-[#FFF8F3] transition shadow-lg">
                        Subscribe
                    </button>
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
