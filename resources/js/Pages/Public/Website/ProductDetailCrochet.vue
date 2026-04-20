<template>
    <CrochetLayout :website="website">
        <Head :title="`${product.name} - ${website.name}`">
            <link v-if="website.favicon_url" rel="icon" :href="website.favicon_url" />
            <meta name="description" :content="product.short_description || product.name" />
            <meta property="og:title" :content="product.name" />
            <meta property="og:description" :content="product.short_description || product.name" />
            <meta v-if="product.processed_featured_image || product.featured_image" property="og:image" :content="product.processed_featured_image || product.featured_image" />
        </Head>

        <!-- Breadcrumb -->
        <div class="bg-[#F8F9FA] py-4">
            <div class="container mx-auto px-4">
                <nav class="flex items-center gap-2 text-sm">
                    <a :href="website.url" class="text-[#588157] hover:text-[#3A5A40] transition-colors">Home</a>
                    <span class="text-[#A3B18A]">/</span>
                    <a :href="`${website.url}/shop`" class="text-[#588157] hover:text-[#3A5A40] transition-colors">Shop</a>
                    <span class="text-[#A3B18A]">/</span>
                    <span class="text-[#3A5A40] font-bold">{{ product.name }}</span>
                </nav>
            </div>
        </div>

        <!-- Product Detail -->
        <section class="py-12 md:py-16">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <div class="aspect-square bg-white rounded-[3rem] overflow-hidden shadow-xl">
                            <img 
                                v-if="product.processed_featured_image || product.featured_image"
                                :src="product.processed_featured_image || product.featured_image"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full bg-[#F0F4EF] flex items-center justify-center">
                                <span class="text-8xl">🧶</span>
                            </div>
                        </div>
                        
                        <!-- Gallery Thumbnails -->
                        <div v-if="product.gallery_images?.length" class="flex gap-3 overflow-x-auto pb-2">
                            <div 
                                v-for="(image, index) in product.gallery_images" 
                                :key="index"
                                class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden border-2 border-transparent hover:border-[#3A5A40] cursor-pointer transition-colors"
                            >
                                <img :src="image" :alt="`${product.name} - ${index + 1}`" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="space-y-8">
                        <!-- Badges -->
                        <div class="flex flex-wrap gap-2">
                            <span v-if="product.is_on_sale" class="px-4 py-1 bg-[#A44A3F] text-white text-xs font-black uppercase rounded-full">Sale</span>
                            <span v-if="product.is_digital" class="px-4 py-1 bg-[#3A5A40] text-white text-xs font-black uppercase rounded-full">Digital Download</span>
                            <span v-if="product.is_featured" class="px-4 py-1 bg-[#FFD7BA] text-[#8B5E3C] text-xs font-black uppercase rounded-full">Featured</span>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-[#3A5A40] leading-tight">{{ product.name }}</h1>

                        <!-- Price -->
                        <div class="flex items-end gap-4">
                            <span v-if="product.is_on_sale" class="text-2xl text-gray-400 line-through">
                                {{ formatPrice(product.price, product.currency) }}
                            </span>
                            <span class="text-4xl md:text-5xl font-black text-[#A44A3F]">
                                {{ formatPrice(product.sale_price || product.price, product.currency) }}
                            </span>
                            <span v-if="product.is_on_sale" class="text-sm font-bold text-white bg-[#A44A3F] px-3 py-1 rounded-full">
                                Save {{ Math.round((1 - product.sale_price / product.price) * 100) }}%
                            </span>
                        </div>

                        <!-- Short Description -->
                        <p v-if="product.short_description" class="text-lg text-[#588157] leading-relaxed">
                            {{ product.short_description }}
                        </p>

                        <!-- Stock Status -->
                        <div v-if="product.track_stock" class="flex items-center gap-2">
                            <span v-if="product.stock_quantity > 0" class="flex items-center gap-2 text-[#3A5A40] font-bold">
                                <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                In Stock ({{ product.stock_quantity }} available)
                            </span>
                            <span v-else class="flex items-center gap-2 text-[#A44A3F] font-bold">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Out of Stock
                            </span>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a 
                                v-if="product.external_url"
                                :href="product.external_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex-1 bg-[#3A5A40] text-white px-8 py-4 rounded-full font-black uppercase tracking-widest text-sm text-center hover:bg-[#344E41] transition-colors shadow-lg"
                            >
                                Buy Now
                            </a>
                            <a 
                                v-else-if="paymentsEnabled"
                                :href="`${website.url}/checkout/${product.slug}`"
                                class="flex-1 bg-[#3A5A40] text-white px-8 py-4 rounded-full font-black uppercase tracking-widest text-sm text-center hover:bg-[#344E41] transition-colors shadow-lg"
                                :class="{ 'opacity-50 pointer-events-none': product.track_stock && product.stock_quantity <= 0 }"
                            >
                                Buy Now
                            </a>
                            <a 
                                v-else-if="product.is_digital && product.digital_file_url"
                                :href="product.digital_file_url"
                                class="flex-1 bg-[#3A5A40] text-white px-8 py-4 rounded-full font-black uppercase tracking-widest text-sm text-center hover:bg-[#344E41] transition-colors shadow-lg"
                            >
                                Download Now
                            </a>
                            <button 
                                v-else
                                class="flex-1 bg-[#3A5A40] text-white px-8 py-4 rounded-full font-black uppercase tracking-widest text-sm hover:bg-[#344E41] transition-colors shadow-lg"
                                :disabled="product.track_stock && product.stock_quantity <= 0"
                            >
                                Contact to Buy
                            </button>
                            <a 
                                :href="`${website.url}/shop`"
                                class="flex-1 bg-white text-[#3A5A40] px-8 py-4 rounded-full font-black uppercase tracking-widest text-sm text-center border-2 border-[#3A5A40]/20 hover:border-[#3A5A40]/40 transition-colors"
                            >
                                Continue Shopping
                            </a>
                        </div>

                        <!-- SKU -->
                        <div v-if="product.sku" class="text-sm text-[#A3B18A]">
                            SKU: <span class="font-bold">{{ product.sku }}</span>
                        </div>
                    </div>
                </div>

                <!-- Full Description -->
                <div v-if="product.description" class="mt-16 max-w-4xl">
                    <h2 class="text-2xl font-black text-[#3A5A40] mb-6">About This Product</h2>
                    <div class="bg-white rounded-[2rem] p-8 shadow-sm prose prose-lg max-w-none text-[#344E41]" v-html="product.description"></div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <section v-if="relatedProducts.length > 0" class="py-16 bg-[#F8F9FA]">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl md:text-3xl font-black text-[#3A5A40] mb-8 text-center">You May Also Like</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a 
                        v-for="relProduct in relatedProducts" 
                        :key="relProduct.id"
                        :href="relProduct.url"
                        class="bg-white rounded-[2rem] overflow-hidden shadow-lg group cursor-pointer hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                    >
                        <div class="p-4">
                            <div class="aspect-square rounded-[1.5rem] relative overflow-hidden">
                                <img 
                                    v-if="relProduct.processed_featured_image || relProduct.featured_image"
                                    :src="relProduct.processed_featured_image || relProduct.featured_image"
                                    :alt="relProduct.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                                <div v-else class="w-full h-full bg-[#F0F4EF] flex items-center justify-center">
                                    <span class="text-4xl">🧶</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 pb-6">
                            <h3 class="text-lg font-bold text-[#3A5A40] mb-2 group-hover:text-[#588157] transition-colors line-clamp-2">{{ relProduct.name }}</h3>
                            <span class="text-xl font-black text-[#A44A3F]">
                                {{ formatPrice(relProduct.sale_price || relProduct.price, relProduct.currency) }}
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    </CrochetLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import CrochetLayout from '@/Layouts/CrochetLayout.vue';

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
