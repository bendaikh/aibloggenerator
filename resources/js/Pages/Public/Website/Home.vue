<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="website.name">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
        </Head>

        <!-- Browse by Category Section -->
        <section class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Browse by Category</h2>
                    <p class="text-gray-500">Find the perfect recipe for any meal of the day</p>
                </div>

                <!-- Circular Category Icons -->
                <div class="flex flex-wrap justify-center gap-6 md:gap-10 mb-8">
                    <a
                        v-for="category in website.categories?.slice(0, 6)"
                        :key="category.id"
                        :href="category.url"
                        class="group text-center"
                    >
                        <div class="w-24 h-24 md:w-32 md:h-32 rounded-full overflow-hidden mb-3 ring-4 ring-transparent group-hover:ring-emerald-400 transition-all duration-300 shadow-lg">
                            <img
                                v-if="category.image"
                                :src="category.image"
                                :alt="category.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                <span class="text-white text-3xl">{{ category.name.charAt(0) }}</span>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-700 group-hover:text-emerald-600 transition uppercase tracking-wide">
                            {{ category.name }}
                        </p>
                    </a>
                </div>
            </div>
        </section>

        <!-- Call to Action Boxes -->
        <section v-if="shouldShowCtaSection" class="py-8 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                    <div v-if="showNewsletterCta" class="bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl p-8 text-white text-center shadow-lg hover:shadow-xl transition">
                        <h3 class="text-2xl font-bold mb-1">GET NEW RECIPES</h3>
                        <h4 class="text-lg mb-4 text-emerald-100">IN YOUR INBOX</h4>
                        <a href="#subscribe" class="inline-block bg-white text-emerald-600 px-8 py-3 rounded-full font-bold hover:bg-emerald-50 transition shadow-md">
                            SUBSCRIBE NOW 💌
                        </a>
                    </div>
                    <div v-if="showShopCta" class="bg-gradient-to-br from-pink-400 to-rose-500 rounded-2xl p-8 text-white text-center shadow-lg hover:shadow-xl transition">
                        <h3 class="text-2xl font-bold mb-1">VISIT OUR SHOP</h3>
                        <h4 class="text-lg mb-4 text-pink-100">FIND GREAT GIFT IDEAS!</h4>
                        <a href="#shop" class="inline-block bg-white text-pink-600 px-8 py-3 rounded-full font-bold hover:bg-pink-50 transition shadow-md">
                            SHOP NOW 🛍️
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- What's New Section -->
        <section v-if="latestArticles.length > 0" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">What's New</h2>
                        <p class="text-gray-500">The newest breakfast ideas, dinners and desserts. These easy recipes are pretty much guaranteed winners!</p>
                    </div>
                    <a :href="website.url + '/articles'" class="text-emerald-500 hover:text-emerald-600 font-semibold flex items-center whitespace-nowrap">
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
                        <div class="overflow-hidden rounded-2xl mb-4 shadow-md group-hover:shadow-xl transition">
                            <img
                                v-if="article.processed_featured_image || article.featured_image"
                                :src="article.processed_featured_image || article.featured_image"
                                :alt="article.title"
                                class="w-full aspect-[4/3] object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full aspect-[4/3] bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                <span class="text-white text-6xl">🍽️</span>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-emerald-500 transition leading-tight">
                            {{ article.title }}
                        </h3>
                    </a>
                </div>
            </div>
        </section>

        <!-- Author Section -->
        <section class="py-16 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                    <!-- Hi I'm Section -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg">
                        <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                            <div class="w-32 h-32 rounded-full overflow-hidden ring-4 ring-emerald-400 shadow-lg flex-shrink-0">
                                <img
                                    v-if="author?.image"
                                    :src="author.image"
                                    :alt="author?.name || 'Author'"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                    <span class="text-white text-4xl">👩‍🍳</span>
                                </div>
                            </div>
                            <div class="text-center md:text-left">
                                <h3 class="text-2xl font-bold text-gray-900 mb-3">Hi I'm {{ author?.name || website.name.split(' ')[0] || 'Chef' }}!</h3>
                                <p class="text-gray-600">
                                    <span v-if="author?.description">{{ author.description }}</span>
                                    <span v-else>
                                        I love to cook! I have been cooking and baking for as long as I can remember. 
                                        Here you will find deliciously simple tried and true recipes. Thanks so much for stopping by!
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Family Favorites -->
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Family Favorites</h3>
                        <div class="space-y-4">
                            <a
                                v-for="article in featuredArticles?.slice(0, 3)"
                                :key="article.id"
                                :href="article.url"
                                class="flex gap-4 items-center group"
                            >
                                <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 shadow-md">
                                    <img
                                        v-if="article.processed_featured_image || article.featured_image"
                                        :src="article.processed_featured_image || article.featured_image"
                                        :alt="article.title"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                    />
                                    <div v-else class="w-full h-full bg-gradient-to-br from-emerald-400 to-teal-500"></div>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 group-hover:text-emerald-500 transition">{{ article.title }}</p>
                                    <div class="flex text-yellow-400 text-sm">
                                        <span v-for="i in 5" :key="i">★</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Readers Love These Recipes -->
        <section v-if="featuredArticles.length > 0" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Readers Love These Recipes!</h2>
                    <p class="text-gray-500">Wondering which recipes everyone is completely loving? Check out these reader favorites:</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    <a
                        v-for="article in featuredArticles"
                        :key="article.id"
                        :href="article.url"
                        class="group text-center"
                    >
                        <div class="overflow-hidden rounded-2xl mb-3 shadow-md group-hover:shadow-xl transition">
                            <img
                                v-if="article.processed_featured_image || article.featured_image"
                                :src="article.processed_featured_image || article.featured_image"
                                :alt="article.title"
                                class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full aspect-square bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                <span class="text-white text-4xl">🍽️</span>
                            </div>
                        </div>
                        <div class="flex justify-center text-yellow-400 text-sm mb-1">
                            <span v-for="i in 5" :key="i">★</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-emerald-500 transition text-sm leading-tight">
                            {{ article.title }}
                        </h3>
                    </a>
                </div>
            </div>
        </section>

    </PublicWebsiteLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PublicWebsiteLayout from '@/Layouts/PublicWebsiteLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    website: Object,
    latestArticles: Array,
    featuredArticles: Array,
    author: Object
});

// Get theme settings with defaults
const themeSettings = computed(() => props.website.theme_settings || {});
const showNewsletterCta = computed(() => themeSettings.value.show_newsletter_cta !== false);
const showShopCta = computed(() => themeSettings.value.show_shop_cta !== false);
const shouldShowCtaSection = computed(() => showNewsletterCta.value || showShopCta.value);
</script>
