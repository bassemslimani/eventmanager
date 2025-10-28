<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import CustomButton from '@/Components/CustomButton.vue';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';
import Card from 'primevue/card';

interface Attendee {
    id: number;
    type: string;
    name: string;
    email: string;
    company: string;
    qr_code: string;
    badge_generated_at: string | null;
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
    templates: any[];
    events: Event[];
    filters: {
        type?: string;
        badge_status?: string;
        event_id?: number;
    };
}

const props = defineProps<Props>();

const filters = ref({
    event_id: props.filters.event_id || null,
    type: props.filters.type || null,
    badge_status: props.filters.badge_status || null,
});

const selectedAttendees = ref<Attendee[]>([]);

const typeOptions = [
    { label: 'All Types', value: null },
    { label: 'Exhibitors', value: 'exhibitor' },
    { label: 'Guests', value: 'guest' },
    { label: 'Organizers', value: 'organizer' },
    { label: 'VIP', value: 'vip' },
];

const statusOptions = [
    { label: 'All Status', value: null },
    { label: 'Generated', value: 'generated' },
    { label: 'Pending', value: 'pending' },
];

const searchBadges = () => {
    router.get('/badges', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const generateSingle = (id: number) => {
    router.post(`/badges/generate/${id}`, {}, {
        preserveState: true,
        onSuccess: () => {
            router.reload();
        },
    });
};

const generateBulk = () => {
    if (selectedAttendees.value.length === 0) {
        alert('Please select at least one attendee.');
        return;
    }

    const attendeeIds = selectedAttendees.value.map(a => a.id);
    router.post('/badges/generate-bulk', { attendee_ids: attendeeIds }, {
        preserveState: true,
        onSuccess: () => {
            selectedAttendees.value = [];
            router.reload();
        },
    });
};

const downloadBadge = async (id: number) => {
    try {
        console.log('Starting badge download for ID:', id);
        const response = await fetch(`/badges/download/${id}`);

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Failed to download badge');
        }

        const data = await response.json();
        console.log('Badge data received:', data);

        const { generatePDFBadge } = await import('@/utils/badgeGenerator');
        console.log('Badge generator imported');

        await generatePDFBadge(data);
        console.log('Badge generated successfully');
    } catch (error) {
        console.error('Error in downloadBadge:', error);
        alert(error instanceof Error ? error.message : 'Failed to download badge. Please try again.');
    }
};

const sendEmail = (attendeeId: number, attendeeName: string) => {
    if (confirm(`Send badge email to ${attendeeName}?`)) {
        router.post(`/badges/send-email/${attendeeId}`, {}, {
            preserveState: true,
            onSuccess: () => {
                alert('Badge email sent successfully! The attendee will receive it shortly.');
            },
            onError: (errors) => {
                alert('Failed to send email. Please try again.');
                console.error(errors);
            }
        });
    }
};

const toggleSelection = (attendee: Attendee) => {
    const index = selectedAttendees.value.findIndex(a => a.id === attendee.id);
    if (index > -1) {
        selectedAttendees.value.splice(index, 1);
    } else {
        selectedAttendees.value.push(attendee);
    }
};

const isSelected = (attendee: Attendee) => {
    return selectedAttendees.value.some(a => a.id === attendee.id);
};
</script>

<template>
    <Head title="Badge Generation" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-3 sm:p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 sm:mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Badge Generation</h1>
                    <CustomButton
                        label="Generate Selected"
                        icon="pi-id-card"
                        severity="primary"
                        class="w-full sm:w-auto"
                        :disabled="selectedAttendees.length === 0"
                        @click="generateBulk"
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
                                @change="searchBadges"
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
                                @change="searchBadges"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Badge Status</label>
                            <Dropdown
                                v-model="filters.badge_status"
                                :options="statusOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Filter by status"
                                class="w-full"
                                @change="searchBadges"
                            />
                        </div>

                        <div class="flex items-end">
                            <CustomButton
                                label="Clear Filters"
                                icon="pi-filter-slash"
                                severity="secondary"
                                class="w-full"
                                @click="filters = { event_id: null, type: null, badge_status: null }; searchBadges()"
                            />
                        </div>
                    </div>
                </div>

                <!-- Mobile View - Cards -->
                <div class="sm:hidden space-y-3 mb-4">
                    <Card v-for="attendee in attendees.data" :key="attendee.id">
                        <template #content>
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-3 flex-1">
                                        <Checkbox
                                            :modelValue="isSelected(attendee)"
                                            @update:modelValue="toggleSelection(attendee)"
                                            binary
                                        />
                                        <div class="flex-1">
                                            <h3 class="font-bold text-base mb-1">{{ attendee.name }}</h3>
                                            <p class="text-xs text-gray-500 break-all">{{ attendee.email }}</p>
                                            <p v-if="attendee.company" class="text-sm text-gray-600 mt-1">{{ attendee.company }}</p>
                                        </div>
                                    </div>
                                    <Tag
                                        v-if="attendee.badge_generated_at"
                                        value="Generated"
                                        severity="success"
                                        icon="pi pi-check"
                                    />
                                    <Tag
                                        v-else
                                        value="Pending"
                                        severity="warning"
                                    />
                                </div>

                                <div class="flex gap-2">
                                    <CustomButton
                                        v-if="!attendee.badge_generated_at"
                                        icon="pi-plus"
                                        label="Generate"
                                        severity="success"
                                        class="flex-1"
                                        @click="generateSingle(attendee.id)"
                                    />
                                    <template v-else>
                                        <CustomButton
                                            icon="pi-download"
                                            label="Download"
                                            severity="info"
                                            class="flex-1"
                                            @click="downloadBadge(attendee.id)"
                                        />
                                        <CustomButton
                                            icon="pi-envelope"
                                            label="Email"
                                            severity="success"
                                            class="flex-1"
                                            @click="sendEmail(attendee.id, attendee.name)"
                                        />
                                    </template>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <div v-if="attendees.data.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">No badges found</p>
                    </div>
                </div>

                <!-- Desktop View - Table -->
                <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                    <DataTable
                        v-model:selection="selectedAttendees"
                        :value="attendees.data"
                        stripedRows
                        paginator
                        :rows="20"
                        :rowsPerPageOptions="[10, 20, 50]"
                        class="custom-datatable"
                        dataKey="id"
                    >
                        <Column selectionMode="multiple" style="width: 3rem" />
                        <Column field="id" header="ID" sortable style="width: 80px" />
                        <Column field="name" header="Name" sortable />
                        <Column field="type" header="Type" sortable />
                        <Column field="company" header="Company" sortable />

                        <Column field="badge_generated_at" header="Badge Status">
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.badge_generated_at"
                                    value="Generated"
                                    severity="success"
                                    icon="pi pi-check"
                                />
                                <Tag
                                    v-else
                                    value="Pending"
                                    severity="warning"
                                />
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 280px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <CustomButton
                                        v-if="!slotProps.data.badge_generated_at"
                                        icon="pi-plus"
                                        label="Generate"
                                        severity="success"
                                        size="small"
                                        @click="generateSingle(slotProps.data.id)"
                                    />
                                    <template v-else>
                                        <CustomButton
                                            icon="pi-download"
                                            label="Download"
                                            severity="info"
                                            size="small"
                                            @click="downloadBadge(slotProps.data.id)"
                                        />
                                        <CustomButton
                                            icon="pi-envelope"
                                            label="Email"
                                            severity="success"
                                            size="small"
                                            @click="sendEmail(slotProps.data.id, slotProps.data.name)"
                                            v-tooltip.top="'Send badge via email'"
                                        />
                                    </template>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
