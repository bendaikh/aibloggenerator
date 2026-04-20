<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const props = defineProps({
    currentWebsite: Object,
    websites: Array,
    products: Object,
});

const deleteProduct = (product) => {
    if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
        router.delete(route('superadmin.products.destroy', { 
            website: props.currentWebsite.id, 
            product: product.id 
        }));
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'published':
            return 'bg-emerald-500/20 text-emerald-400';
        case 'draft':
            return 'bg-yellow-500/20 text-yellow-400';
        case 'archived':
            return 'bg-gray-500/20 text-gray-400';
        default:
            return 'bg-gray-500/20 text-gray-400';
    }
};

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

<template>
    <Head title="Shop Products" />
    
    <SuperAdminLayout :currentWebsite="currentWebsite">
        <div class="p-4 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-white">Shop Products</h1>
                    <p class="text-gray-400 mt-1">Manage products for your online shop</p>
                </div>
                <Link 
                    :href="route('superadmin.products.create', { website: currentWebsite.id })"
                    class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-sm font-medium rounded-lg transition-all"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Product
                </Link>
            </div>

            <!-- Products Grid -->
            <div v-if="products.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div 
                    v-for="product in products.data" 
                    :key="product.id"
                    class="bg-[#1a1a1a] rounded-2xl overflow-hidden border border-[#2a2a2a] hover:border-[#3a3a3a] transition-all group"
                >
                    <!-- Product Image -->
                    <div class="aspect-square relative overflow-hidden bg-[#252525]">
                        <img 
                            v-if="product.processed_featured_image || product.featured_image"
                            :src="product.processed_featured_image || product.featured_image"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <!-- Status Badge -->
                        <div class="absolute top-3 left-3">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(product.status)]">
                                {{ product.status }}
                            </span>
                        </div>
                        <!-- Featured Badge -->
                        <div v-if="product.is_featured" class="absolute top-3 right-3">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-500/20 text-amber-400">
                                Featured
                            </span>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <h3 class="text-white font-semibold text-lg truncate mb-1">{{ product.name }}</h3>
                        <p class="text-gray-400 text-sm line-clamp-2 mb-3 h-10">{{ product.short_description || 'No description' }}</p>
                        
                        <!-- Price -->
                        <div class="flex items-center gap-2 mb-4">
                            <span v-if="product.sale_price" class="text-gray-500 line-through text-sm">
                                {{ formatPrice(product.price, product.currency) }}
                            </span>
                            <span class="text-emerald-400 font-bold text-lg">
                                {{ formatPrice(product.sale_price || product.price, product.currency) }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <Link 
                                :href="route('superadmin.products.edit', { website: currentWebsite.id, product: product.id })"
                                class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-[#252525] hover:bg-[#303030] text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </Link>
                            <button 
                                @click="deleteProduct(product)"
                                class="flex items-center justify-center p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-12 text-center">
                <div class="w-20 h-20 bg-[#252525] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-white text-xl font-semibold mb-2">No Products Yet</h3>
                <p class="text-gray-400 mb-6">Start by adding your first product to your shop.</p>
                <Link 
                    :href="route('superadmin.products.create', { website: currentWebsite.id })"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-lg transition-all"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Product
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="products.last_page > 1" class="flex justify-center gap-2 mt-8">
                <Link 
                    v-for="page in products.last_page"
                    :key="page"
                    :href="route('superadmin.products.index', { website: currentWebsite.id, page })"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        products.current_page === page 
                            ? 'bg-emerald-500 text-white' 
                            : 'bg-[#252525] text-gray-400 hover:bg-[#303030] hover:text-white'
                    ]"
                >
                    {{ page }}
                </Link>
            </div>
        </div>
    </SuperAdminLayout>
</template>
