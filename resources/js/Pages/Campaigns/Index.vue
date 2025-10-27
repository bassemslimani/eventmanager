<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Email Campaigns</h2>
                <Link :href="route('campaigns.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                    Create Campaign
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Campaigns Table -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipients</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="campaign in campaigns.data" :key="campaign.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ campaign.name }}</div>
                                    <div class="text-sm text-gray-500">{{ campaign.subject }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClass(campaign.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ campaign.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ campaign.recipients_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ campaign.sent_count }} / {{ campaign.recipients_count }}
                                    <span v-if="campaign.failed_count > 0" class="text-red-600">({{ campaign.failed_count }} failed)</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(campaign.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <Link :href="route('campaigns.show', campaign.id)" class="text-indigo-600 hover:text-indigo-900">
                                        View
                                    </Link>
                                    <Link v-if="campaign.status === 'draft'" :href="route('campaigns.edit', campaign.id)" class="text-blue-600 hover:text-blue-900">
                                        Edit
                                    </Link>
                                    <button v-if="['draft', 'scheduled', 'failed'].includes(campaign.status)" @click="sendCampaign(campaign.id)" class="text-green-600 hover:text-green-900">
                                        Send
                                    </button>
                                    <button @click="deleteCampaign(campaign.id)" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="campaigns.data.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No campaigns found. <Link :href="route('campaigns.create')" class="text-indigo-600 hover:text-indigo-900">Create your first campaign</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="campaigns.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ campaigns.from }} to {{ campaigns.to }} of {{ campaigns.total }} campaigns
                            </div>
                            <div class="flex space-x-2">
                                <Link v-for="link in campaigns.links" :key="link.label" :href="link.url" v-html="link.label"
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
    campaigns: Object
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

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const sendCampaign = (id) => {
    if (confirm('Are you sure you want to send this campaign? This action cannot be undone.')) {
        router.post(route('campaigns.send', id));
    }
};

const deleteCampaign = (id) => {
    if (confirm('Are you sure you want to delete this campaign?')) {
        router.delete(route('campaigns.destroy', id));
    }
};
</script>
