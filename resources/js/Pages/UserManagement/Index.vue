<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Card from 'primevue/card';
import CustomButton from '@/Components/CustomButton.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    pivot?: {
        role: string;
    };
}

interface Event {
    id: number;
    name: string;
    status: string;
    managers: User[];
    agents: User[];
}

interface Props {
    events: Event[];
    userRole: string;
}

const props = defineProps<Props>();

const goToCreateUser = () => {
    if (props.userRole === 'admin') {
        router.visit('/users/create');
    } else {
        router.visit('/event-users/create');
    }
};

const viewEventUsers = (eventId: number) => {
    router.visit(`/events/${eventId}/users`);
};

const getRoleSeverity = (role: string) => {
    const severities: Record<string, string> = {
        event_manager: 'warning',
        agent: 'info',
        admin: 'danger',
    };
    return severities[role] || 'secondary';
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Manage Event Managers and Agents for your events
                        </p>
                    </div>
                    <CustomButton
                        label="Add New User"
                        icon="pi-user-plus"
                        severity="primary"
                        @click="goToCreateUser"
                    />
                </div>

                <!-- No Events Warning -->
                <Card v-if="events.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-md border-2 border-yellow-500">
                    <template #content>
                        <div class="text-center py-8">
                            <i class="pi pi-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
                            <h3 class="text-2xl font-bold mb-2">No Events Available</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ userRole === 'admin' ? 'Create an event first to assign users.' : 'You have not been assigned to any events yet.' }}
                            </p>
                        </div>
                    </template>
                </Card>

                <!-- Events List -->
                <div v-else class="space-y-6">
                    <Card
                        v-for="event in events"
                        :key="event.id"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md"
                    >
                        <template #header>
                            <div class="flex items-center justify-between p-4 border-b">
                                <div>
                                    <h3 class="text-2xl font-bold">{{ event.name }}</h3>
                                    <Tag
                                        :value="event.status"
                                        :severity="event.status === 'active' ? 'success' : 'secondary'"
                                        class="mt-2"
                                    />
                                </div>
                                <CustomButton
                                    label="View All Users"
                                    icon="pi-eye"
                                    severity="info"
                                    @click="viewEventUsers(event.id)"
                                />
                            </div>
                        </template>
                        <template #content>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Event Managers -->
                                <div>
                                    <h4 class="text-lg font-semibold mb-3 flex items-center">
                                        <i class="pi pi-users text-yellow-500 mr-2"></i>
                                        Event Managers ({{ event.managers.length }})
                                    </h4>
                                    <div v-if="event.managers.length > 0" class="space-y-2">
                                        <div
                                            v-for="manager in event.managers"
                                            :key="manager.id"
                                            class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-semibold">{{ manager.name }}</p>
                                                    <p class="text-sm text-gray-500">{{ manager.email }}</p>
                                                </div>
                                                <Tag value="Manager" severity="warning" />
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="text-gray-500 italic">No managers assigned</p>
                                </div>

                                <!-- Agents -->
                                <div>
                                    <h4 class="text-lg font-semibold mb-3 flex items-center">
                                        <i class="pi pi-user text-blue-500 mr-2"></i>
                                        Agents ({{ event.agents.length }})
                                    </h4>
                                    <div v-if="event.agents.length > 0" class="space-y-2">
                                        <div
                                            v-for="agent in event.agents"
                                            :key="agent.id"
                                            class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-semibold">{{ agent.name }}</p>
                                                    <p class="text-sm text-gray-500">{{ agent.email }}</p>
                                                </div>
                                                <Tag value="Agent" severity="info" />
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="text-gray-500 italic">No agents assigned</p>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
