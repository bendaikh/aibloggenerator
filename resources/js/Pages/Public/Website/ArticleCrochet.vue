<template>
    <CrochetLayout :website="website">
        <Head :title="article.meta_title || (article.title + ' - ' + website.name)">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
            <meta name="description" :content="article.meta_description || article.excerpt || ''" />
            <meta property="og:type" content="article" />
            <meta property="og:title" :content="article.title" />
            <meta property="og:description" :content="article.excerpt || article.meta_description || ''" />
            <meta v-if="articleImageUrl" property="og:image" :content="articleImageUrl" />
            <meta property="og:url" :content="currentPageUrl" />
            <meta property="og:site_name" :content="website.name" />
        </Head>

        <!-- Article Container -->
        <article class="bg-[#F0F4EF] min-h-screen">
            <!-- Hero Section -->
            <header class="relative py-12 md:py-20">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto text-center">
                        <!-- Category Badge -->
                        <div v-if="article.category" class="mb-6">
                            <a :href="article.category.url" class="inline-block bg-[#D8E2DC] text-[#588157] text-[10px] font-black uppercase tracking-[0.2em] px-6 py-2 rounded-full hover:bg-[#A3B18A]/30 transition-colors">
                                {{ article.category.name }}
                            </a>
                        </div>
                        
                        <!-- Title -->
                        <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-[#3A5A40] mb-8 leading-tight">
                            {{ article.title }}
                        </h1>
                        
                        <!-- Subtitle/Excerpt -->
                        <div v-if="article.excerpt" class="bg-white/60 backdrop-blur-sm p-8 rounded-[2.5rem] border-2 border-white shadow-xl mb-10 max-w-2xl mx-auto">
                            <p class="text-lg md:text-xl text-[#344E41] font-medium leading-relaxed">
                                {{ article.excerpt }}
                            </p>
                        </div>
                        
                        <!-- Author & Date Row -->
                        <div class="flex items-center justify-center gap-4 text-sm font-black text-[#A3B18A] uppercase tracking-widest">
                            <span class="text-[#3A5A40]">{{ article.author?.name || article.user?.name || 'Admin' }}</span>
                            <span class="w-1.5 h-1.5 bg-[#A3B18A] rounded-full"></span>
                            <time :datetime="article.published_at">
                                {{ formatDate(article.published_at) }}
                            </time>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            <div v-if="article.processed_featured_image || article.featured_image" class="max-w-6xl mx-auto px-4 -mb-16 relative z-10">
                <div class="aspect-[21/9] rounded-[3rem] md:rounded-[5rem] overflow-hidden shadow-2xl border-[12px] border-white">
                    <img
                        :src="article.processed_featured_image || article.featured_image"
                        :alt="article.title"
                        class="w-full h-full object-cover"
                    />
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="bg-white pt-32 pb-20 rounded-t-[3rem] md:rounded-t-[5rem]">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto">
                        <!-- Article Content -->
                        <div class="prose prose-lg max-w-none article-content-crochet" v-html="processedContent"></div>

                        <!-- Gallery Section -->
                        <div v-if="galleryImages.length > 0" class="mt-20 space-y-12">
                            <div class="text-center">
                                <h3 class="text-3xl font-black text-[#3A5A40] mb-4">Step-by-Step Visuals</h3>
                                <div class="w-20 h-1.5 bg-[#A44A3F] mx-auto rounded-full"></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div
                                    v-for="(image, index) in galleryImages"
                                    :key="image.id || index"
                                    class="space-y-4 group"
                                >
                                    <div class="relative rounded-[2.5rem] overflow-hidden shadow-xl border-4 border-[#F0F4EF] group-hover:border-[#A3B18A] transition-colors duration-500">
                                        <img
                                            :src="image.url || image.local_path"
                                            :alt="image.title"
                                            class="w-full h-auto group-hover:scale-105 transition-transform duration-700"
                                            loading="lazy"
                                        />
                                        <!-- Pinterest Pin Overlay -->
                                        <div class="absolute top-6 right-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <a :href="getPinterestUrl(image.url || image.local_path, image.title)" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 px-4 py-2 bg-[#E60023] text-white rounded-full font-black text-[10px] uppercase tracking-widest hover:bg-[#bd081c] transition shadow-lg">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                                Pin
                                            </a>
                                        </div>
                                    </div>
                                    <p v-if="image.title" class="text-center text-sm font-black text-[#A3B18A] uppercase tracking-widest italic">
                                        {{ image.title }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="article.meta_tags && article.meta_tags.length > 0" class="mt-20 pt-12 border-t border-[#F0F4EF]">
                            <div class="flex flex-wrap justify-center gap-3">
                                <span
                                    v-for="(tag, index) in article.meta_tags"
                                    :key="index"
                                    class="px-6 py-2 bg-[#F0F4EF] text-[#3A5A40] text-xs font-black uppercase tracking-widest rounded-full hover:bg-[#A3B18A]/20 transition-colors cursor-pointer"
                                >
                                    #{{ tag }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Author Bio Section -->
            <section class="bg-[#F0F4EF] py-20">
                <div class="container mx-auto px-4">
                    <div class="max-w-3xl mx-auto bg-white p-10 md:p-16 rounded-[3rem] shadow-xl text-center relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#FFD7BA] via-[#A3B18A] to-[#FCE1E4]"></div>
                        
                        <div class="w-32 h-32 rounded-full overflow-hidden mx-auto mb-8 border-8 border-[#F0F4EF] shadow-lg">
                            <img
                                v-if="article.author?.image"
                                :src="article.author.image"
                                :alt="article.author?.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full bg-[#3A5A40] flex items-center justify-center">
                                <span class="text-4xl text-white font-black">{{ (article.author?.name || article.user?.name || 'A').charAt(0) }}</span>
                            </div>
                        </div>
                        
                        <h3 class="text-3xl font-black text-[#3A5A40] mb-4">
                            About {{ article.author?.name || article.user?.name || 'the Author' }}
                        </h3>
                        <p class="text-[#588157] mb-8 leading-relaxed text-lg">
                            {{ article.author?.description || "A passionate crochet artisan dedicated to sharing the joy of creative stitching with the world. Every pattern is a labor of love." }}
                        </p>
                        
                        <div class="flex justify-center gap-4">
                            <a href="#" class="w-12 h-12 rounded-2xl bg-[#F0F4EF] flex items-center justify-center text-[#3A5A40] hover:bg-[#3A5A40] hover:text-white transition-all duration-300">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a href="#" class="w-12 h-12 rounded-2xl bg-[#F0F4EF] flex items-center justify-center text-[#3A5A40] hover:bg-[#3A5A40] hover:text-white transition-all duration-300">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Newsletter CTA Section -->
            <section class="bg-[#3A5A40] py-20 rounded-t-[3rem] md:rounded-t-[5rem]">
                <div class="container mx-auto px-4">
                    <div class="max-w-2xl mx-auto text-center">
                        <h2 class="text-4xl font-black text-white mb-6">
                            Loved this pattern?
                        </h2>
                        <p class="text-[#DAD7CD] mb-10 text-lg">
                            Get our latest free patterns and stitch guides delivered to your inbox every Tuesday.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                            <input
                                v-model="newsletterEmail"
                                type="email"
                                placeholder="Your email address"
                                class="flex-1 bg-white/10 border border-white/20 rounded-full px-8 py-4 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/50"
                            />
                            <button
                                @click="subscribeNewsletter"
                                :disabled="isSubscribing"
                                class="bg-[#A44A3F] text-white px-10 py-4 rounded-full font-black uppercase tracking-widest text-[10px] hover:bg-[#8B3D35] transition-all shadow-lg disabled:opacity-50"
                            >
                                {{ isSubscribing ? 'Joining...' : 'Join Now' }}
                            </button>
                        </div>
                        <p v-if="subscribeMessage" :class="subscribeSuccess ? 'text-[#A3B18A]' : 'text-red-300'" class="mt-6 font-bold uppercase tracking-widest text-xs">
                            {{ subscribeMessage }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Related Articles -->
            <section v-if="relatedArticles && relatedArticles.length > 0" class="py-20 bg-white">
                <div class="container mx-auto px-4">
                    <div class="max-w-6xl mx-auto">
                        <h2 class="text-4xl font-black text-[#3A5A40] mb-12 text-center">
                            More <span class="text-[#A44A3F] font-serif italic font-normal">masterpieces!</span>
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <a
                                v-for="related in relatedArticles.slice(0, 3)"
                                :key="related.id"
                                :href="related.url"
                                class="group"
                            >
                                <div class="aspect-[4/3] rounded-[2.5rem] overflow-hidden mb-6 bg-[#F0F4EF] shadow-lg">
                                    <img
                                        v-if="related.processed_featured_image || related.featured_image"
                                        :src="related.processed_featured_image || related.featured_image"
                                        :alt="related.title"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <span class="text-4xl">🧶</span>
                                    </div>
                                </div>
                                <h3 class="text-xl font-black text-[#3A5A40] group-hover:text-[#588157] transition-colors line-clamp-2">
                                    {{ related.title }}
                                </h3>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </article>
    </CrochetLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CrochetLayout from '@/Layouts/CrochetLayout.vue';
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
        subscribeMessage.value = response.data.message || 'Thank you for joining!';
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

// Get only non-hero images for gallery
const galleryImages = computed(() => {
    const images = props.articleImages || [];
    return images
        .filter(img => !img.metadata?.is_hero && img.generation_type !== 'ai_hero')
        .sort((a, b) => (a.position ?? 0) - (b.position ?? 0));
});

const processedContent = computed(() => props.article?.processed_content || props.article?.content || '');

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
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

const getPinterestUrl = (imageUrl, title) => {
    const url = encodeURIComponent(currentPageUrl.value);
    const media = encodeURIComponent(imageUrl);
    const description = encodeURIComponent(title || props.article?.title || '');
    return `https://pinterest.com/pin/create/button/?url=${url}&media=${media}&description=${description}`;
};

// SEO
const seoSettings = computed(() => props.website?.seo_settings || {});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,900;1,900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.article-content-crochet {
    color: #344E41;
    line-height: 1.8;
}

.article-content-crochet h2 {
    font-size: 2.5rem;
    font-weight: 900;
    color: #3A5A40;
    margin-top: 4rem;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.article-content-crochet h3 {
    font-size: 1.875rem;
    font-weight: 900;
    color: #3A5A40;
    margin-top: 3rem;
    margin-bottom: 1rem;
}

.article-content-crochet p {
    margin-bottom: 1.5rem;
    font-size: 1.125rem;
}

.article-content-crochet ul,
.article-content-crochet ol {
    margin-bottom: 2rem;
    padding-left: 1.5rem;
}

.article-content-crochet li {
    margin-bottom: 0.75rem;
}

.article-content-crochet blockquote {
    border-left: 8px solid #A44A3F;
    padding: 2rem;
    background: #F0F4EF;
    border-radius: 2.5rem;
    margin: 3rem 0;
    font-style: italic;
    font-size: 1.25rem;
    color: #3A5A40;
}

.article-content-crochet img {
    border-radius: 2.5rem;
    margin: 3rem 0;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.article-content-crochet a {
    color: #A44A3F;
    text-decoration: underline;
    font-weight: 700;
}

.article-content-crochet a:hover {
    color: #8B3D35;
}
</style>
