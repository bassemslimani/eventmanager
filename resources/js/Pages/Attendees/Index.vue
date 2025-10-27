<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';

interface Attendee {
    id: number;
    type: string;
    name: string;
    email: string;
    company: string;
    qr_code: string;
    checked_in_at: string | null;
}

interface Event {
    id: number;
    name: string;
}

interface Props {
    attendees: {
        data: Attendee[];
        links: any[];
        meta: any;
    };
    events: Event[];
    filters: {
        type?: string;
        search?: string;
        event_id?: number;
    };
}

const props = defineProps<Props>();

const filters = ref({
    event_id: props.filters.event_id || null,
    type: props.filters.type || null,
    search: props.filters.search || '',
});

const typeOptions = [
    { label: 'All Types', value: null },
    { label: 'Exhibitors', value: 'exhibitor' },
    { label: 'Guests', value: 'guest' },
    { label: 'Organizers', value: 'organizer' },
    { label: 'VIP', value: 'vip' },
];

const searchAttendees = () => {
    router.get('/attendees', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getTypeSeverity = (type: string) => {
    const severities: Record<string, string> = {
        exhibitor: 'success',
        guest: 'info',
        organizer: 'warn',
        vip: 'danger',
    };
    return severities[type] || 'secondary';
};

const deleteAttendee = (id: number) => {
    if (confirm('Are you sure you want to delete this attendee?')) {
        router.delete(`/attendees/${id}`);
    }
};
</script>

<template>
    <Head title="Attendees" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Attendees</h1>
                    <Button
                        label="Add New Attendee"
                        icon="pi pi-plus"
                        class="gradient-btn"
                        @click="router.visit('/attendees/create')"
                    />
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Event</label>
                            <Dropdown
                                v-model="filters.event_id"
                                :options="[{ id: null, name: 'All Events' }, ...events]"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Filter by event"
                                class="w-full"
                                @change="searchAttendees"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Type</label>
                            <Dropdown
                                v-model="filters.type"
                                :options="typeOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Filter by type"
                                class="w-full"
                                @change="searchAttendees"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Search</label>
                            <InputText
                                v-model="filters.search"
                                placeholder="Search by name, email..."
                                class="w-full"
                                @keyup.enter="searchAttendees"
                            />
                        </div>

                        <div class="flex items-end">
                            <Button
                                label="Clear Filters"
                                icon="pi pi-filter-slash"
                                severity="secondary"
                                @click="filters = { event_id: null, type: null, search: '' }; searchAttendees()"
                            />
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                    <DataTable
                        :value="attendees.data"
                        stripedRows
                        paginator
                        :rows="20"
                        :rowsPerPageOptions="[10, 20, 50, 100]"
                        class="custom-datatable"
                    >
                        <Column field="id" header="ID" sortable style="width: 80px" />

                        <Column field="type" header="Type" sortable>
                            <template #body="slotProps">
                                <Tag
                                    :value="slotProps.data.type"
                                    :severity="getTypeSeverity(slotProps.data.type)"
                                />
                            </template>
                        </Column>

                        <Column field="name" header="Name" sortable />
                        <Column field="email" header="Email" sortable />
                        <Column field="company" header="Company" sortable />
                        <Column field="qr_code" header="QR Code" />

                        <Column field="checked_in_at" header="Check-in Status">
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.checked_in_at"
                                    value="Checked In"
                                    severity="success"
                                    icon="pi pi-check"
                                />
                                <Tag
                                    v-else
                                    value="Not Checked In"
                                    severity="secondary"
                                />
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 150px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-pencil"
                                        severity="info"
                                        size="small"
                                        @click="router.visit(`/attendees/${slotProps.data.id}/edit`)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        size="small"
                                        @click="deleteAttendee(slotProps.data.id)"
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
