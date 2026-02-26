<template>
    <HomeDecorLayout :website="website">
        <Head :title="seoTitle">
            <link v-if="website.favicon_url" rel="icon" :href="website.favicon_url" />
            <meta name="description" :content="seoDescription" />
            <meta v-if="seoKeywords" name="keywords" :content="seoKeywords" />
            <link rel="canonical" :href="canonicalUrl" />
            <meta name="robots" :content="robotsContent" />
            <meta property="og:type" content="website" />
            <meta property="og:title" :content="ogTitle" />
            <meta property="og:description" :content="ogDescription" />
            <meta property="og:url" :content="website.url" />
            <meta v-if="ogImage" property="og:image" :content="ogImage" />
            <meta property="og:site_name" :content="website.name" />
            <meta name="twitter:card" :content="twitterCard" />
            <meta name="twitter:title" :content="twitterTitle" />
            <meta name="twitter:description" :content="twitterDescription" />
            <meta v-if="twitterImage" name="twitter:image" :content="twitterImage" />
            <component :is="'script'" type="application/ld+json" v-html="schemaJson" />
        </Head>

        <!-- Hero Section -->
        <section class="bg-white py-8 md:py-12">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12">
                        <!-- Left: Text Content -->
                        <div class="flex-1 text-center md:text-left">
                            <div class="inline-block bg-[#FFF8F3] text-[#FF6B4A] text-xs sm:text-sm font-semibold px-4 py-2 rounded-full mb-4">
                                ✨ NEW IDEAS EVERY DAY! Join our community →
                            </div>
                            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-serif font-bold mb-6 text-[#2D2D2D]">
                                Living <span class="text-[#FF6B4A]">Warm</span> &<br/>
                                Styling <span class="text-[#FF6B4A]">Wild</span>
                            </h1>
                            <p class="text-base md:text-lg text-[#5D5D5D] mb-8 max-w-lg mx-auto md:mx-0">
                                Explore modern Bohemian decor tactics, and DIY projects. Create a space that feels as good as it looks with our curated guides.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                                <button @click="scrollToArticles" class="bg-[#FF6B4A] text-white px-8 py-4 rounded-lg font-bold hover:bg-[#E55A3A] transition shadow-lg flex items-center justify-center gap-2">
                                    Start Exploring
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                                <a href="#" class="border-2 border-[#E5D5C3] text-[#5D5D5D] px-8 py-4 rounded-lg font-bold hover:border-[#FF6B4A] hover:text-[#FF6B4A] transition flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Watch Tutorial
                                </a>
                            </div>
                        </div>
                        
                        <!-- Right: Featured Image with Decorative Elements -->
                        <div class="flex-1 relative">
                            <div class="relative">
                                <!-- Decorative Diamond Shape Background -->
                                <div class="absolute -top-4 -right-4 w-32 h-32 bg-[#FFE5DC] rounded-full opacity-40 -z-10"></div>
                                <div class="absolute -bottom-4 -left-4 w-24 h-24 rotate-45 border-4 border-[#D4A574] opacity-30 -z-10"></div>
                                
                                <!-- Main Image Container with Arch Design -->
                                <div class="relative">
                                    <!-- Main Image with Rounded Top -->
                                    <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-gradient-to-br from-[#E5D5C3] to-[#D4A574]">
                                        <div class="aspect-[3/4]">
                                            <img 
                                                v-if="heroImage" 
                                                :src="heroImage" 
                                                alt="Beautiful home decor" 
                                                class="w-full h-full object-cover"
                                            />
                                            <!-- Fallback gradient when no image -->
                                            <div v-else class="w-full h-full bg-gradient-to-br from-[#E5D5C3] via-[#D4A574] to-[#C4956D]"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Decorative White Frame in Bottom Right Corner -->
                                    <div class="absolute -bottom-8 -right-8 bg-white rounded-2xl shadow-2xl p-6 w-40 h-40 hidden md:flex items-center justify-center border-4 border-[#F5F1ED]">
                                        <div class="text-center">
                                            <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-[#8B9D83] to-[#6B7D63] flex items-center justify-center mb-2">
                                                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                                </svg>
                                            </div>
                                            <p class="text-xs font-semibold text-[#5D5D5D]">Your Style</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Small Plant Pot Decorative Element (Optional) -->
                                    <div class="absolute top-0 -left-12 hidden lg:block">
                                        <div class="w-20 h-28 bg-[#8B9D83] rounded-t-full opacity-20"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Curated Collections Section -->
        <section class="py-16 bg-[#FDFCFB]">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-serif font-bold text-[#2D2D2D] mb-3">Curated Collections</h2>
                    <p class="text-[#5D5D5D]">Get lost in our tactile quilts category</p>
                </div>

                <!-- Circular Category Icons -->
                <div class="flex flex-wrap justify-center gap-6 md:gap-12 max-w-4xl mx-auto">
                    <a
                        v-for="category in curatedCollections"
                        :key="category.id"
                        :href="category.url"
                        class="group text-center"
                        style="width: 140px;"
                    >
                        <div class="w-28 h-28 md:w-32 md:h-32 mx-auto rounded-full overflow-hidden mb-3 ring-4 ring-transparent group-hover:ring-[#FF6B4A] transition-all duration-300 shadow-xl">
                            <img
                                v-if="category.image"
                                :src="category.image"
                                :alt="category.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-br from-[#D4A574] to-[#C4956D] flex items-center justify-center">
                                <span class="text-white text-3xl">{{ category.name.charAt(0) }}</span>
                            </div>
                        </div>
                        <p class="text-sm font-semibold text-[#2D2D2D] group-hover:text-[#FF6B4A] transition">
                            {{ category.name }}
                        </p>
                    </a>
                </div>
            </div>
        </section>

        <!-- Latest Journal Section -->
        <section id="articles" class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-serif font-bold text-[#2D2D2D] mb-2">Latest Journal</h2>
                        <p class="text-[#5D5D5D]">Sharing fresh ideas for inspired living</p>
                    </div>
                    <a :href="website.url + '/articles'" class="text-[#FF6B4A] hover:text-[#E55A3A] font-semibold flex items-center whitespace-nowrap group">
                        View All Posts →
                    </a>
                </div>

                <!-- Article Grid: Featured Left + Small Grid Right -->
                <div v-if="latestArticles.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Featured Article (Large Left) -->
                    <article
                        v-if="latestArticles[0]"
                        class="group cursor-pointer"
                        @click="navigateToArticle(latestArticles[0].url)"
                    >
                        <div class="aspect-[4/3] rounded-2xl overflow-hidden mb-4 bg-[#E5D5C3] shadow-lg relative">
                            <img
                                v-if="latestArticles[0].processed_featured_image || latestArticles[0].featured_image"
                                :src="latestArticles[0].processed_featured_image || latestArticles[0].featured_image"
                                :alt="latestArticles[0].title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-br from-[#D4A574] to-[#C4956D]"></div>
                            
                            <!-- Category Badge Overlay -->
                            <div v-if="latestArticles[0].category" class="absolute top-4 left-4 bg-[#FF6B4A] text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider">
                                {{ latestArticles[0].category.name }}
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-xs text-[#9D9D9D]">
                                <span v-if="latestArticles[0].published_at">{{ formatDate(latestArticles[0].published_at) }}</span>
                            </div>
                            
                            <h3 class="text-2xl md:text-3xl font-serif font-bold text-[#2D2D2D] group-hover:text-[#FF6B4A] transition line-clamp-2">
                                {{ latestArticles[0].title }}
                            </h3>
                            
                            <p v-if="latestArticles[0].excerpt" class="text-[#5D5D5D] text-base line-clamp-3">
                                {{ latestArticles[0].excerpt }}
                            </p>
                            
                            <div v-if="latestArticles[0].author" class="flex items-center gap-2 pt-2">
                                <div class="w-10 h-10 rounded-full overflow-hidden bg-[#D4A574]">
                                    <img
                                        v-if="latestArticles[0].author.image"
                                        :src="latestArticles[0].author.image"
                                        :alt="latestArticles[0].author.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-white text-sm font-bold">
                                        {{ latestArticles[0].author.name.charAt(0) }}
                                    </div>
                                </div>
                                <span class="text-sm text-[#5D5D5D] font-medium">{{ latestArticles[0].author.name }}</span>
                            </div>
                        </div>
                    </article>

                    <!-- Small Articles Grid (Right) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <article
                            v-for="article in latestArticles.slice(1, 5)"
                            :key="article.id"
                            class="group cursor-pointer"
                            @click="navigateToArticle(article.url)"
                        >
                            <div class="aspect-[4/3] rounded-xl overflow-hidden mb-3 bg-[#E5D5C3] shadow-md relative">
                                <img
                                    v-if="article.processed_featured_image || article.featured_image"
                                    :src="article.processed_featured_image || article.featured_image"
                                    :alt="article.title"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                />
                                <div v-else class="w-full h-full bg-gradient-to-br from-[#8B9D83] to-[#6B7D63]"></div>
                                
                                <!-- Small Category Badge -->
                                <div v-if="article.category" class="absolute top-2 left-2 bg-[#FF6B4A] text-white px-3 py-1 rounded text-xs font-bold uppercase">
                                    {{ article.category.name }}
                                </div>
                            </div>
                            
                            <div class="space-y-1">
                                <div class="text-xs text-[#9D9D9D]">
                                    <span v-if="article.published_at">{{ formatDate(article.published_at) }}</span>
                                </div>
                                
                                <h3 class="text-base font-serif font-bold text-[#2D2D2D] group-hover:text-[#FF6B4A] transition line-clamp-2">
                                    {{ article.title }}
                                </h3>
                                
                                <p v-if="article.excerpt" class="text-[#5D5D5D] text-xs line-clamp-2">
                                    {{ article.excerpt }}
                                </p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Quote Section -->
        <section class="py-16 bg-[#2D2D2D] text-white">
            <div class="container mx-auto px-4">
                <div class="max-w-3xl mx-auto text-center">
                    <svg class="w-12 h-12 mx-auto mb-6 text-[#FF6B4A]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/>
                    </svg>
                    <blockquote class="text-2xl md:text-3xl font-serif italic mb-4">
                        "Home is not a place, it's a feeling"
                    </blockquote>
                    <p class="text-[#B8B8B8]">— Cecilia Ahern</p>
                </div>
            </div>
        </section>

        <!-- Popular Articles Section -->
        <section v-if="featuredArticles.length > 0" class="py-16 bg-[#FDFCFB]">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-serif font-bold text-[#2D2D2D] mb-3">Reader Favorites</h2>
                    <p class="text-[#5D5D5D]">Most loved ideas from our community</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
                    <a
                        v-for="article in featuredArticles"
                        :key="article.id"
                        :href="article.url"
                        class="group"
                    >
                        <div class="aspect-square rounded-xl overflow-hidden mb-3 bg-[#E5D5C3] shadow-md">
                            <img
                                v-if="article.processed_featured_image || article.featured_image"
                                :src="article.processed_featured_image || article.featured_image"
                                :alt="article.title"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-br from-[#8B9D83] to-[#6B7D63]"></div>
                        </div>
                        <p class="text-sm font-semibold text-[#2D2D2D] group-hover:text-[#FF6B4A] transition line-clamp-2">
                            {{ article.title }}
                        </p>
                    </a>
                </div>
            </div>
        </section>
    </HomeDecorLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HomeDecorLayout from '@/Layouts/HomeDecorLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    website: Object,
    latestArticles: Array,
    featuredArticles: Array,
    author: Object
});

const themeSettings = computed(() => props.website.theme_settings || {});
const heroImage = computed(() => themeSettings.value.hero_image || '');

const curatedCollections = computed(() => {
    return props.website.categories?.slice(0, 4) || [];
});

const seoSettings = computed(() => props.website.seo_settings || {});
const seoTitle = computed(() => seoSettings.value.meta_title || props.website.name);
const seoDescription = computed(() => seoSettings.value.meta_description || props.website.description || '');
const seoKeywords = computed(() => seoSettings.value.meta_keywords || '');
const canonicalUrl = computed(() => seoSettings.value.canonical_url || props.website.url);

const robotsContent = computed(() => {
    const index = seoSettings.value.enable_indexing !== false ? 'index' : 'noindex';
    const follow = seoSettings.value.enable_follow_links !== false ? 'follow' : 'nofollow';
    return `${index}, ${follow}`;
});

const ogTitle = computed(() => seoSettings.value.og_title || props.website.name);
const ogDescription = computed(() => seoSettings.value.og_description || seoDescription.value);
const ogImage = computed(() => seoSettings.value.og_image || props.website.logo_url || '');

const twitterCard = computed(() => seoSettings.value.twitter_card || 'summary_large_image');
const twitterTitle = computed(() => seoSettings.value.twitter_title || ogTitle.value);
const twitterDescription = computed(() => seoSettings.value.twitter_description || ogDescription.value);
const twitterImage = computed(() => seoSettings.value.twitter_image || ogImage.value);

const schemaJson = computed(() => {
    const schema = {
        '@context': 'https://schema.org',
        '@type': seoSettings.value.schema_type || 'Organization',
        'name': seoSettings.value.schema_name || props.website.name,
        'description': seoSettings.value.schema_description || seoDescription.value,
        'url': props.website.url,
    };
    
    const logo = seoSettings.value.schema_logo || props.website.logo_url;
    if (logo) {
        schema.logo = logo;
    }
    
    return JSON.stringify(schema);
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const scrollToArticles = () => {
    const articlesSection = document.getElementById('articles');
    if (articlesSection) {
        articlesSection.scrollIntoView({ behavior: 'smooth' });
    }
};

const navigateToArticle = (url) => {
    window.location.href = url;
};
</script>
