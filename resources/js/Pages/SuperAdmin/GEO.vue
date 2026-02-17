<script setup>
import { useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    currentWebsite: {
        type: Object,
        default: () => null
    }
});

const showKeyFactsInfo = ref(false);
const showQueriesInfo = ref(false);
const showEntityInfo = ref(false);

const form = useForm({
    geo_settings: {
        // Core GEO Settings
        structured_data_enhanced: props.currentWebsite?.geo_settings?.structured_data_enhanced ?? true,
        ai_readability_optimized: props.currentWebsite?.geo_settings?.ai_readability_optimized ?? true,
        fact_checking_enabled: props.currentWebsite?.geo_settings?.fact_checking_enabled ?? true,
        
        // Citation & Attribution
        citation_format: props.currentWebsite?.geo_settings?.citation_format || 'apa',
        content_attribution: props.currentWebsite?.geo_settings?.content_attribution || '',
        author_credentials: props.currentWebsite?.geo_settings?.author_credentials || '',
        expertise_areas: props.currentWebsite?.geo_settings?.expertise_areas || [],
        
        // Content Optimization
        ai_summary: props.currentWebsite?.geo_settings?.ai_summary || '',
        technical_depth: props.currentWebsite?.geo_settings?.technical_depth || 'intermediate',
        
        // Structured Data for AI
        key_facts: props.currentWebsite?.geo_settings?.key_facts || [],
        conversational_queries: props.currentWebsite?.geo_settings?.conversational_queries || [],
        entity_definitions: props.currentWebsite?.geo_settings?.entity_definitions || [],
        
        // AI Crawler Settings
        api_access_enabled: props.currentWebsite?.geo_settings?.api_access_enabled ?? true,
        llm_training_opt_out: props.currentWebsite?.geo_settings?.llm_training_opt_out ?? false,
        preferred_llms: props.currentWebsite?.geo_settings?.preferred_llms || ['chatgpt', 'perplexity', 'gemini', 'claude'],
    }
});

// Key Facts Management
const newKeyFact = ref('');
const addKeyFact = () => {
    if (newKeyFact.value.trim()) {
        form.geo_settings.key_facts.push(newKeyFact.value.trim());
        newKeyFact.value = '';
    }
};
const removeKeyFact = (index) => {
    form.geo_settings.key_facts.splice(index, 1);
};

// Conversational Queries Management
const newQuery = ref('');
const addQuery = () => {
    if (newQuery.value.trim()) {
        form.geo_settings.conversational_queries.push(newQuery.value.trim());
        newQuery.value = '';
    }
};
const removeQuery = (index) => {
    form.geo_settings.conversational_queries.splice(index, 1);
};

// Entity Definitions Management
const newEntity = ref({ name: '', type: '', description: '' });
const addEntity = () => {
    if (newEntity.value.name && newEntity.value.type && newEntity.value.description) {
        form.geo_settings.entity_definitions.push({ ...newEntity.value });
        newEntity.value = { name: '', type: '', description: '' };
    }
};
const removeEntity = (index) => {
    form.geo_settings.entity_definitions.splice(index, 1);
};

// Expertise Areas Management
const newExpertise = ref('');
const addExpertise = () => {
    if (newExpertise.value.trim()) {
        form.geo_settings.expertise_areas.push(newExpertise.value.trim());
        newExpertise.value = '';
    }
};
const removeExpertise = (index) => {
    form.geo_settings.expertise_areas.splice(index, 1);
};

