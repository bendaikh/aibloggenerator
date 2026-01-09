<template>
    <SuperAdminLayout>
        <Head title="Create Article" />

        <div class="p-8">
            <div class="max-w-4xl">
                <div class="mb-8">
                    <Link 
                        :href="route('superadmin.articles.index', { website: currentWebsite?.id })" 
                        class="text-gray-400 hover:text-white flex items-center text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Articles
                    </Link>
                </div>

                <!-- Step 1: Choose Creation Method -->
                <div v-if="!creationMethod" class="space-y-6">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-white">Create New Article</h1>
                        <p class="text-gray-400 mt-1">Choose how you want to create your article</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Manual Creation -->
                        <button 
                            @click="creationMethod = 'manual'"
                            class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8 text-left hover:border-emerald-500 transition-all group"
                        >
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mb-6">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Write Manually</h3>
                            <p class="text-gray-400 text-sm">
                                Create your article from scratch. Write your own content with full creative control.
                            </p>
                            <div class="mt-6 flex items-center text-emerald-400 text-sm font-medium">
                                Start writing
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>

                        <!-- AI Generation -->
                        <button 
                            @click="creationMethod = 'ai'"
                            :disabled="!hasApiKey"
                            :class="[
                                'bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8 text-left transition-all group',
                                hasApiKey ? 'hover:border-emerald-500' : 'opacity-60 cursor-not-allowed'
                            ]"
                        >
                            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center mb-6">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Generate with AI</h3>
                            <p class="text-gray-400 text-sm">
                                Let AI write your article. Just provide a topic and our AI will create engaging content.
                            </p>
                            <div v-if="hasApiKey" class="mt-6 flex items-center text-emerald-400 text-sm font-medium">
                                Generate article
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                            <div v-else class="mt-6">
                                <Link 
                                    :href="route('organization.settings')" 
                                    class="text-yellow-400 text-sm font-medium hover:underline"
                                    @click.stop
                                >
                                    Configure API Key in Settings →
                                </Link>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Manual Creation Form -->
                <div v-else-if="creationMethod === 'manual'">
                    <div class="flex items-center gap-4 mb-8">
                        <button 
                            @click="creationMethod = null"
                            class="text-gray-400 hover:text-white p-2 rounded-lg hover:bg-[#1a1a1a] transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Write Article</h1>
                            <p class="text-gray-400 mt-1">Create your article manually</p>
                        </div>
                    </div>

                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8">
                        <form @submit.prevent="submitManual">
                            <!-- Category Selection -->
                            <div class="mb-6">
                                <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">
                                    Category
                                </label>
                                <select
                                    id="category_id"
                                    v-model="manualForm.category_id"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                >
                                    <option value="">Select a category</option>
                                    <option v-for="category in currentWebsite?.categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="manualForm.errors.category_id" class="mt-2 text-sm text-red-500">{{ manualForm.errors.category_id }}</p>
                            </div>

                            <!-- Title -->
                            <div class="mb-6">
                                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                                    Title *
                                </label>
                                <input
                                    id="title"
                                    v-model="manualForm.title"
                                    type="text"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    required
                                />
                                <p v-if="manualForm.errors.title" class="mt-2 text-sm text-red-500">{{ manualForm.errors.title }}</p>
                            </div>

                            <!-- Slug -->
                            <div class="mb-6">
                                <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">
                                    Slug (URL)
                                </label>
                                <input
                                    id="slug"
                                    v-model="manualForm.slug"
                                    type="text"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="Auto-generated from title"
                                />
                                <p v-if="manualForm.errors.slug" class="mt-2 text-sm text-red-500">{{ manualForm.errors.slug }}</p>
                            </div>

                            <!-- Article Images Upload (Multiple) -->
                            <div class="mb-6 space-y-4">
                                <label class="block text-sm font-medium text-gray-300">
                                    Article Images (Optional)
                                </label>
                                <p class="text-xs text-gray-500">Upload multiple images. The first will be the featured image, second will be the secondary image.</p>
                                
                                <!-- Multiple Image Upload -->
                                <label 
                                    for="articleImagesUpload"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleImagesDrop"
                                    :class="[
                                        'border-2 border-dashed rounded-lg p-8 text-center transition-all block',
                                        isUploading ? 'cursor-wait opacity-70' : 'cursor-pointer',
                                        isDragging ? 'border-emerald-500 bg-emerald-900/20' : 'border-[#3a3a3a] hover:border-[#4a4a4a]'
                                    ]"
                                >
                                    <input
                                        id="articleImagesUpload"
                                        type="file"
                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                        multiple
                                        class="sr-only"
                                        @change="handleImagesSelect"
                                        :disabled="isUploading"
                                    />
                                    <div class="flex flex-col items-center">
                                        <div v-if="isUploading" class="w-12 h-12 bg-[#252525] rounded-lg flex items-center justify-center mb-3">
                                            <svg class="animate-spin w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        <div v-else class="w-12 h-12 bg-[#252525] rounded-lg flex items-center justify-center mb-3">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-white text-sm font-medium mb-1">
                                            {{ isUploading ? 'Uploading images...' : 'Drop images here or click to browse' }}
                                        </p>
                                        <p class="text-gray-500 text-xs">
                                            PNG, JPG, WebP up to 5MB each
                                        </p>
                                    </div>
                                </label>
                                
                                <!-- Upload Error -->
                                <div v-if="uploadError" class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-3 rounded-lg text-sm">
                                    {{ uploadError }}
                                </div>
                                
                                <!-- Uploaded Images Preview -->
                                <div v-if="uploadedImages.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div v-for="(img, index) in uploadedImages" :key="index" class="relative group">
                                        <div class="absolute -top-2 -right-2 z-10">
                                            <button 
                                                type="button"
                                                @click="removeImageAt(index)"
                                                class="w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="aspect-square bg-[#252525] rounded-lg overflow-hidden border-2 border-[#3a3a3a]">
                                            <img :src="img" :alt="`Image ${index + 1}`" class="w-full h-full object-cover" />
                                        </div>
                                        <div class="mt-2 text-center">
                                            <p class="text-xs text-gray-400">{{ index === 0 ? 'Featured' : index === 1 ? 'Secondary' : `Image ${index + 1}` }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Time Information -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                <div>
                                    <label for="prep_time" class="block text-sm font-medium text-gray-300 mb-2">Prep Time</label>
                                    <input id="prep_time" v-model="manualForm.prep_time" type="text" class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="e.g. 10 mins" />
                                </div>
                                <div>
                                    <label for="cook_time" class="block text-sm font-medium text-gray-300 mb-2">Cook Time</label>
                                    <input id="cook_time" v-model="manualForm.cook_time" type="text" class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="e.g. 20 mins" />
                                </div>
                                <div>
                                    <label for="rest_time" class="block text-sm font-medium text-gray-300 mb-2">Rest Time</label>
                                    <input id="rest_time" v-model="manualForm.rest_time" type="text" class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="e.g. 5 mins" />
                                </div>
                                <div>
                                    <label for="total_time" class="block text-sm font-medium text-gray-300 mb-2">Total Time</label>
                                    <input id="total_time" v-model="manualForm.total_time" type="text" class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="e.g. 35 mins" />
                                </div>
                            </div>

                            <!-- Excerpt -->
                            <div class="mb-6">
                                <label for="excerpt" class="block text-sm font-medium text-gray-300 mb-2">
                                    Excerpt
                                </label>
                                <textarea
                                    id="excerpt"
                                    v-model="manualForm.excerpt"
                                    rows="3"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="A brief summary of the article..."
                                ></textarea>
                                <p v-if="manualForm.errors.excerpt" class="mt-2 text-sm text-red-500">{{ manualForm.errors.excerpt }}</p>
                            </div>

                            <!-- Content -->
                            <div class="mb-6">
                                <label for="content" class="block text-sm font-medium text-gray-300 mb-2">
                                    Content *
                                </label>
                                <textarea
                                    id="content"
                                    v-model="manualForm.content"
                                    rows="15"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-sm"
                                    placeholder="Write your article content here... (HTML supported)"
                                    required
                                ></textarea>
                                <p class="mt-1 text-sm text-gray-500">You can use HTML for formatting.</p>
                                <p v-if="manualForm.errors.content" class="mt-2 text-sm text-red-500">{{ manualForm.errors.content }}</p>
                            </div>

                            <!-- Status -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Status *
                                </label>
                                <div class="flex gap-6">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" v-model="manualForm.status" value="draft" class="mr-2 accent-emerald-500" />
                                        <span class="text-gray-300">Draft</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" v-model="manualForm.status" value="published" class="mr-2 accent-emerald-500" />
                                        <span class="text-gray-300">Published</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" v-model="manualForm.status" value="scheduled" class="mr-2 accent-emerald-500" />
                                        <span class="text-gray-300">Scheduled</span>
                                    </label>
                                </div>
                                <p v-if="manualForm.errors.status" class="mt-2 text-sm text-red-500">{{ manualForm.errors.status }}</p>
                            </div>

                            <!-- Published At (for scheduled) -->
                            <div v-if="manualForm.status === 'scheduled'" class="mb-6">
                                <label for="published_at" class="block text-sm font-medium text-gray-300 mb-2">
                                    Publish Date
                                </label>
                                <input
                                    id="published_at"
                                    v-model="manualForm.published_at"
                                    type="datetime-local"
                                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                />
                                <p v-if="manualForm.errors.published_at" class="mt-2 text-sm text-red-500">{{ manualForm.errors.published_at }}</p>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex items-center justify-end gap-4">
                                <button
                                    type="button"
                                    @click="creationMethod = null"
                                    class="px-6 py-2 text-gray-400 hover:text-white transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="manualForm.processing"
                                    class="px-6 py-3 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="manualForm.processing">Creating...</span>
                                    <span v-else>Create Article</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Step 2: AI Generation Form -->
                <div v-else-if="creationMethod === 'ai'">
                    <div class="flex items-center gap-4 mb-8">
                        <button 
                            @click="creationMethod = null"
                            class="text-gray-400 hover:text-white p-2 rounded-lg hover:bg-[#1a1a1a] transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Generate with AI</h1>
                            <p class="text-gray-400 mt-1">Let AI create your article</p>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="aiForm.errors.error" class="bg-red-900/50 border border-red-500 text-red-200 px-6 py-4 rounded-lg mb-6">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ aiForm.errors.error }}</span>
                        </div>
                    </div>

                    <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-8">
                        <form @submit.prevent="submitAI" class="space-y-6">
                            <!-- Topic -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Article Topic *
                                </label>
                                <input
                                    v-model="aiForm.topic"
                                    type="text"
                                    required
                                    placeholder="e.g., 'Best Italian Pasta Recipes for Beginners'"
                                    class="w-full px-4 py-3 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500 text-lg"
                                />
                                <p v-if="aiForm.errors.topic" class="mt-1 text-sm text-red-500">{{ aiForm.errors.topic }}</p>
                                <p class="mt-1 text-xs text-gray-500">Be specific for better results - AI will auto-categorize based on your topic</p>
                            </div>

                            <!-- Auto-Categorize Info -->
                            <div class="bg-gradient-to-r from-purple-900/30 to-indigo-900/30 border border-purple-800/50 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-purple-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                    <div>
                                        <h4 class="text-purple-300 font-medium">Smart Categorization</h4>
                                        <p class="text-sm text-purple-200/70 mt-1">
                                            AI will automatically determine the best category for your article based on the topic and your available categories.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Settings Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Article Length
                                    </label>
                                    <select
                                        v-model="aiForm.length"
                                        class="w-full px-4 py-2 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="short">Short (500-700 words)</option>
                                        <option value="medium">Medium (1000-1500 words)</option>
                                        <option value="long">Long (2000-3000 words)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Writing Tone
                                    </label>
                                    <select
                                        v-model="aiForm.tone"
                                        class="w-full px-4 py-2 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="conversational">Conversational</option>
                                        <option value="professional">Professional</option>
                                        <option value="casual">Casual</option>
                                        <option value="friendly">Friendly</option>
                                        <option value="formal">Formal</option>
                                    </select>
                                </div>

                                <div class="flex items-end">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="aiForm.auto_publish"
                                            type="checkbox"
                                            class="w-4 h-4 text-emerald-600 bg-[#252525] border-[#3a3a3a] rounded focus:ring-emerald-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-300">Auto-publish article</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Keywords -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Keywords (Optional)
                                </label>
                                <input
                                    v-model="aiForm.keywords"
                                    type="text"
                                    placeholder="e.g., italian cuisine, pasta, cooking tips"
                                    class="w-full px-4 py-2 bg-[#252525] border border-[#3a3a3a] rounded-lg text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">Separate keywords with commas</p>
                            </div>

                            <!-- Article Images Upload (Multiple) -->
                            <div class="mb-6 space-y-4">
                                <label class="block text-sm font-medium text-gray-300">
                                    Article Images (Optional)
                                </label>
                                <p class="text-xs text-gray-500">Upload multiple images. The first will be the featured image, second will be the secondary image.</p>
                                
                                <!-- Multiple Image Upload -->
                                <label 
                                    for="aiArticleImagesUpload"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleAIImagesDrop"
                                    :class="[
                                        'border-2 border-dashed rounded-lg p-8 text-center transition-all block',
                                        isUploading ? 'cursor-wait opacity-70' : 'cursor-pointer',
                                        isDragging ? 'border-emerald-500 bg-emerald-900/20' : 'border-[#3a3a3a] hover:border-[#4a4a4a]'
                                    ]"
                                >
                                    <input
                                        id="aiArticleImagesUpload"
                                        type="file"
                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                        multiple
                                        class="sr-only"
                                        @change="handleAIImagesSelect"
                                        :disabled="isUploading"
                                    />
                                    <div class="flex flex-col items-center">
                                        <div v-if="isUploading" class="w-12 h-12 bg-[#252525] rounded-lg flex items-center justify-center mb-3">
                                            <svg class="animate-spin w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        <div v-else class="w-12 h-12 bg-[#252525] rounded-lg flex items-center justify-center mb-3">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-white text-sm font-medium mb-1">
                                            {{ isUploading ? 'Uploading images...' : 'Drop images here or click to browse' }}
                                        </p>
                                        <p class="text-gray-500 text-xs">
                                            PNG, JPG, WebP up to 5MB each
                                        </p>
                                    </div>
                                </label>
                                
                                <!-- Uploaded Images Preview -->
                                <div v-if="uploadedImages.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div v-for="(img, index) in uploadedImages" :key="index" class="relative group">
                                        <div class="absolute -top-2 -right-2 z-10">
                                            <button 
                                                type="button"
                                                @click="removeImageAt(index)"
                                                class="w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center shadow-lg"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="aspect-square bg-[#252525] rounded-lg overflow-hidden border-2 border-[#3a3a3a]">
                                            <img :src="img" :alt="`Image ${index + 1}`" class="w-full h-full object-cover" />
                                        </div>
                                        <div class="mt-2 text-center">
                                            <p class="text-xs text-gray-400">{{ index === 0 ? 'Featured' : index === 1 ? 'Secondary' : `Image ${index + 1}` }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Background Processing Info -->
                            <div class="bg-gradient-to-r from-emerald-900/30 to-teal-900/30 border border-emerald-800/50 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <div>
                                        <h4 class="text-emerald-300 font-medium">Background Generation</h4>
                                        <p class="text-sm text-emerald-200/70 mt-1">
                                            Articles are generated in the background, so you can continue working or create multiple articles at once.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-[#2a2a2a]">
                                <button
                                    type="button"
                                    @click="creationMethod = null"
                                    class="px-6 py-2 text-gray-400 hover:text-white transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="aiForm.processing"
                                    class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <svg v-if="aiForm.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    {{ aiForm.processing ? 'Queueing...' : 'Generate Article' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import ImageUpload from '@/Components/ImageUpload.vue';

const page = usePage();

const props = defineProps({
    currentWebsite: Object,
    websites: Array,
    preselectedWebsite: [String, Number],
    hasApiKey: {
        type: Boolean,
        default: false
    },
    defaultTone: {
        type: String,
        default: 'conversational'
    }
});

const creationMethod = ref(null);

// Image upload states
const isDragging = ref(false);
const isUploading = ref(false);
const uploadError = ref('');
const uploadedImages = ref([]);

// Manual form
const manualForm = useForm({
    category_id: '',
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    featured_image: '',
    secondary_image: '',
    prep_time: '',
    cook_time: '',
    rest_time: '',
    total_time: '',
    status: 'draft',
    generation_type: 'manual',
    published_at: ''
});

// AI form
const aiForm = useForm({
    category_id: '',
    topic: '',
    tone: props.defaultTone,
    length: 'medium',
    keywords: '',
    featured_image: '',
    secondary_image: '',
    auto_publish: false,
    auto_categorize: true,
    background: true
});

// Handle multiple image upload
const handleImagesSelect = (event) => {
    const files = Array.from(event.target.files);
    uploadImages(files, 'manual');
};

const handleImagesDrop = (event) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files).filter(file => file.type.startsWith('image/'));
    uploadImages(files, 'manual');
};

const handleAIImagesSelect = (event) => {
    const files = Array.from(event.target.files);
    uploadImages(files, 'ai');
};

const handleAIImagesDrop = (event) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files).filter(file => file.type.startsWith('image/'));
    uploadImages(files, 'ai');
};

