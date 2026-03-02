<template>
    <CrochetLayout :website="website">
        <Head :title="page.meta_title || page.title + ' - ' + website.name">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
        </Head>

        <div class="py-12 md:py-20 bg-[#F0F4EF]">
            <div class="container mx-auto px-4">
                <!-- Page Header -->
                <header class="text-center mb-16 max-w-4xl mx-auto">
                    <h1 class="text-5xl md:text-7xl font-black text-[#3A5A40] mb-8 leading-tight">
                        {{ page.title }}
                    </h1>
                    
                    <div v-if="page.featured_image" class="mb-12 rounded-[3rem] overflow-hidden shadow-2xl border-[12px] border-white relative z-10">
                        <img 
                            :src="page.featured_image" 
                            :alt="page.title"
                            class="w-full h-96 object-cover"
                        />
                    </div>

                    <p v-if="page.excerpt" class="text-xl text-[#344E41] font-medium leading-relaxed bg-white/60 backdrop-blur-sm p-8 rounded-[2.5rem] border-2 border-white shadow-xl">
                        {{ page.excerpt }}
                    </p>
                </header>

                <!-- Page Content -->
                <article class="max-w-4xl mx-auto bg-white p-10 md:p-16 rounded-[3rem] shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#FFD7BA] via-[#A3B18A] to-[#FCE1E4]"></div>
                    <div 
                        v-if="page.content"
                        class="prose prose-lg max-w-none article-content-crochet"
                        v-html="page.content"
                    ></div>
                    
                    <div v-else class="text-center py-12">
                        <p class="text-[#588157] text-lg font-medium">This page has no content yet.</p>
                    </div>
                </article>
            </div>
        </div>
    </CrochetLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import CrochetLayout from '@/Layouts/CrochetLayout.vue';

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
