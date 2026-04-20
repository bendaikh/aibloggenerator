<template>
    <div class="min-h-screen bg-[#F5F1ED]">
        <!-- Global Meta Tags for Website Verification -->
        <Head>
            <meta v-if="website.pinterest_verification" name="p:domain_verify" :content="website.pinterest_verification" />
            <meta v-if="website.google_verification" name="google-site-verification" :content="website.google_verification" />
            <meta v-if="website.bing_verification" name="msvalidate.01" :content="website.bing_verification" />
            <meta v-if="website.yandex_verification" name="yandex-verification" :content="website.yandex_verification" />
        </Head>
        
        <!-- Cookie Consent Banner -->
        <CookieConsent 
            v-if="showCustomCMP"
            :show-settings-button="true"
            :auto-show="true"
            @consent-given="onConsentGiven"
            @consent-rejected="onConsentRejected"
        />

        <!-- Top Banner -->
        <div class="bg-gradient-to-r from-[#D4A574] to-[#C4956D] py-2">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center text-xs sm:text-sm">
                    <div class="text-white font-medium flex items-center gap-2 flex-1 min-w-0">
                        <span class="hidden sm:inline">✨</span>
                        <span class="truncate">{{ website.theme_settings?.banner_text || 'NEW IDEAS EVERY DAY! Join our community →' }}</span>
                    </div>
                    <div class="hidden sm:flex gap-4 items-center shrink-0">
                        <a v-if="website.social_media?.instagram" :href="website.social_media.instagram" target="_blank" class="text-white hover:text-[#F5F1ED] transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a v-if="website.social_media?.pinterest" :href="website.social_media.pinterest" target="_blank" class="text-white hover:text-[#F5F1ED] transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                        </a>
                        <a v-if="website.social_media?.facebook" :href="website.social_media.facebook" target="_blank" class="text-white hover:text-[#F5F1ED] transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header -->
        <header class="bg-[#F5F1ED] border-b border-[#E5D5C3] sticky top-0 z-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between py-6">
                    <!-- Logo -->
                    <a :href="website.url" class="flex items-center">
                        <img v-if="website.logo_url" :src="website.logo_url" :alt="website.name" class="h-10 md:h-14" />
                        <div v-else class="flex items-center gap-2">
                            <svg class="w-8 h-8 text-[#FF6B4A]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                            <span class="text-2xl md:text-3xl font-serif font-bold text-[#2D2D2D]">{{ website.name }}</span>
                        </div>
                    </a>

                    <!-- Main Navigation (Desktop) -->
                    <nav class="hidden md:flex items-center space-x-8">
                        <a :href="website.url" class="text-[#5D5D5D] hover:text-[#FF6B4A] font-medium transition">blog</a>
                        
                        <div class="relative group">
                            <button class="text-[#5D5D5D] hover:text-[#FF6B4A] font-medium transition flex items-center py-2">
                                DIY Projects
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-56 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 border border-[#E5D5C3]">
                                <div class="py-2">
                                    <a
                                        v-for="category in website.categories"
                                        :key="category.id"
                                        :href="category.url"
                                        class="block px-4 py-3 text-[#5D5D5D] hover:bg-[#FFF8F3] hover:text-[#FF6B4A] transition"
                                    >
                                        {{ category.name }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a :href="`${website.url}/shop`" class="text-[#FF6B4A] hover:text-[#E55A3A] font-bold transition flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            shop
                        </a>
                        
                        <a
                            v-for="page in website.pages"
                            :key="page.id"
                            :href="page.url"
                            class="text-[#5D5D5D] hover:text-[#FF6B4A] font-medium transition"
                        >
                            {{ page.title }}
                        </a>
                    </nav>

                    <!-- Mobile Hamburger Button -->
                    <button 
                        @click="toggleMobileMenu"
                        class="md:hidden p-2 text-[#5D5D5D] hover:text-[#FF6B4A] hover:bg-[#E5D5C3]/30 rounded-lg transition-colors"
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
                    class="md:hidden border-t border-[#E5D5C3] py-4"
                >
                    <nav class="space-y-2">
                        <a 
                            :href="website.url" 
                            @click="closeMobileMenu"
                            class="block px-4 py-3 text-[#5D5D5D] hover:text-[#FF6B4A] hover:bg-[#FFF8F3] rounded-lg font-medium transition"
                        >
                            blog
                        </a>

                        <a 
                            :href="`${website.url}/shop`" 
                            @click="closeMobileMenu"
                            class="flex items-center gap-2 px-4 py-3 text-[#FF6B4A] hover:bg-[#FFF8F3] rounded-lg font-bold transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            shop
                        </a>
                        
                        <div class="px-4 py-2">
                            <span class="text-xs text-[#9D9D9D] uppercase tracking-wider font-medium">DIY Projects</span>
                        </div>
                        <a
                            v-for="category in website.categories"
                            :key="category.id"
                            :href="category.url"
                            @click="closeMobileMenu"
                            class="block px-4 py-3 text-[#5D5D5D] hover:text-[#FF6B4A] hover:bg-[#FFF8F3] rounded-lg transition ml-2"
                        >
                            {{ category.name }}
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Search Bar Section -->
        <div class="bg-white py-4 md:py-6 border-b border-[#E5D5C3]">
            <div class="container mx-auto px-4">
                <div class="max-w-2xl mx-auto">
                    <form :action="website.url + '/search'" method="GET" class="relative">
                        <input
                            type="text"
                            name="q"
                            :value="searchQuery"
                            placeholder="🔍 Search home decor ideas..."
                            class="w-full px-4 sm:px-6 py-3 sm:py-4 pr-20 sm:pr-28 rounded-full border-2 border-[#E5D5C3] focus:border-[#FF6B4A] focus:outline-none text-[#2D2D2D] shadow-sm text-sm sm:text-base bg-[#FDFCFB]"
                        />
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-[#FF6B4A] text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-medium hover:bg-[#E55A3A] transition shadow-md text-sm sm:text-base">
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
        <section id="subscribe" class="bg-white py-12 md:py-16 border-t border-[#E5D5C3]">
            <div class="container mx-auto px-4">
                <div class="max-w-3xl mx-auto text-center">
                    <div class="mb-8">
                        <svg class="w-16 h-16 mx-auto text-[#FF6B4A] mb-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-bold mb-3 md:mb-4 text-[#2D2D2D]">Join the Artisan Community</h2>
                        <p class="text-base md:text-lg text-[#5D5D5D]">Get weekly for easy inspiration, exclusive shop updates, and behind-the-scenes content we'll send right to your inbox.</p>
                    </div>
                    
                    <div class="max-w-md mx-auto">
                        <div v-if="subscribeSuccess && !showSubscribePopup" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                            {{ subscribeSuccess }}
                        </div>
                        
                        <div v-if="subscribeError && !showSubscribePopup" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                            {{ subscribeError }}
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input
                                v-model="footerEmail"
                                type="email"
                                placeholder="Your Email Address"
                                class="flex-1 px-4 py-3 rounded-lg border-2 border-[#E5D5C3] focus:border-[#FF6B4A] focus:outline-none text-sm sm:text-base bg-[#FDFCFB]"
                                @keyup.enter="handleFooterSubscribe"
                            />
                            <button 
                                @click="handleFooterSubscribe"
                                :disabled="isSubscribing"
                                class="bg-[#FF6B4A] text-white px-8 py-3 rounded-lg font-bold hover:bg-[#E55A3A] transition shadow-lg text-sm sm:text-base disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <svg v-if="isSubscribing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ isSubscribing ? 'SUBSCRIBING...' : 'SUBSCRIBE' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-[#2D2D2D] text-white py-8 md:py-12">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 mb-6 md:mb-8">
                    <!-- Logo Column -->
                    <div class="col-span-2 md:col-span-1">
                        <a :href="website.url" class="inline-block mb-3 md:mb-4">
                            <img v-if="website.logo_url" :src="website.logo_url" :alt="website.name" class="h-8 md:h-10 brightness-0 invert" />
                            <span v-else class="text-lg md:text-xl font-serif font-bold">{{ website.name }}</span>
                        </a>
                        <p class="text-[#B8B8B8] text-xs sm:text-sm">Your daily inspiration for mindful living and beautiful spaces.</p>
                    </div>

                    <!-- Explore Column -->
                    <div>
                        <h3 class="text-white font-bold uppercase tracking-wider text-sm mb-4">Explore</h3>
                        <ul class="space-y-3">
                            <li><a :href="website.url" class="text-[#B8B8B8] hover:text-[#FF6B4A] transition text-sm">blog</a></li>
                            <li><a href="#" class="text-[#B8B8B8] hover:text-[#FF6B4A] transition text-sm">DIY Projects</a></li>
                            <li><a href="#" class="text-[#B8B8B8] hover:text-[#FF6B4A] transition text-sm">shop</a></li>
                            <li><a href="#" class="text-[#B8B8B8] hover:text-[#FF6B4A] transition text-sm">About</a></li>
                        </ul>
                    </div>

                    <!-- Support Column -->
                    <div>
                        <h3 class="text-white font-bold uppercase tracking-wider text-sm mb-4">Support</h3>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-[#B8B8B8] hover:text-[#FF6B4A] transition text-sm">Shipping & Returns</a></li>
                            <li><a href="#" class="text-[#B8B8B8] hover:text-[#FF6B4A] transition text-sm">FAQ</a></li>
                            <li><a href="#" class="text-[#B8B8B8] hover:text-[#FF6B4A] transition text-sm">Privacy Policy</a></li>
                            <li><a href="#" class="text-[#B8B8B8] hover:text-[#FF6B4A] transition text-sm">Terms & Conditions</a></li>
                        </ul>
                    </div>

                    <!-- From the Shop Column -->
                    <div>
                        <h3 class="text-white font-bold uppercase tracking-wider text-sm mb-4">From the Shop</h3>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="aspect-square bg-[#4D4D4D] rounded-lg overflow-hidden">
                                <div class="w-full h-full bg-gradient-to-br from-[#D4A574] to-[#C4956D]"></div>
                            </div>
                            <div class="aspect-square bg-[#4D4D4D] rounded-lg overflow-hidden">
                                <div class="w-full h-full bg-gradient-to-br from-[#FF6B4A] to-[#E55A3A]"></div>
                            </div>
                            <div class="aspect-square bg-[#4D4D4D] rounded-lg overflow-hidden">
                                <div class="w-full h-full bg-gradient-to-br from-[#8B9D83] to-[#6B7D63]"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-[#4D4D4D] pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-[#B8B8B8] text-sm">&copy; {{ new Date().getFullYear() }} {{ website.name }}. All rights reserved. Designed with ♥️</p>
                    
                    <div class="flex gap-4">
                        <a v-if="website.social_media?.instagram" :href="website.social_media.instagram" target="_blank" class="w-10 h-10 bg-[#4D4D4D] rounded-full flex items-center justify-center text-[#B8B8B8] hover:bg-[#FF6B4A] hover:text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a v-if="website.social_media?.pinterest" :href="website.social_media.pinterest" target="_blank" class="w-10 h-10 bg-[#4D4D4D] rounded-full flex items-center justify-center text-[#B8B8B8] hover:bg-[#FF6B4A] hover:text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                        </a>
                        <a v-if="website.social_media?.facebook" :href="website.social_media.facebook" target="_blank" class="w-10 h-10 bg-[#4D4D4D] rounded-full flex items-center justify-center text-[#B8B8B8] hover:bg-[#FF6B4A] hover:text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Ad Placement: Sticky Footer Ad -->
        <div v-if="website.hbagency_active && website.hbagency_placements?.sticky_footer" class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-t border-[#E5D5C3] shadow-lg py-2">
            <div class="container mx-auto px-4 flex justify-center">
                <div :id="'hbagency_space_' + website.hbagency_placements.sticky_footer" class="min-h-[90px] w-full max-w-[728px]"></div>
            </div>
        </div>

        <div v-if="website.google_ads_active && website.google_ads_placements?.sticky_footer" class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-sm border-t border-[#E5D5C3] shadow-lg py-2">
            <div class="container mx-auto px-4 flex justify-center">
                <ins class="adsbygoogle"
                     style="display:inline-block;width:728px;height:90px"
                     :data-ad-client="website.google_adsense_id"
                     :data-ad-slot="website.google_ads_placements.sticky_footer"></ins>
            </div>
        </div>

        <div v-if="(website.hbagency_active && website.hbagency_placements?.sticky_footer) || (website.google_ads_active && website.google_ads_placements?.sticky_footer)" class="h-[100px]"></div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
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

const { hasConsentFor, consentGiven, initializeHBAgencyAds } = useConsentManagement();

const showCustomCMP = computed(() => {
    const hasActiveHBAgency = props.website?.hbagency_script && props.website?.hbagency_active;
    if (hasActiveHBAgency) {
        return false;
    }
    return true;
});

const onConsentGiven = (details) => {
    console.log('[HomeDecorLayout] Consent given:', details);
};

const onConsentRejected = () => {
    console.log('[HomeDecorLayout] Consent rejected');
};

onMounted(() => {
    nextTick(() => {
        if (props.website?.gtm_id) {
            initGTM(props.website.gtm_id);
        }
        
        if (props.website?.google_analytics_id && hasConsentFor(CONSENT_CATEGORIES.ANALYTICS)) {
            initGoogleAnalytics(props.website.google_analytics_id);
        }
        
        if (props.website?.google_ads_active && props.website?.google_adsense_id) {
            if (hasConsentFor(CONSENT_CATEGORIES.ADVERTISING)) {
                setTimeout(() => {
                    initGoogleAdsenseAds();
                }, 500);
            }
        }
    });
});

const initGTM = (gtmId) => {
    if (typeof window === 'undefined' || !gtmId) return;
    (function(w,d,s,l,i){
        w[l]=w[l]||[];
        w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
        j.async=true;
        j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
        f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer',gtmId);
};

const initGoogleAnalytics = (gaId) => {
    if (typeof window === 'undefined' || !gaId) return;
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
    document.head.appendChild(script);
    window.dataLayer = window.dataLayer || [];
    function gtag(){window.dataLayer.push(arguments);}
    window.gtag = gtag;
    gtag('js', new Date());
    gtag('config', gaId);
};

const initGoogleAdsenseAds = () => {
    if (typeof window === 'undefined') return;
    if (!props.website?.google_ads_active || !props.website?.google_adsense_id) return;
    const adElements = document.querySelectorAll('.adsbygoogle');
    adElements.forEach((adElement) => {
        try {
            const status = adElement.getAttribute('data-adsbygoogle-status');
            if (status === 'done') return;
            (window.adsbygoogle = window.adsbygoogle || []).push({});
        } catch (e) {
            console.error('[Google Ads] Error:', e);
        }
    });
};

const mobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const footerEmail = ref('');
const isSubscribing = ref(false);
const subscribeSuccess = ref('');
const subscribeError = ref('');
const showSubscribePopup = ref(false);

const submitSubscribe = async (email) => {
    if (!email || !email.includes('@')) {
        subscribeError.value = 'Please enter a valid email address.';
        return;
    }
    
    isSubscribing.value = true;
    subscribeError.value = '';
    subscribeSuccess.value = '';
    
    try {
        let subscribeUrl = `/site/${props.website.id}/subscribe`;
        const currentHost = window.location.host;
        const baseDomain = props.website.base_domain || 'localhost';
        
        if (currentHost !== baseDomain && !currentHost.includes('localhost') && !currentHost.includes('127.0.0.1')) {
            subscribeUrl = '/subscribe';
        }
        
        const response = await axios.post(subscribeUrl, {
            email: email,
            source: 'footer'
        });
        
        if (response.data.success) {
            subscribeSuccess.value = response.data.message || 'Thank you for subscribing!';
            footerEmail.value = '';
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
    submitSubscribe(footerEmail.value);
};
</script>
