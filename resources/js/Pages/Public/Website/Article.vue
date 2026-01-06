<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="article.title + ' - ' + website.name">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
        </Head>

        <article class="py-12 bg-gradient-to-b from-white via-gray-50/50 to-white min-h-screen">
            <div class="container mx-auto px-4">
                <div class="max-w-7xl mx-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                        <!-- Main Article Content -->
                        <div class="lg:col-span-8">
                            <!-- Article Header -->
                            <header class="mb-8">
                                <nav class="flex mb-4 text-sm text-gray-500" aria-label="Breadcrumb">
                                    <ol class="flex items-center space-x-2">
                                        <li><a :href="website.url" class="hover:text-emerald-600 transition">Home</a></li>
                                        <li><span class="text-gray-300">/</span></li>
                                        <li v-if="article.category">
                                            <a :href="article.category.url" class="hover:text-emerald-600 transition">{{ article.category.name }}</a>
                                        </li>
                                    </ol>
                                </nav>
                                
                                <h1 class="text-4xl md:text-6xl font-serif font-bold text-gray-900 mb-6 leading-tight">
                                    {{ article.title }}
                                </h1>
                                
                                <div class="flex flex-wrap items-center gap-6 text-gray-600 text-sm mb-8 pb-8 border-b border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center overflow-hidden border-2 border-white shadow-sm">
                                            <span class="text-emerald-700 font-bold uppercase">{{ (article.user?.name || 'A').charAt(0) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">By {{ article.user?.name || 'Admin' }}</span>
                                            <span class="text-xs text-gray-400 font-medium uppercase tracking-wider">Author</span>
                                        </div>
                                    </div>
                                    <div class="hidden sm:block h-8 w-px bg-gray-200"></div>
                                    <div class="flex flex-col">
                                        <time :datetime="article.published_at" class="font-bold text-gray-900">
                                            {{ formatDate(article.published_at) }}
                                        </time>
                                        <span class="text-xs text-gray-400 font-medium uppercase tracking-wider">Published</span>
                                    </div>
                                    <div class="hidden sm:block h-8 w-px bg-gray-200"></div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ article.views || 0 }}</span>
                                        <span class="text-xs text-gray-400 font-medium uppercase tracking-wider">Views</span>
                                    </div>
                                    
                                    <!-- Action Buttons Bar -->
                                    <div class="ml-auto flex flex-wrap items-center gap-2">
                                        <button @click="scrollToRecipe" class="flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-full font-bold text-xs uppercase tracking-widest hover:bg-emerald-600 transition shadow-md shadow-emerald-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                            Jump to Recipe
                                        </button>
                                        <button class="flex items-center gap-2 px-4 py-2 bg-[#E60023] text-white rounded-full font-bold text-xs uppercase tracking-widest hover:bg-[#bd081c] transition shadow-md shadow-red-100">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.965 1.406-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                            Pin
                                        </button>
                                        <button class="p-2 bg-white text-gray-400 rounded-full hover:bg-gray-50 hover:text-gray-600 transition shadow-sm border border-gray-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                        </button>
                                        <button class="p-2 bg-white text-gray-400 rounded-full hover:bg-gray-50 hover:text-gray-600 transition shadow-sm border border-gray-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </header>

                            <!-- Featured Image -->
                            <div v-if="article.processed_featured_image || article.featured_image" class="mb-12 relative group">
                                <div class="absolute -inset-1 bg-gradient-to-r from-emerald-100 to-teal-100 rounded-[2rem] blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                                <div class="relative rounded-[2rem] overflow-hidden shadow-2xl">
                                    <img
                                        :src="article.processed_featured_image || article.featured_image"
                                        :alt="article.title"
                                        class="w-full h-auto object-cover transform transition duration-500 group-hover:scale-[1.02]"
                                    />
                                </div>
                            </div>

                            <!-- Article Excerpt -->
                            <div v-if="article.excerpt" class="relative mb-12">
                                <div class="absolute -left-4 top-0 bottom-0 w-1 bg-emerald-500 rounded-full"></div>
                                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 italic text-xl text-gray-700 leading-relaxed">
                                    {{ article.excerpt }}
                                </div>
                            </div>

                            <!-- Article Content -->
                            <div class="prose prose-emerald prose-lg max-w-none article-body" v-html="cleanedContent"></div>

                            <!-- Share Buttons (Bottom) -->
                            <div class="mt-16 bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                                    <div class="text-center md:text-left">
                                        <h4 class="text-2xl font-serif font-bold text-gray-900 mb-1">Loved this recipe?</h4>
                                        <p class="text-gray-500">Spread the deliciousness with your friends!</p>
                                    </div>
                                    <div class="flex gap-4">
                                        <a href="#" class="group flex items-center gap-3 px-6 py-3 bg-[#1877F2] text-white rounded-2xl hover:bg-[#166fe5] transition shadow-lg shadow-blue-200">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                            <span class="font-bold">Share</span>
                                        </a>
                                        <a href="#" class="group flex items-center gap-3 px-6 py-3 bg-[#E60023] text-white rounded-2xl hover:bg-[#bd081c] transition shadow-lg shadow-red-200">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.965 1.406-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                            <span class="font-bold">Pin</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <aside class="lg:col-span-4 space-y-12">
                            <!-- Author Profile Box -->
                            <div class="sticky top-28 space-y-12">
                                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 text-center relative overflow-hidden group">
                                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-10"></div>
                                    <div class="relative">
                                        <div class="w-32 h-32 rounded-full mx-auto mb-6 bg-gradient-to-br from-emerald-400 to-teal-500 p-1.5 shadow-xl ring-4 ring-white">
                                            <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                                                <span class="text-4xl text-emerald-600 font-serif font-bold italic">{{ (article.user?.name || 'A').charAt(0) }}</span>
                                            </div>
                                        </div>
                                        <h3 class="text-2xl font-serif font-bold text-gray-900 mb-2">Hey, I'm {{ article.user?.name || 'Admin' }}!</h3>
                                        <p class="text-gray-600 mb-6 leading-relaxed">
                                            I'm so glad you're here! I love creating simple, delicious recipes that your whole family will love.
                                        </p>
                                        <div class="flex justify-center gap-4 mb-8">
                                            <a href="#" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-500 hover:text-white transition shadow-sm">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                            </a>
                                            <a href="#" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-[#E60023] hover:text-white transition shadow-sm">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                            </a>
                                        </div>
                                        <button class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-bold uppercase tracking-widest hover:bg-emerald-600 transition shadow-lg shadow-emerald-200">
                                            Follow on Social
                                        </button>
                                    </div>
                                </div>

                                <!-- Popular Posts Section -->
                                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                                    <h3 class="text-xl font-serif font-bold text-gray-900 mb-6 flex items-center gap-2">
                                        <span class="text-2xl">✨</span>
                                        You might also enjoy
                                    </h3>
                                    <div class="space-y-6">
                                        <a
                                            v-for="related in relatedArticles.slice(0, 5)"
                                            :key="related.id"
                                            :href="related.url"
                                            class="flex gap-4 group"
                                        >
                                            <div class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden shadow-sm">
                                                <img
                                                    v-if="related.processed_featured_image || related.featured_image"
                                                    :src="related.processed_featured_image || related.featured_image"
                                                    :alt="related.title"
                                                    class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                                                />
                                                <div v-else class="w-full h-full bg-emerald-100 flex items-center justify-center">
                                                    <span class="text-xl">🥗</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-col justify-center">
                                                <h4 class="text-sm font-bold text-gray-900 group-hover:text-emerald-600 transition leading-tight line-clamp-2">
                                                    {{ related.title }}
                                                </h4>
                                                <div class="flex text-yellow-400 text-[10px] mt-1">
                                                    <span v-for="i in 5" :key="i">★</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </article>
    </PublicWebsiteLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicWebsiteLayout from '@/Layouts/PublicWebsiteLayout.vue';

const props = defineProps({
    website: Object,
    article: Object,
    relatedArticles: Array
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Clean the article content by removing markdown code block markers
// Use processed_content if available (which has fixed image URLs), otherwise fall back to content
const cleanedContent = computed(() => {
    let content = props.article?.processed_content || props.article?.content || '';
    
    // Remove markdown code block markers (```html, ```, ```xml, etc.)
    content = content.replace(/^```(?:html|xml|markdown|md)?\s*\n?/gi, '');
    content = content.replace(/\n?```\s*$/gi, '');
    content = content.replace(/```(?:html|xml|markdown|md)?/gi, '');
    
    return content.trim();
});

const scrollToRecipe = () => {
    const headers = document.querySelectorAll('.article-body h2');
    let recipeHeader = null;
    
    for (const h2 of headers) {
        if (h2.textContent.toLowerCase().includes('ingredient') || h2.textContent.toLowerCase().includes('recipe')) {
            recipeHeader = h2;
            break;
        }
    }
    
    if (recipeHeader) {
        recipeHeader.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

.font-serif {
    font-family: 'Playfair Display', serif;
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.article-body {
    color: #374151;
}

.article-body h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 800;
    margin-top: 3rem;
    margin-bottom: 1.5rem;
    color: #111827;
    line-height: 1.2;
}

.article-body h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.875rem;
    font-weight: 700;
    margin-top: 2.5rem;
    margin-bottom: 1rem;
    color: #111827;
}

.article-body p {
    margin-bottom: 1.5rem;
    line-height: 1.8;
}

.article-body ul, .article-body ol {
    margin-bottom: 2rem;
    padding-left: 1.25rem;
}

.article-body li {
    margin-bottom: 0.75rem;
    padding-left: 0.5rem;
}

.article-body blockquote {
    font-style: italic;
    border-left: none;
    padding: 2rem;
    background: #f0fdf4;
    border-radius: 1.5rem;
    margin: 2.5rem 0;
    position: relative;
}

.article-body blockquote::before {
    content: '"';
    position: absolute;
    top: -1rem;
    left: 2rem;
    font-size: 5rem;
    color: #10b981;
    opacity: 0.2;
    font-family: 'Playfair Display', serif;
}

.article-body img {
    border-radius: 2rem;
    margin: 3rem 0;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Custom Card-like sections for Ingredients and Instructions */
.article-body h2 + ul, 
.article-body h2 + ol {
    background: white;
    padding: 2.5rem;
    border-radius: 2rem;
    border: 1px solid #f3f4f6;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.article-body ul li {
    list-style-type: none;
    position: relative;
    padding-left: 2rem;
}

.article-body ul li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
}

.article-body ol {
    counter-reset: recipe-step;
}

.article-body ol li {
    list-style-type: none;
    position: relative;
    padding-left: 3.5rem;
    min-height: 2.5rem;
    display: flex;
    align-items: center;
}

.article-body ol li::before {
    counter-increment: recipe-step;
    content: counter(recipe-step);
    position: absolute;
    left: 0;
    width: 2.5rem;
    height: 2.5rem;
    background: #111827;
    color: white;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.875rem;
}

/* Callout Box styling - using a more standard approach */
.article-body h3 {
    position: relative;
}

/* We can't easily target text content with CSS, so we'll style all H3s to be able to act as headers for boxes if needed, or just style them nicely */
.article-body .callout {
    background: #f9fafb;
    padding: 2rem;
    border-radius: 1.5rem;
    border: 1px solid #f3f4f6;
    margin: 2.5rem 0;
}

.article-body .callout h3 {
    margin-top: 0;
    font-size: 1.25rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
}
</style>
