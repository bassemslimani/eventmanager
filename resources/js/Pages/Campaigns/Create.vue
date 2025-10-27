<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Email Campaign</h2>
                <Link :href="route('campaigns.index')" class="text-gray-600 hover:text-gray-900">
                    ← Back to Campaigns
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">

                    <!-- Campaign Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Campaign Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="e.g., Event Reminder - March 2025"
                        />
                        <p class="mt-1 text-sm text-gray-500">Internal name for this campaign</p>
                    </div>

                    <!-- Email Subject -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email Subject <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.subject"
                            type="text"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="e.g., Don't miss our upcoming event!"
                        />
                        <p class="mt-1 text-sm text-gray-500">Subject line recipients will see in their inbox</p>
                    </div>

                    <!-- Email Body -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email Body <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.body"
                            rows="12"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Dear {name},&#10;&#10;We're excited to invite you to...&#10;&#10;Best regards,&#10;The Team"
                        ></textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            Email message content. You can use {name} to personalize. Line breaks will be preserved.
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recipient Filters</h3>
                        <p class="text-sm text-gray-600 mb-4">Select who should receive this campaign</p>
                    </div>

                    <!-- Event Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Event</label>
                        <select
                            v-model="form.filters.event_id"
                            @change="updatePreview"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">All Events</option>
                            <option v-for="event in events" :key="event.id" :value="event.id">
                                {{ event.name }}
                            </option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Leave blank to send to attendees from all events</p>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Category</label>
                        <div class="space-y-2">
                            <label v-for="category in categories" :key="category" class="flex items-center">
                                <input
                                    type="checkbox"
                                    :value="category"
                                    v-model="form.filters.categories"
                                    @change="updatePreview"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <span class="ml-2 text-sm text-gray-700 capitalize">{{ category }}</span>
                            </label>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Leave unchecked to send to all categories</p>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registered After</label>
                            <input
                                type="date"
                                v-model="form.filters.registered_after"
                                @change="updatePreview"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registered Before</label>
                            <input
                                type="date"
                                v-model="form.filters.registered_before"
                                @change="updatePreview"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Attachments (Optional)</label>
                        <input
                            type="file"
                            multiple
                            @change="handleFiles"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <p class="mt-1 text-sm text-gray-500">Max 10MB per file. Supports: PDF, DOC, XLS, images</p>

                        <!-- Selected Files -->
                        <div v-if="form.attachments.length > 0" class="mt-2 space-y-1">
                            <div v-for="(file, index) in form.attachments" :key="index" class="flex items-center justify-between text-sm text-gray-600 bg-gray-50 px-3 py-2 rounded">
                                <span>📎 {{ file.name }} ({{ formatFileSize(file.size) }})</span>
                                <button type="button" @click="removeFile(index)" class="text-red-600 hover:text-red-800">Remove</button>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Recipients -->
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-indigo-900">Recipient Preview</h4>
                                <p v-if="recipientCount !== null" class="text-2xl font-bold text-indigo-600 mt-1">
                                    {{ recipientCount }} recipients
                                </p>
                                <p v-else class="text-sm text-indigo-700 mt-1">Click to preview recipient count</p>
                            </div>
                            <button
                                type="button"
                                @click="previewRecipients"
                                :disabled="loading"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50"
                            >
                                {{ loading ? 'Loading...' : 'Preview Recipients' }}
                            </button>
                        </div>

                        <!-- Preview List -->
                        <div v-if="previewList.length > 0" class="mt-3 pt-3 border-t border-indigo-200">
                            <p class="text-xs font-medium text-indigo-900 mb-2">First 10 recipients:</p>
                            <div class="space-y-1">
                                <div v-for="attendee in previewList" :key="attendee.id" class="text-xs text-indigo-700 bg-white px-2 py-1 rounded">
                                    {{ attendee.name }} ({{ attendee.email }}) - {{ attendee.type }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <Link
                            :href="route('campaigns.index')"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md font-medium"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="processing || recipientCount === 0"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md font-medium disabled:opacity-50"
                        >
                            {{ processing ? 'Creating...' : 'Create Campaign' }}
                        </button>
                    </div>

                    <!-- Warning -->
                    <div v-if="recipientCount === 0 && recipientCount !== null" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-sm text-yellow-800">
                            ⚠️ No recipients match your filters. Please adjust your filters or the campaign cannot be created.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    events: Array,
    categories: Array
});

const form = reactive({
    name: '',
    subject: '',
    body: '',
    filters: {
        event_id: '',
        categories: [],
        registered_after: '',
        registered_before: ''
    },
    attachments: []
});

const recipientCount = ref(null);
const previewList = ref([]);
const loading = ref(false);
const processing = ref(false);

const handleFiles = (event) => {
    const files = Array.from(event.target.files);

    // Validate file sizes
    const validFiles = files.filter(file => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`File ${file.name} is too large. Maximum size is 10MB.`);
            return false;
        }
        return true;
    });

    form.attachments = validFiles;
};

const removeFile = (index) => {
    form.attachments.splice(index, 1);
};

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const updatePreview = () => {
    // Reset preview when filters change
    recipientCount.value = null;
    previewList.value = [];
};

const previewRecipients = async () => {
    loading.value = true;
    try {
        const response = await axios.post(route('campaigns.preview-recipients'), {
            filters: form.filters
        });
        recipientCount.value = response.data.count;
        previewList.value = response.data.preview || [];
    } catch (error) {
        console.error('Failed to preview recipients', error);
        alert('Failed to preview recipients. Please try again.');
    } finally {
        loading.value = false;
    }
};

const submit = () => {
    if (!form.name || !form.subject || !form.body) {
        alert('Please fill in all required fields');
        return;
    }

    if (recipientCount.value === null) {
        alert('Please preview recipients before creating the campaign');
        return;
    }

    if (recipientCount.value === 0) {
        alert('Cannot create campaign with no recipients');
        return;
    }

    processing.value = true;

    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('subject', form.subject);
    formData.append('body', form.body);
    formData.append('filters', JSON.stringify(form.filters));

    form.attachments.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file);
    });

    router.post(route('campaigns.store'), formData, {
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>
