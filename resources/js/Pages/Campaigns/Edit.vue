<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Campaign</h2>
                <Link :href="route('campaigns.show', campaign.id)" class="text-gray-600 hover:text-gray-900">
                    ← Back to Campaign
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Warning Banner -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-yellow-800">
                        ⚠️ You are editing a {{ campaign.status }} campaign. Changes will not affect emails that have already been sent.
                    </p>
                </div>

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
                        ></textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            Email message content. You can use {name} to personalize.
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recipient Filters</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Note: Filters cannot be changed after campaign creation. Current filters shown below.
                        </p>
                    </div>

                    <!-- Current Filters (Read-only) -->
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div v-if="campaign.filters?.event_id">
                            <span class="text-sm font-medium text-gray-700">Event ID:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ campaign.filters.event_id }}</span>
                        </div>
                        <div v-if="campaign.filters?.categories && campaign.filters.categories.length > 0">
                            <span class="text-sm font-medium text-gray-700">Categories:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ campaign.filters.categories.join(', ') }}</span>
                        </div>
                        <div v-if="campaign.filters?.registered_after">
                            <span class="text-sm font-medium text-gray-700">Registered After:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ campaign.filters.registered_after }}</span>
                        </div>
                        <div v-if="campaign.filters?.registered_before">
                            <span class="text-sm font-medium text-gray-700">Registered Before:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ campaign.filters.registered_before }}</span>
                        </div>
                        <div v-if="!campaign.filters || Object.keys(campaign.filters).length === 0">
                            <span class="text-sm text-gray-500">No filters applied (all attendees)</span>
                        </div>
                    </div>

                    <!-- Current Attachments -->
                    <div v-if="campaign.attachments && campaign.attachments.length > 0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Attachments</label>
                        <div class="space-y-1">
                            <div v-for="(attachment, index) in campaign.attachments" :key="index" class="flex items-center gap-2 text-sm text-gray-700 bg-gray-50 px-3 py-2 rounded">
                                <span>📎</span>
                                <span>{{ getFileName(attachment) }}</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Note: Attachments cannot be modified after creation</p>
                    </div>

                    <!-- Recipient Count -->
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-indigo-900">Total Recipients</h4>
                                <p class="text-2xl font-bold text-indigo-600 mt-1">
                                    {{ campaign.recipients_count }} recipients
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <Link
                            :href="route('campaigns.show', campaign.id)"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md font-medium"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md font-medium disabled:opacity-50"
                        >
                            {{ processing ? 'Updating...' : 'Update Campaign' }}
                        </button>
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

const props = defineProps({
    campaign: Object,
    events: Array,
    categories: Array
});

const form = reactive({
    name: props.campaign.name,
    subject: props.campaign.subject,
    body: props.campaign.body,
    filters: props.campaign.filters || {}
});

const processing = ref(false);

const getFileName = (path) => {
    return path.split('/').pop();
};

const submit = () => {
    if (!form.name || !form.subject || !form.body) {
        alert('Please fill in all required fields');
        return;
    }

    processing.value = true;

    router.put(route('campaigns.update', props.campaign.id), form, {
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>
