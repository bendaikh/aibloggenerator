<template>
    <div v-if="hasRecipeData" ref="cardElement" class="recipe-card-container my-12" id="recipe-card">
        <div class="bg-[#F8FEFA] rounded-3xl shadow-xl shadow-emerald-100/50 overflow-hidden border border-emerald-100">
            <!-- Recipe Header -->
            <div class="px-8 pt-8 pb-6 border-b border-emerald-100/50">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ recipeTitle }}</h2>
                <p v-if="recipeDescription" class="text-gray-500 text-sm leading-relaxed">{{ recipeDescription }}</p>
            </div>

            <!-- Time Metadata Row -->
            <div v-if="hasTimeMetadata" class="px-8 py-4 border-b border-emerald-100/50 bg-emerald-50/30">
                <div class="flex flex-wrap justify-center gap-8 md:gap-16">
                    <div v-if="prepTime" class="flex flex-col items-center">
                        <div class="flex items-center gap-2 text-gray-600 mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-semibold uppercase tracking-wide">Prep Time</span>
                        </div>
                        <span class="text-gray-900 font-medium text-sm">{{ prepTime }}</span>
                    </div>
                    <div v-if="cookTime" class="flex flex-col items-center">
                        <div class="flex items-center gap-2 text-gray-600 mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            </svg>
                            <span class="text-xs font-semibold uppercase tracking-wide">Cook Time</span>
                        </div>
                        <span class="text-gray-900 font-medium text-sm">{{ cookTime }}</span>
                    </div>
                    <div v-if="totalTime" class="flex flex-col items-center">
                        <div class="flex items-center gap-2 text-gray-600 mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-semibold uppercase tracking-wide">Total Time</span>
                        </div>
                        <span class="text-gray-900 font-medium text-sm">{{ totalTime }}</span>
                    </div>
                </div>
            </div>

            <!-- Additional Metadata -->
            <div v-if="hasAdditionalMetadata" class="px-8 py-4 border-b border-emerald-100/50 text-sm">
                <div class="space-y-2">
                    <div v-if="author" class="flex items-center gap-2">
                        <span class="text-emerald-600 font-semibold">👤 By:</span>
                        <span class="text-teal-600">{{ author }}</span>
                    </div>
                    <div v-if="category" class="flex items-center gap-2">
                        <span class="text-emerald-600 font-semibold">📂 Category:</span>
                        <span class="text-teal-600">{{ category }}</span>
                    </div>
                    <div v-if="skillLevel" class="flex items-center gap-2">
                        <span class="text-emerald-600 font-semibold">📊 Skill Level:</span>
                        <span class="text-gray-700">{{ skillLevel }}</span>
                    </div>
                    <div v-if="cuisine" class="flex items-center gap-2">
                        <span class="text-emerald-600 font-semibold">🍽️ Cuisine:</span>
                        <span class="text-gray-700">{{ cuisine }}</span>
                    </div>
                    <div v-if="servings" class="flex items-center gap-2">
                        <span class="text-emerald-600 font-semibold">🍴 Yield:</span>
                        <span class="text-teal-600">{{ servings }} Servings</span>
                    </div>
                    <div v-if="dietaryCategories.length > 0" class="flex items-center gap-2">
                        <span class="text-emerald-600 font-semibold">🥗 Dietary Categories:</span>
                        <span class="text-teal-600">{{ dietaryCategories.join(', ') }}</span>
                    </div>
                </div>
            </div>

            <!-- Recipe Content -->
            <div class="px-8 py-8">
                <!-- Ingredients and Instructions Section -->
                <div v-if="ingredients.length > 0 || instructions.length > 0" class="mb-10">
                    <!-- Ingredients -->
                    <div v-if="ingredients.length > 0" class="mb-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Ingredients</h3>
                        <p v-if="ingredientSubheader" class="text-gray-500 text-sm mb-4 flex items-center gap-1">
                            <span class="text-gray-400">◆</span>
                            {{ ingredientSubheader }}
                        </p>
                        <div class="space-y-3">
                            <div v-for="(ingredient, index) in ingredients" :key="index" class="flex items-start gap-3">
                                <span :class="getIngredientBadgeClass(index)" class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold text-white shadow-sm">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                                <span class="text-gray-700 leading-relaxed flex-1 pt-1">{{ ingredient }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions (shown right after ingredients) -->
                    <div v-if="instructions.length > 0" class="pt-6 border-t border-emerald-100/50">
                        <h3 class="text-xl font-bold text-gray-900 mb-5">Instructions</h3>
                        <div class="space-y-6">
                            <div v-for="(instruction, index) in instructions" :key="index">
                                <div :class="getStepBadgeClass(index)" class="inline-flex items-center px-3 py-1 rounded-md text-white text-xs font-bold mb-2">
                                    Step {{ String(index + 1).padStart(2, '0') }}
                                </div>
                                <p class="text-gray-700 leading-relaxed text-sm pl-1">{{ instruction }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div v-if="notes.length > 0">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Notes</h3>
                    <div class="space-y-2">
                        <div v-for="(note, index) in notes" :key="index" class="flex items-start gap-2 text-sm">
                            <span class="flex-shrink-0 w-5 h-5 bg-rose-500 rounded-full flex items-center justify-center text-white text-xs font-bold">!</span>
                            <span class="text-gray-700 leading-relaxed">{{ note }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';

const cardElement = ref(null);

defineExpose({
    scrollIntoView: (options) => {
        if (cardElement.value) {
            cardElement.value.scrollIntoView(options);
        }
    }
});

const props = defineProps({
    content: {
        type: String,
        required: true
    },
    title: {
        type: String,
        default: ''
    },
    gradients: {
        type: Object,
        default: null
    }
});

const ingredients = ref([]);
const instructions = ref([]);
const notes = ref([]);
const prepTime = ref('');
const cookTime = ref('');
const totalTime = ref('');
const servings = ref('');
const recipeTitle = ref('');
const recipeDescription = ref('');
const author = ref('');
const category = ref('');
const skillLevel = ref('');
const cuisine = ref('');
const dietaryCategories = ref([]);
const ingredientSubheader = ref('Core Ingredients');

// Default colorful badge classes for ingredients (emerald/teal theme)
const defaultIngredientBadgeColors = [
    'bg-gradient-to-br from-emerald-400 to-emerald-600',
    'bg-gradient-to-br from-teal-400 to-teal-600',
    'bg-gradient-to-br from-emerald-500 to-teal-500',
    'bg-gradient-to-br from-teal-500 to-emerald-600',
    'bg-gradient-to-br from-emerald-400 to-teal-500',
    'bg-gradient-to-br from-teal-400 to-emerald-500',
];

// Default colorful badge classes for steps (emerald/teal theme)
const defaultStepBadgeColors = [
    'bg-gradient-to-r from-emerald-500 to-emerald-600',
    'bg-gradient-to-r from-teal-500 to-teal-600',
    'bg-gradient-to-r from-emerald-600 to-teal-500',
    'bg-gradient-to-r from-teal-600 to-emerald-500',
    'bg-gradient-to-r from-emerald-400 to-teal-400',
    'bg-gradient-to-r from-teal-400 to-emerald-400',
];

// Computed gradients from props or defaults
const ingredientBadgeColors = computed(() => {
    if (props.gradients && props.gradients.ingredient_gradients && Array.isArray(props.gradients.ingredient_gradients)) {
        return props.gradients.ingredient_gradients;
    }
    return defaultIngredientBadgeColors;
});

const stepBadgeColors = computed(() => {
    if (props.gradients && props.gradients.step_gradients && Array.isArray(props.gradients.step_gradients)) {
        return props.gradients.step_gradients;
    }
    return defaultStepBadgeColors;
});

const getIngredientBadgeClass = (index) => {
    return ingredientBadgeColors.value[index % ingredientBadgeColors.value.length];
};

const getStepBadgeClass = (index) => {
    return stepBadgeColors.value[index % stepBadgeColors.value.length];
};

const hasRecipeData = computed(() => {
    return ingredients.value.length > 0 || instructions.value.length > 0;
});

const hasTimeMetadata = computed(() => {
    return prepTime.value || cookTime.value || totalTime.value;
});

const hasAdditionalMetadata = computed(() => {
    return author.value || category.value || skillLevel.value || cuisine.value || servings.value || dietaryCategories.value.length > 0;
});

onMounted(() => {
    parseRecipeContent();
});

const parseRecipeContent = () => {
    if (!props.content) return;

    // Create a temporary DOM element to parse HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = props.content;

    // Find Ingredients section
    const ingredientsSection = findSection(tempDiv, ['ingredients', 'ingredient']);
    if (ingredientsSection) {
        ingredients.value = extractListItems(ingredientsSection);
    }

    // Find Instructions section
    const instructionsSection = findSection(tempDiv, ['instructions', 'instruction', 'steps', 'step', 'directions', 'direction']);
    if (instructionsSection) {
        instructions.value = extractListItems(instructionsSection);
    }

    // Find Notes section
    const notesSection = findSection(tempDiv, ['notes', 'note', 'tips', 'tip', 'pro tips']);
    if (notesSection) {
        notes.value = extractListItems(notesSection);
    }

    // Extract metadata from content
    extractMetadata(tempDiv);

    // Set recipe title and description
    recipeTitle.value = props.title || 'Recipe';
    recipeDescription.value = extractDescription(tempDiv);
};

const findSection = (container, keywords) => {
    // Look for h2/h3 headers with matching keywords
    const headers = container.querySelectorAll('h2, h3');
    
    for (const header of headers) {
        const headerText = header.textContent.toLowerCase().trim();
        
        for (const keyword of keywords) {
            if (headerText.includes(keyword)) {
                // Get the next sibling element(s) that contain the list
                let current = header.nextElementSibling;
                while (current) {
                    if (current.tagName === 'UL' || current.tagName === 'OL') {
                        return current;
                    }
                    // If we hit another h2/h3, stop
                    if (current.tagName === 'H2' || current.tagName === 'H3') {
                        break;
                    }
                    // Skip empty text nodes and other elements
                    current = current.nextElementSibling;
                }
                
                // If no immediate sibling, check if header is in a div/container
                // and look for lists within the same parent
                const parent = header.parentElement;
                if (parent) {
                    const lists = parent.querySelectorAll('ul, ol');
                    for (const list of lists) {
                        // Check if this list comes after the header
                        if (list.compareDocumentPosition(header) & Node.DOCUMENT_POSITION_FOLLOWING) {
                            return list;
                        }
                    }
                }
            }
        }
    }
    
    return null;
};

const extractListItems = (listElement) => {
    const items = [];
    const listItems = listElement.querySelectorAll('li');
    
    listItems.forEach(li => {
        const text = li.textContent.trim();
        if (text) {
            items.push(text);
        }
    });
    
    return items;
};

const extractMetadata = (container) => {
    const text = container.textContent;
    const textLower = text.toLowerCase();
    
    // Look for time patterns - more flexible matching
    const prepTimeMatch = textLower.match(/(?:prep|preparation)\s*time[:\s]*(\d+\s*(?:min|minute|minutes|hour|hours|hr|hrs|h|m))/i);
    if (prepTimeMatch) {
        prepTime.value = prepTimeMatch[1].trim();
    }
    
    const cookTimeMatch = textLower.match(/(?:cook|cooking)\s*time[:\s]*(\d+\s*(?:min|minute|minutes|hour|hours|hr|hrs|h|m))/i);
    if (cookTimeMatch) {
        cookTime.value = cookTimeMatch[1].trim();
    }
    
    const totalTimeMatch = textLower.match(/(?:total)\s*time[:\s]*(\d+\s*(?:min|minute|minutes|hour|hours|hr|hrs|h|m))/i);
    if (totalTimeMatch) {
        totalTime.value = totalTimeMatch[1].trim();
    }
    
    // Look for servings/yield - more flexible
    const servingsMatch = textLower.match(/(?:servings?|yield|makes?|serves?)[:\s]*(\d+)/i);
    if (servingsMatch) {
        servings.value = servingsMatch[1];
    }
    
    // Extract author/by
    const authorMatch = text.match(/(?:by|author|chef)[:\s]*([A-Za-z\s]+?)(?:\n|<|$)/i);
    if (authorMatch) {
        author.value = authorMatch[1].trim();
    }
    
    // Extract category
    const categoryMatch = text.match(/(?:category|type)[:\s]*([A-Za-z\s&]+?)(?:\n|<|$)/i);
    if (categoryMatch) {
        category.value = categoryMatch[1].trim();
    }
    
    // Extract skill level
    const skillMatch = textLower.match(/(?:skill\s*level|difficulty)[:\s]*(easy|medium|hard|beginner|intermediate|advanced)/i);
    if (skillMatch) {
        skillLevel.value = skillMatch[1].charAt(0).toUpperCase() + skillMatch[1].slice(1);
    }
    
    // Extract cuisine
    const cuisineMatch = text.match(/(?:cuisine)[:\s]*([A-Za-z\s]+?)(?:\n|<|$)/i);
    if (cuisineMatch) {
        cuisine.value = cuisineMatch[1].trim();
    }
    
    // Extract dietary categories (vegetarian, vegan, gluten-free, etc.)
    const dietaryPatterns = [
        'vegetarian', 'vegan', 'gluten[- ]?free', 'dairy[- ]?free', 
        'nut[- ]?free', 'keto', 'paleo', 'low[- ]?carb', 'sugar[- ]?free',
        'organic', 'whole30', 'kosher', 'halal'
    ];
    const foundDietary = [];
    for (const pattern of dietaryPatterns) {
        const regex = new RegExp(pattern, 'i');
        if (regex.test(textLower)) {
            const match = textLower.match(regex);
            if (match) {
                // Format the dietary category nicely
                let formatted = match[0].replace(/-/g, '-').replace(/\s+/g, ' ');
                formatted = formatted.split(' ').map(word => 
                    word.charAt(0).toUpperCase() + word.slice(1)
                ).join(' ');
                if (!foundDietary.includes(formatted)) {
                    foundDietary.push(formatted);
                }
            }
        }
    }
    dietaryCategories.value = foundDietary;
};

const extractDescription = (container) => {
    // Try to find a description paragraph before the recipe sections
    const paragraphs = container.querySelectorAll('p');
    for (const p of paragraphs) {
        const text = p.textContent.trim();
        if (text.length > 50 && text.length < 300) {
            // Check if it's not part of ingredients/instructions
            const lowerText = text.toLowerCase();
            if (!lowerText.includes('ingredient') && 
                !lowerText.includes('instruction') && 
                !lowerText.includes('step')) {
                return text;
            }
        }
    }
    return '';
};
</script>

<style scoped>
.recipe-card-container {
    scroll-margin-top: 2rem;
}
</style>

