<template>
    <component :is="layoutComponent" :website="website" :search-query="searchQuery">
        <slot />
    </component>
</template>

<script setup>
import { computed } from 'vue';
import PublicWebsiteLayout from './PublicWebsiteLayout.vue';
import HomeDecorLayout from './HomeDecorLayout.vue';
import CrochetLayout from './CrochetLayout.vue';

const props = defineProps({
    website: {
        type: Object,
        required: true
    },
    searchQuery: {
        type: String,
        default: ''
    }
});

const layoutComponent = computed(() => {
    // Check if website has a theme and if it's the home-decor theme
    if (props.website?.theme?.slug === 'home-decor' || props.website?.theme === 'home-decor') {
        return HomeDecorLayout;
    }
    
    if (props.website?.theme?.slug === 'crochet' || props.website?.theme === 'crochet') {
        return CrochetLayout;
    }
    
    // Default to the recipe theme layout
    return PublicWebsiteLayout;
});
</script>
