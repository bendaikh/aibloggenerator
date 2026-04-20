<template>
    <CrochetLayout :website="website">
        <Head :title="`Shop - ${website.name}`">
            <link v-if="website.favicon_url" rel="icon" :href="website.favicon_url" />
            <meta name="description" :content="`Shop handmade products from ${website.name}`" />
        </Head>

        <!-- Hero Section -->
        <section class="relative py-16 md:py-20 overflow-hidden bg-gradient-to-b from-[#E9EDC9]/50 to-transparent">
            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <span class="px-4 py-1 bg-[#FFD7BA] text-[#8B5E3C] text-xs font-black uppercase tracking-widest rounded-full mb-6 inline-block">Shop</span>
                    <h1 class="text-4xl md:text-6xl font-black text-[#3A5A40] mb-6 leading-tight">
                        Our Handcrafted<br/>Collection
                    </h1>
                    <p class="text-lg md:text-xl text-[#588157] font-medium">
                        Discover beautiful handmade patterns, kits, and supplies curated with love.
                    </p>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-[#A3B18A]/10 rounded-full translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
        </section>

        <!-- Featured Products -->
        <section v-if="featuredProducts.length > 0" class="py-12">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl md:text-3xl font-black text-[#3A5A40]">Featured Products</h2>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a 
                        v-for="product in featuredProducts" 
                        :key="product.id"
                        :href="product.url"
                        class="bg-white rounded-[2rem] overflow-hidden shadow-lg group cursor-pointer hover:shadow-xl transition-all duration-300"
                    >
                        <div class="aspect-square relative overflow-hidden">
                            <img 
                                v-if="product.processed_featured_image || product.featured_image"
                                :src="product.processed_featured_image || product.featured_image"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full bg-[#F0F4EF] flex items-center justify-center">
                                <span class="text-4xl">🧶</span>
                            </div>
                            <div v-if="product.is_on_sale" class="absolute top-4 left-4">
                                <span class="px-3 py-1 bg-[#A44A3F] text-white text-xs font-black uppercase rounded-full">Sale</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-[#3A5A40] mb-2 group-hover:text-[#588157] transition-colors line-clamp-2">{{ product.name }}</h3>
                            <div class="flex items-center gap-2">
                                <span v-if="product.is_on_sale" class="text-gray-400 line-through text-sm">
                                    {{ formatPrice(product.price, product.currency) }}
                                </span>
                                <span class="text-xl font-black text-[#A44A3F]">
                                    {{ formatPrice(product.sale_price || product.price, product.currency) }}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- All Products -->
        <section class="py-12 bg-[#F8F9FA]">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl md:text-3xl font-black text-[#3A5A40]">All Products</h2>
                    <span class="text-[#588157] font-bold">{{ products.total }} items</span>
                </div>
                
                <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <a 
                        v-for="product in products.data" 
                        :key="product.id"
                        :href="product.url"
                        class="bg-white rounded-[2rem] overflow-hidden shadow-lg group cursor-pointer hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                    >
                        <div class="p-4">
                            <div class="aspect-square rounded-[1.5rem] relative overflow-hidden">
                                <img 
                                    v-if="product.processed_featured_image || product.featured_image"
                                    :src="product.processed_featured_image || product.featured_image"
                                    :alt="product.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                                <div v-else class="w-full h-full bg-[#F0F4EF] flex items-center justify-center">
                                    <span class="text-5xl">🧶</span>
                                </div>
                                <div v-if="product.is_on_sale" class="absolute top-4 left-4">
                                    <span class="px-3 py-1 bg-[#A44A3F] text-white text-xs font-black uppercase rounded-full shadow">Sale</span>
                                </div>
                                <div v-if="product.is_digital" class="absolute top-4 right-4">
                                    <span class="px-3 py-1 bg-[#3A5A40] text-white text-xs font-black uppercase rounded-full shadow">Digital</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 pb-6">
                            <h3 class="text-lg font-bold text-[#3A5A40] mb-2 group-hover:text-[#588157] transition-colors line-clamp-2">{{ product.name }}</h3>
                            <p v-if="product.short_description" class="text-sm text-[#588157] mb-3 line-clamp-2">{{ product.short_description }}</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span v-if="product.is_on_sale" class="text-gray-400 line-through text-sm">
                                        {{ formatPrice(product.price, product.currency) }}
                                    </span>
                                    <span class="text-xl font-black text-[#A44A3F]">
                                        {{ formatPrice(product.sale_price || product.price, product.currency) }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-black text-[#3A5A40] uppercase tracking-widest bg-[#F0F4EF] px-3 py-2 rounded-full">
                                    View
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-[2rem] p-12 text-center shadow-sm">
                    <div class="w-24 h-24 bg-[#F0F4EF] rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">🧺</span>
                    </div>
                    <h3 class="text-2xl font-bold text-[#3A5A40] mb-2">Coming Soon</h3>
                    <p class="text-[#588157]">We're preparing something special for you. Check back soon!</p>
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
                                ? 'bg-[#3A5A40] text-white' 
                                : 'bg-white text-[#3A5A40] hover:bg-[#E9EDC9]'
                        ]"
                    >
                        {{ page }}
                    </a>
                </div>
            </div>
        </section>

        <!-- Newsletter CTA -->
        <section class="py-16 px-4">
            <div class="max-w-4xl mx-auto bg-[#3A5A40] rounded-[3rem] p-10 md:p-16 text-center shadow-2xl relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-black text-white mb-4">Stay Updated!</h2>
                    <p class="text-[#DAD7CD] text-lg mb-8">Be the first to know about new products and exclusive offers.</p>
                    <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                        <input type="email" placeholder="Enter your email" class="flex-1 bg-white/10 border border-white/20 rounded-full px-6 py-4 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/50" />
                        <button class="bg-[#A44A3F] text-white px-8 py-4 rounded-full font-black uppercase tracking-widest text-xs hover:bg-[#8B3D35] transition-colors shadow-lg">Subscribe</button>
                    </div>
                </div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full translate-x-1/2 -translate-y-1/2"></div>
            </div>
        </section>
    </CrochetLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import CrochetLayout from '@/Layouts/CrochetLayout.vue';

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
