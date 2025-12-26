<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="website.name" />

        <!-- Category Grid Section -->
        <section class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Browse by Category</h2>
                    <p class="text-gray-600 text-lg">Find the perfect recipe for any meal of the day</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <a
                        v-for="category in website.categories"
                        :key="category.id"
                        :href="category.url"
                        class="group relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-all duration-300"
                    >
                        <div class="aspect-square relative">
                            <img
                                v-if="category.image"
                                :src="category.image"
                                :alt="category.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-br from-teal-400 to-cyan-500"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <h3 class="absolute bottom-4 left-4 right-4 text-white text-xl font-bold">
                                {{ category.name }}
                            </h3>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Call to Action Boxes -->
        <section class="py-8 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-gradient-to-br from-teal-400 to-cyan-500 rounded-lg p-8 text-white text-center">
                        <h3 class="text-2xl font-bold mb-2">GET NEW RECIPES</h3>
                        <h4 class="text-xl mb-4">IN YOUR INBOX</h4>
                        <a href="#subscribe" class="inline-block bg-white text-teal-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition">
                            SUBSCRIBE NOW 💌
                        </a>
                    </div>
                    <div class="bg-gradient-to-br from-pink-400 to-rose-500 rounded-lg p-8 text-white text-center">
                        <h3 class="text-2xl font-bold mb-2">VISIT OUR SHOP</h3>
                        <h4 class="text-xl mb-4">FIND GREAT GIFT IDEAS!</h4>
                        <a href="#shop" class="inline-block bg-white text-pink-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition">
                            SHOP NOW 🛍️
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Latest Articles -->
        <section v-if="latestArticles.length > 0" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-4xl font-bold text-gray-900 mb-2">What's New</h2>
                        <p class="text-gray-600">The newest breakfast ideas, dinners and desserts. These easy recipes are pretty much guaranteed winners!</p>
                    </div>
                    <a :href="website.url" class="text-teal-500 hover:text-teal-600 font-semibold flex items-center">
                        Browse All The Latest
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <a
                        v-for="article in latestArticles"
                        :key="article.id"
                        :href="article.url"
                        class="group"
                    >
                        <div class="overflow-hidden rounded-lg mb-4">
                            <img
                                v-if="article.featured_image"
                                :src="article.featured_image"
                                :alt="article.title"
                                class="w-full aspect-[4/3] object-cover group-hover:scale-110 transition-transform duration-300"
                            />
                            <div v-else class="w-full aspect-[4/3] bg-gradient-to-br from-teal-400 to-cyan-500"></div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-teal-500 transition">
                            {{ article.title }}
                        </h3>
                    </a>
                </div>
            </div>
        </section>

        <!-- Featured Articles -->
        <section v-if="featuredArticles.length > 0" class="py-16 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Readers Love These Recipes!</h2>
                    <p class="text-gray-600 text-lg">"Wondering which recipes everyone is completely loving? Check out these reader favorites:"</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <a
                        v-for="article in featuredArticles"
                        :key="article.id"
                        :href="article.url"
                        class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group"
                    >
                        <div class="flex flex-col md:flex-row">
                            <div class="md:w-1/2 overflow-hidden">
                                <img
                                    v-if="article.featured_image"
                                    :src="article.featured_image"
                                    :alt="article.title"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                />
                                <div v-else class="w-full h-64 bg-gradient-to-br from-teal-400 to-cyan-500"></div>
                            </div>
                            <div class="md:w-1/2 p-6">
                                <div class="flex mb-2">
                                    <span v-for="i in 5" :key="i" class="text-yellow-400">★</span>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-teal-500 transition">
                                    {{ article.title }}
                                </h3>
                                <p v-if="article.excerpt" class="text-gray-600">{{ article.excerpt }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    </PublicWebsiteLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicWebsiteLayout from '@/Layouts/PublicWebsiteLayout.vue';

defineProps({
    website: Object,
    latestArticles: Array,
    featuredArticles: Array
});
</script>

