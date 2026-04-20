<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import ImageUpload from '@/Components/ImageUpload.vue';

const props = defineProps({
    currentWebsite: Object,
    websites: Array,
});

const form = useForm({
    name: '',
    slug: '',
    description: '',
    short_description: '',
    price: 0,
    sale_price: null,
    currency: 'USD',
    featured_image: '',
    gallery_images: [],
    sku: '',
    stock_quantity: 0,
    track_stock: false,
    is_digital: false,
    digital_file_url: '',
    external_url: '',
    status: 'draft',
    is_featured: false,
    order: 0,
});

const currencies = [
    { code: 'USD', name: 'US Dollar', symbol: '$' },
    { code: 'EUR', name: 'Euro', symbol: '€' },
    { code: 'GBP', name: 'British Pound', symbol: '£' },
    { code: 'CAD', name: 'Canadian Dollar', symbol: 'C$' },
    { code: 'AUD', name: 'Australian Dollar', symbol: 'A$' },
];

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' },
];

const submit = () => {
    form.post(route('superadmin.products.store', { website: props.currentWebsite.id }));
};

const generateSlug = () => {
    form.slug = form.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)+/g, '');
};
</script>

<template>
    <Head title="Add Product" />
    
    <SuperAdminLayout :currentWebsite="currentWebsite">
        <div class="p-4 lg:p-8 max-w-4xl">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <Link 
                    :href="route('superadmin.products.index', { website: currentWebsite.id })"
                    class="p-2 bg-[#1a1a1a] hover:bg-[#252525] text-gray-400 hover:text-white rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-white">Add Product</h1>
                    <p class="text-gray-400 mt-1">Create a new product for your shop</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Basic Info -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Basic Information</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Product Name *</label>
                            <input 
                                v-model="form.name"
                                @blur="generateSlug"
                                type="text"
                                class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                placeholder="Enter product name"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">URL Slug</label>
                            <input 
                                v-model="form.slug"
                                type="text"
                                class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                placeholder="product-url-slug"
                            />
                            <p class="mt-1 text-sm text-gray-500">Leave empty to auto-generate from name</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Short Description</label>
                            <input 
                                v-model="form.short_description"
                                type="text"
                                class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                placeholder="Brief product description"
                                maxlength="500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Full Description</label>
                            <textarea 
                                v-model="form.description"
                                rows="5"
                                class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors resize-none"
                                placeholder="Detailed product description..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Pricing</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Price *</label>
                            <input 
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                placeholder="0.00"
                            />
                            <p v-if="form.errors.price" class="mt-1 text-sm text-red-400">{{ form.errors.price }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Sale Price</label>
                            <input 
                                v-model="form.sale_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                placeholder="0.00"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Currency</label>
                            <select 
                                v-model="form.currency"
                                class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                            >
                                <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                                    {{ currency.symbol }} {{ currency.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Image -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Product Image</h2>
                    
                    <ImageUpload
                        v-model="form.featured_image"
                        label="Featured Image"
                        type="product"
                        hint="Upload a high-quality product image (PNG, JPG, WebP up to 5MB)"
                        :allow-url="true"
                    />
                    <p v-if="form.errors.featured_image" class="mt-2 text-sm text-red-400">{{ form.errors.featured_image }}</p>
                </div>

                <!-- Inventory -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Inventory</h2>
                    
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">SKU</label>
                                <input 
                                    v-model="form.sku"
                                    type="text"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                    placeholder="Product SKU"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Stock Quantity</label>
                                <input 
                                    v-model="form.stock_quantity"
                                    type="number"
                                    min="0"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                    :disabled="!form.track_stock"
                                />
                            </div>
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="form.track_stock"
                                class="w-5 h-5 rounded border-[#3a3a3a] bg-[#252525] text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0"
                            />
                            <span class="text-gray-300">Track stock quantity</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="form.is_digital"
                                class="w-5 h-5 rounded border-[#3a3a3a] bg-[#252525] text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0"
                            />
                            <span class="text-gray-300">This is a digital product</span>
                        </label>

                        <div v-if="form.is_digital">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Digital File URL</label>
                            <input 
                                v-model="form.digital_file_url"
                                type="text"
                                class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                placeholder="https://example.com/download/file.zip"
                            />
                        </div>
                    </div>
                </div>

                <!-- External Link -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">External Link (Optional)</h2>
                    <p class="text-gray-400 text-sm mb-4">If you want customers to purchase from an external site (like Etsy, Amazon, etc.)</p>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">External Purchase URL</label>
                        <input 
                            v-model="form.external_url"
                            type="url"
                            class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                            placeholder="https://etsy.com/your-product"
                        />
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Publishing</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                            <select 
                                v-model="form.status"
                                class="w-full bg-[#252525] border border-[#3a3a3a] rounded-lg px-4 py-3 text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                            >
                                <option v-for="status in statuses" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="form.is_featured"
                                class="w-5 h-5 rounded border-[#3a3a3a] bg-[#252525] text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0"
                            />
                            <span class="text-gray-300">Feature this product</span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4">
                    <Link 
                        :href="route('superadmin.products.index', { website: currentWebsite.id })"
                        class="px-6 py-3 text-gray-400 hover:text-white transition-colors"
                    >
                        Cancel
                    </Link>
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-medium rounded-lg transition-all disabled:opacity-50"
                    >
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Product</span>
                    </button>
                </div>
            </form>
        </div>
    </SuperAdminLayout>
</template>