const uploadImages = async (files, method = 'manual') => {
    if (files.length === 0) return;
    
    uploadError.value = '';
    isUploading.value = true;

    for (const file of files) {
        if (file.size > 5 * 1024 * 1024) {
            uploadError.value = `${file.name} is too large. Maximum size is 5MB.`;
            continue;
        }

        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', 'article');

        try {
            const response = await fetch(route('image.upload'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData,
            });

            const data = await response.json();
            
            if (data.url) {
                uploadedImages.value.push(data.url);
                // Update appropriate form
                const targetForm = method === 'manual' ? manualForm : aiForm;
                
                if (uploadedImages.value.length === 1) {
                    targetForm.featured_image = data.url;
                } else if (uploadedImages.value.length === 2) {
                    targetForm.secondary_image = data.url;
                }
            } else {
                uploadError.value = data.error || 'Upload failed';
            }
        } catch (error) {
            uploadError.value = 'Upload failed. Please try again.';
            console.error('Upload error:', error);
        }
    }

    isUploading.value = false;
};

const removeImageAt = (index) => {
    uploadedImages.value.splice(index, 1);
    // Update both form fields to be safe
    manualForm.featured_image = uploadedImages.value[0] || '';
    manualForm.secondary_image = uploadedImages.value[1] || '';
    aiForm.featured_image = uploadedImages.value[0] || '';
    aiForm.secondary_image = uploadedImages.value[1] || '';
};

const submitManual = () => {
    manualForm.post(route('superadmin.articles.store', { website: props.currentWebsite?.id }));
};

const submitAI = () => {
    aiForm.post(route('superadmin.ai-articles.generate', { website: props.currentWebsite?.id }));
};
</script>
