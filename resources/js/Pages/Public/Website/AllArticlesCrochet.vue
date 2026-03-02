<template>
    <CrochetLayout :website="website" :search-query="searchQuery">
        <Head :title="searchQuery ? `Search results for '${searchQuery}' - ${website.name}` : `All Patterns - ${website.name}`">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
        </Head>

        <div class="py-12 md:py-20 bg-[#F0F4EF]">
            <div class="container mx-auto px-4">
                <!-- Page Header -->
                <header class="text-center mb-16">
                    <div class="inline-block px-6 py-2 bg-[#D8E2DC] text-[#588157] rounded-full text-[10px] font-black uppercase tracking-[0.2em] mb-6">
                        {{ searchQuery ? 'Search Results' : 'Pattern Library' }}
                    </div>
                    <h1 class="text-5xl md:text-7xl font-black text-[#3A5A40] mb-6 leading-tight">
                        {{ searchQuery ? 'Search: ' + searchQuery : 'Explore All Patterns' }}
                    </h1>
                    <p class="text-lg md:text-xl text-[#344E41] max-w-2xl mx-auto font-medium">
                        {{ searchQuery ? `Showing ${articles.total} results for your search.` : 'Master the art of crochet with our full collection of expertly crafted free patterns and guides.' }}
                    </p>
                </header>

                <!-- Articles Grid -->
                <div v-if="articles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16 max-w-6xl mx-auto">
                    <div 
                        v-for="article in articles.data" 
                        :key="article.id"
                        class="bg-white rounded-[3rem] overflow-hidden shadow-xl group cursor-pointer hover:scale-[1.02] transition-all duration-500"
                        @click="navigateToArticle(article.url)"
                    >
                        <div class="p-4">
                            <div class="aspect-[4/3] rounded-[2.5rem] overflow-hidden relative">
                                <img 
                                    v-if="article.processed_featured_image || article.featured_image"
                                    :src="article.processed_featured_image || article.featured_image"
                                    :alt="article.title"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                />
                                <div v-else class="w-full h-full bg-[#F0F4EF] flex items-center justify-center">
                                    <span class="text-4xl">🧶</span>
                                </div>
                                <div class="absolute top-6 left-6">
                                    <span class="px-4 py-2 bg-white/90 backdrop-blur-sm text-[#3A5A40] text-[10px] font-black uppercase tracking-widest rounded-full shadow-sm">
                                        {{ article.category?.name || 'Pattern' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="px-10 pb-10 pt-4">
                            <h3 class="text-2xl font-black text-[#3A5A40] mb-4 group-hover:text-[#588157] transition-colors leading-tight">{{ article.title }}</h3>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2 text-[#A3B18A] text-xs font-black uppercase tracking-widest">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>4-6 Hours</span>
                                </div>
                                <button class="bg-[#3A5A40] text-white px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-[#344E41] transition-colors shadow-md">
                                    Get Pattern
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20 bg-white rounded-[3rem] shadow-xl max-w-4xl mx-auto border-4 border-dashed border-[#D8E2DC]">
                    <div class="text-7xl mb-6">🧶</div>
                    <h3 class="text-3xl font-black text-[#3A5A40] mb-4 uppercase tracking-widest">No patterns found</h3>
                    <p class="text-[#588157] font-medium">Try searching for something else or browse our categories!</p>
                </div>

                <!-- Pagination -->
                <div v-if="articles.data.length > 0 && (articles.prev_page_url || articles.next_page_url)" class="flex justify-center gap-4">
                    <a
                        v-if="articles.prev_page_url"
                        :href="articles.prev_page_url"
                        class="px-10 py-4 bg-white text-[#3A5A40] rounded-full font-black uppercase tracking-widest text-[10px] hover:bg-[#F0F4EF] transition shadow-lg flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </a>
                    <a
                        v-if="articles.next_page_url"
                        :href="articles.next_page_url"
                        class="px-10 py-4 bg-[#3A5A40] text-white rounded-full font-black uppercase tracking-widest text-[10px] hover:bg-[#344E41] transition shadow-lg flex items-center gap-2"
                    >
                        Next
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </CrochetLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import CrochetLayout from '@/Layouts/CrochetLayout.vue';

const props = defineProps({
    website: Object,
    articles: Object,
    searchQuery: String
});

const navigateToArticle = (url) => {
    window.location.href = url;
};
</script>
