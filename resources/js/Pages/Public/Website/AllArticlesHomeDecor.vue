<template>
    <HomeDecorLayout :website="website" :search-query="searchQuery">
        <Head :title="searchQuery ? `Search results for '${searchQuery}' - ${website.name}` : `Latest Journal - ${website.name}`">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
        </Head>

        <div class="py-12 md:py-20 bg-[#FDFCFB]">
            <div class="container mx-auto px-4">
                <!-- Page Header -->
                <header class="text-center mb-16 max-w-4xl mx-auto">
                    <div class="inline-block px-4 py-1 border border-[#D4A574] text-[#8B7355] rounded-full text-xs font-bold uppercase tracking-widest mb-6">
                        {{ searchQuery ? 'Search Results' : 'Our Journal' }}
                    </div>
                    <h1 class="text-4xl md:text-6xl font-serif font-bold text-[#2D2D2D] mb-6 leading-tight">
                        {{ searchQuery ? 'Search: ' + searchQuery : 'Latest Journal Entries' }}
                    </h1>
                    <p class="text-lg md:text-xl text-[#6B6B6B] italic font-serif">
                        {{ searchQuery ? `Showing ${articles.total} results for your search.` : 'Explore our latest ideas for inspired living and beautiful spaces.' }}
                    </p>
                    <div class="mt-8 w-24 h-1 bg-[#FF6B4A] mx-auto rounded-full"></div>
                </header>

                <!-- Articles Grid -->
                <div v-if="articles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 max-w-6xl mx-auto">
                    <article 
                        v-for="article in articles.data" 
                        :key="article.id"
                        class="group cursor-pointer"
                        @click="navigateToArticle(article.url)"
                    >
                        <div class="aspect-[4/5] rounded-2xl overflow-hidden mb-6 bg-[#F5F1ED] shadow-lg relative">
                            <img 
                                v-if="article.processed_featured_image || article.featured_image"
                                :src="article.processed_featured_image || article.featured_image"
                                :alt="article.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-br from-[#E5D5C3] to-[#D4A574]"></div>
                            
                            <!-- Category Badge -->
                            <div v-if="article.category" class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm text-[#2D2D2D] px-3 py-1 rounded text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                {{ article.category.name }}
                            </div>
                        </div>
                        
                        <div class="space-y-3 text-center">
                            <h3 class="text-xl font-serif font-bold text-[#2D2D2D] group-hover:text-[#FF6B4A] transition-colors leading-tight line-clamp-2">
                                {{ article.title }}
                            </h3>
                            <p v-if="article.excerpt" class="text-[#6B6B6B] text-sm line-clamp-2 italic">
                                {{ article.excerpt }}
                            </p>
                        </div>
                    </article>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20 bg-white rounded-3xl shadow-sm border border-[#E5D5C3] max-w-4xl mx-auto">
                    <div class="text-6xl mb-6">🔍</div>
                    <h3 class="text-2xl font-serif font-bold text-[#2D2D2D] mb-4">No journal entries found</h3>
                    <p class="text-[#6B6B6B]">We couldn't find any entries matching your search. Try different keywords or browse our collections!</p>
                </div>

                <!-- Pagination -->
                <div v-if="articles.data.length > 0 && (articles.prev_page_url || articles.next_page_url)" class="mt-16 flex justify-center gap-4">
                    <a
                        v-if="articles.prev_page_url"
                        :href="articles.prev_page_url"
                        class="px-8 py-3 border-2 border-[#E5D5C3] text-[#5D5D5D] rounded-lg font-bold hover:border-[#FF6B4A] hover:text-[#FF6B4A] transition flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </a>
                    <a
                        v-if="articles.next_page_url"
                        :href="articles.next_page_url"
                        class="px-8 py-3 bg-[#FF6B4A] text-white rounded-lg font-bold hover:bg-[#E55A3A] transition shadow-lg flex items-center gap-2"
                    >
                        Next
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </HomeDecorLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HomeDecorLayout from '@/Layouts/HomeDecorLayout.vue';

const props = defineProps({
    website: Object,
    articles: Object,
    searchQuery: String
});

const navigateToArticle = (url) => {
    window.location.href = url;
};
</script>
