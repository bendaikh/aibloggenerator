<template>
    <CrochetLayout :website="website">
        <Head :title="article.meta_title || (article.title + ' - ' + website.name)">
            <link v-if="website.favicon_url" :rel="'icon'" :href="website.favicon_url" />
            <meta name="description" :content="article.meta_description || article.excerpt || ''" />
            <meta property="og:type" content="article" />
            <meta property="og:title" :content="article.title" />
            <meta property="og:description" :content="article.excerpt || article.meta_description || ''" />
            <meta v-if="articleImageUrl" property="og:image" :content="articleImageUrl" />
            <meta property="og:url" :content="currentPageUrl" />
            <meta property="og:site_name" :content="website.name" />
        </Head>

        <!-- Article Container -->
        <article class="bg-[#FBF9F6] min-h-screen">
            <!-- Hero Section with Pattern Stats -->
            <header class="relative py-8 md:py-12 bg-gradient-to-b from-[#E8F3E8] to-[#FBF9F6]">
                <div class="container mx-auto px-4">
                    <div class="max-w-5xl mx-auto">
                        <!-- Breadcrumb & Print/Share Actions -->
                        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
                            <div class="flex items-center gap-2 text-sm">
                                <a :href="website.url" class="text-[#6B8E6B] hover:text-[#3A5A40] font-medium transition-colors">Home</a>
                                <span class="text-[#A3B18A]">/</span>
                                <a v-if="article.category" :href="article.category.url" class="text-[#6B8E6B] hover:text-[#3A5A40] font-medium transition-colors">{{ article.category.name }}</a>
                            </div>
                            <div class="flex items-center gap-3">
                                <button @click="printArticle" class="flex items-center gap-2 px-4 py-2 bg-white border border-[#D8E2DC] rounded-lg text-[#3A5A40] text-sm font-semibold hover:bg-[#E8F3E8] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                    Print
                                </button>
                                <button @click="shareArticle" class="flex items-center gap-2 px-4 py-2 bg-[#E60023] text-white rounded-lg text-sm font-semibold hover:bg-[#bd081c] transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                    Pin
                                </button>
                            </div>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl md:text-5xl lg:text-6xl font-black text-[#2D4A2D] mb-6 leading-[1.15]">
                            {{ article.title }}
                        </h1>

                        <!-- Pattern Metadata Cards -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#E8F3E8]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-2xl">⭐</span>
                                    <span class="text-xs font-bold text-[#A3B18A] uppercase">Rating</span>
                                </div>
                                <div class="text-2xl font-black text-[#3A5A40]">4.5★</div>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#E8F3E8]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-2xl">⏱️</span>
                                    <span class="text-xs font-bold text-[#A3B18A] uppercase">Time</span>
                                </div>
                                <div class="text-2xl font-black text-[#3A5A40]">4-5 hrs</div>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#E8F3E8]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-2xl">📐</span>
                                    <span class="text-xs font-bold text-[#A3B18A] uppercase">Level</span>
                                </div>
                                <div class="text-sm font-black text-[#3A5A40]">Intermediate</div>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#E8F3E8]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-2xl">💝</span>
                                    <span class="text-xs font-bold text-[#A3B18A] uppercase">Made</span>
                                </div>
                                <div class="text-2xl font-black text-[#3A5A40]">2.5K</div>
                            </div>
                        </div>
                        
                        <!-- Excerpt/Description Box -->
                        <div v-if="article.excerpt" class="bg-white/80 backdrop-blur-sm p-6 md:p-8 rounded-2xl border-2 border-[#D8E2DC] shadow-lg mb-6">
                            <p class="text-base md:text-lg text-[#344E41] leading-relaxed font-medium">
                                {{ article.excerpt }}
                            </p>
                        </div>
                        
                        <!-- Author & Date Row -->
                        <div class="flex items-center gap-4 text-sm text-[#6B8E6B]">
                            <div class="flex items-center gap-2">
                                <div v-if="article.author?.image" class="w-8 h-8 rounded-full overflow-hidden border-2 border-white shadow-sm">
                                    <img :src="article.author.image" :alt="article.author?.name" class="w-full h-full object-cover" />
                                </div>
                                <div v-else class="w-8 h-8 rounded-full bg-[#3A5A40] flex items-center justify-center border-2 border-white shadow-sm">
                                    <span class="text-xs text-white font-bold">{{ (article.author?.name || article.user?.name || 'A').charAt(0) }}</span>
                                </div>
                                <span class="font-semibold text-[#3A5A40]">{{ article.author?.name || article.user?.name || 'Admin' }}</span>
                            </div>
                            <span class="w-1 h-1 bg-[#A3B18A] rounded-full"></span>
                            <time :datetime="article.published_at" class="font-medium">
                                {{ formatDate(article.published_at) }}
                            </time>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            <div v-if="article.processed_featured_image || article.featured_image" class="max-w-6xl mx-auto px-4 mb-8 relative z-10">
                <div class="aspect-[16/9] md:aspect-[21/9] rounded-3xl overflow-hidden shadow-2xl border-8 border-white ring-1 ring-[#E8F3E8]">
                    <img
                        :src="article.processed_featured_image || article.featured_image"
                        :alt="article.title"
                        class="w-full h-full object-cover"
                    />
                </div>
            </div>

            <!-- Main Content Area with Sidebar Layout -->
            <div class="bg-white pt-12 pb-20">
                <div class="container mx-auto px-4">
                    <div class="max-w-7xl mx-auto">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <!-- Main Article Content -->
                            <div class="lg:col-span-8">
                                <!-- Jump to Pattern Button -->
                                <div class="mb-8 text-center">
                                    <button @click="scrollToPattern" class="inline-flex items-center gap-2 bg-gradient-to-r from-[#588157] to-[#3A5A40] text-white px-8 py-4 rounded-full font-bold text-sm uppercase tracking-wider shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                                        <span>Jump to Pattern</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </button>
                                </div>

                                <!-- Gallery Images (Before Introduction) -->
                                <div v-if="galleryImages.length > 0" class="mb-12 space-y-8">
                                    <div class="text-center">
                                        <h3 class="text-3xl font-black text-[#2D4A2D] mb-2">Project Gallery</h3>
                                        <div class="w-16 h-1 bg-gradient-to-r from-[#588157] to-[#A44A3F] mx-auto rounded-full"></div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div
                                            v-for="(image, index) in galleryImages"
                                            :key="image.id || index"
                                            class="space-y-3 group"
                                        >
                                            <div class="relative rounded-2xl overflow-hidden shadow-lg border-4 border-[#FBF9F6] group-hover:border-[#A3B18A] transition-all duration-300">
                                                <img
                                                    :src="image.url || image.local_path"
                                                    :alt="image.title"
                                                    class="w-full h-auto group-hover:scale-105 transition-transform duration-500"
                                                    loading="lazy"
                                                />
                                                <!-- Pinterest Pin Overlay -->
                                                <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                                    <a :href="getPinterestUrl(image.url || image.local_path, image.title)" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 px-3 py-2 bg-[#E60023] text-white rounded-full font-bold text-xs hover:bg-[#bd081c] transition shadow-lg">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                                        Pin
                                                    </a>
                                                </div>
                                                <!-- Step Number Badge -->
                                                <div class="absolute top-4 left-4 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg">
                                                    <span class="text-sm font-black text-[#3A5A40]">{{ index + 1 }}</span>
                                                </div>
                                            </div>
                                            <p v-if="image.title" class="text-center text-sm font-semibold text-[#6B8E6B]">
                                                {{ image.title }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Article Content -->
                                <div class="prose prose-lg max-w-none article-content-crochet" v-html="processedContent"></div>

                                <!-- Rounds/Pattern Section (for crochet patterns) -->
                                <div v-if="article.instructions && article.instructions.length > 0" id="pattern-section" class="mt-16 bg-gradient-to-br from-[#F0F4EF] to-[#FBF9F6] rounded-3xl p-8 md:p-12 border-4 border-[#E8F3E8] shadow-xl">
                                    <div class="text-center mb-10">
                                        <h2 class="text-4xl font-black text-[#2D4A2D] mb-3">Pattern Rounds</h2>
                                        <div class="w-20 h-1 bg-gradient-to-r from-[#588157] to-[#A44A3F] mx-auto rounded-full"></div>
                                        <p class="mt-4 text-[#6B8E6B] text-lg">Follow these rounds to create your masterpiece</p>
                                    </div>
                                    
                                    <!-- Generate Rounds Button (shown when rounds not generated yet) -->
                                    <div v-if="!roundsGenerated" class="text-center">
                                        <button 
                                            @click="generateRounds"
                                            :disabled="isGeneratingRounds"
                                            class="inline-flex items-center gap-3 bg-gradient-to-r from-[#588157] to-[#3A5A40] text-white px-12 py-5 rounded-2xl font-black text-lg shadow-2xl hover:shadow-xl hover:scale-105 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed"
                                        >
                                            <svg v-if="isGeneratingRounds" class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            <span>{{ isGeneratingRounds ? 'Generating Rounds...' : 'Generate Pattern Rounds' }}</span>
                                        </button>
                                        <p class="mt-4 text-sm text-[#6B8E6B] italic">Click to reveal the complete pattern with typing animation</p>
                                    </div>
                                    
                                    <!-- Rounds Display with Checkboxes -->
                                    <div v-if="roundsGenerated" class="space-y-4">
                                        <div 
                                            v-for="(round, index) in displayedRounds" 
                                            :key="index"
                                            class="bg-white rounded-2xl p-6 shadow-md border-l-4 border-[#588157] hover:shadow-lg transition-all duration-300"
                                            :class="{ 'opacity-0 animate-fadeIn': index === displayedRounds.length - 1 }"
                                        >
                                            <div class="flex gap-4 items-start">
                                                <!-- Checkbox -->
                                                <div class="shrink-0 pt-1">
                                                    <input 
                                                        type="checkbox"
                                                        :id="'round-' + index"
                                                        v-model="checkedRounds[index]"
                                                        class="w-6 h-6 text-[#588157] bg-white border-2 border-[#A3B18A] rounded focus:ring-[#588157] focus:ring-2 cursor-pointer transition-all"
                                                    />
                                                </div>
                                                
                                                <!-- Round Number Badge -->
                                                <div class="shrink-0">
                                                    <div class="w-12 h-12 bg-gradient-to-br from-[#588157] to-[#3A5A40] rounded-full flex items-center justify-center shadow-lg">
                                                        <span class="text-white font-black text-lg">{{ index + 1 }}</span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Round Text with Typing Effect -->
                                                <label 
                                                    :for="'round-' + index"
                                                    class="flex-1 cursor-pointer"
                                                    :class="{ 'line-through opacity-60': checkedRounds[index] }"
                                                >
                                                    <p class="text-[#344E41] text-base md:text-lg leading-relaxed font-medium">
                                                        <span v-if="typingIndex === index">{{ typingText }}</span>
                                                        <span v-else>{{ round }}</span>
                                                        <span v-if="typingIndex === index && !typingComplete" class="inline-block w-0.5 h-5 bg-[#588157] ml-1 animate-pulse"></span>
                                                    </p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="roundsGenerated" class="mt-8 p-6 bg-white/80 rounded-2xl border-2 border-dashed border-[#A3B18A]">
                                        <div class="flex items-start gap-4">
                                            <div class="text-3xl">💡</div>
                                            <div>
                                                <h3 class="text-lg font-black text-[#3A5A40] mb-2">Pro Tip</h3>
                                                <p class="text-[#6B8E6B] text-sm">
                                                    Use stitch markers to keep track of your rounds. Remember to count your stitches at the end of each round to ensure accuracy! Check off each round as you complete it.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        <!-- Tags -->
                        <div v-if="article.meta_tags && article.meta_tags.length > 0" class="mt-16 pt-10 border-t border-[#E8F3E8]">
                            <div class="flex flex-wrap justify-center gap-2">
                                <span
                                    v-for="(tag, index) in article.meta_tags"
                                    :key="index"
                                    class="px-5 py-2 bg-[#E8F3E8] text-[#3A5A40] text-xs font-bold rounded-full hover:bg-[#A3B18A]/20 transition-colors cursor-pointer"
                                >
                                    #{{ tag }}
                                </span>
                            </div>
                        </div>
                            </div>

                            <!-- Sticky Sidebar -->
                            <aside class="lg:col-span-4 space-y-6">
                                <div class="sticky top-24 space-y-6">
                                    <!-- About This Pattern Card -->
                                    <div class="bg-gradient-to-br from-[#E8F3E8] to-[#F0F4EF] rounded-2xl p-6 shadow-lg border border-white">
                                        <h3 class="text-xl font-black text-[#2D4A2D] mb-4">About This Pattern</h3>
                                        <div class="space-y-3 text-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">
                                                    <span class="text-lg">🎯</span>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-bold text-[#A3B18A] uppercase">Skill Level</div>
                                                    <div class="font-bold text-[#3A5A40]">Intermediate</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">
                                                    <span class="text-lg">🌸</span>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-bold text-[#A3B18A] uppercase">Season</div>
                                                    <div class="font-bold text-[#3A5A40]">Spring / Summer</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">
                                                    <span class="text-lg">📏</span>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-bold text-[#A3B18A] uppercase">Techniques</div>
                                                    <div class="font-bold text-[#3A5A40]">Magic Ring, Increases</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Newsletter Sidebar CTA -->
                                    <div class="bg-gradient-to-br from-[#3A5A40] to-[#2D4A2D] rounded-2xl p-6 shadow-xl text-center text-white">
                                        <div class="text-4xl mb-3">💌</div>
                                        <h3 class="text-xl font-black mb-2">Get More Patterns!</h3>
                                        <p class="text-sm text-[#DAD7CD] mb-4">Free crochet patterns delivered weekly.</p>
                                        <input
                                            v-model="sidebarEmail"
                                            type="email"
                                            placeholder="Enter your email"
                                            class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-sm text-white placeholder:text-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 mb-3"
                                        />
                                        <button
                                            @click="subscribeSidebar"
                                            :disabled="isSubscribing"
                                            class="w-full bg-[#A44A3F] text-white px-6 py-3 rounded-lg font-bold text-sm hover:bg-[#8B3D35] transition-all shadow-lg disabled:opacity-50"
                                        >
                                            {{ isSubscribing ? 'Joining...' : 'Subscribe Free' }}
                                        </button>
                                        <p v-if="sidebarMessage" :class="subscribeSuccess ? 'text-green-300' : 'text-red-300'" class="mt-3 font-bold text-xs">
                                            {{ sidebarMessage }}
                                        </p>
                                    </div>

                                    <!-- Share Card -->
                                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-[#E8F3E8]">
                                        <h3 class="text-lg font-black text-[#2D4A2D] mb-4">Share This Pattern</h3>
                                        <div class="flex flex-col gap-2">
                                            <a :href="getPinterestShareUrl()" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 bg-[#E60023] text-white rounded-lg font-bold text-sm hover:bg-[#bd081c] transition-colors shadow-sm">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                                Save to Pinterest
                                            </a>
                                            <button @click="copyLink" class="flex items-center gap-3 px-4 py-3 bg-[#E8F3E8] text-[#3A5A40] rounded-lg font-bold text-sm hover:bg-[#D8E2DC] transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                {{ linkCopied ? 'Link Copied!' : 'Copy Link' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Author Bio Section -->
            <section class="bg-[#FBF9F6] py-16">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto bg-white p-8 md:p-12 rounded-3xl shadow-lg border border-[#E8F3E8]">
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                            <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-[#E8F3E8] shadow-lg flex-shrink-0">
                                <img
                                    v-if="article.author?.image"
                                    :src="article.author.image"
                                    :alt="article.author?.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full bg-[#3A5A40] flex items-center justify-center">
                                    <span class="text-3xl text-white font-black">{{ (article.author?.name || article.user?.name || 'A').charAt(0) }}</span>
                                </div>
                            </div>
                            <div class="flex-1 text-center md:text-left">
                                <h3 class="text-2xl font-black text-[#2D4A2D] mb-2">
                                    {{ article.author?.name || article.user?.name || 'The Author' }}
                                </h3>
                                <p class="text-[#6B8E6B] mb-4 leading-relaxed">
                                    {{ article.author?.description || "A passionate crochet artisan sharing the joy of creative stitching. Every pattern is crafted with love and attention to detail." }}
                                </p>
                                <div class="flex justify-center md:justify-start gap-3">
                                    <a href="#" class="w-10 h-10 rounded-xl bg-[#E8F3E8] flex items-center justify-center text-[#3A5A40] hover:bg-[#3A5A40] hover:text-white transition-all duration-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    </a>
                                    <a href="#" class="w-10 h-10 rounded-xl bg-[#E8F3E8] flex items-center justify-center text-[#3A5A40] hover:bg-[#3A5A40] hover:text-white transition-all duration-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146 1.124.347 2.317.535 3.554.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Newsletter CTA Section -->
            <section class="bg-gradient-to-br from-[#3A5A40] to-[#2D4A2D] py-16">
                <div class="container mx-auto px-4">
                    <div class="max-w-2xl mx-auto text-center">
                        <div class="text-5xl mb-4">🎉</div>
                        <h2 class="text-3xl md:text-4xl font-black text-white mb-4">
                            Loved this pattern?
                        </h2>
                        <p class="text-[#DAD7CD] mb-8 text-lg">
                            Join thousands of crocheters getting free patterns delivered every week!
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                            <input
                                v-model="newsletterEmail"
                                type="email"
                                placeholder="Your email address"
                                class="flex-1 bg-white/10 border border-white/20 rounded-full px-6 py-4 text-white placeholder:text-white/50 focus:outline-none focus:ring-2 focus:ring-white/50"
                            />
                            <button
                                @click="subscribeNewsletter"
                                :disabled="isSubscribing"
                                class="bg-[#A44A3F] text-white px-8 py-4 rounded-full font-bold uppercase tracking-wide text-sm hover:bg-[#8B3D35] transition-all shadow-xl disabled:opacity-50"
                            >
                                {{ isSubscribing ? 'Joining...' : 'Subscribe' }}
                            </button>
                        </div>
                        <p v-if="subscribeMessage" :class="subscribeSuccess ? 'text-green-300' : 'text-red-300'" class="mt-4 font-bold text-sm">
                            {{ subscribeMessage }}
                        </p>
                        <p class="text-xs text-white/60 mt-4">No spam, ever. Unsubscribe anytime.</p>
                    </div>
                </div>
            </section>

            <!-- Related Articles -->
            <section v-if="relatedArticles && relatedArticles.length > 0" class="py-16 bg-[#FBF9F6]">
                <div class="container mx-auto px-4">
                    <div class="max-w-6xl mx-auto">
                        <div class="text-center mb-10">
                            <h2 class="text-3xl md:text-4xl font-black text-[#2D4A2D] mb-2">
                                You Might Also Like
                            </h2>
                            <div class="w-16 h-1 bg-gradient-to-r from-[#588157] to-[#A44A3F] mx-auto rounded-full"></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <a
                                v-for="related in relatedArticles.slice(0, 3)"
                                :key="related.id"
                                :href="related.url"
                                class="group bg-white rounded-2xl overflow-hidden shadow-lg border border-[#E8F3E8] hover:shadow-2xl transition-all duration-300"
                            >
                                <div class="aspect-[4/3] overflow-hidden bg-[#E8F3E8]">
                                    <img
                                        v-if="related.processed_featured_image || related.featured_image"
                                        :src="related.processed_featured_image || related.featured_image"
                                        :alt="related.title"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <span class="text-5xl">🧶</span>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <h3 class="text-lg font-black text-[#2D4A2D] group-hover:text-[#588157] transition-colors line-clamp-2 mb-2">
                                        {{ related.title }}
                                    </h3>
                                    <p v-if="related.excerpt" class="text-sm text-[#6B8E6B] line-clamp-2">
                                        {{ related.excerpt }}
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </article>
    </CrochetLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CrochetLayout from '@/Layouts/CrochetLayout.vue';
import axios from 'axios';

const props = defineProps({
    website: Object,
    article: Object,
    relatedArticles: Array,
    articleImages: {
        type: Array,
        default: () => []
    }
});

// Newsletter
const newsletterEmail = ref('');
const sidebarEmail = ref('');
const sidebarMessage = ref('');
const isSubscribing = ref(false);
const subscribeMessage = ref('');
const subscribeSuccess = ref(false);
const linkCopied = ref(false);

// Rounds generation and typing animation
const roundsGenerated = ref(false);
const isGeneratingRounds = ref(false);
const displayedRounds = ref([]);
const checkedRounds = ref({});
const typingIndex = ref(-1);
const typingText = ref('');
const typingComplete = ref(false);

// Generate rounds with typing animation
const generateRounds = async () => {
    if (isGeneratingRounds.value || roundsGenerated.value) return;
    
    isGeneratingRounds.value = true;
    
    // Simulate initial loading
    await new Promise(resolve => setTimeout(resolve, 800));
    
    isGeneratingRounds.value = false;
    roundsGenerated.value = true;
    
    // Start typing animation for each round
    const rounds = props.article.instructions || [];
    for (let i = 0; i < rounds.length; i++) {
        displayedRounds.value.push('');
        typingIndex.value = i;
        typingText.value = '';
        typingComplete.value = false;
        
        // Type out each character
        const round = rounds[i];
        for (let j = 0; j < round.length; j++) {
            typingText.value = round.substring(0, j + 1);
            await new Promise(resolve => setTimeout(resolve, 15)); // Typing speed
        }
        
        // Complete this round
        displayedRounds.value[i] = round;
        typingComplete.value = true;
        typingIndex.value = -1;
        
        // Brief pause before next round
        await new Promise(resolve => setTimeout(resolve, 100));
    }
};

const subscribeNewsletter = async () => {
    if (!newsletterEmail.value || !newsletterEmail.value.includes('@')) {
        subscribeMessage.value = 'Please enter a valid email address.';
        subscribeSuccess.value = false;
        return;
    }

    isSubscribing.value = true;
    try {
        const response = await axios.post(`/site/${props.website.id}/subscribe`, {
            email: newsletterEmail.value,
            source: 'article_newsletter'
        });
        subscribeMessage.value = response.data.message || 'Thank you for joining!';
        subscribeSuccess.value = true;
        newsletterEmail.value = '';
    } catch (error) {
        subscribeMessage.value = error.response?.data?.message || 'Something went wrong. Please try again.';
        subscribeSuccess.value = false;
    } finally {
        isSubscribing.value = false;
    }
};

const subscribeSidebar = async () => {
    if (!sidebarEmail.value || !sidebarEmail.value.includes('@')) {
        sidebarMessage.value = 'Please enter a valid email.';
        subscribeSuccess.value = false;
        return;
    }

    isSubscribing.value = true;
    try {
        const response = await axios.post(`/site/${props.website.id}/subscribe`, {
            email: sidebarEmail.value,
            source: 'article_sidebar'
        });
        sidebarMessage.value = response.data.message || 'Thank you!';
        subscribeSuccess.value = true;
        sidebarEmail.value = '';
    } catch (error) {
        sidebarMessage.value = error.response?.data?.message || 'Try again.';
        subscribeSuccess.value = false;
    } finally {
        isSubscribing.value = false;
    }
};

const scrollToPattern = () => {
    const patternElement = document.querySelector('#pattern-section');
    if (patternElement) {
        patternElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        // Fallback to content if pattern section doesn't exist
        const contentElement = document.querySelector('.article-content-crochet');
        if (contentElement) {
            contentElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
};

const shareArticle = () => {
    const url = encodeURIComponent(currentPageUrl.value);
    const media = encodeURIComponent(articleImageUrl.value || '');
    const description = encodeURIComponent(props.article?.title || '');
    window.open(`https://pinterest.com/pin/create/button/?url=${url}&media=${media}&description=${description}`, '_blank');
};

const printArticle = () => {
    window.print();
};

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(currentPageUrl.value);
        linkCopied.value = true;
        setTimeout(() => {
            linkCopied.value = false;
        }, 2000);
    } catch (error) {
        console.error('Failed to copy link:', error);
    }
};

// Article images from props
const articleImages = computed(() => props.articleImages || []);

// Get all images for gallery (excluding hero which is position 0)
const galleryImages = computed(() => {
    const images = props.articleImages || [];
    // Show all uploaded images - they are already non-hero (position > 0)
    return images
        .filter(img => img.generation_type === 'uploaded' || img.position > 0)
        .sort((a, b) => (a.position ?? 0) - (b.position ?? 0));
});

const processedContent = computed(() => props.article?.processed_content || props.article?.content || '');

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// URLs
const currentPageUrl = computed(() => {
    if (typeof window !== 'undefined') {
        return window.location.href;
    }
    return props.article?.url || '';
});

const articleImageUrl = computed(() => {
    const image = props.article?.processed_featured_image || props.article?.featured_image;
    if (image && image.startsWith('http')) {
        return image;
    }
    if (image && typeof window !== 'undefined') {
        return new URL(image, window.location.origin).href;
    }
    return image || '';
});

const getPinterestUrl = (imageUrl, title) => {
    const url = encodeURIComponent(currentPageUrl.value);
    const media = encodeURIComponent(imageUrl);
    const description = encodeURIComponent(title || props.article?.title || '');
    return `https://pinterest.com/pin/create/button/?url=${url}&media=${media}&description=${description}`;
};

const getPinterestShareUrl = () => {
    const url = encodeURIComponent(currentPageUrl.value);
    const media = encodeURIComponent(articleImageUrl.value || '');
    const description = encodeURIComponent(props.article?.title || '');
    return `https://pinterest.com/pin/create/button/?url=${url}&media=${media}&description=${description}`;
};

// SEO
const seoSettings = computed(() => props.website?.seo_settings || {});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800;900&family=Crimson+Pro:ital,wght@0,400;0,700;1,400&display=swap');

* {
    font-family: 'Work Sans', sans-serif;
}

.article-content-crochet {
    color: #344E41;
    line-height: 1.75;
    font-size: 1.0625rem;
}

.article-content-crochet h2 {
    font-size: 2.25rem;
    font-weight: 900;
    color: #2D4A2D;
    margin-top: 3rem;
    margin-bottom: 1.25rem;
    line-height: 1.25;
    position: relative;
    padding-bottom: 0.75rem;
}

.article-content-crochet h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(to right, #588157, #A44A3F);
    border-radius: 2px;
}

.article-content-crochet h3 {
    font-size: 1.75rem;
    font-weight: 800;
    color: #3A5A40;
    margin-top: 2.5rem;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.article-content-crochet h4 {
    font-size: 1.375rem;
    font-weight: 700;
    color: #3A5A40;
    margin-top: 2rem;
    margin-bottom: 0.875rem;
}

.article-content-crochet p {
    margin-bottom: 1.5rem;
    font-size: 1.0625rem;
    line-height: 1.75;
}

.article-content-crochet ul,
.article-content-crochet ol {
    margin-bottom: 2rem;
    padding-left: 1.75rem;
    list-style-position: outside;
}

.article-content-crochet ul {
    list-style-type: disc;
}

.article-content-crochet ol {
    list-style-type: decimal;
}

.article-content-crochet li {
    margin-bottom: 0.875rem;
    padding-left: 0.5rem;
}

.article-content-crochet li::marker {
    color: #A44A3F;
    font-weight: 700;
}

.article-content-crochet strong {
    font-weight: 700;
    color: #2D4A2D;
}

.article-content-crochet em {
    font-family: 'Crimson Pro', serif;
    font-style: italic;
}

.article-content-crochet blockquote {
    border-left: 6px solid #588157;
    padding: 1.5rem 2rem;
    background: linear-gradient(to right, #E8F3E8, #FBF9F6);
    border-radius: 1rem;
    margin: 2.5rem 0;
    font-style: italic;
    font-size: 1.125rem;
    color: #2D4A2D;
    position: relative;
}

.article-content-crochet blockquote::before {
    content: '"';
    font-size: 4rem;
    font-family: 'Crimson Pro', serif;
    position: absolute;
    top: -0.5rem;
    left: 1rem;
    color: #A3B18A;
    opacity: 0.3;
}

.article-content-crochet img {
    border-radius: 1.5rem;
    margin: 2.5rem 0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 4px solid #FBF9F6;
}

.article-content-crochet a {
    color: #588157;
    text-decoration: underline;
    font-weight: 600;
    transition: color 0.2s;
}

.article-content-crochet a:hover {
    color: #3A5A40;
}

.article-content-crochet code {
    background: #E8F3E8;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.9375rem;
    color: #2D4A2D;
    font-family: 'Courier New', monospace;
}

.article-content-crochet pre {
    background: #2D4A2D;
    padding: 1.5rem;
    border-radius: 1rem;
    overflow-x: auto;
    margin: 2rem 0;
}

.article-content-crochet pre code {
    background: transparent;
    color: #E8F3E8;
    padding: 0;
}

.article-content-crochet table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.article-content-crochet th {
    background: #3A5A40;
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 700;
}

.article-content-crochet td {
    padding: 1rem;
    border-bottom: 1px solid #E8F3E8;
}

.article-content-crochet tr:last-child td {
    border-bottom: none;
}

.article-content-crochet tr:nth-child(even) {
    background: #FBF9F6;
}

.article-content-crochet hr {
    border: none;
    height: 2px;
    background: linear-gradient(to right, transparent, #A3B18A, transparent);
    margin: 3rem 0;
}

/* Print Styles */
@media print {
    .sticky,
    button,
    aside,
    footer,
    header nav {
        display: none !important;
    }
    
    .article-content-crochet {
        font-size: 12pt;
    }
}

/* Typing Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>
