<template>
    <HomeDecorLayout :website="website">
        <Head :title="article.meta_title || (article.title + ' - ' + website.name)">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
            <meta name="description" :content="article.meta_description || article.excerpt || ''" />
            <meta v-if="article.meta_keywords" name="keywords" :content="article.meta_keywords" />
            <link rel="canonical" :href="currentPageUrl" />
            <meta name="robots" :content="robotsContent" />
            
            <!-- Open Graph -->
            <meta property="og:type" content="article" />
            <meta property="og:title" :content="article.title" />
            <meta property="og:description" :content="article.excerpt || article.meta_description || ''" />
            <meta property="og:url" :content="currentPageUrl" />
            <meta v-if="articleImageUrl" property="og:image" :content="articleImageUrl" />
            <meta property="og:site_name" :content="website.name" />
            
            <!-- Twitter Card -->
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" :content="article.title" />
            <meta name="twitter:description" :content="article.excerpt || article.meta_description || ''" />
            <meta v-if="articleImageUrl" name="twitter:image" :content="articleImageUrl" />
            
            <!-- Article Schema.org JSON-LD -->
            <component :is="'script'" type="application/ld+json" v-html="articleSchemaJson" />
        </Head>

        <!-- Article Container -->
        <article class="bg-white">
            <!-- Hero Section -->
            <header class="relative bg-[#F9F7F4] py-12 md:py-20">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto text-center">
                        <!-- Category Badge -->
                        <div v-if="article.category" class="mb-6">
                            <a :href="article.category.url" class="inline-block bg-[#E8DFD5] text-[#8B7355] text-xs font-semibold px-4 py-2 rounded-full uppercase tracking-wider hover:bg-[#D4C4B0] transition">
                                {{ article.category.name }}
                            </a>
                        </div>
                        
                        <!-- Title -->
                        <h1 
                            class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-[#2D2D2D] mb-6 leading-tight"
                            :style="{ fontFamily: articleTitleFontFamily }"
                        >
                            {{ article.title }}
                        </h1>
                        
                        <!-- Subtitle/Excerpt -->
                        <p v-if="article.excerpt" class="text-lg md:text-xl text-[#6B6B6B] max-w-2xl mx-auto mb-8 italic font-serif">
                            "{{ article.excerpt }}"
                        </p>
                        
                        <!-- Author & Date Row -->
                        <div class="flex items-center justify-center gap-6 text-sm text-[#8B8B8B]">
                            <div class="flex items-center gap-2">
                                <span class="text-[#2D2D2D] font-medium">{{ article.author?.name || article.user?.name || 'Admin' }}</span>
                            </div>
                            <span class="text-[#D4D4D4]">|</span>
                            <time :datetime="article.published_at" class="text-[#8B8B8B]">
                                {{ formatDate(article.published_at) }}
                            </time>
                        </div>
                        
                        <!-- Share Buttons -->
                        <div class="flex items-center justify-center gap-3 mt-6">
                            <a :href="pinterestShareUrl" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full border border-[#E5D5C3] flex items-center justify-center text-[#8B8B8B] hover:bg-[#E60023] hover:text-white hover:border-[#E60023] transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                            </a>
                            <a :href="facebookShareUrl" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full border border-[#E5D5C3] flex items-center justify-center text-[#8B8B8B] hover:bg-[#1877F2] hover:text-white hover:border-[#1877F2] transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <button @click="copyLink" class="w-10 h-10 rounded-full border border-[#E5D5C3] flex items-center justify-center text-[#8B8B8B] hover:bg-[#2D2D2D] hover:text-white hover:border-[#2D2D2D] transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </header>


            <!-- Main Content Area -->
            <div class="container mx-auto px-4 py-12 md:py-16">
                <div class="max-w-7xl mx-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                        
                        <!-- Sidebar - Table of Contents -->
                        <aside class="lg:col-span-3 order-2 lg:order-1">
                            <div class="lg:sticky lg:top-28 space-y-8">
                                <!-- Table of Contents -->
                                <div v-if="tableOfContents.length > 0" class="bg-[#F9F7F4] rounded-2xl p-6">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#8B8B8B] mb-4">IN THIS ARTICLE</h3>
                                    <nav class="space-y-3">
                                        <a
                                            v-for="(item, index) in tableOfContents"
                                            :key="index"
                                            :href="'#' + item.id"
                                            class="block text-sm text-[#5D5D5D] hover:text-[#FF6B4A] transition pl-3 border-l-2 border-transparent hover:border-[#FF6B4A]"
                                        >
                                            {{ item.text }}
                                        </a>
                                    </nav>
                                </div>

                                <!-- Featured Products / Shop Section -->
                                <div v-if="articleImages.length > 0 || isImageGenerationPending" class="bg-white border border-[#E5D5C3] rounded-2xl p-6">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#8B8B8B] mb-4">FEATURED IN THIS ARTICLE</h3>
                                    <div v-if="articleImages.length > 0" class="space-y-4">
                                        <div
                                            v-for="(image, index) in articleImages.slice(0, 3)"
                                            :key="index"
                                            class="flex gap-3 group cursor-pointer"
                                        >
                                            <div class="w-16 h-16 rounded-lg overflow-hidden bg-[#F5F1ED] flex-shrink-0">
                                                <img
                                                    :src="image.url || image.local_path"
                                                    :alt="image.title"
                                                    class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-[#2D2D2D] group-hover:text-[#FF6B4A] transition line-clamp-2">
                                                    {{ image.title }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="space-y-4">
                                        <div v-for="n in 3" :key="`pending-thumb-${n}`" class="flex gap-3 animate-pulse">
                                            <div class="w-16 h-16 rounded-lg bg-[#EFE7DE] flex-shrink-0"></div>
                                            <div class="flex-1 min-w-0 space-y-2 pt-2">
                                                <div class="h-3 bg-[#EFE7DE] rounded w-4/5"></div>
                                                <div class="h-3 bg-[#EFE7DE] rounded w-3/5"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <a v-if="articleImages.length > 3" href="#" class="block mt-4 text-sm text-[#FF6B4A] font-medium hover:underline">
                                        SHOP THE LOOK →
                                    </a>
                                </div>
                            </div>
                        </aside>

                        <!-- Main Article Content -->
                        <main class="lg:col-span-9 order-1 lg:order-2">
                            <!-- First Letter Drop Cap Intro -->
                            <div 
                                class="prose prose-lg max-w-none article-content-homedecor" 
                                :style="{ '--article-body-font': articleFontFamily, '--article-title-font': articleTitleFontFamily }"
                                v-html="processedContent"
                            ></div>

                            <!-- Image Generation Status -->
                            <div v-if="isImageGenerationPending" class="mt-8 p-4 rounded-xl bg-[#F9F7F4] border border-[#E5D5C3] text-[#6B6B6B] text-sm">
                                Images are being generated for this article. They will appear automatically in a few moments.
                            </div>

                            <!-- Remaining Images Section - Shows images that weren't matched to H2 headers -->
                            <!-- Each image is displayed with its title as a full section, not just a gallery -->
                            <div v-if="galleryImages.length > 0" class="mt-12 space-y-12">
                                <div
                                    v-for="(image, index) in galleryImages"
                                    :key="image.id || index"
                                    class="space-y-4"
                                >
                                    <!-- Section Header for this image -->
                                    <h2 v-if="image.title" class="text-2xl md:text-3xl font-serif font-bold text-[#2D2D2D]">
                                        {{ image.title }}
                                    </h2>
                                    
                                    <!-- Image with Pinterest overlay -->
                                    <figure class="relative group">
                                        <div class="rounded-2xl overflow-hidden shadow-lg relative bg-[#F5F1ED]">
                                            <img
                                                :src="image.url || image.local_path"
                                                :alt="image.title"
                                                class="w-full h-auto object-contain"
                                                loading="lazy"
                                            />
                                            <!-- Pinterest Pin Button Overlay -->
                                            <div class="absolute top-4 left-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                                <a :href="getPinterestUrl(image.url || image.local_path, image.title)" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 px-4 py-2 bg-[#E60023] text-white rounded-full font-bold text-sm hover:bg-[#bd081c] transition shadow-lg">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                                    Pin it
                                                </a>
                                            </div>
                                        </div>
                                    </figure>
                                    
                                    <!-- Description/Content for this image -->
                                    <div v-if="image.metadata?.item?.description" class="prose prose-lg max-w-none">
                                        <p class="text-[#5D5D5D] leading-relaxed">
                                            <strong class="text-[#2D2D2D]">{{ getImageIntroTitle(image) }}:</strong>
                                            {{ image.metadata.item.description }}
                                        </p>
                                    </div>
                                    
                                    <!-- Fallback description based on title theme -->
                                    <div v-else class="prose prose-lg max-w-none">
                                        <p class="text-[#5D5D5D] leading-relaxed">
                                            <strong class="text-[#2D2D2D]">Stunning Design:</strong>
                                            This exquisite space showcases the perfect blend of luxury and comfort, featuring carefully curated elements that create an inviting atmosphere. The attention to detail and harmonious color palette make this a standout example of sophisticated interior design.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pull Quote -->
                            <div v-if="pullQuote" class="my-12 py-8 border-t border-b border-[#E5D5C3]">
                                <blockquote class="text-2xl md:text-3xl font-serif italic text-center text-[#5D5D5D] leading-relaxed">
                                    "{{ pullQuote }}"
                                </blockquote>
                            </div>

                            <!-- Tags -->
                            <div v-if="article.meta_tags && article.meta_tags.length > 0" class="mt-12 pt-8 border-t border-[#E5D5C3]">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(tag, index) in article.meta_tags"
                                        :key="index"
                                        class="px-4 py-2 bg-[#F5F1ED] text-[#5D5D5D] text-sm rounded-full hover:bg-[#E8DFD5] transition cursor-pointer"
                                    >
                                        #{{ tag }}
                                    </span>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
            </div>

            <!-- Author Bio Section -->
            <section class="bg-[#F9F7F4] py-12 md:py-16">
                <div class="container mx-auto px-4">
                    <div class="max-w-3xl mx-auto">
                        <div class="flex flex-col md:flex-row items-center gap-6 md:gap-8">
                            <!-- Author Image -->
                            <div class="w-24 h-24 md:w-32 md:h-32 rounded-full overflow-hidden bg-[#E8DFD5] flex-shrink-0 ring-4 ring-white shadow-lg">
                                <img
                                    v-if="article.author?.image"
                                    :src="article.author.image"
                                    :alt="article.author?.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <span class="text-3xl font-serif text-[#8B7355]">{{ (article.author?.name || article.user?.name || 'A').charAt(0) }}</span>
                                </div>
                            </div>
                            
                            <!-- Author Info -->
                            <div class="text-center md:text-left flex-1">
                                <h3 class="text-xl font-serif font-bold text-[#2D2D2D] mb-2">
                                    {{ article.author?.name || article.user?.name || 'Admin' }}
                                </h3>
                                <p class="text-[#6B6B6B] mb-4 leading-relaxed">
                                    {{ article.author?.description || "Interior design enthusiast and writer. Passionate about creating beautiful, functional spaces that tell a story." }}
                                </p>
                                <div class="flex items-center justify-center md:justify-start gap-3">
                                    <a href="#" class="text-[#8B8B8B] hover:text-[#FF6B4A] transition">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    </a>
                                    <a href="#" class="text-[#8B8B8B] hover:text-[#FF6B4A] transition">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Newsletter CTA Section -->
            <section class="bg-[#8B9D83] py-12 md:py-16">
                <div class="container mx-auto px-4">
                    <div class="max-w-2xl mx-auto text-center">
                        <h2 class="text-2xl md:text-3xl font-serif font-bold text-white mb-4">
                            Bring the North Home
                        </h2>
                        <p class="text-white/80 mb-8">
                            Get weekly interior inspiration and exclusive content delivered straight to your inbox.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                            <input
                                v-model="newsletterEmail"
                                type="email"
                                placeholder="your email address"
                                class="flex-1 px-4 py-3 rounded-lg border-0 text-[#2D2D2D] placeholder:text-[#9D9D9D] focus:outline-none focus:ring-2 focus:ring-white/50"
                            />
                            <button
                                @click="subscribeNewsletter"
                                :disabled="isSubscribing"
                                class="px-6 py-3 bg-[#2D2D2D] text-white rounded-lg font-semibold hover:bg-[#1D1D1D] transition disabled:opacity-50"
                            >
                                {{ isSubscribing ? 'Subscribing...' : 'Subscribe' }}
                            </button>
                        </div>
                        <p v-if="subscribeMessage" :class="subscribeSuccess ? 'text-white' : 'text-red-200'" class="mt-4 text-sm">
                            {{ subscribeMessage }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Related Articles -->
            <section v-if="relatedArticles && relatedArticles.length > 0" class="py-12 md:py-16 bg-white">
                <div class="container mx-auto px-4">
                    <div class="max-w-6xl mx-auto">
                        <h2 class="text-2xl md:text-3xl font-serif font-bold text-[#2D2D2D] mb-8 text-center">
                            You Might Also Like
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <a
                                v-for="related in relatedArticles.slice(0, 3)"
                                :key="related.id"
                                :href="related.url"
                                class="group"
                            >
                                <div class="aspect-[4/3] rounded-2xl overflow-hidden mb-4 bg-[#F5F1ED]">
                                    <img
                                        v-if="related.processed_featured_image || related.featured_image"
                                        :src="related.processed_featured_image || related.featured_image"
                                        :alt="related.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    />
                                </div>
                                <h3 class="text-lg font-serif font-bold text-[#2D2D2D] group-hover:text-[#FF6B4A] transition line-clamp-2">
                                    {{ related.title }}
                                </h3>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </article>
    </HomeDecorLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import HomeDecorLayout from '@/Layouts/HomeDecorLayout.vue';
import axios from 'axios';

const props = defineProps({
    website: Object,
    article: Object,
    relatedArticles: Array,
    articleImages: {
        type: Array,
        default: () => []
    }
});

// Get font families from website theme settings
const articleTitleFontFamily = computed(() => {
    const fontId = props.website?.theme_settings?.article_title_font_family || 'playfair-display';
    const fontMap = {
        'default': "'Plus Jakarta Sans', sans-serif",
        'inter': "'Inter', sans-serif",
        'roboto': "'Roboto', sans-serif",
        'open-sans': "'Open Sans', sans-serif",
        'lato': "'Lato', sans-serif",
        'montserrat': "'Montserrat', sans-serif",
        'poppins': "'Poppins', sans-serif",
        'raleway': "'Raleway', sans-serif",
        'bebas-neue': "'Bebas Neue', cursive",
        'playfair-display': "'Playfair Display', serif",
        'merriweather': "'Merriweather', serif",
        'lora': "'Lora', serif",
        'dancing-script': "'Dancing Script', cursive",
        'pacifico': "'Pacifico', cursive",
        'great-vibes': "'Great Vibes', cursive",
    };
    return fontMap[fontId] || fontMap['playfair-display'];
});

const articleFontFamily = computed(() => {
    const fontId = props.website?.theme_settings?.article_font_family || 'default';
    const fontMap = {
        'default': "'Plus Jakarta Sans', sans-serif",
        'inter': "'Inter', sans-serif",
        'roboto': "'Roboto', sans-serif",
        'open-sans': "'Open Sans', sans-serif",
        'lato': "'Lato', sans-serif",
        'montserrat': "'Montserrat', sans-serif",
        'poppins': "'Poppins', sans-serif",
        'raleway': "'Raleway', sans-serif",
        'bebas-neue': "'Bebas Neue', cursive",
        'playfair-display': "'Playfair Display', serif",
        'merriweather': "'Merriweather', serif",
        'lora': "'Lora', serif",
        'dancing-script': "'Dancing Script', cursive",
        'pacifico': "'Pacifico', cursive",
        'great-vibes': "'Great Vibes', cursive",
    };
    return fontMap[fontId] || fontMap['default'];
});

const imagePollingActive = ref(false);
let imagePollingTimer = null;
const imagePollingAttempt = ref(0);
const MAX_IMAGE_POLL_ATTEMPTS = 18; // ~3 minutes at 10s interval
const IMAGE_POLL_INTERVAL_MS = 10000;

// Newsletter
const newsletterEmail = ref('');
const isSubscribing = ref(false);
const subscribeMessage = ref('');
const subscribeSuccess = ref(false);

const subscribeNewsletter = async () => {
    if (!newsletterEmail.value || !newsletterEmail.value.includes('@')) {
        subscribeMessage.value = 'Please enter a valid email address.';
        subscribeSuccess.value = false;
        return;
    }

    isSubscribing.value = true;
    try {
        const response = await axios.post(`/site/${props.website.id}/subscribe`, {
            email: newsletterEmail.value,
            source: 'article_newsletter'
        });
        subscribeMessage.value = response.data.message || 'Thank you for subscribing!';
        subscribeSuccess.value = true;
        newsletterEmail.value = '';
    } catch (error) {
        subscribeMessage.value = error.response?.data?.message || 'Something went wrong. Please try again.';
        subscribeSuccess.value = false;
    } finally {
        isSubscribing.value = false;
    }
};

// Article images from props
const articleImages = computed(() => props.articleImages || []);

const isImageGenerationPending = computed(() => {
    // Never show "generating" state if article has user-uploaded featured images
    const hasUserUploadedImage = props.article?.featured_image || props.article?.processed_featured_image;
    if (hasUserUploadedImage) {
        return false;
    }
    // Never show "generating" state if article has user-uploaded image sections
    const hasUserUploadedSections = props.article?.variation_metadata?.image_sections?.length > 0;
    if (hasUserUploadedSections) {
        return false;
    }
    return imagePollingActive.value && articleImages.value.length === 0;
});

// Separate hero images from content images
const heroImage = computed(() => {
    const images = props.articleImages || [];
    return images.find(img => img.metadata?.is_hero || img.generation_type === 'ai_hero');
});

// Get only non-hero images for embedding in content
// First check for user-uploaded image_sections in variation_metadata, then fall back to articleImages
const contentImages = computed(() => {
    // Check for user-uploaded image sections in variation_metadata
    const imageSections = props.article?.variation_metadata?.image_sections;
    if (imageSections && Array.isArray(imageSections) && imageSections.length > 0) {
        // Convert image sections to the format expected by the content processor
        return imageSections.map((img, index) => ({
            id: `user-upload-${index}`,
            url: img.url,
            title: img.title,
            position: index,
            is_user_uploaded: true
        }));
    }
    
    // Fall back to AI-generated articleImages
    const images = props.articleImages || [];
    return images
        .filter(img => !img.metadata?.is_hero && img.generation_type !== 'ai_hero')
        .sort((a, b) => (a.position ?? 0) - (b.position ?? 0));
});

// Create a map of image titles to images for matching with content sections
const imagesByTitle = computed(() => {
    const map = new Map();
    const images = contentImages.value;
    
    images.forEach(img => {
        if (img.title) {
            // Normalize the title for matching (lowercase, trim)
            const normalizedTitle = img.title.toLowerCase().trim();
            map.set(normalizedTitle, img);
            
            // Also store partial matches (first part before " - ")
            const firstPart = normalizedTitle.split(' - ')[0].trim();
            if (firstPart && firstPart !== normalizedTitle) {
                map.set(firstPart, img);
            }
        }
    });
    
    return map;
});

// Process content with drop cap, clean styling, and inject images
// Also returns the IDs of images that were embedded
const contentWithImages = computed(() => {
    let content = props.article?.processed_content || props.article?.content || '';
    const images = contentImages.value; // Use only non-hero images
    const usedIds = new Set();
    
    // Add IDs to headers and inject images after matching sections
    let headingCount = 0;
    let imageIndex = 0;
    
    content = content.replace(/<h([23])([^>]*)>([^<]+)<\/h\1>/gi, (match, level, attrs, text) => {
        headingCount++;
        const id = `section-${headingCount}`;
        const headerHtml = `<h${level}${attrs} id="${id}">${text}</h${level}>`;
        
        // Only inject images for H2 headers (main sections)
        if (level === '2') {
            const headerText = text.trim().toLowerCase();
            
            // Try to find a matching image
            let matchedImage = null;
            
            // Try exact match first
            if (imagesByTitle.value.has(headerText)) {
                matchedImage = imagesByTitle.value.get(headerText);
            } else {
                // Try partial matching - check if header contains image title or vice versa
                for (const [title, img] of imagesByTitle.value.entries()) {
                    if (headerText.includes(title) || title.includes(headerText)) {
                        if (!usedIds.has(img.id)) {
                            matchedImage = img;
                            break;
                        }
                    }
                }
            }
            
            // If no match found, use sequential fallback for ANY H2 section
            if (!matchedImage) {
                const availableImages = images.filter(img => !usedIds.has(img.id));
                if (availableImages.length > 0 && imageIndex < images.length) {
                    matchedImage = availableImages[0];
                }
            }
            
            if (matchedImage && !usedIds.has(matchedImage.id)) {
                usedIds.add(matchedImage.id);
                imageIndex++;
                
                // Create image HTML to inject after the header with Pinterest overlay
                const pinterestUrl = `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(currentPageUrl.value)}&media=${encodeURIComponent(matchedImage.url || matchedImage.local_path)}&description=${encodeURIComponent(matchedImage.title || text)}`;
                const imageHtml = `
                    <figure class="my-8 relative group">
                        <div class="rounded-2xl overflow-hidden shadow-lg relative bg-[#F5F1ED]">
                            <img 
                                src="${matchedImage.url || matchedImage.local_path}" 
                                alt="${matchedImage.title || text}" 
                                class="w-full h-auto object-contain"
                                loading="lazy"
                            />
                            <!-- Pinterest Pin Button Overlay -->
                            <div class="absolute top-4 left-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <a href="${pinterestUrl}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 px-4 py-2 bg-[#E60023] text-white rounded-full font-bold text-sm hover:bg-[#bd081c] transition shadow-lg">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                    Pin it
                                </a>
                            </div>
                        </div>
                        ${matchedImage.title ? `<figcaption class="mt-3 text-center text-sm text-[#8B8B8B] italic">${matchedImage.title}</figcaption>` : ''}
                    </figure>
                `;
                
                return headerHtml + imageHtml;
            }

            // While image generation is pending, show a skeleton placeholder
            // directly under each H2 so users see where images will appear.
            if (isImageGenerationPending.value) {
                const placeholderHtml = `
                    <figure class="my-8">
                        <div class="rounded-2xl overflow-hidden shadow-sm bg-[#EFE7DE] animate-pulse h-72 flex items-center justify-center">
                            <div class="flex items-center gap-2 text-[#8B7355] text-sm">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span>Generating image for this section...</span>
                            </div>
                        </div>
                    </figure>
                `;
                return headerHtml + placeholderHtml;
            }
        }
        
        return headerHtml;
    });
    
    return { content, usedIds: Array.from(usedIds) };
});

// Get the processed content HTML
const processedContent = computed(() => contentWithImages.value.content);

// Get images that weren't embedded in the content (for gallery section)
const galleryImages = computed(() => {
    const images = contentImages.value; // Exclude hero images from gallery
    const usedIds = new Set(contentWithImages.value.usedIds);
    return images.filter(img => !usedIds.has(img.id));
});

// Extract table of contents from headings
const tableOfContents = computed(() => {
    const content = props.article?.content || '';
    const items = [];
    let count = 0;
    
    const regex = /<h([23])[^>]*>([^<]+)<\/h\1>/gi;
    let match;
    
    while ((match = regex.exec(content)) !== null) {
        count++;
        const text = match[2].trim();
        // Filter out generic headers
        if (!text.match(/^(introduction|conclusion|summary|overview)$/i)) {
            items.push({
                level: parseInt(match[1]),
                text: text,
                id: `section-${count}`
            });
        }
    }
    
    return items.slice(0, 8); // Limit to 8 items
});

// Extract a pull quote from the content
const pullQuote = computed(() => {
    const content = props.article?.content || '';
    
    // Look for blockquote content
    const blockquoteMatch = content.match(/<blockquote[^>]*>([^<]+)<\/blockquote>/i);
    if (blockquoteMatch) {
        return blockquoteMatch[1].trim().replace(/["""]/g, '');
    }
    
    return null;
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const copyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    alert('Link copied to clipboard!');
};

// URLs
const currentPageUrl = computed(() => {
    if (typeof window !== 'undefined') {
        return window.location.href;
    }
    return props.article?.url || '';
});

const articleImageUrl = computed(() => {
    const image = props.article?.processed_featured_image || props.article?.featured_image;
    if (image && image.startsWith('http')) {
        return image;
    }
    if (image && typeof window !== 'undefined') {
        return new URL(image, window.location.origin).href;
    }
    return image || '';
});

const facebookShareUrl = computed(() => {
    return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentPageUrl.value)}`;
});

const pinterestShareUrl = computed(() => {
    const url = encodeURIComponent(currentPageUrl.value);
    const media = encodeURIComponent(articleImageUrl.value);
    const description = encodeURIComponent(props.article?.title || '');
    return `https://pinterest.com/pin/create/button/?url=${url}&media=${media}&description=${description}`;
});

const getPinterestUrl = (imageUrl, title) => {
    const url = encodeURIComponent(currentPageUrl.value);
    const media = encodeURIComponent(imageUrl);
    const description = encodeURIComponent(title || props.article?.title || '');
    return `https://pinterest.com/pin/create/button/?url=${url}&media=${media}&description=${description}`;
};

// Generate an intro title phrase for remaining images based on the image title
const getImageIntroTitle = (image) => {
    const title = image.title?.toLowerCase() || '';
    
    if (title.includes('luxury') || title.includes('luxe') || title.includes('opulent')) {
        return 'Luxurious Excellence';
    }
    if (title.includes('bedroom') || title.includes('sleeping')) {
        return 'Bedroom Elegance';
    }
    if (title.includes('living room') || title.includes('lounge')) {
        return 'Living Space Beauty';
    }
    if (title.includes('kitchen') || title.includes('culinary')) {
        return 'Kitchen Sophistication';
    }
    if (title.includes('bathroom') || title.includes('bath') || title.includes('spa')) {
        return 'Spa-Like Retreat';
    }
    if (title.includes('modern') || title.includes('contemporary')) {
        return 'Modern Aesthetic';
    }
    if (title.includes('minimalist') || title.includes('minimal')) {
        return 'Minimalist Beauty';
    }
    if (title.includes('coastal') || title.includes('beach')) {
        return 'Coastal Charm';
    }
    if (title.includes('rustic') || title.includes('farmhouse')) {
        return 'Rustic Appeal';
    }
    
    return 'Design Highlight';
};

// SEO
const seoSettings = computed(() => props.website?.seo_settings || {});
const robotsContent = computed(() => {
    const index = seoSettings.value.enable_indexing !== false ? 'index' : 'noindex';
    const follow = seoSettings.value.enable_follow_links !== false ? 'follow' : 'nofollow';
    return `${index}, ${follow}`;
});

const articleSchemaJson = computed(() => {
    const schema = {
        '@context': 'https://schema.org',
        '@type': 'Article',
        'headline': props.article?.title || '',
        'description': props.article?.excerpt || props.article?.meta_description || '',
        'url': currentPageUrl.value,
        'datePublished': props.article?.published_at || '',
        'author': {
            '@type': 'Person',
            'name': props.article?.author?.name || props.article?.user?.name || 'Admin'
        },
        'publisher': {
            '@type': 'Organization',
            'name': props.website?.name || ''
        }
    };
    
    if (articleImageUrl.value) {
        schema.image = articleImageUrl.value;
    }
    
    return JSON.stringify(schema);
});

const stopImagePolling = () => {
    imagePollingActive.value = false;
    if (imagePollingTimer) {
        clearInterval(imagePollingTimer);
        imagePollingTimer = null;
    }
};

const startImagePollingIfNeeded = () => {
    // Only poll if no images yet; this component is home-decor specific.
    // Skip polling if article has user-uploaded featured images or image sections
    const hasArticleImages = (props.articleImages || []).length > 0;
    const hasUserUploadedImage = props.article?.featured_image || props.article?.processed_featured_image;
    const hasUserUploadedSections = props.article?.variation_metadata?.image_sections?.length > 0;
    
    if (hasArticleImages || hasUserUploadedImage || hasUserUploadedSections || imagePollingTimer) {
        return;
    }

    imagePollingActive.value = true;
    imagePollingAttempt.value = 0;

    imagePollingTimer = setInterval(() => {
        imagePollingAttempt.value += 1;

        router.reload({
            only: ['article', 'articleImages'],
            preserveState: true,
            preserveScroll: true,
        });

        const hasImagesNow = (props.articleImages || []).length > 0;
        if (hasImagesNow || imagePollingAttempt.value >= MAX_IMAGE_POLL_ATTEMPTS) {
            stopImagePolling();
        }
    }, IMAGE_POLL_INTERVAL_MS);
};

onMounted(() => {
    startImagePollingIfNeeded();
});

onBeforeUnmount(() => {
    stopImagePolling();
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700&family=Source+Sans+3:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Lato:wght@300;400;700&family=Montserrat:wght@300;400;600;700&family=Poppins:wght@300;400;600;700&family=Raleway:wght@300;400;600;700&family=Bebas+Neue&family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Lora:ital,wght@0,400;0,600;1,400&family=Dancing+Script:wght@400;600;700&family=Pacifico&family=Great+Vibes&display=swap');

.article-content-homedecor {
    font-family: var(--article-body-font, 'Source Sans 3', sans-serif);
    color: #3D3D3D;
    line-height: 1.8;
}

.article-content-homedecor h2 {
    font-family: var(--article-title-font, 'Playfair Display', serif);
    font-size: 2rem;
    font-weight: 600;
    color: #2D2D2D;
    margin-top: 3rem;
    margin-bottom: 1.5rem;
    scroll-margin-top: 100px;
}

.article-content-homedecor h3 {
    font-family: var(--article-title-font, 'Playfair Display', serif);
    font-size: 1.5rem;
    font-weight: 600;
    color: #2D2D2D;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.article-content-homedecor p {
    margin-bottom: 1.5rem;
    font-size: 1.125rem;
}

/* Drop cap for first paragraph */
.article-content-homedecor > p:first-of-type::first-letter {
    float: left;
    font-family: var(--article-title-font, 'Playfair Display', serif);
    font-size: 4.5rem;
    line-height: 0.8;
    padding-right: 0.75rem;
    padding-top: 0.25rem;
    color: #8B9D83;
    font-weight: 600;
}

.article-content-homedecor img {
    border-radius: 1rem;
    margin: 2rem 0;
}

.article-content-homedecor figure {
    margin: 2rem 0;
}

.article-content-homedecor figure img {
    margin: 0;
    width: 100%;
    height: auto;
}

.article-content-homedecor figure figcaption {
    margin-top: 0.75rem;
    text-align: center;
    font-size: 0.875rem;
    color: #8B8B8B;
    font-style: italic;
}

.article-content-homedecor blockquote {
    border-left: 4px solid #8B9D83;
    padding-left: 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    color: #5D5D5D;
    font-family: var(--article-title-font, 'Playfair Display', serif);
    font-size: 1.25rem;
}

.article-content-homedecor ul,
.article-content-homedecor ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.article-content-homedecor li {
    margin-bottom: 0.75rem;
}

.article-content-homedecor a {
    color: #8B9D83;
    text-decoration: underline;
}

.article-content-homedecor a:hover {
    color: #6B7D63;
}
</style>
