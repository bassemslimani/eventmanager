<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';

interface Attendee {
    id: number;
    type: string;
    name: string;
    email: string;
    company: string;
    qr_code: string;
    badge_generated_at: string | null;
}

interface Props {
    attendees: {
        data: Attendee[];
        links: any[];
        meta: any;
    };
    templates: any[];
    filters: {
        type?: string;
        badge_status?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
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
        const response = await fetch(`/badges/download/${id}`);
        const data = await response.json();

        if (!data.success) {
            alert(data.error || 'Failed to download badge.');
            return;
        }

        // Use professional PDF badge generator
        const { generatePDFBadge } = await import('@/utils/badgeGenerator');
        await generatePDFBadge(data);

    } catch (error) {
        console.error('Error downloading badge:', error);
        alert('Failed to download badge. Please try again.');
    }
};
</script>

<template>
    <Head title="Badge Generation" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Badge Generation</h1>
                    <Button
                        label="Generate Selected Badges"
                        icon="pi pi-id-card"
                        class="gradient-btn"
                        :disabled="selectedAttendees.length === 0"
                        @click="generateBulk"
                    />
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                            <Button
                                label="Clear Filters"
                                icon="pi pi-filter-slash"
                                severity="secondary"
                                @click="filters = { type: null, badge_status: null }; searchBadges()"
                            />
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
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

                        <Column header="Actions" style="width: 200px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button
                                        v-if="!slotProps.data.badge_generated_at"
                                        icon="pi pi-plus"
                                        label="Generate"
                                        severity="success"
                                        size="small"
                                        @click="generateSingle(slotProps.data.id)"
                                    />
                                    <Button
                                        v-else
                                        icon="pi pi-download"
                                        label="Download"
                                        severity="info"
                                        size="small"
                                        @click="downloadBadge(slotProps.data.id)"
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
