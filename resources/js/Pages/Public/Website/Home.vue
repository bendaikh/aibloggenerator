<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="website.name">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
            <meta v-if="website.pinterest_verification" name="p:domain_verify" :content="website.pinterest_verification" />
        </Head>

        <!-- Browse by Category Section -->
        <section class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Browse by Category</h2>
                    <p class="text-gray-500">Find the perfect recipe for any meal of the day</p>
                </div>

                <!-- Circular Category Icons - Flexbox for proper centering -->
                <div class="flex flex-wrap justify-center gap-4 sm:gap-6 md:gap-8 max-w-5xl mx-auto mb-8">
                    <a
                        v-for="category in website.categories?.slice(0, 6)"
                        :key="category.id"
                        :href="category.url"
                        class="group text-center flex-shrink-0"
                        style="width: 100px; max-width: calc(33.333% - 1rem);"
                    >
                        <div class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 mx-auto rounded-full overflow-hidden mb-2 sm:mb-3 ring-4 ring-transparent group-hover:ring-emerald-400 transition-all duration-300 shadow-lg">
                            <img
                                v-if="category.image"
                                :src="category.image"
                                :alt="category.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                                <span class="text-white text-2xl sm:text-3xl">{{ category.name.charAt(0) }}</span>
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-emerald-600 transition uppercase tracking-wide leading-tight">
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
                        <h3 class="text-2xl font-bold mb-1">{{ newsletterCtaTitle }}</h3>
                        <h4 class="text-lg mb-4 text-emerald-100">{{ newsletterCtaSubtitle }}</h4>
                        <button @click="openSubscribePopup" class="inline-block bg-white text-emerald-600 px-8 py-3 rounded-full font-bold hover:bg-emerald-50 transition shadow-md cursor-pointer">
                            {{ newsletterCtaButton }} 💌
                        </button>
                    </div>
                    <div v-if="showShopCta" class="bg-gradient-to-br from-pink-400 to-rose-500 rounded-2xl p-8 text-white text-center shadow-lg hover:shadow-xl transition">
                        <h3 class="text-2xl font-bold mb-1">{{ shopCtaTitle }}</h3>
                        <h4 class="text-lg mb-4 text-pink-100">{{ shopCtaSubtitle }}</h4>
                        <a :href="shopCtaLink" class="inline-block bg-white text-pink-600 px-8 py-3 rounded-full font-bold hover:bg-pink-50 transition shadow-md">
                            {{ shopCtaButton }} 🛍️
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
                    <ArticleCard
                        v-for="article in latestArticles"
                        :key="article.id"
                        :article="article"
                        :title-font-family="articleTitleFontFamily"
                    />
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
                                v-for="article in familyFavorites"
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

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
                    <ArticleCard
                        v-for="article in featuredArticles"
                        :key="article.id"
                        :article="article"
                        :show-time="false"
                        :show-date="false"
                        :show-tags="false"
                        :title-font-family="articleTitleFontFamily"
                    />
                </div>
            </div>
        </section>

    </PublicWebsiteLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PublicWebsiteLayout from '@/Layouts/PublicWebsiteLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import { computed } from 'vue';
import { useSubscribePopup } from '@/composables/useSubscribePopup';

const props = defineProps({
    website: Object,
    latestArticles: Array,
    featuredArticles: Array,
    familyFavorites: Array,
    author: Object
});

// Get theme settings with defaults
const themeSettings = computed(() => props.website.theme_settings || {});
const showNewsletterCta = computed(() => themeSettings.value.show_newsletter_cta !== false);
const showShopCta = computed(() => themeSettings.value.show_shop_cta !== false);
const shouldShowCtaSection = computed(() => showNewsletterCta.value || showShopCta.value);

// Newsletter CTA customization
const newsletterCtaTitle = computed(() => themeSettings.value.newsletter_cta_title || 'GET NEW RECIPES');
const newsletterCtaSubtitle = computed(() => themeSettings.value.newsletter_cta_subtitle || 'IN YOUR INBOX');
const newsletterCtaButton = computed(() => themeSettings.value.newsletter_cta_button || 'SUBSCRIBE NOW');

// Shop CTA customization
const shopCtaTitle = computed(() => themeSettings.value.shop_cta_title || 'VISIT OUR SHOP');
const shopCtaSubtitle = computed(() => themeSettings.value.shop_cta_subtitle || 'FIND GREAT GIFT IDEAS!');
const shopCtaButton = computed(() => themeSettings.value.shop_cta_button || 'SHOP NOW');
const shopCtaLink = computed(() => themeSettings.value.shop_cta_link || '#shop');

const articleTitleFontFamily = computed(() => {
    const fontId = props.website?.theme_settings?.article_title_font_family || 'merriweather';
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
    return fontMap[fontId] || fontMap['merriweather'];
});

// Get the openSubscribePopup function from the shared composable
const { openSubscribePopup } = useSubscribePopup();
</script>
