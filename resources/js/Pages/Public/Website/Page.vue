<template>
    <PublicWebsiteLayout :website="website">
        <Head :title="page.meta_title || page.title + ' - ' + website.name" />

        <div class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <!-- Page Header -->
                <header class="text-center mb-12 max-w-4xl mx-auto">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">{{ page.title }}</h1>
                    
                    <div v-if="page.featured_image" class="mb-8 rounded-2xl overflow-hidden shadow-xl">
                        <img 
                            :src="page.featured_image" 
                            :alt="page.title"
                            class="w-full h-96 object-cover"
                        />
                    </div>

                    <p v-if="page.excerpt" class="text-xl text-gray-600 leading-relaxed">
                        {{ page.excerpt }}
                    </p>
                </header>

                <!-- Page Content -->
                <article class="max-w-4xl mx-auto">
                    <div 
                        v-if="page.content"
                        class="prose prose-lg max-w-none prose-headings:text-gray-900 prose-p:text-gray-700 prose-a:text-emerald-600 prose-strong:text-gray-900"
                        v-html="page.content"
                    ></div>
                    
                    <div v-else class="text-center py-12">
                        <p class="text-gray-500 text-lg">This page has no content yet.</p>
                    </div>
                </article>

                <!-- Meta Information -->
                <div v-if="page.meta_description" class="hidden">
                    {{ page.meta_description }}
                </div>
            </div>
        </div>
    </PublicWebsiteLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PublicWebsiteLayout from '@/Layouts/PublicWebsiteLayout.vue';

defineProps({
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

<style scoped>
/* Custom prose styles for better content rendering */
:deep(.prose) {
    color: #374151;
}

:deep(.prose h1),
:deep(.prose h2),
:deep(.prose h3),
:deep(.prose h4),
:deep(.prose h5),
:deep(.prose h6) {
    margin-top: 2em;
    margin-bottom: 1em;
    font-weight: 700;
}

:deep(.prose p) {
    margin-bottom: 1.25em;
    line-height: 1.75;
}

:deep(.prose ul),
:deep(.prose ol) {
    margin-bottom: 1.25em;
    padding-left: 1.5em;
}

:deep(.prose li) {
    margin-bottom: 0.5em;
}

:deep(.prose img) {
    margin-top: 2em;
    margin-bottom: 2em;
    border-radius: 0.5rem;
}

:deep(.prose a) {
    color: #10b981;
    text-decoration: underline;
}

:deep(.prose a:hover) {
    color: #059669;
}

:deep(.prose blockquote) {
    border-left: 4px solid #10b981;
    padding-left: 1em;
    font-style: italic;
    color: #6b7280;
}

:deep(.prose code) {
    background-color: #f3f4f6;
    padding: 0.2em 0.4em;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

:deep(.prose pre) {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1em;
    border-radius: 0.5rem;
    overflow-x: auto;
}

:deep(.prose pre code) {
    background-color: transparent;
    padding: 0;
    color: inherit;
}
</style>

