<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import CustomButton from '@/Components/CustomButton.vue';
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
        per_page?: number;
    };
}

const props = defineProps<Props>();

const filters = ref({
    event_id: props.filters.event_id || null,
    type: props.filters.type || null,
    search: props.filters.search || '',
    per_page: props.filters.per_page || 20,
});

const typeOptions = [
    { label: 'All Types', value: null },
    { label: 'Exhibitors', value: 'exhibitor' },
    { label: 'Guests', value: 'guest' },
    { label: 'Organizers', value: 'organizer' },
    { label: 'Visitors', value: 'visitor' },
];

const perPageOptions = [
    { label: '20 per page', value: 20 },
    { label: '50 per page', value: 50 },
    { label: '100 per page', value: 100 },
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
        visitor: 'danger',
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
                    <CustomButton
                        label="Add Attendee"
                        icon="pi-plus"
                        severity="primary"
                        class="w-full sm:w-auto"
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
                            <CustomButton
                                label="Clear Filters"
                                icon="pi-filter-slash"
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
                                    <CustomButton
                                        icon="pi-pencil"
                                        label="Edit"
                                        severity="info"
                                        class="flex-1"
                                        @click="router.visit(`/attendees/${attendee.id}/edit`)"
                                    />
                                    <CustomButton
                                        icon="pi-trash"
                                        label="Delete"
                                        severity="danger"
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

                <!-- Pagination Controls -->
                <div class="flex justify-between items-center mb-4 bg-white dark:bg-gray-800 rounded-xl shadow-md p-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Items per page:</span>
                        <Dropdown
                            v-model="filters.per_page"
                            :options="perPageOptions"
                            optionLabel="label"
                            optionValue="value"
                            @change="searchAttendees"
                            class="w-40"
                        />
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ attendees.meta?.from || 0 }} to {{ attendees.meta?.to || 0 }} of {{ attendees.meta?.total || 0 }} results
                    </div>
                </div>

                <!-- Desktop View - Table -->
                <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                    <DataTable
                        :value="attendees.data"
                        stripedRows
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
                                    <CustomButton
                                        icon="pi-pencil"
                                        severity="info"
                                        size="small"
                                        @click="router.visit(`/attendees/${slotProps.data.id}/edit`)"
                                    />
                                    <CustomButton
                                        icon="pi-trash"
                                        severity="danger"
                                        size="small"
                                        @click="deleteAttendee(slotProps.data.id)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>

                    <!-- Pagination -->
                    <div v-if="attendees.links && attendees.links.length > 3" class="flex justify-center items-center gap-1 p-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                        <template v-for="(link, index) in attendees.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600'
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                :class="[
                                    'px-3 py-2 rounded-md text-sm font-medium',
                                    'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed border border-gray-300 dark:border-gray-600'
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
