<template>
    <Head title="Select Theme - Global Articles" />

    <OrganizationLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Select Theme for Article Generation</h1>
                <p class="text-gray-400">Choose which theme you want to work with. Only websites from the selected theme will be available.</p>
            </div>

            <!-- Theme Selection Grid -->
            <div v-if="themes.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl">
                <Link 
                    v-for="theme in themes"
                    :key="theme.id"
                    :href="route('organization.global-articles.index', { theme: theme.slug })"
                    :class="[
                        'group relative bg-[#1a1a1a] rounded-2xl border-2 border-[#2a2a2a] transition-all overflow-hidden cursor-pointer',
                        getThemeHoverClass(theme.slug)
                    ]"
                >
                    <div :class="['absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity', getThemeGradientClass(theme.slug)]"></div>
                    <div class="relative p-8">
                        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center mb-6', getThemeBadgeClass(theme.slug)]">
                            <svg v-html="getThemeIcon(theme.slug)" class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                        </div>
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="text-xl font-bold text-white">{{ theme.name }}</h3>
                            <span v-if="isSuperAdmin && !theme.is_public" class="px-2 py-0.5 bg-purple-500/20 text-purple-400 rounded text-xs font-medium">
                                Private
                            </span>
                        </div>
                        <p class="text-gray-400 text-sm mb-4">{{ theme.description }}</p>
                        <div :class="['flex items-center text-sm font-medium', getThemeTextClass(theme.slug)]">
                            <span>Select Theme</span>
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- No Themes Available Message -->
            <div v-else class="max-w-5xl">
                <div class="bg-yellow-900/20 border border-yellow-500/50 rounded-xl p-8 text-center">
                    <svg class="w-12 h-12 text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">No Themes Available</h3>
                    <p class="text-gray-400">There are no public themes available at this time. Please contact your administrator.</p>
                </div>
            </div>

            <!-- Info Section -->
            <div v-if="themes.length > 0" class="mt-8 max-w-5xl">
                <div class="bg-blue-900/20 border border-blue-500/50 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-white font-semibold mb-2">How Theme Selection Works</h4>
                            <ul class="text-blue-200 text-sm space-y-2">
                                <li>• When you select a theme, only websites using that theme will be shown in the website selector</li>
                                <li>• Each theme has a customized article generation experience tailored to its content type</li>
                                <li>• Home Decor theme includes special multi-image support with individual titles for each image</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OrganizationLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import OrganizationLayout from '@/Layouts/OrganizationLayout.vue';

defineProps({
    themes: {
        type: Array,
        required: true,
    },
    isSuperAdmin: {
        type: Boolean,
        default: false,
    },
});

// Theme color schemes mapping
const themeColors = {
    'recipe': {
        hover: 'hover:border-emerald-500',
        gradient: 'bg-gradient-to-br from-emerald-500/10 to-teal-500/10',
        badge: 'bg-gradient-to-br from-emerald-500 to-teal-500',
        text: 'text-emerald-400',
    },
    'home-decor': {
        hover: 'hover:border-amber-500',
        gradient: 'bg-gradient-to-br from-amber-500/10 to-orange-500/10',
        badge: 'bg-gradient-to-br from-amber-500 to-orange-500',
        text: 'text-amber-400',
    },
    'crochet': {
        hover: 'hover:border-green-500',
        gradient: 'bg-gradient-to-br from-green-500/10 to-emerald-500/10',
        badge: 'bg-gradient-to-br from-green-500 to-emerald-500',
        text: 'text-green-400',
    },
    'default': {
        hover: 'hover:border-blue-500',
        gradient: 'bg-gradient-to-br from-blue-500/10 to-cyan-500/10',
        badge: 'bg-gradient-to-br from-blue-500 to-cyan-500',
        text: 'text-blue-400',
    },
};

// Theme icons mapping
const themeIcons = {
    'recipe': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />',
    'home-decor': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
    'crochet': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />',
    'default': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />',
};

const getThemeHoverClass = (slug) => {
    return themeColors[slug]?.hover || themeColors.default.hover;
};

const getThemeGradientClass = (slug) => {
    return themeColors[slug]?.gradient || themeColors.default.gradient;
};

const getThemeBadgeClass = (slug) => {
    return themeColors[slug]?.badge || themeColors.default.badge;
};

const getThemeTextClass = (slug) => {
    return themeColors[slug]?.text || themeColors.default.text;
};

const getThemeIcon = (slug) => {
    return themeIcons[slug] || themeIcons.default;
};
</script>
