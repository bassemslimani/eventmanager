<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Card from 'primevue/card';
import CustomButton from '@/Components/CustomButton.vue';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Event {
    id: number;
    name: string;
    status: string;
}

interface Props {
    event: Event;
    managers: User[];
    agents: User[];
    userRole: string;
}

const props = defineProps<Props>();

const removeUser = (userId: number) => {
    if (confirm('Are you sure you want to remove this user from the event?')) {
        router.delete(`/events/${props.event.id}/users/${userId}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="`Users - ${event.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <CustomButton
                        label="Back to User Management"
                        icon="pi-arrow-left"
                        severity="secondary"
                        @click="router.visit(userRole === 'admin' ? '/users' : '/event-users')"
                        class="mb-3"
                    />
                    <h1 class="text-3xl font-bold text-gradient">{{ event.name }}</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Manage users assigned to this event
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Event Managers -->
                    <Card class="glass-card">
                        <template #header>
                            <div class="p-4 border-b">
                                <h3 class="text-xl font-bold flex items-center">
                                    <i class="pi pi-users text-yellow-500 mr-2"></i>
                                    Event Managers ({{ managers.length }})
                                </h3>
                            </div>
                        </template>
                        <template #content>
                            <DataTable
                                v-if="managers.length > 0"
                                :value="managers"
                                stripedRows
                                class="custom-datatable"
                            >
                                <Column field="name" header="Name" />
                                <Column field="email" header="Email" />
                                <Column header="Actions" style="width: 100px">
                                    <template #body="slotProps">
                                        <CustomButton
                                            icon="pi-trash"
                                            severity="danger"
                                            size="small"
                                            @click="removeUser(slotProps.data.id)"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                            <div v-else class="text-center py-8 text-gray-500">
                                <i class="pi pi-users text-4xl mb-3 block"></i>
                                <p>No managers assigned to this event</p>
                            </div>
                        </template>
                    </Card>

                    <!-- Agents -->
                    <Card class="glass-card">
                        <template #header>
                            <div class="p-4 border-b">
                                <h3 class="text-xl font-bold flex items-center">
                                    <i class="pi pi-user text-blue-500 mr-2"></i>
                                    Agents ({{ agents.length }})
                                </h3>
                            </div>
                        </template>
                        <template #content>
                            <DataTable
                                v-if="agents.length > 0"
                                :value="agents"
                                stripedRows
                                class="custom-datatable"
                            >
                                <Column field="name" header="Name" />
                                <Column field="email" header="Email" />
                                <Column header="Actions" style="width: 100px">
                                    <template #body="slotProps">
                                        <CustomButton
                                            icon="pi-trash"
                                            severity="danger"
                                            size="small"
                                            @click="removeUser(slotProps.data.id)"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                            <div v-else class="text-center py-8 text-gray-500">
                                <i class="pi pi-user text-4xl mb-3 block"></i>
                                <p>No agents assigned to this event</p>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Add Users Button -->
                <div class="mt-6">
                    <Card class="glass-card text-center">
                        <template #content>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                Need to add more users to this event?
                            </p>
                            <CustomButton
                                label="Create New User"
                                icon="pi-user-plus"
                                severity="primary"
                                @click="router.visit(userRole === 'admin' ? '/users/create' : '/event-users/create')"
                            />
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
