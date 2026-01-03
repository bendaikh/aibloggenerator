<template>
    <div class="min-h-screen bg-white">
        <!-- Top Banner -->
        <div class="bg-gradient-to-r from-emerald-400 to-teal-400 py-2">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center text-sm">
                    <div class="text-white font-medium flex items-center gap-2">
                        <span>🍳</span>
                        <span>{{ website.theme_settings?.banner_text || '30-MINUTE MEALS! Get the email series now →' }}</span>
                    </div>
                    <div class="flex gap-4 items-center">
                        <a v-if="website.social_media?.instagram" :href="website.social_media.instagram" target="_blank" class="text-white hover:text-emerald-100 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a v-if="website.social_media?.pinterest" :href="website.social_media.pinterest" target="_blank" class="text-white hover:text-emerald-100 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                        </a>
                        <a v-if="website.social_media?.facebook" :href="website.social_media.facebook" target="_blank" class="text-white hover:text-emerald-100 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
            <div class="container mx-auto px-4">
                <!-- Top Header with Logo and Nav -->
                <div class="flex items-center justify-between py-4">
                    <!-- Logo -->
                    <a :href="website.url" class="flex items-center">
                        <img v-if="website.logo_url || website.logo" :src="website.logo_url || website.logo" :alt="website.name" class="h-12" />
                        <span v-else class="text-2xl font-bold text-gray-900">{{ website.name }}</span>
                    </a>

                    <!-- Main Navigation -->
                    <nav class="hidden md:flex items-center space-x-8">
                        <a :href="website.url" class="text-gray-700 hover:text-emerald-500 font-medium transition">Home</a>
                        
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-emerald-500 font-medium transition flex items-center py-2">
                                Categories
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-56 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 border border-gray-100">
                                <div class="py-2">
                                    <a
                                        v-for="category in website.categories"
                                        :key="category.id"
                                        :href="category.url"
                                        class="block px-4 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition"
                                    >
                                        {{ category.name }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dynamic Pages from Content Management -->
                        <a
                            v-for="page in website.pages"
                            :key="page.id"
                            :href="page.url"
                            class="text-gray-700 hover:text-emerald-500 font-medium transition"
                        >
                            {{ page.title }}
                        </a>
                    </nav>

                    <!-- Right Icons -->
                    <div class="flex items-center gap-4">
                        <button class="text-gray-600 hover:text-emerald-500 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        <button class="text-gray-600 hover:text-emerald-500 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Category Tabs -->
                <div class="border-t border-gray-100 overflow-x-auto scrollbar-hide">
                    <div class="flex items-center justify-center space-x-1 py-3">
                        <a
                            v-for="category in website.categories?.slice(0, 6)"
                            :key="category.id"
                            :href="category.url"
                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-full whitespace-nowrap transition"
                        >
                            {{ category.name }}
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Search Bar Section -->
        <div class="bg-gray-50 py-6">
            <div class="container mx-auto px-4">
                <div class="max-w-2xl mx-auto">
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="🔍 Search recipes and articles..."
                            class="w-full px-6 py-4 rounded-full border-2 border-gray-200 focus:border-emerald-400 focus:outline-none text-gray-700 shadow-sm"
                        />
                        <button class="absolute right-2 top-1/2 -translate-y-1/2 bg-emerald-500 text-white px-6 py-2.5 rounded-full font-medium hover:bg-emerald-600 transition shadow-md">
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Newsletter Section -->
        <section class="bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 py-16">
            <div class="container mx-auto px-4">
                <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-center gap-12">
                    <div class="flex-1 text-white text-center md:text-left">
                        <p class="text-emerald-100 text-sm uppercase tracking-wider mb-2">Free Email Bonus</p>
                        <h2 class="text-4xl md:text-5xl font-bold mb-4">30-Minute Meals!</h2>
                        <p class="text-lg text-emerald-50">Join to receive our email series which contains a round-up of some of our quick and easy family favorite recipes.</p>
                    </div>
                    <div class="flex-1 w-full max-w-md">
                        <div class="bg-white p-8 rounded-2xl shadow-2xl">
                            <p class="text-gray-600 text-sm mb-4">Enter your email address:</p>
                            <input
                                type="email"
                                placeholder="you@example.com"
                                class="w-full px-4 py-3 mb-4 rounded-lg border-2 border-gray-200 focus:border-emerald-400 focus:outline-none"
                            />
                            <button class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white px-6 py-4 rounded-lg font-bold hover:from-pink-600 hover:to-rose-600 transition shadow-lg">
                                SUBSCRIBE NOW
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- As Seen On Section -->
        <section class="py-8 bg-gray-50 border-t border-gray-100">
            <div class="container mx-auto px-4">
                <p class="text-center text-gray-500 text-sm uppercase tracking-wider mb-4">As Seen On</p>
                <div class="flex flex-wrap justify-center items-center gap-8 opacity-50">
                    <span class="text-gray-400 font-serif text-xl">Buzzfeed</span>
                    <span class="text-gray-400 font-serif text-xl">Country Living</span>
                    <span class="text-gray-400 font-serif text-xl">The Kitchn</span>
                    <span class="text-gray-400 font-serif text-xl">Delish</span>
                    <span class="text-gray-400 font-serif text-xl">HuffPost</span>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-12">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                    <!-- Logo Column -->
                    <div>
                        <a :href="website.url" class="inline-block mb-4">
                            <img v-if="website.logo_url || website.logo" :src="website.logo_url || website.logo" :alt="website.name" class="h-10" />
                            <span v-else class="text-xl font-bold text-gray-900">{{ website.name }}</span>
                        </a>
                        <p class="text-gray-500 text-sm">Delicious recipes for every occasion.</p>
                    </div>

                    <!-- Browse Column -->
                    <div>
                        <h3 class="text-gray-900 font-bold uppercase tracking-wider text-sm mb-4">Browse</h3>
                        <ul class="space-y-3">
                            <li><a :href="website.url" class="text-gray-500 hover:text-emerald-500 transition">Home</a></li>
                            <li v-for="category in website.categories?.slice(0, 4)" :key="category.id">
                                <a :href="category.url" class="text-gray-500 hover:text-emerald-500 transition">
                                    {{ category.name }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Explore Column -->
                    <div>
                        <h3 class="text-gray-900 font-bold uppercase tracking-wider text-sm mb-4">Explore</h3>
                        <ul class="space-y-3">
                            <li><a href="#about" class="text-gray-500 hover:text-emerald-500 transition">About Us</a></li>
                            <li><a href="#contact" class="text-gray-500 hover:text-emerald-500 transition">Contact</a></li>
                            <li><a href="#privacy" class="text-gray-500 hover:text-emerald-500 transition">Privacy Policy</a></li>
                            <li><a href="#terms" class="text-gray-500 hover:text-emerald-500 transition">Terms of Use</a></li>
                        </ul>
                    </div>

                    <!-- Follow Column -->
                    <div>
                        <h3 class="text-gray-900 font-bold uppercase tracking-wider text-sm mb-4">Follow</h3>
                        <div class="flex gap-4">
                            <a v-if="website.social_media?.instagram" :href="website.social_media.instagram" target="_blank" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 hover:bg-emerald-500 hover:text-white transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a v-if="website.social_media?.pinterest" :href="website.social_media.pinterest" target="_blank" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 hover:bg-emerald-500 hover:text-white transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                            </a>
                            <a v-if="website.social_media?.facebook" :href="website.social_media.facebook" target="_blank" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 hover:bg-emerald-500 hover:text-white transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-8 text-center text-gray-500 text-sm">
                    <p>&copy; {{ new Date().getFullYear() }} {{ website.name }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
defineProps({
    website: {
        type: Object,
        required: true
    }
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
