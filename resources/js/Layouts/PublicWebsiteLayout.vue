<template>
    <div class="min-h-screen bg-white">
        <!-- Global Meta Tags for Website Verification -->
        <Head>
            <!-- Pinterest Domain Verification -->
            <meta v-if="website.pinterest_verification" name="p:domain_verify" :content="website.pinterest_verification" />
            
            <!-- Google Site Verification -->
            <meta v-if="website.google_verification" name="google-site-verification" :content="website.google_verification" />
            
            <!-- Bing Verification -->
            <meta v-if="website.bing_verification" name="msvalidate.01" :content="website.bing_verification" />
            
            <!-- Yandex Verification -->
            <meta v-if="website.yandex_verification" name="yandex-verification" :content="website.yandex_verification" />
        </Head>
        
        <!-- Cookie Consent Banner (Custom CMP) -->
        <!-- Shown for Google Ads and when no ad system is configured -->
        <!-- Only hidden when HBAgency is active (they provide their own CMP) -->
        <CookieConsent 
            v-if="showCustomCMP"
            :show-settings-button="true"
            :auto-show="true"
            @consent-given="onConsentGiven"
            @consent-rejected="onConsentRejected"
        />
        <!-- Top Banner -->
        <div class="bg-gradient-to-r from-emerald-400 to-teal-400 py-2">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center text-xs sm:text-sm">
                    <div class="text-white font-medium flex items-center gap-2 flex-1 min-w-0">
                        <span class="hidden sm:inline">🍳</span>
                        <span class="truncate">{{ website.theme_settings?.banner_text || '30-MINUTE MEALS! Get the email series now →' }}</span>
                    </div>
                    <div class="hidden sm:flex gap-4 items-center shrink-0">
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
                        <img v-if="website.logo_url" :src="website.logo_url" :alt="website.name" class="h-10 md:h-12" />
                        <span v-else class="text-xl md:text-2xl font-bold text-gray-900">{{ website.name }}</span>
                    </a>

                    <!-- Main Navigation (Desktop) -->
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

                    <!-- Mobile Hamburger Button -->
                    <button 
                        @click="toggleMobileMenu"
                        class="md:hidden p-2 text-gray-600 hover:text-emerald-500 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Navigation Menu -->
                <div 
                    v-show="mobileMenuOpen"
                    class="md:hidden border-t border-gray-100 py-4"
                >
                    <nav class="space-y-2">
                        <a 
                            :href="website.url" 
                            @click="closeMobileMenu"
                            class="block px-4 py-3 text-gray-700 hover:text-emerald-500 hover:bg-emerald-50 rounded-lg font-medium transition"
                        >
                            Home
                        </a>
                        
                        <!-- Categories Section -->
                        <div class="px-4 py-2">
                            <span class="text-xs text-gray-400 uppercase tracking-wider font-medium">Categories</span>
                        </div>
                        <a
                            v-for="category in website.categories"
                            :key="category.id"
                            :href="category.url"
                            @click="closeMobileMenu"
                            class="block px-4 py-3 text-gray-600 hover:text-emerald-500 hover:bg-emerald-50 rounded-lg transition ml-2"
                        >
                            {{ category.name }}
                        </a>
                        
                        <!-- Dynamic Pages -->
                        <div v-if="website.pages?.length > 0" class="pt-2">
                            <div class="px-4 py-2">
                                <span class="text-xs text-gray-400 uppercase tracking-wider font-medium">Pages</span>
                            </div>
                            <a
                                v-for="page in website.pages"
                                :key="page.id"
                                :href="page.url"
                                @click="closeMobileMenu"
                                class="block px-4 py-3 text-gray-600 hover:text-emerald-500 hover:bg-emerald-50 rounded-lg transition ml-2"
                            >
                                {{ page.title }}
                            </a>
                        </div>
                        
                        <!-- Social Links (Mobile) -->
                        <div v-if="website.social_media?.instagram || website.social_media?.pinterest || website.social_media?.facebook" class="pt-4 px-4">
                            <span class="text-xs text-gray-400 uppercase tracking-wider font-medium">Follow Us</span>
                            <div class="flex gap-4 mt-3">
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
                    </nav>
                </div>

                <!-- Category Tabs (Desktop only - mobile uses hamburger menu) -->
                <div class="hidden md:block border-t border-gray-100 overflow-x-auto scrollbar-hide">
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
        <div class="bg-gray-50 py-4 md:py-6">
            <div class="container mx-auto px-4">
                <div class="max-w-2xl mx-auto">
                    <form :action="website.url + '/search'" method="GET" class="relative">
                        <input
                            type="text"
                            name="q"
                            :value="searchQuery"
                            placeholder="🔍 Search recipes and articles..."
                            class="w-full px-4 sm:px-6 py-3 sm:py-4 pr-20 sm:pr-28 rounded-full border-2 border-gray-200 focus:border-emerald-400 focus:outline-none text-gray-700 shadow-sm text-sm sm:text-base"
                        />
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-emerald-500 text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-medium hover:bg-emerald-600 transition shadow-md text-sm sm:text-base">
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Newsletter Section -->
        <section id="subscribe" class="bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 py-10 md:py-16">
            <div class="container mx-auto px-4">
                <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-center gap-8 md:gap-12">
                    <div class="flex-1 text-white text-center md:text-left">
                        <p class="text-emerald-100 text-xs sm:text-sm uppercase tracking-wider mb-2">Free Email Bonus</p>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-3 md:mb-4">30-Minute Meals!</h2>
                        <p class="text-base md:text-lg text-emerald-50">Join to receive our email series which contains a round-up of some of our quick and easy family favorite recipes.</p>
                    </div>
                    <div class="flex-1 w-full max-w-md">
                        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-2xl">
                            <p class="text-gray-600 text-sm mb-3 md:mb-4">Enter your email address:</p>
                            
                            <!-- Success Message -->
                            <div v-if="subscribeSuccess && !showSubscribePopup" class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-4 text-sm">
                                {{ subscribeSuccess }}
                            </div>
                            
                            <!-- Error Message -->
                            <div v-if="subscribeError && !showSubscribePopup" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                                {{ subscribeError }}
                            </div>
                            
                            <input
                                v-model="footerEmail"
                                type="email"
                                placeholder="you@example.com"
                                class="w-full px-4 py-3 mb-3 md:mb-4 rounded-lg border-2 border-gray-200 focus:border-emerald-400 focus:outline-none text-sm sm:text-base"
                                @keyup.enter="handleFooterSubscribe"
                            />
                            <button 
                                @click="handleFooterSubscribe"
                                :disabled="isSubscribing"
                                class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white px-6 py-3 md:py-4 rounded-lg font-bold hover:from-pink-600 hover:to-rose-600 transition shadow-lg text-sm sm:text-base disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <svg v-if="isSubscribing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ isSubscribing ? 'SUBSCRIBING...' : 'SUBSCRIBE NOW' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- As Seen On Section -->
        <section class="py-6 md:py-8 bg-gray-50 border-t border-gray-100">
            <div class="container mx-auto px-4">
                <p class="text-center text-gray-500 text-xs sm:text-sm uppercase tracking-wider mb-3 md:mb-4">As Seen On</p>
                <div class="flex flex-wrap justify-center items-center gap-4 sm:gap-6 md:gap-8 opacity-50">
                    <span class="text-gray-400 font-serif text-base sm:text-lg md:text-xl">Buzzfeed</span>
                    <span class="text-gray-400 font-serif text-base sm:text-lg md:text-xl">Country Living</span>
                    <span class="text-gray-400 font-serif text-base sm:text-lg md:text-xl">The Kitchn</span>
                    <span class="text-gray-400 font-serif text-base sm:text-lg md:text-xl">Delish</span>
                    <span class="text-gray-400 font-serif text-base sm:text-lg md:text-xl">HuffPost</span>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-8 md:py-12">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 mb-6 md:mb-8">
                    <!-- Logo Column -->
                    <div class="col-span-2 md:col-span-1">
                        <a :href="website.url" class="inline-block mb-3 md:mb-4">
                            <img v-if="website.logo_url" :src="website.logo_url" :alt="website.name" class="h-8 md:h-10" />
                            <span v-else class="text-lg md:text-xl font-bold text-gray-900">{{ website.name }}</span>
                        </a>
                        <p class="text-gray-500 text-xs sm:text-sm">Delicious recipes for every occasion.</p>
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

        <!-- ========== AD PLACEMENT: Sticky Footer Ad (728x90) ========== -->
        <!-- HBAgency Sticky Footer -->
        <div v-if="website.hbagency_active && website.hbagency_placements?.sticky_footer" class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-t border-gray-200 shadow-lg py-2">
            <div class="container mx-auto px-4 flex justify-center">
                <div :id="'hbagency_space_' + website.hbagency_placements.sticky_footer" class="min-h-[90px] w-full max-w-[728px]">
                    <!-- HBAgency will inject sticky footer ad here -->
                </div>
            </div>
        </div>

        <!-- Google Ads Sticky Footer -->
        <div v-if="website.google_ads_active && website.google_ads_placements?.sticky_footer" class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-t border-gray-200 shadow-lg py-2">
            <div class="container mx-auto px-4 flex justify-center">
                <ins class="adsbygoogle"
                     style="display:inline-block;width:728px;height:90px"
                     :data-ad-client="website.google_adsense_id"
                     :data-ad-slot="website.google_ads_placements.sticky_footer"></ins>
            </div>
        </div>

        <!-- Spacer for sticky footer ad -->
        <div v-if="(website.hbagency_active && website.hbagency_placements?.sticky_footer) || (website.google_ads_active && website.google_ads_placements?.sticky_footer)" class="h-[100px]"></div>

        <!-- Subscribe Popup Modal -->
        <div v-if="showSubscribePopup" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeSubscribePopup"></div>
            
            <!-- Modal -->
            <div class="relative bg-gradient-to-br from-emerald-400 via-teal-400 to-cyan-400 rounded-3xl shadow-2xl max-w-4xl w-full overflow-hidden">
                <!-- Close button -->
                <button 
                    @click="closeSubscribePopup"
                    class="absolute top-4 right-4 text-white/80 hover:text-white transition z-10"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <!-- Content with image on left -->
                <div class="p-6 md:p-8">
                    <div class="flex flex-col md:flex-row gap-6 md:gap-8 items-center md:items-start">
                        <!-- Prize Image on left (desktop) / top (mobile) - Takes more space -->
                        <div v-if="subscriptionPopupImage" class="flex-shrink-0 w-full md:w-2/5 flex justify-center md:justify-start">
                            <img 
                                :src="subscriptionPopupImage" 
                                alt="Subscribe prize" 
                                class="w-full max-w-xs md:max-w-none md:w-full h-auto md:h-[400px] object-cover rounded-xl shadow-2xl border-2 border-white/30"
                            />
                        </div>
                        
                        <!-- Content on right -->
                        <div class="flex-1 md:w-3/5 text-center md:text-left">
                            <div class="mb-4">
                                <h3 class="text-2xl md:text-3xl font-bold text-white mb-2">{{ subscriptionPopupTitle }}</h3>
                                <p class="text-lg text-emerald-100">{{ subscriptionPopupSubtitle }}</p>
                            </div>
                            
                            <!-- Description -->
                            <p v-if="subscriptionPopupDescription" class="text-white/90 text-sm mb-4">
                                {{ subscriptionPopupDescription }}
                            </p>
                            
                            <!-- Success Message -->
                            <div v-if="subscribeSuccess" class="bg-white/20 border border-white/40 text-white px-4 py-3 rounded-xl mb-4 text-sm">
                                <div class="flex items-center justify-center md:justify-start gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ subscribeSuccess }}
                                </div>
                            </div>
                            
                            <!-- Error Message -->
                            <div v-if="subscribeError" class="bg-red-500/20 border border-red-400/40 text-white px-4 py-3 rounded-xl mb-4 text-sm">
                                {{ subscribeError }}
                            </div>
                            
                            <div v-if="!subscribeSuccess" class="bg-white rounded-2xl p-6 shadow-xl">
                                <!-- Email field - HIDDEN by default, only shown after button click -->
                                <template v-if="showEmailField">
                                    <p class="text-gray-600 text-sm mb-4">Enter your email address:</p>
                                    <input
                                        v-model="subscribeEmail"
                                        type="email"
                                        placeholder="you@example.com"
                                        class="w-full px-4 py-3 mb-4 rounded-xl border-2 border-gray-200 focus:border-emerald-400 focus:outline-none text-base"
                                        @keyup.enter="handlePopupSubscribe"
                                        autofocus
                                    />
                                </template>
                                
                                <!-- Button - reveals email field on first click, submits on second click -->
                                <button 
                                    @click="showEmailField ? handlePopupSubscribe() : revealEmailField()"
                                    :disabled="isSubscribing"
                                    class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white px-6 py-4 rounded-xl font-bold hover:from-pink-600 hover:to-rose-600 transition shadow-lg disabled:opacity-50 flex items-center justify-center gap-2"
                                >
                                    <svg v-if="isSubscribing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ isSubscribing ? 'SUBSCRIBING...' : 'SUBSCRIBE NOW' }}
                                </button>
                            </div>
                            
                            <p class="text-white/70 text-xs mt-4">
                                We respect your privacy. Unsubscribe at any time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useSubscribePopup } from '@/composables/useSubscribePopup';
