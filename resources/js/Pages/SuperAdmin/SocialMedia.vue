<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    currentWebsite: Object,
    websites: Array,
    socialMedia: Object,
});

// Platform configurations
const platforms = [
    {
        id: 'twitter',
        name: 'Twitter',
        color: '#1DA1F2',
        hoverColor: '#1a8cd8',
        placeholder: 'https://twitter.com/yourhandle',
        icon: 'M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z',
    },
    {
        id: 'facebook',
        name: 'Facebook',
        color: '#1877F2',
        hoverColor: '#166fe5',
        placeholder: 'https://facebook.com/yourpage',
        icon: 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
    },
    {
        id: 'linkedin',
        name: 'LinkedIn',
        color: '#0A66C2',
        hoverColor: '#095196',
        placeholder: 'https://linkedin.com/in/yourprofile',
        icon: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
    },
    {
        id: 'pinterest',
        name: 'Pinterest',
        color: '#E60023',
        hoverColor: '#c5001e',
        placeholder: 'https://pinterest.com/yourprofile',
        icon: 'M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z',
    },
    {
        id: 'instagram',
        name: 'Instagram',
        color: '#E4405F',
        hoverColor: '#d62e4c',
        placeholder: 'https://instagram.com/yourhandle',
        icon: 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z',
    },
    {
        id: 'youtube',
        name: 'YouTube',
        color: '#FF0000',
        hoverColor: '#cc0000',
        placeholder: 'https://youtube.com/@yourchannel',
        icon: 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z',
    },
];

// Track which platform is being edited
const editingPlatform = ref(null);
const urlInput = ref('');
const saving = ref(false);

// Check if a platform is connected
const isConnected = (platformId) => {
    return props.socialMedia && props.socialMedia[platformId];
};

// Get the URL for a platform
const getUrl = (platformId) => {
    return props.socialMedia?.[platformId] || '';
};

// Open edit mode for a platform
const openEdit = (platform) => {
    editingPlatform.value = platform.id;
    urlInput.value = getUrl(platform.id);
};

// Cancel editing
const cancelEdit = () => {
    editingPlatform.value = null;
    urlInput.value = '';
};

// Save connection
const saveConnection = (platformId) => {
    saving.value = true;
    
    router.post(route('superadmin.social-media.update', { website: props.currentWebsite.id }), {
        platform: platformId,
        url: urlInput.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editingPlatform.value = null;
            urlInput.value = '';
        },
        onFinish: () => {
            saving.value = false;
        },
    });
};

// Disconnect a platform
const disconnect = (platformId) => {
    if (confirm(`Are you sure you want to disconnect ${platformId}?`)) {
        router.delete(route('superadmin.social-media.disconnect', { 
            website: props.currentWebsite.id, 
            platform: platformId 
        }), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Social Media" />

    <SuperAdminLayout>
        <div class="p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">Social Media</h1>
                <p class="text-gray-400 mt-1">Connect and manage your social media accounts</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Dynamic Platform Cards -->
                <div 
                    v-for="platform in platforms" 
                    :key="platform.id"
                    class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6"
                >
                    <div class="flex items-center gap-4 mb-4">
                        <div 
                            class="w-12 h-12 rounded-xl flex items-center justify-center"
                            :style="{ backgroundColor: platform.color + '20' }"
                        >
                            <svg class="w-6 h-6" :style="{ color: platform.color }" fill="currentColor" viewBox="0 0 24 24">
                                <path :d="platform.icon"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-white font-semibold">{{ platform.name }}</h3>
                            <p v-if="isConnected(platform.id)" class="text-emerald-400 text-sm flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Connected
                            </p>
                            <p v-else class="text-gray-500 text-sm">Not connected</p>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div v-if="editingPlatform === platform.id" class="space-y-3">
                        <div>
                            <label class="text-gray-400 text-sm block mb-2">Profile URL</label>
                            <input 
                                v-model="urlInput"
                                type="url" 
                                :placeholder="platform.placeholder"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                @keyup.enter="saveConnection(platform.id)"
                            />
                        </div>
                        <div class="flex gap-2">
                            <button 
                                @click="saveConnection(platform.id)"
                                :disabled="saving"
                                class="flex-1 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-white py-2 rounded-lg font-medium transition-colors"
                            >
                                {{ saving ? 'Saving...' : 'Save' }}
                            </button>
                            <button 
                                @click="cancelEdit"
                                class="px-4 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-gray-300 py-2 rounded-lg font-medium transition-colors"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Connected State -->
                    <div v-else-if="isConnected(platform.id)" class="space-y-3">
                        <div class="bg-[#252525] rounded-lg p-3">
                            <p class="text-gray-400 text-xs mb-1">Connected URL</p>
                            <a 
                                :href="getUrl(platform.id)" 
                                target="_blank"
                                class="text-sm text-emerald-400 hover:text-emerald-300 truncate block"
                            >
                                {{ getUrl(platform.id) }}
                            </a>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                @click="openEdit(platform)"
                                class="flex-1 bg-[#2a2a2a] hover:bg-[#3a3a3a] text-white py-2 rounded-lg font-medium transition-colors"
                            >
                                Edit
                            </button>
                            <button 
                                @click="disconnect(platform.id)"
                                class="px-4 bg-red-500/20 hover:bg-red-500/30 text-red-400 py-2 rounded-lg font-medium transition-colors"
                            >
                                Disconnect
                            </button>
                        </div>
                    </div>

                    <!-- Not Connected State -->
                    <button 
                        v-else
                        @click="openEdit(platform)"
                        class="w-full text-white py-2 rounded-lg font-medium transition-colors"
                        :style="{ 
                            backgroundColor: platform.color,
                        }"
                        @mouseenter="$event.target.style.backgroundColor = platform.hoverColor"
                        @mouseleave="$event.target.style.backgroundColor = platform.color"
                    >
                        Connect
                    </button>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

