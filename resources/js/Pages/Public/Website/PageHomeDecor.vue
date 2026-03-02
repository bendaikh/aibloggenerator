<template>
    <HomeDecorLayout :website="website">
        <Head :title="page.meta_title || page.title + ' - ' + website.name">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
        </Head>

        <div class="py-12 md:py-20 bg-[#FDFCFB]">
            <div class="container mx-auto px-4">
                <!-- Page Header -->
                <header class="text-center mb-16 max-w-4xl mx-auto">
                    <h1 class="text-4xl md:text-6xl font-serif font-bold text-[#2D2D2D] mb-8 leading-tight">
                        {{ page.title }}
                    </h1>
                    
                    <div v-if="page.featured_image" class="mb-12 rounded-3xl overflow-hidden shadow-2xl border-8 border-white">
                        <img 
                            :src="page.featured_image" 
                            :alt="page.title"
                            class="w-full h-96 object-cover"
                        />
                    </div>

                    <p v-if="page.excerpt" class="text-xl text-[#6B6B6B] italic font-serif leading-relaxed">
                        {{ page.excerpt }}
                    </p>
                    <div class="mt-8 w-24 h-1 bg-[#FF6B4A] mx-auto rounded-full"></div>
                </header>

                <!-- Page Content -->
                <article class="max-w-4xl mx-auto bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-[#E5D5C3]">
                    <div 
                        v-if="page.content"
                        class="prose prose-lg max-w-none article-content-homedecor"
                        v-html="page.content"
                    ></div>
                    
                    <div v-else class="text-center py-12">
                        <p class="text-[#6B6B6B] text-lg italic">This page has no content yet.</p>
                    </div>
                </article>
            </div>
        </div>
    </HomeDecorLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HomeDecorLayout from '@/Layouts/HomeDecorLayout.vue';

const props = defineProps({
    website: {
        type: Object,
        required: true
    },
    page: {
        type: Object,
        required: true
    }
});
</script>

<style>
.article-content-homedecor {
    font-family: 'Source Sans 3', sans-serif;
    color: #3D3D3D;
    line-height: 1.8;
}

.article-content-homedecor h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 600;
    color: #2D2D2D;
    margin-top: 3rem;
    margin-bottom: 1.5rem;
}

.article-content-homedecor h3 {
    font-family: 'Playfair Display', serif;
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

.article-content-homedecor img {
    border-radius: 1rem;
    margin: 2rem 0;
}

.article-content-homedecor blockquote {
    border-left: 4px solid #8B9D83;
    padding-left: 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    color: #5D5D5D;
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
}

.article-content-homedecor a {
    color: #8B9D83;
    text-decoration: underline;
}

.article-content-homedecor a:hover {
    color: #6B7D63;
}
</style>