import { useConsentManagement, CONSENT_CATEGORIES } from '@/composables/useConsentManagement';
import CookieConsent from '@/Components/CookieConsent.vue';

const props = defineProps({
    website: {
        type: Object,
        required: true
    },
    searchQuery: {
        type: String,
        default: ''
    }
});

// Consent Management
const { hasConsentFor, consentGiven, initializeHBAgencyAds } = useConsentManagement();

// Show custom CMP when Google Ads is active OR when neither ad system is configured
// Only hide CMP when HBAgency is active (they provide their own CMP)
const showCustomCMP = computed(() => {
    const hasActiveHBAgency = props.website?.hbagency_script && props.website?.hbagency_active;
    
    // If HBAgency is active, they handle their own CMP, so don't show ours
    if (hasActiveHBAgency) {
        return false;
    }
    
    // Always show CMP if Google Ads is active (they need consent)
    // OR if no ad system is configured (default behavior)
    return true;
});

// Handle consent given
const onConsentGiven = (details) => {
    console.log('[Layout] Consent given:', details);
    // Ads will be loaded automatically by the consent management system
};

// Handle consent rejected
const onConsentRejected = () => {
    console.log('[Layout] Consent rejected - ads will not be shown');
};

// Debug: Log website ad configuration on mount
onMounted(() => {
    nextTick(() => {
        console.log('[Layout] Website HBAgency config:', {
            hasScript: !!props.website?.hbagency_script,
            scriptLength: props.website?.hbagency_script?.length || 0,
            placements: props.website?.hbagency_placements,
            active: props.website?.hbagency_active,
        });
        
        console.log('[Layout] Website Google Ads config:', {
            hasAdsenseId: !!props.website?.google_adsense_id,
            placements: props.website?.google_ads_placements,
            active: props.website?.google_ads_active,
        });
        
        // Initialize Google Tag Manager
        if (props.website?.gtm_id) {
            initGTM(props.website.gtm_id);
        }
        
        // Initialize Google Analytics
        if (props.website?.google_analytics_id && hasConsentFor(CONSENT_CATEGORIES.ANALYTICS)) {
            initGoogleAnalytics(props.website.google_analytics_id);
        }
        
        // Initialize Google AdSense ads only if consent is given
        if (props.website?.google_ads_active && props.website?.google_adsense_id) {
            // Check if advertising consent is given
            if (hasConsentFor(CONSENT_CATEGORIES.ADVERTISING)) {
                setTimeout(() => {
                    initGoogleAdsenseAds();
                }, 500);
            } else {
                console.log('[Google Ads] Waiting for advertising consent...');
            }
        }
    });
});

