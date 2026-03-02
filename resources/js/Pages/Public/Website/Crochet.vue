<template>
    <CrochetLayout :website="website">
        <Head :title="seoTitle">
            <link v-if="website.favicon_url" rel="icon" :href="website.favicon_url" />
            <meta name="description" :content="seoDescription" />
            <meta property="og:title" :content="ogTitle" />
            <meta property="og:description" :content="ogDescription" />
            <meta property="og:url" :content="website.url" />
            <meta v-if="ogImage" property="og:image" :content="ogImage" />
        </Head>

        <!-- Hero Section -->
        <section class="relative py-16 md:py-24 overflow-hidden">
            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-4xl mx-auto text-center">
                    <div class="flex justify-center gap-2 mb-6">
                        <span class="px-3 py-1 bg-[#FFD7BA] text-[#8B5E3C] text-xs font-bold rounded-full uppercase tracking-widest">Patterns</span>
                        <span class="px-3 py-1 bg-[#D8E2DC] text-[#588157] text-xs font-bold rounded-full uppercase tracking-widest">Free</span>
                        <span class="px-3 py-1 bg-[#FCE1E4] text-[#A44A3F] text-xs font-bold rounded-full uppercase tracking-widest">Guides</span>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-black text-[#3A5A40] mb-8 leading-tight">
                        Crochet Patterns <br/>& Complete Stitch Guides
                    </h1>
                    <div class="bg-white/60 backdrop-blur-sm p-6 rounded-3xl border-2 border-white shadow-xl mb-10 max-w-2xl mx-auto">
                        <p class="text-lg md:text-xl text-[#344E41] font-medium">
                            ❤️ Master the art of crochet with our 800+ expertly crafted free patterns.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button @click="scrollToArticles" class="bg-[#3A5A40] text-white px-10 py-4 rounded-full font-bold text-lg hover:bg-[#344E41] transition-all shadow-lg hover:scale-105">
                            Explore Pattern Library
                        </button>
                        <button class="bg-white text-[#3A5A40] px-10 py-4 rounded-full font-bold text-lg hover:bg-[#F8F9FA] transition-all border-2 border-[#3A5A40]/10">
                            Start Learning Today
                        </button>
                    </div>
                    
                    <!-- Stats -->
                    <div class="flex justify-center gap-8 md:gap-16 mt-16">
                        <div class="text-center">
                            <p class="text-3xl font-black text-[#3A5A40]">800+</p>
                            <p class="text-xs font-bold text-[#A3B18A] uppercase tracking-widest">Free Patterns</p>
                        </div>
                        <div class="text-center border-x border-[#A3B18A]/20 px-8 md:px-16">
                            <p class="text-3xl font-black text-[#3A5A40]">25K+</p>
                            <p class="text-xs font-bold text-[#A3B18A] uppercase tracking-widest">Active Makers</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-[#3A5A40]">Expert</p>
                            <p class="text-xs font-bold text-[#A3B18A] uppercase tracking-widest">Support 24/7</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Decorative blobs -->
            <div class="absolute top-0 left-0 w-64 h-64 bg-[#A3B18A]/10 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-[#3A5A40]/5 rounded-full translate-x-1/3 translate-y-1/3 blur-3xl"></div>
        </section>

        <!-- Why Choose Us -->
        <section class="py-20 bg-[#3A5A40] rounded-[3rem] md:rounded-[5rem] mx-4 my-10">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-black text-white mb-4">Why we are <br/>your perfect choice!</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                    <div class="bg-white p-8 rounded-[2.5rem] text-center shadow-xl hover:-translate-y-2 transition-transform">
                        <div class="w-12 h-12 bg-[#F0F4EF] rounded-full flex items-center justify-center mx-auto mb-6 text-[#3A5A40]">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#3A5A40] mb-4">Professional Grade Patterns</h3>
                        <p class="text-sm text-[#588157] leading-relaxed">Each pattern is hand-tested, triple-checked by certified makers to ensure your success every single time.</p>
                    </div>
                    
                    <div class="bg-[#FFD7BA] p-8 rounded-[2.5rem] text-center shadow-xl hover:-translate-y-2 transition-transform">
                        <div class="w-12 h-12 bg-white/50 rounded-full flex items-center justify-center mx-auto mb-6 text-[#8B5E3C]">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#8B5E3C] mb-4">Skill-Building Tutorial System</h3>
                        <p class="text-sm text-[#8B5E3C]/80 leading-relaxed">Progress naturally with our step-by-step guides, video demonstrations, and handy troubleshooting tips.</p>
                    </div>
                    
                    <div class="bg-[#D8E2DC] p-8 rounded-[2.5rem] text-center shadow-xl hover:-translate-y-2 transition-transform">
                        <div class="w-12 h-12 bg-white/50 rounded-full flex items-center justify-center mx-auto mb-6 text-[#588157]">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#3A5A40] mb-4">Lightning-fast Access</h3>
                        <p class="text-sm text-[#588157] leading-relaxed">No wait times! Download patterns instantly, access our full library, and start stitching now.</p>
                    </div>
                    
                    <div class="bg-[#FCE1E4] p-8 rounded-[2.5rem] text-center shadow-xl hover:-translate-y-2 transition-transform">
                        <div class="w-12 h-12 bg-white/50 rounded-full flex items-center justify-center mx-auto mb-6 text-[#A44A3F]">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#A44A3F] mb-4">Vibrant Maker Community</h3>
                        <p class="text-sm text-[#A44A3F]/80 leading-relaxed">Join thousands of passionate crocheters sharing their latest projects and celebrating finished pieces.</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto mt-6">
                    <div class="bg-white p-8 rounded-[2.5rem] text-center shadow-xl hover:-translate-y-2 transition-transform">
                        <div class="w-12 h-12 bg-[#F0F4EF] rounded-full flex items-center justify-center mx-auto mb-6 text-[#3A5A40]">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 2 7.5 2c1.74 0 3.41.81 4.5 2.09C13.09 2.81 14.76 2 16.5 2 19.58 2 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#3A5A40] mb-4">Exclusive Pattern Vault</h3>
                        <p class="text-sm text-[#588157] leading-relaxed">Unlock signature designs unavailable anywhere else. From intricate lacework to modern decor.</p>
                    </div>
                    <div class="bg-white p-8 rounded-[2.5rem] text-center shadow-xl hover:-translate-y-2 transition-transform">
                        <div class="w-12 h-12 bg-[#F0F4EF] rounded-full flex items-center justify-center mx-auto mb-6 text-[#3A5A40]">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#3A5A40] mb-4">Complete Learning Ecosystem</h3>
                        <p class="text-sm text-[#588157] leading-relaxed">Beyond patterns - your stitch dictionary, gauge calculator, and yarn substitution guides.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Masterpieces -->
        <section id="articles" class="py-20">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-6xl font-black text-[#3A5A40] mb-4">Featured <br/><span class="text-[#A44A3F] font-serif italic font-normal">masterpieces!</span></h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
                    <div 
                        v-for="article in latestArticles.slice(0, 4)" 
                        :key="article.id"
                        class="bg-white rounded-[3rem] overflow-hidden shadow-xl group cursor-pointer hover:scale-[1.02] transition-all duration-500"
                        @click="navigateToArticle(article.url || `/site/${website.slug}/${article.slug}`)"
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
                        <div class="px-10 pb-10 pt-4 flex justify-between items-end">
                            <div>
                                <h3 class="text-2xl font-black text-[#3A5A40] mb-2 group-hover:text-[#588157] transition-colors leading-tight">{{ article.title }}</h3>
                                <div class="flex items-center gap-2 text-[#A3B18A] text-sm font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>4-6 Hours</span>
                                </div>
                            </div>
                            <button class="bg-[#3A5A40] text-white px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-[#344E41] transition-colors shadow-md">
                                Get Pattern
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-24 bg-gradient-to-b from-transparent to-[#E9EDC9]/30">
            <div class="container mx-auto px-4 max-w-4xl">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-black text-[#3A5A40] mb-4">Common <br/>questions, expert <br/>answers.</h2>
                </div>
                
                <div class="space-y-4">
                    <div v-for="(faq, i) in faqs" :key="i" class="bg-white rounded-2xl shadow-sm border border-[#E0E7E0] overflow-hidden">
                        <button 
                            @click="activeFaq = activeFaq === i ? null : i"
                            class="w-full px-8 py-6 text-left flex justify-between items-center hover:bg-[#F8F9FA] transition-colors"
                        >
                            <span class="font-bold text-[#3A5A40]">{{ faq.q }}</span>
                            <svg 
                                class="w-5 h-5 text-[#A3B18A] transition-transform duration-300"
                                :class="activeFaq === i ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="activeFaq === i" class="px-8 pb-6 text-[#588157] text-sm leading-relaxed">
                            {{ faq.a }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission Section -->
        <section class="py-24 bg-white">
            <div class="container mx-auto px-4 text-center max-w-4xl">
                <span class="px-4 py-1 bg-[#D8E2DC] text-[#588157] text-[10px] font-black uppercase tracking-widest rounded-full mb-6 inline-block">Our Mission</span>
                <h2 class="text-3xl font-serif italic text-[#A44A3F] mb-8">About {{ website.name }}: Crochet Excellence</h2>
                <p class="text-lg text-[#3A5A40] leading-relaxed mb-16">
                    {{ website.description || "Founded by master artisans with decades of combined experience, Hook Vibes represents the pinnacle of creative education. We believe exceptional craftsmanship should be accessible to all, which is why our comprehensive library remains completely free." }}
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="member in team" :key="member.name" class="p-8 rounded-[2.5rem]" :style="{ backgroundColor: member.bg }">
                        <div class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-6 border-4 border-white shadow-lg">
                            <img :src="member.img" :alt="member.name" class="w-full h-full object-cover" />
                        </div>
                        <h4 class="font-black text-[#3A5A40] mb-1">{{ member.name }}</h4>
                        <p class="text-[10px] font-black text-[#A3B18A] uppercase tracking-widest">{{ member.role }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter CTA -->
        <section class="py-10 px-4">
            <div class="max-w-5xl mx-auto bg-[#3A5A40] rounded-[3rem] p-10 md:p-16 flex flex-col md:flex-row items-center justify-between gap-10 shadow-2xl relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-4xl font-black text-white mb-4">Join the Elite Circle!</h2>
                    <p class="text-[#DAD7CD] text-lg">Get exclusive access to premium patterns and advanced <br/>techniques delivered to your inbox weekly.</p>
                    <div class="mt-8 flex gap-2 max-w-md">
                        <input type="email" placeholder="Enter your email" class="flex-1 bg-white/10 border border-white/20 rounded-full px-6 py-4 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/50" />
                        <button class="bg-[#A44A3F] text-white px-8 py-4 rounded-full font-black uppercase tracking-widest text-[10px] hover:bg-[#8B3D35] transition-colors shadow-lg">Join</button>
                    </div>
                </div>
                <div class="w-32 h-32 md:w-48 md:h-48 bg-[#FFD7BA] rounded-[2.5rem] flex items-center justify-center shadow-inner relative z-10">
                    <svg class="w-16 h-16 md:w-24 md:h-24 text-[#8B5E3C]" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                </div>
                
                <!-- Decorative circle -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full translate-x-1/2 -translate-y-1/2"></div>
            </div>
        </section>
    </CrochetLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import CrochetLayout from '@/Layouts/CrochetLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    website: Object,
    latestArticles: Array,
    featuredArticles: Array,
    author: Object
});

const activeFaq = ref(null);

const faqs = [
    { q: 'How comprehensive are your free crochet pattern instructions?', a: 'Our patterns include detailed row-by-step instructions, stitch counts, high-resolution photos, and video links for complex techniques.' },
    { q: 'What skill levels do your crochet patterns accommodate?', a: 'We cater to everyone from absolute beginners picking up a hook for the first time to advanced artisans looking for complex lacework.' },
    { q: 'Can I access patterns offline or print them?', a: 'Yes! All our patterns are optimized for mobile viewing and include a "Print-Friendly" mode for your physical pattern collection.' }
];

const team = [
    { name: 'Jessica Harper', role: 'Master Designer & Founder', img: props.author?.image || 'https://i.pravatar.cc/150?u=jessica', bg: '#FFD7BA' },
    { name: 'David Kim', role: 'Lead Instructor & Analyst', img: 'https://i.pravatar.cc/150?u=david', bg: '#F0F4EF' },
    { name: 'Aria Thompson', role: 'Community Director', img: 'https://i.pravatar.cc/150?u=aria', bg: '#D8E2DC' }
];

const seoSettings = computed(() => props.website.seo_settings || {});
const seoTitle = computed(() => seoSettings.value.meta_title || props.website.name);
const seoDescription = computed(() => seoSettings.value.meta_description || props.website.description || '');

const ogTitle = computed(() => seoSettings.value.og_title || props.website.name);
const ogDescription = computed(() => seoSettings.value.og_description || seoDescription.value);
const ogImage = computed(() => seoSettings.value.og_image || props.website.logo_url || '');

const scrollToArticles = () => {
    const el = document.getElementById('articles');
    if (el) el.scrollIntoView({ behavior: 'smooth' });
};

const navigateToArticle = (url) => {
    if (!url) return;
    window.location.href = url;
};
</script>
