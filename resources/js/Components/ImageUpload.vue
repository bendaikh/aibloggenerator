<template>
    <div class="image-upload">
        <label v-if="label" class="block text-sm font-medium text-gray-300 mb-2">
            {{ label }}
        </label>
        
        <!-- Current Image Preview -->
        <div v-if="modelValue && !uploading" class="mb-3">
            <div class="relative inline-block">
                <img 
                    :src="modelValue" 
                    :alt="label || 'Image preview'"
                    class="h-32 w-auto rounded-lg object-cover border border-[#3a3a3a]"
                    @error="handleImageError"
                />
                <button
                    type="button"
                    @click="removeImage"
                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors"
                    title="Remove image"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Upload Area -->
        <div 
            v-if="!modelValue || uploading"
            class="relative"
        >
            <div
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
                :class="[
                    'border-2 border-dashed rounded-lg p-6 text-center transition-all cursor-pointer',
                    isDragging ? 'border-emerald-500 bg-emerald-900/20' : 'border-[#3a3a3a] hover:border-[#4a4a4a]',
                    uploading ? 'pointer-events-none opacity-60' : ''
                ]"
                @click="triggerFileInput"
            >
                <input
                    ref="fileInput"
                    type="file"
                    :accept="acceptedTypes"
                    class="hidden"
                    @change="handleFileSelect"
                />

                <div v-if="uploading" class="flex flex-col items-center">
                    <svg class="animate-spin h-8 w-8 text-emerald-500 mb-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-400 text-sm">Uploading...</p>
                </div>

                <div v-else class="flex flex-col items-center">
                    <div class="w-12 h-12 bg-[#252525] rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-white text-sm font-medium mb-1">
                        Drop an image here or click to browse
                    </p>
                    <p class="text-gray-500 text-xs">
                        {{ hint || 'PNG, JPG, GIF, WebP up to 5MB' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- URL Input Toggle (Optional fallback) -->
        <div v-if="allowUrl && !modelValue" class="mt-3">
            <button
                type="button"
                @click="showUrlInput = !showUrlInput"
                class="text-sm text-gray-400 hover:text-white flex items-center gap-1"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                Or use image URL
            </button>
            <div v-if="showUrlInput" class="mt-2">
                <input
                    v-model="urlInput"
                    type="url"
                    placeholder="https://example.com/image.jpg"
                    class="w-full bg-[#252525] border border-[#3a3a3a] text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                    @blur="handleUrlInput"
                    @keydown.enter.prevent="handleUrlInput"
                />
            </div>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-sm text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    label: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'article' // article, website, category
    },
    hint: {
        type: String,
        default: ''
    },
    allowUrl: {
        type: Boolean,
        default: true
    },
    acceptedTypes: {
        type: String,
        default: 'image/jpeg,image/png,image/gif,image/webp'
    },
    maxSize: {
        type: Number,
        default: 5 * 1024 * 1024 // 5MB
    }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const isDragging = ref(false);
const uploading = ref(false);
const error = ref('');
const showUrlInput = ref(false);
const urlInput = ref('');
const imagePath = ref(''); // Track the uploaded file path for deletion

const triggerFileInput = () => {
    if (!uploading.value) {
        fileInput.value?.click();
    }
};

const handleFileSelect = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        uploadFile(file);
    }
    // Reset input so same file can be selected again
    event.target.value = '';
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (file) {
        uploadFile(file);
    }
};

const uploadFile = async (file) => {
    error.value = '';

    // Validate file type
    const validTypes = props.acceptedTypes.split(',');
    if (!validTypes.includes(file.type)) {
        error.value = 'Invalid file type. Please upload an image (PNG, JPG, GIF, WebP).';
        return;
    }

    // Validate file size
    if (file.size > props.maxSize) {
        error.value = `File is too large. Maximum size is ${Math.round(props.maxSize / 1024 / 1024)}MB.`;
        return;
    }

    uploading.value = true;

    try {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', props.type);

        const response = await axios.post('/upload/image', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.success) {
            imagePath.value = response.data.path;
            emit('update:modelValue', response.data.url);
        } else {
            error.value = 'Upload failed. Please try again.';
        }
    } catch (err) {
        console.error('Upload error:', err);
        error.value = err.response?.data?.message || 'Upload failed. Please try again.';
    } finally {
        uploading.value = false;
    }
};

const removeImage = async () => {
    // If we have a path, try to delete the file from server
    if (imagePath.value) {
        try {
            await axios.delete('/upload/image', {
                data: { path: imagePath.value }
            });
        } catch (err) {
            console.error('Delete error:', err);
            // Continue anyway - don't block the UI
        }
    }
    
    imagePath.value = '';
    emit('update:modelValue', '');
};

const handleUrlInput = () => {
    if (urlInput.value && isValidUrl(urlInput.value)) {
        emit('update:modelValue', urlInput.value);
        showUrlInput.value = false;
        urlInput.value = '';
    } else if (urlInput.value) {
        error.value = 'Please enter a valid URL.';
    }
};

const isValidUrl = (string) => {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
};

const handleImageError = () => {
    error.value = 'Failed to load image. The URL may be invalid or inaccessible.';
};

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
    if (!newValue) {
        imagePath.value = '';
    }
});
</script>