const submit = () => {
    form.post(route('superadmin.geo.update', props.currentWebsite.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="GEO - Generative Engine Optimization" />

    <SuperAdminLayout :currentWebsite="currentWebsite">
        <div class="p-8">
            <div class="mb-8">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-white">GEO - Generative Engine Optimization</h1>
                        <p class="text-gray-400 mt-1">Optimize your content for AI-powered search engines like ChatGPT, Perplexity, Gemini, and Claude</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="max-w-5xl space-y-6">
                <!-- What is GEO? Info Card -->
                <div class="bg-gradient-to-r from-purple-900/20 to-pink-900/20 border border-purple-800/50 rounded-xl p-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-purple-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="text-white font-semibold text-lg mb-2">What is GEO?</h3>
                            <p class="text-purple-200/70 text-sm leading-relaxed">
                                <strong class="text-purple-200">Generative Engine Optimization (GEO)</strong> is the practice of optimizing your content to be discovered, understood, and cited by AI-powered search engines and large language models (LLMs). 
                                Unlike traditional SEO which focuses on Google rankings, GEO ensures your content is accurately parsed and presented by ChatGPT, Perplexity AI, Google Gemini, Claude, and similar AI tools.
                            </p>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="flex items-center gap-2 text-purple-200/70 text-xs">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Structured data for AI parsing
                                </div>
                                <div class="flex items-center gap-2 text-purple-200/70 text-xs">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Clear entity definitions
                                </div>
                                <div class="flex items-center gap-2 text-purple-200/70 text-xs">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Conversational query optimization
                                </div>
                                <div class="flex items-center gap-2 text-purple-200/70 text-xs">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Source attribution & credibility
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Core GEO Settings -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-white font-semibold text-lg mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Core GEO Settings
                    </h3>

                    <div class="space-y-4">
                        <!-- Enhanced Structured Data -->
                        <label class="flex items-center justify-between p-4 bg-[#252525] rounded-lg cursor-pointer hover:bg-[#2a2a2a] transition">
                            <div class="flex-1">
                                <div class="text-white font-medium">Enhanced Structured Data</div>
                                <div class="text-gray-400 text-sm mt-1">Add rich Schema.org markup optimized for AI comprehension</div>
                            </div>
                            <input type="checkbox" v-model="form.geo_settings.structured_data_enhanced" class="w-5 h-5 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500 focus:ring-2">
                        </label>

                        <!-- AI Readability -->
                        <label class="flex items-center justify-between p-4 bg-[#252525] rounded-lg cursor-pointer hover:bg-[#2a2a2a] transition">
                            <div class="flex-1">
                                <div class="text-white font-medium">AI Readability Optimization</div>
                                <div class="text-gray-400 text-sm mt-1">Structure content for optimal AI parsing and understanding</div>
                            </div>
                            <input type="checkbox" v-model="form.geo_settings.ai_readability_optimized" class="w-5 h-5 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500 focus:ring-2">
                        </label>

                        <!-- Fact Checking -->
                        <label class="flex items-center justify-between p-4 bg-[#252525] rounded-lg cursor-pointer hover:bg-[#2a2a2a] transition">
                            <div class="flex-1">
                                <div class="text-white font-medium">Fact Checking & Verification</div>
                                <div class="text-gray-400 text-sm mt-1">Enable structured facts for AI verification and trust signals</div>
                            </div>
                            <input type="checkbox" v-model="form.geo_settings.fact_checking_enabled" class="w-5 h-5 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500 focus:ring-2">
                        </label>
                    </div>
                </div>

                <!-- Citation & Attribution -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-white font-semibold text-lg mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Citation & Attribution
                    </h3>

                    <div class="space-y-4">
                        <!-- Citation Format -->
                        <div>
                            <label class="text-gray-300 text-sm block mb-2">Preferred Citation Format</label>
                            <select v-model="form.geo_settings.citation_format" class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="apa">APA (American Psychological Association)</option>
                                <option value="mla">MLA (Modern Language Association)</option>
                                <option value="chicago">Chicago/Turabian</option>
                                <option value="harvard">Harvard</option>
                                <option value="ieee">IEEE</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">How AI models should cite your content in responses</p>
                        </div>

                        <!-- Content Attribution -->
                        <div>
                            <label class="text-gray-300 text-sm block mb-2">Content Attribution Line</label>
                            <input 
                                v-model="form.geo_settings.content_attribution"
                                type="text"
                                placeholder="e.g., Content by [Your Name/Brand], Expert in [Topic]"
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            />
                            <p class="mt-1 text-xs text-gray-500">How you want to be attributed when AI cites your content</p>
                        </div>

                        <!-- Author Credentials -->
                        <div>
                            <label class="text-gray-300 text-sm block mb-2">Author Credentials & Bio</label>
                            <textarea
                                v-model="form.geo_settings.author_credentials"
                                rows="3"
                                placeholder="e.g., [Name] is a certified nutritionist with 10+ years of experience in healthy cooking..."
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">Establish authority and expertise for AI trust signals</p>
                        </div>

                        <!-- Expertise Areas -->
                        <div>
                            <label class="text-gray-300 text-sm block mb-2">Expertise Areas</label>
                            <div class="flex gap-2 mb-2">
                                <input 
                                    v-model="newExpertise"
                                    @keyup.enter="addExpertise"
                                    type="text"
                                    placeholder="e.g., Italian Cuisine, Baking, Meal Prep"
                                    class="flex-1 bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                />
                                <button 
                                    type="button"
                                    @click="addExpertise"
                                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
                                >
                                    Add
                                </button>
                            </div>
                            <div v-if="form.geo_settings.expertise_areas.length > 0" class="flex flex-wrap gap-2">
                                <div v-for="(area, index) in form.geo_settings.expertise_areas" :key="index" class="flex items-center gap-2 bg-purple-900/30 border border-purple-700/50 text-purple-200 px-3 py-1 rounded-lg text-sm">
                                    {{ area }}
                                    <button type="button" @click="removeExpertise(index)" class="text-purple-400 hover:text-purple-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Topics where you/your brand has expertise (helps AI understand your authority)</p>
                        </div>
                    </div>
                </div>

                <!-- Content Optimization for AI -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-white font-semibold text-lg mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Content Optimization for AI
                    </h3>

                    <div class="space-y-4">
                        <!-- AI Summary -->
                        <div>
                            <label class="text-gray-300 text-sm block mb-2">AI-Friendly Website Summary</label>
                            <textarea
                                v-model="form.geo_settings.ai_summary"
                                rows="4"
                                placeholder="Write a clear, concise summary of your website's purpose and content for AI models..."
                                class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">A clear description that AI models can use to understand your website's content and expertise</p>
                        </div>

                        <!-- Technical Depth -->
                        <div>
                            <label class="text-gray-300 text-sm block mb-2">Content Technical Depth</label>
                            <select v-model="form.geo_settings.technical_depth" class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="beginner">Beginner-Friendly</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="expert">Expert/Technical</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Helps AI understand your content complexity level and target audience</p>
                        </div>
                    </div>
                </div>

                <!-- Structured Data for AI (Key Facts) -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-white font-semibold text-lg flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Key Facts & Statistics
                        </h3>
                        <button type="button" @click="showKeyFactsInfo = !showKeyFactsInfo" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="showKeyFactsInfo" class="mb-4 bg-amber-900/20 border border-amber-700/50 rounded-lg p-4 text-amber-200 text-sm">
                        <p><strong>Why Key Facts Matter:</strong> AI models extract and cite specific facts from your content. By providing structured key facts, you increase the chances of being cited accurately.</p>
                        <p class="mt-2"><strong>Example:</strong> "Our recipes use 30% less sugar than traditional methods" or "Average prep time: 15 minutes"</p>
                    </div>

                    <div class="flex gap-2 mb-3">
                        <input 
                            v-model="newKeyFact"
                            @keyup.enter="addKeyFact"
                            type="text"
                            placeholder="e.g., Recipes tested by professional chefs"
                            class="flex-1 bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        />
                        <button 
                            type="button"
                            @click="addKeyFact"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition"
                        >
                            Add Fact
                        </button>
                    </div>

                    <div v-if="form.geo_settings.key_facts.length > 0" class="space-y-2">
                        <div v-for="(fact, index) in form.geo_settings.key_facts" :key="index" class="flex items-center gap-3 bg-[#252525] p-3 rounded-lg">
                            <svg class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="flex-1 text-white">{{ fact }}</span>
                            <button type="button" @click="removeKeyFact(index)" class="text-red-400 hover:text-red-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-sm italic">No key facts added yet. Add structured facts that AI models can extract and cite.</p>
                </div>

                <!-- Conversational Queries -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-white font-semibold text-lg flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            Conversational Queries Your Content Answers
                        </h3>
                        <button type="button" @click="showQueriesInfo = !showQueriesInfo" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="showQueriesInfo" class="mb-4 bg-cyan-900/20 border border-cyan-700/50 rounded-lg p-4 text-cyan-200 text-sm">
                        <p><strong>Why This Matters:</strong> AI models respond to natural language questions. By listing the specific questions your content answers, you help AI understand when to reference your site.</p>
                        <p class="mt-2"><strong>Examples:</strong></p>
                        <ul class="list-disc ml-5 mt-1 space-y-1">
                            <li>"How do I make gluten-free pasta from scratch?"</li>
                            <li>"What's the best way to store fresh herbs?"</li>
                            <li>"Can I substitute butter with olive oil in baking?"</li>
                        </ul>
                    </div>

                    <div class="flex gap-2 mb-3">
                        <input 
                            v-model="newQuery"
                            @keyup.enter="addQuery"
                            type="text"
                            placeholder="e.g., How do I make the perfect chocolate chip cookies?"
                            class="flex-1 bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        />
                        <button 
                            type="button"
                            @click="addQuery"
                            class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition"
                        >
                            Add Query
                        </button>
                    </div>

                    <div v-if="form.geo_settings.conversational_queries.length > 0" class="space-y-2">
                        <div v-for="(query, index) in form.geo_settings.conversational_queries" :key="index" class="flex items-center gap-3 bg-[#252525] p-3 rounded-lg">
                            <svg class="w-5 h-5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="flex-1 text-white">{{ query }}</span>
                            <button type="button" @click="removeQuery(index)" class="text-red-400 hover:text-red-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-sm italic">No queries added yet. List natural language questions your content answers.</p>
                </div>

                <!-- Entity Definitions -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-white font-semibold text-lg flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Entity Definitions
                        </h3>
                        <button type="button" @click="showEntityInfo = !showEntityInfo" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="showEntityInfo" class="mb-4 bg-pink-900/20 border border-pink-700/50 rounded-lg p-4 text-pink-200 text-sm">
                        <p><strong>Why Entity Definitions Matter:</strong> AI models need to understand specific people, brands, products, or concepts in your content. Clear entity definitions help AI accurately represent your content.</p>
                        <p class="mt-2"><strong>Entity Types:</strong> Person, Organization, Product, Recipe, Tool, Ingredient, Technique, etc.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <input 
                            v-model="newEntity.name"
                            type="text"
                            placeholder="Entity name"
                            class="bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        />
                        <select v-model="newEntity.type" class="bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select type</option>
                            <option value="Person">Person</option>
                            <option value="Organization">Organization</option>
                            <option value="Product">Product</option>
                            <option value="Recipe">Recipe</option>
                            <option value="Tool">Tool/Equipment</option>
                            <option value="Ingredient">Ingredient</option>
                            <option value="Technique">Technique/Method</option>
                        </select>
                        <input 
                            v-model="newEntity.description"
                            type="text"
                            placeholder="Brief description"
                            class="bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        />
                    </div>
                    <button 
                        type="button"
                        @click="addEntity"
                        class="w-full px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg transition"
                    >
                        Add Entity Definition
                    </button>

                    <div v-if="form.geo_settings.entity_definitions.length > 0" class="mt-4 space-y-2">
                        <div v-for="(entity, index) in form.geo_settings.entity_definitions" :key="index" class="bg-[#252525] p-4 rounded-lg">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-white font-medium">{{ entity.name }}</span>
                                        <span class="px-2 py-0.5 bg-pink-900/30 border border-pink-700/50 text-pink-300 text-xs rounded">{{ entity.type }}</span>
                                    </div>
                                    <p class="text-gray-400 text-sm">{{ entity.description }}</p>
                                </div>
                                <button type="button" @click="removeEntity(index)" class="text-red-400 hover:text-red-300 ml-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="mt-3 text-gray-500 text-sm italic">No entities defined yet. Add clear definitions for people, products, or concepts in your content.</p>
                </div>

                <!-- AI Crawler & LLM Settings -->
                <div class="bg-[#1a1a1a] rounded-2xl border border-[#2a2a2a] p-6">
                    <h3 class="text-white font-semibold text-lg mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                        AI Crawler & LLM Settings
                    </h3>

                    <div class="space-y-4">
                        <!-- API Access -->
                        <label class="flex items-center justify-between p-4 bg-[#252525] rounded-lg cursor-pointer hover:bg-[#2a2a2a] transition">
                            <div class="flex-1">
                                <div class="text-white font-medium">Allow AI Crawler API Access</div>
                                <div class="text-gray-400 text-sm mt-1">Enable structured API access for AI models to better understand your content</div>
                            </div>
                            <input type="checkbox" v-model="form.geo_settings.api_access_enabled" class="w-5 h-5 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500 focus:ring-2">
                        </label>

                        <!-- LLM Training Opt-Out -->
                        <label class="flex items-center justify-between p-4 bg-[#252525] rounded-lg cursor-pointer hover:bg-[#2a2a2a] transition">
                            <div class="flex-1">
                                <div class="text-white font-medium">Opt-Out of LLM Training Data</div>
                                <div class="text-gray-400 text-sm mt-1">Request that AI models not use your content for training (via TDM reservation)</div>
                            </div>
                            <input type="checkbox" v-model="form.geo_settings.llm_training_opt_out" class="w-5 h-5 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500 focus:ring-2">
                        </label>

                        <!-- Preferred LLMs -->
                        <div>
                            <label class="text-gray-300 text-sm block mb-2">Preferred AI Models to Target</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 p-3 bg-[#252525] rounded-lg cursor-pointer hover:bg-[#2a2a2a] transition">
                                    <input type="checkbox" value="chatgpt" v-model="form.geo_settings.preferred_llms" class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded">
                                    <span class="text-white text-sm">ChatGPT (OpenAI)</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 bg-[#252525] rounded-lg cursor-pointer hover:bg-[#2a2a2a] transition">
                                    <input type="checkbox" value="perplexity" v-model="form.geo_settings.preferred_llms" class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded">
                                    <span class="text-white text-sm">Perplexity AI</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 bg-[#252525] rounded-lg cursor-pointer hover:bg-[#2a2a2a] transition">
                                    <input type="checkbox" value="gemini" v-model="form.geo_settings.preferred_llms" class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded">
                                    <span class="text-white text-sm">Google Gemini</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 bg-[#252525] rounded-lg cursor-pointer hover:bg-[#2a2a2a] transition">
                                    <input type="checkbox" value="claude" v-model="form.geo_settings.preferred_llms" class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded">
                                    <span class="text-white text-sm">Claude (Anthropic)</span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Select which AI models you want to optimize for</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : 'Save GEO Settings' }}
                    </button>
                </div>
            </form>
        </div>
    </SuperAdminLayout>
</template>

