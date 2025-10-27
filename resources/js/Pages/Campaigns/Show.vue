<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Campaign Details</h2>
                <Link :href="route('campaigns.index')" class="text-gray-600 hover:text-gray-900">
                    ← Back to Campaigns
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Success/Error Messages -->
                <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Campaign Info Card -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-2xl font-bold text-gray-900">{{ campaign.name }}</h3>
                                <span :class="getStatusClass(campaign.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
                                    {{ campaign.status }}
                                </span>
                            </div>
                            <p class="text-gray-600">{{ campaign.subject }}</p>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-if="campaign.status === 'draft' || campaign.status === 'failed'"
                                :href="route('campaigns.edit', campaign.id)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="['draft', 'scheduled', 'failed'].includes(campaign.status)"
                                @click="sendCampaign"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm"
                            >
                                Send Campaign
                            </button>
                            <button
                                @click="deleteCampaign"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Email Body</h4>
                                <div class="bg-gray-50 p-3 rounded border border-gray-200 whitespace-pre-wrap text-sm">{{ campaign.body }}</div>
                            </div>

                            <div v-if="campaign.attachments && campaign.attachments.length > 0">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Attachments</h4>
                                <div class="space-y-1">
                                    <div v-for="(attachment, index) in campaign.attachments" :key="index" class="flex items-center gap-2 text-sm text-gray-700 bg-gray-50 px-3 py-2 rounded">
                                        <span>📎</span>
                                        <span>{{ getFileName(attachment) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Campaign Info</h4>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Created by:</dt>
                                        <dd class="font-medium">{{ campaign.creator?.name || 'Unknown' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Created at:</dt>
                                        <dd class="font-medium">{{ formatDate(campaign.created_at) }}</dd>
                                    </div>
                                    <div v-if="campaign.sent_at" class="flex justify-between">
                                        <dt class="text-gray-600">Sent at:</dt>
                                        <dd class="font-medium">{{ formatDate(campaign.sent_at) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div v-if="campaign.filters && Object.keys(campaign.filters).length > 0">
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Applied Filters</h4>
                                <div class="space-y-1 text-sm">
                                    <div v-if="campaign.filters.event_id" class="bg-indigo-50 px-3 py-1 rounded">
                                        <span class="text-indigo-700">Event ID: {{ campaign.filters.event_id }}</span>
                                    </div>
                                    <div v-if="campaign.filters.categories && campaign.filters.categories.length > 0" class="bg-indigo-50 px-3 py-1 rounded">
                                        <span class="text-indigo-700">Categories: {{ campaign.filters.categories.join(', ') }}</span>
                                    </div>
                                    <div v-if="campaign.filters.registered_after" class="bg-indigo-50 px-3 py-1 rounded">
                                        <span class="text-indigo-700">After: {{ campaign.filters.registered_after }}</span>
                                    </div>
                                    <div v-if="campaign.filters.registered_before" class="bg-indigo-50 px-3 py-1 rounded">
                                        <span class="text-indigo-700">Before: {{ campaign.filters.registered_before }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Total Recipients</div>
                        <div class="text-3xl font-bold text-gray-900">{{ stats.total }}</div>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Sent</div>
                        <div class="text-3xl font-bold text-green-600">{{ stats.sent }}</div>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Pending</div>
                        <div class="text-3xl font-bold text-yellow-600">{{ stats.pending }}</div>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 mb-1">Failed</div>
                        <div class="text-3xl font-bold text-red-600">{{ stats.failed }}</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6" v-if="stats.total > 0">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Sending Progress</h4>
                    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                        <div class="h-full flex">
                            <div
                                v-if="stats.sent > 0"
                                :style="{ width: (stats.sent / stats.total * 100) + '%' }"
                                class="bg-green-500 transition-all duration-300"
                            ></div>
                            <div
                                v-if="stats.failed > 0"
                                :style="{ width: (stats.failed / stats.total * 100) + '%' }"
                                class="bg-red-500 transition-all duration-300"
                            ></div>
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 mt-2">
                        <span>{{ Math.round((stats.sent + stats.failed) / stats.total * 100) }}% complete</span>
                        <span>{{ stats.sent + stats.failed }} / {{ stats.total }}</span>
                    </div>
                </div>

                <!-- Recipients Table -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recipients</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="recipient in recipients.data" :key="recipient.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ recipient.attendee?.name || 'Unknown' }}</div>
                                    <div class="text-sm text-gray-500">{{ recipient.attendee?.type || 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ recipient.attendee?.email || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getRecipientStatusClass(recipient.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ recipient.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ recipient.sent_at ? formatDate(recipient.sent_at) : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-red-600">
                                    {{ recipient.error || '-' }}
                                </td>
                            </tr>
                            <tr v-if="recipients.data.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No recipients found
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="recipients.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ recipients.from }} to {{ recipients.to }} of {{ recipients.total }} recipients
                            </div>
                            <div class="flex space-x-2">
                                <Link v-for="link in recipients.links" :key="link.label" :href="link.url" v-html="link.label"
                                    :class="[
                                        'px-3 py-2 border rounded-md text-sm',
                                        link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                    :disabled="!link.url"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    campaign: Object,
    recipients: Object,
    stats: Object
});

const getStatusClass = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-800',
        'scheduled': 'bg-blue-100 text-blue-800',
        'sending': 'bg-yellow-100 text-yellow-800',
        'sent': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getRecipientStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'sent': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getFileName = (path) => {
    return path.split('/').pop();
};

const sendCampaign = () => {
    if (confirm('Are you sure you want to send this campaign to ' + props.stats.total + ' recipients? This action cannot be undone.')) {
        router.post(route('campaigns.send', props.campaign.id));
    }
};

const deleteCampaign = () => {
    if (confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
        router.delete(route('campaigns.destroy', props.campaign.id));
    }
};
</script>
