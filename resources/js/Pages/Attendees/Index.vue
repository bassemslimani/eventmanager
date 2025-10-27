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
import Card from 'primevue/card';

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
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-3 sm:p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 sm:mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Attendees</h1>
                    <Button
                        label="Add Attendee"
                        icon="pi pi-plus"
                        class="gradient-btn w-full sm:w-auto"
                        @click="router.visit('/attendees/create')"
                    />
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-3 sm:p-4 mb-4 sm:mb-6">
                    <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 md:grid-cols-4 sm:gap-4">
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

                        <div class="sm:col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium mb-2">Search</label>
                            <InputText
                                v-model="filters.search"
                                placeholder="Name, email..."
                                class="w-full"
                                @keyup.enter="searchAttendees"
                            />
                        </div>

                        <div class="flex items-end">
                            <Button
                                label="Clear Filters"
                                icon="pi pi-filter-slash"
                                severity="secondary"
                                class="w-full"
                                @click="filters = { event_id: null, type: null, search: '' }; searchAttendees()"
                            />
                        </div>
                    </div>
                </div>

                <!-- Mobile View - Cards -->
                <div class="sm:hidden space-y-3 mb-4">
                    <Card v-for="attendee in attendees.data" :key="attendee.id">
                        <template #content>
                            <div class="space-y-3">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-base mb-1">{{ attendee.name }}</h3>
                                        <p class="text-xs text-gray-500 break-all mb-2">{{ attendee.email }}</p>
                                        <Tag
                                            :value="attendee.type"
                                            :severity="getTypeSeverity(attendee.type)"
                                            class="text-xs"
                                        />
                                    </div>
                                    <Tag
                                        v-if="attendee.checked_in_at"
                                        value="Checked In"
                                        severity="success"
                                        icon="pi pi-check"
                                        class="ml-2"
                                    />
                                    <Tag
                                        v-else
                                        value="Pending"
                                        severity="secondary"
                                        class="ml-2"
                                    />
                                </div>

                                <div v-if="attendee.company" class="text-sm">
                                    <span class="text-gray-500">Company:</span>
                                    <span class="ml-2 font-medium">{{ attendee.company }}</span>
                                </div>

                                <div class="text-sm">
                                    <span class="text-gray-500">QR Code:</span>
                                    <span class="ml-2 font-mono text-xs">{{ attendee.qr_code }}</span>
                                </div>

                                <div class="flex gap-2 pt-2">
                                    <Button
                                        icon="pi pi-pencil"
                                        label="Edit"
                                        severity="info"
                                        class="flex-1"
                                        @click="router.visit(`/attendees/${attendee.id}/edit`)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        label="Delete"
                                        severity="danger"
                                        outlined
                                        class="flex-1"
                                        @click="deleteAttendee(attendee.id)"
                                    />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <div v-if="attendees.data.length === 0" class="text-center py-12">
                        <i class="pi pi-users text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">No attendees found</p>
                    </div>
                </div>

                <!-- Desktop View - Table -->
                <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
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