// Initialize Google Tag Manager
const initGTM = (gtmId) => {
    if (typeof window === 'undefined' || !gtmId) return;
    
    // Add GTM script
    (function(w,d,s,l,i){
        w[l]=w[l]||[];
        w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
        j.async=true;
        j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
        f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer',gtmId);
    
    console.log('[Analytics] GTM initialized:', gtmId);
};

// Initialize Google Analytics
const initGoogleAnalytics = (gaId) => {
    if (typeof window === 'undefined' || !gaId) return;
    
    // Load gtag.js
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
    document.head.appendChild(script);
    
    // Initialize gtag
    window.dataLayer = window.dataLayer || [];
    function gtag(){window.dataLayer.push(arguments);}
    window.gtag = gtag;
    gtag('js', new Date());
    gtag('config', gaId);
    
    console.log('[Analytics] GA4 initialized:', gaId);
};

// Initialize Google AdSense ads
const initGoogleAdsenseAds = () => {
    if (typeof window === 'undefined') return;
    if (!props.website?.google_ads_active || !props.website?.google_adsense_id) {
        console.log('[Google Ads] Not active or missing AdSense ID');
        return;
    }
    
    console.log('[Google Ads] Initializing AdSense ads...');
    
    // Get all adsbygoogle elements
    const adElements = document.querySelectorAll('.adsbygoogle');
    console.log('[Google Ads] Found', adElements.length, 'ad slots');
    
    if (adElements.length === 0) {
        console.warn('[Google Ads] No ad slots found on page');
        return;
    }
    
    // Initialize each ad slot
    adElements.forEach((adElement, index) => {
        try {
            // Check if already initialized
            const status = adElement.getAttribute('data-adsbygoogle-status');
            if (status === 'done') {
                console.log('[Google Ads] Ad slot', index + 1, 'already initialized');
                return;
            }
            
            // Verify required attributes
            const client = adElement.getAttribute('data-ad-client');
            const slot = adElement.getAttribute('data-ad-slot');
            
            if (!client) {
                console.error('[Google Ads] Missing data-ad-client for ad slot', index + 1);
                return;
            }
            
            if (!slot) {
                console.error('[Google Ads] Missing data-ad-slot for ad slot', index + 1);
                return;
            }
            
            console.log('[Google Ads] Initializing ad slot', index + 1, '- Client:', client, 'Slot:', slot);
            
            // Push to adsbygoogle
            (window.adsbygoogle = window.adsbygoogle || []).push({});
            
            console.log('[Google Ads] Ad slot', index + 1, 'initialized successfully');
        } catch (e) {
            console.error('[Google Ads] Error initializing ad slot', index + 1, ':', e);
        }
    });
    
    console.log('[Google Ads] All ad slots processed');
};

// Get subscription popup settings
const themeSettings = computed(() => props.website?.theme_settings || {});
const subscriptionPopupTitle = computed(() => themeSettings.value.subscription_popup_title || 'GET NEW RECIPES');
const subscriptionPopupSubtitle = computed(() => themeSettings.value.subscription_popup_subtitle || 'IN YOUR INBOX');
const subscriptionPopupDescription = computed(() => themeSettings.value.subscription_popup_description || 'Join to receive our email series which contains a round-up of some of our quick and easy family favorite recipes.');
const subscriptionPopupImage = computed(() => themeSettings.value.subscription_popup_image || '');

const mobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

// Subscribe functionality - using shared composable
const {
    showSubscribePopup,
    subscribeEmail,
    isSubscribing,
    subscribeSuccess,
    subscribeError,
    showEmailField,
    openSubscribePopup,
    closeSubscribePopup,
    revealEmailField
} = useSubscribePopup();

const footerEmail = ref('');

const submitSubscribe = async (email, source = 'popup') => {
    if (!email || !email.includes('@')) {
        subscribeError.value = 'Please enter a valid email address.';
        return;
    }
    
    isSubscribing.value = true;
    subscribeError.value = '';
    subscribeSuccess.value = '';
    
    try {
        // Determine the correct subscribe URL based on website configuration
        let subscribeUrl = `/site/${props.website.id}/subscribe`;
        
        // If we're on a subdomain or custom domain, use the domain-based route
        const currentHost = window.location.host;
        const baseDomain = props.website.base_domain || 'localhost';
        
        if (currentHost !== baseDomain && !currentHost.includes('localhost') && !currentHost.includes('127.0.0.1')) {
            subscribeUrl = '/subscribe';
        }
        
        const response = await axios.post(subscribeUrl, {
            email: email,
            source: source
        });
        
        if (response.data.success) {
            subscribeSuccess.value = response.data.message || 'Thank you for subscribing!';
            subscribeEmail.value = '';
            footerEmail.value = '';
            
            // Close popup after success
            if (source === 'popup') {
                setTimeout(() => {
                    closeSubscribePopup();
                }, 2000);
            }
        } else {
            subscribeError.value = response.data.message || 'Something went wrong.';
        }
    } catch (error) {
        console.error('Subscribe error:', error);
        subscribeError.value = error.response?.data?.message || 'Something went wrong. Please try again.';
    } finally {
        isSubscribing.value = false;
    }
};

const handleFooterSubscribe = () => {
    submitSubscribe(footerEmail.value, 'footer');
};

const handlePopupSubscribe = () => {
    submitSubscribe(subscribeEmail.value, 'popup');
};
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
