<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Top Banner -->
        <div v-if="website.theme_settings?.show_banner" class="bg-gradient-to-r from-teal-400 to-cyan-400 py-2">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center text-sm">
                    <div class="text-white font-medium">
                        {{ website.theme_settings?.banner_text || '30-MINUTE MEALS! Get the email series now →' }}
                    </div>
                    <div class="flex gap-4">
                        <a v-if="website.social_media?.instagram" :href="website.social_media.instagram" target="_blank" class="text-white hover:text-gray-100">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a v-if="website.social_media?.pinterest" :href="website.social_media.pinterest" target="_blank" class="text-white hover:text-gray-100">
                            <i class="fab fa-pinterest"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <div class="container mx-auto px-4">
                <!-- Logo -->
                <div class="py-6 text-center">
                    <a :href="website.url" class="inline-block">
                        <img v-if="website.logo" :src="website.logo" :alt="website.name" class="h-16 mx-auto" />
                        <h1 v-else class="text-4xl font-bold text-gray-900">{{ website.name }}</h1>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="border-t border-gray-200">
                    <ul class="flex justify-center items-center space-x-8 py-4">
                        <li>
                            <a :href="website.url" class="text-gray-700 hover:text-teal-500 font-medium transition">
                                Home
                            </a>
                        </li>
                        <li class="relative group">
                            <button class="text-gray-700 hover:text-teal-500 font-medium transition flex items-center">
                                Categories
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <div class="py-2">
                                    <a
                                        v-for="category in website.categories"
                                        :key="category.id"
                                        :href="category.url"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-500"
                                    >
                                        {{ category.name }}
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a href="#about" class="text-gray-700 hover:text-teal-500 font-medium transition">About</a>
                        </li>
                        <li>
                            <a href="#contact" class="text-gray-700 hover:text-teal-500 font-medium transition">Contact</a>
                        </li>
                    </ul>
                </nav>

                <!-- Search Bar -->
                <div class="py-4 border-t border-gray-200">
                    <div class="max-w-2xl mx-auto">
                        <div class="relative">
                            <input
                                type="text"
                                placeholder="Search recipes and articles..."
                                class="w-full px-4 py-3 pr-12 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-500"
                            />
                            <button class="absolute right-2 top-1/2 -translate-y-1/2 bg-teal-500 text-white px-4 py-2 rounded-md hover:bg-teal-600 transition">
                                Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Newsletter Section -->
        <section v-if="website.theme_settings?.newsletter_enabled" class="bg-gradient-to-r from-teal-400 to-cyan-400 py-16">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center gap-8">
                    <div class="flex-1 text-white">
                        <p class="text-sm uppercase tracking-wide mb-2">Free Email Bonus</p>
                        <h2 class="text-4xl font-bold mb-4">30-Minute Meals!</h2>
                        <p class="text-lg">Join to receive our email series which contains a round-up of some of our quick and easy family favorite recipes.</p>
                    </div>
                    <div class="flex-1">
                        <div class="bg-white p-6 rounded-lg shadow-lg">
                            <input
                                type="email"
                                placeholder="Enter email address"
                                class="w-full px-4 py-3 mb-4 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-500"
                            />
                            <button class="w-full bg-pink-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-pink-600 transition">
                                SUBSCRIBE
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">BROWSE</h3>
                        <ul class="space-y-2">
                            <li><a :href="website.url" class="text-gray-400 hover:text-white transition">Home</a></li>
                            <li v-for="category in website.categories?.slice(0, 5)" :key="category.id">
                                <a :href="category.url" class="text-gray-400 hover:text-white transition">
                                    {{ category.name }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">EXPLORE</h3>
                        <ul class="space-y-2">
                            <li><a href="#about" class="text-gray-400 hover:text-white transition">About</a></li>
                            <li><a href="#contact" class="text-gray-400 hover:text-white transition">Contact</a></li>
                            <li><a href="#privacy" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">FOLLOW</h3>
                        <div class="flex gap-4">
                            <a v-if="website.social_media?.instagram" :href="website.social_media.instagram" target="_blank" class="text-gray-400 hover:text-white transition">
                                <i class="fab fa-instagram text-2xl"></i>
                            </a>
                            <a v-if="website.social_media?.pinterest" :href="website.social_media.pinterest" target="_blank" class="text-gray-400 hover:text-white transition">
                                <i class="fab fa-pinterest text-2xl"></i>
                            </a>
                            <a v-if="website.social_media?.facebook" :href="website.social_media.facebook" target="_blank" class="text-gray-400 hover:text-white transition">
                                <i class="fab fa-facebook text-2xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                    <p>&copy; {{ new Date().getFullYear() }} {{ website.name }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    website: {
        type: Object,
        required: true
    }
});
</script>

