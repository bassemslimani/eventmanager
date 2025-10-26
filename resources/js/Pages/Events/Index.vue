<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

interface Event {
    id: number;
    name: string;
    name_ar: string | null;
    date: string;
    location: string;
    status: string;
}

interface Props {
    events: {
        data: Event[];
        links: any[];
        meta: any;
    };
}

const props = defineProps<Props>();

const getStatusSeverity = (status: string) => {
    const severities: Record<string, string> = {
        active: 'success',
        draft: 'info',
        completed: 'secondary',
        cancelled: 'danger',
    };
    return severities[status] || 'secondary';
};

const deleteEvent = (id: number) => {
    if (confirm('Are you sure you want to delete this event?')) {
        router.delete(`/events/${id}`);
    }
};
</script>

<template>
    <Head title="Events" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gradient mb-2">Events</h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Manage your events and conferences
                        </p>
                    </div>
                    <Button
                        label="Create New Event"
                        icon="pi pi-plus"
                        class="gradient-btn"
                        @click="router.visit('/events/create')"
                    />
                </div>

                <!-- Data Table -->
                <div class="glass-card overflow-hidden">
                    <DataTable
                        :value="events.data"
                        stripedRows
                        paginator
                        :rows="20"
                        :rowsPerPageOptions="[10, 20, 50]"
                        class="custom-datatable"
                    >
                        <Column field="id" header="ID" sortable style="width: 80px" />
                        <Column field="name" header="Event Name" sortable />
                        <Column field="date" header="Date" sortable>
                            <template #body="slotProps">
                                {{ new Date(slotProps.data.date).toLocaleDateString() }}
                            </template>
                        </Column>
                        <Column field="location" header="Location" sortable />

                        <Column field="status" header="Status" sortable>
                            <template #body="slotProps">
                                <Tag
                                    :value="slotProps.data.status"
                                    :severity="getStatusSeverity(slotProps.data.status)"
                                />
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 200px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-id-card"
                                        severity="success"
                                        size="small"
                                        v-tooltip.top="'Badge Designer'"
                                        @click="router.visit(`/events/${slotProps.data.id}/badge-designer`)"
                                    />
                                    <Button
                                        icon="pi pi-pencil"
                                        severity="warning"
                                        size="small"
                                        v-tooltip.top="'Edit Event'"
                                        @click="router.visit(`/events/${slotProps.data.id}/edit`)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        size="small"
                                        v-tooltip.top="'Delete Event'"
                                        @click="deleteEvent(slotProps.data.id)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
