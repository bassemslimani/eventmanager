<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import CustomButton from '@/Components/CustomButton.vue';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import ProgressBar from 'primevue/progressbar';

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

interface BatchDownload {
    id: number;
    event_name: string | null;
    status: string;
    total_attendees: number;
    processed_attendees: number;
    successful_badges: number;
    failed_badges: number;
    progress_percentage: number;
    is_ready_for_download: boolean;
    error_message: string | null;
    started_at: string | null;
    completed_at: string | null;
    created_at: string;
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
        search?: string;
        per_page?: number;
    };
}

const props = defineProps<Props>();

const filters = ref({
    event_id: props.filters.event_id || null,
    type: props.filters.type || null,
    badge_status: props.filters.badge_status || null,
    search: props.filters.search || '',
    per_page: props.filters.per_page || 20,
});

const selectedAttendees = ref<Attendee[]>([]);
const showProgressDialog = ref(false);
const progressMessage = ref('');
const progressPercent = ref(0);

// Batch downloads
const batchDownloads = ref<BatchDownload[]>([]);
const showBatchDialog = ref(false);
const pollingInterval = ref<number | null>(null);

const typeOptions = [
    { label: 'All Types', value: null },
    { label: 'Exhibitors', value: 'exhibitor' },
    { label: 'Guests', value: 'guest' },
    { label: 'Organizers', value: 'organizer' },
    { label: 'Visitors', value: 'visitor' },
];

const statusOptions = [
    { label: 'All Status', value: null },
    { label: 'Generated', value: 'generated' },
    { label: 'Pending', value: 'pending' },
];

const perPageOptions = [
    { label: '20 per page', value: 20 },
    { label: '50 per page', value: 50 },
    { label: '100 per page', value: 100 },
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

const downloadBulk = async () => {
    if (selectedAttendees.value.length === 0) {
        alert('Please select at least one attendee.');
        return;
    }

    // Filter only generated badges
    const generatedAttendees = selectedAttendees.value.filter(a => a.badge_generated_at !== null);

    if (generatedAttendees.length === 0) {
        alert('No generated badges found in selection. Please generate badges first.');
        return;
    }

    const attendeeIds = generatedAttendees.map(a => a.id);
    const totalBadges = attendeeIds.length;

    // Show progress dialog
    showProgressDialog.value = true;
    progressPercent.value = 0;
    progressMessage.value = `Preparing to download ${totalBadges} badge${totalBadges > 1 ? 's' : ''}...`;

    try {
        // Simulate progress (since we can't track actual server progress)
        const progressInterval = setInterval(() => {
            if (progressPercent.value < 90) {
                progressPercent.value += 10;
                const current = Math.floor((progressPercent.value / 100) * totalBadges);
                progressMessage.value = `Generating badge ${current} of ${totalBadges}...`;
            }
        }, 500);

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Make request
        const response = await fetch('/badges/download-bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/octet-stream',
            },
            body: JSON.stringify({ attendee_ids: attendeeIds }),
        });

        clearInterval(progressInterval);

        if (!response.ok) {
            throw new Error('Failed to generate badges');
        }

        progressPercent.value = 95;
        progressMessage.value = 'Creating ZIP file...';

        // Get the blob
        const blob = await response.blob();

        progressPercent.value = 100;
        progressMessage.value = 'Download complete!';

        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `badges_${new Date().toISOString().slice(0, 10)}.zip`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        // Close dialog after short delay
        setTimeout(() => {
            showProgressDialog.value = false;
            progressPercent.value = 0;
        }, 1000);

    } catch (error) {
        console.error('Download error:', error);
        showProgressDialog.value = false;
        alert(error instanceof Error ? error.message : 'Failed to download badges. Please try again.');
    }
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

const sendEmailBulk = () => {
    if (selectedAttendees.value.length === 0) {
        alert('Please select at least one attendee.');
        return;
    }

    // Filter only generated badges with email
    const generatedAttendees = selectedAttendees.value.filter(a => a.badge_generated_at !== null && a.email);

    if (generatedAttendees.length === 0) {
        alert('No generated badges with email addresses found in selection.');
        return;
    }

    if (!confirm(`Send badge emails to ${generatedAttendees.length} attendee(s)?`)) {
        return;
    }

    const attendeeIds = generatedAttendees.map(a => a.id);
    router.post('/badges/send-email-bulk', { attendee_ids: attendeeIds }, {
        preserveState: true,
        onSuccess: () => {
            selectedAttendees.value = [];
            alert(`Badge emails are being sent to ${generatedAttendees.length} attendee(s)!`);
        },
        onError: (errors) => {
            alert('Failed to send emails. Please try again.');
            console.error(errors);
        }
    });
};

const generateAll = () => {
    if (!confirm('Generate ALL pending badges based on current filters? This may take a while.')) {
        return;
    }

    router.post('/badges/generate-all', filters.value, {
        preserveState: true,
        onSuccess: () => {
            router.reload();
        },
    });
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

// Batch download methods
const startBatchDownload = async () => {
    if (!confirm('Start batch download for all filtered attendees? This will process badges in the background.')) {
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('/badges/batch/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            body: JSON.stringify(filters.value),
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            showBatchDialog.value = true;
            await loadBatchDownloads();
            startPolling();
        } else {
            alert('Failed to start batch download.');
        }
    } catch (error) {
        console.error('Batch download error:', error);
        alert('Failed to start batch download. Please try again.');
    }
};

const loadBatchDownloads = async () => {
    try {
        const response = await fetch('/badges/batch/list');
        const data = await response.json();

        if (data.success) {
            batchDownloads.value = data.batches;
        }
    } catch (error) {
        console.error('Failed to load batch downloads:', error);
    }
};

const downloadBatchFile = (batchId: number) => {
    window.location.href = `/badges/batch/${batchId}/download`;
};

const deleteBatch = async (batchId: number) => {
    if (!confirm('Delete this batch download?')) {
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/badges/batch/${batchId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
            },
        });

        const data = await response.json();

        if (data.success) {
            await loadBatchDownloads();
        } else {
            alert('Failed to delete batch download.');
        }
    } catch (error) {
        console.error('Failed to delete batch:', error);
        alert('Failed to delete batch download.');
    }
};

const startPolling = () => {
    if (pollingInterval.value) return;

    pollingInterval.value = window.setInterval(async () => {
        await loadBatchDownloads();

        // Stop polling if no batches are processing
        const hasProcessing = batchDownloads.value.some(b => b.status === 'pending' || b.status === 'processing');
        if (!hasProcessing) {
            stopPolling();
        }
    }, 3000); // Poll every 3 seconds
};

const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

const getStatusSeverity = (status: string) => {
    switch (status) {
        case 'completed':
            return 'success';
        case 'processing':
            return 'info';
        case 'failed':
            return 'danger';
        default:
            return 'warning';
    }
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
};

// Lifecycle hooks
onMounted(async () => {
    await loadBatchDownloads();

    // Start polling if there are active batches
    const hasProcessing = batchDownloads.value.some(b => b.status === 'pending' || b.status === 'processing');
    if (hasProcessing) {
        startPolling();
    }
});

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <Head title="Badge Generation" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-3 sm:p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 sm:mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Badge Generation</h1>
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <div class="flex gap-2">
                            <CustomButton
                                label="Generate All"
                                icon="pi-sparkles"
                                severity="warning"
                                class="flex-1 sm:flex-initial"
                                @click="generateAll"
                                v-tooltip.bottom="'Generate all pending badges based on current filters'"
                            />
                            <CustomButton
                                label="Batch Download All"
                                icon="pi-cloud-download"
                                severity="help"
                                class="flex-1 sm:flex-initial"
                                @click="startBatchDownload"
                                v-tooltip.bottom="'Download all filtered badges as ZIP in background'"
                            />
                            <CustomButton
                                label="View Batches"
                                icon="pi-list"
                                severity="secondary"
                                class="flex-1 sm:flex-initial"
                                @click="showBatchDialog = true; loadBatchDownloads()"
                                :badge="batchDownloads.length > 0 ? batchDownloads.length.toString() : undefined"
                                v-tooltip.bottom="'View batch download history'"
                            />
                        </div>
                        <div class="flex gap-2">
                            <CustomButton
                                label="Generate Selected"
                                icon="pi-id-card"
                                severity="primary"
                                class="flex-1 sm:flex-initial"
                                :disabled="selectedAttendees.length === 0"
                                @click="generateBulk"
                            />
                            <CustomButton
                                label="Email Selected"
                                icon="pi-send"
                                severity="success"
                                class="flex-1 sm:flex-initial"
                                :disabled="selectedAttendees.length === 0"
                                @click="sendEmailBulk"
                                v-tooltip.bottom="'Send badges via email to selected attendees'"
                            />
                            <CustomButton
                                label="Download Selected"
                                icon="pi-download"
                                severity="info"
                                class="flex-1 sm:flex-initial"
                                :disabled="selectedAttendees.length === 0"
                                @click="downloadBulk"
                            />
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-3 sm:p-4 mb-4 sm:mb-6">
                    <!-- Search Bar -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Search</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="pi pi-search text-gray-400"></i>
                            </span>
                            <InputText
                                v-model="filters.search"
                                placeholder="Search by name, email, or mobile..."
                                class="w-full pl-10"
                                @keyup.enter="searchBadges"
                            />
                            <CustomButton
                                v-if="filters.search"
                                icon="pi-times"
                                severity="secondary"
                                text
                                rounded
                                class="absolute inset-y-0 right-0"
                                @click="filters.search = ''; searchBadges()"
                            />
                        </div>
                    </div>

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
                                @click="filters = { event_id: null, type: null, badge_status: null, search: '' }; searchBadges()"
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

                <!-- Pagination Controls -->
                <div class="flex justify-between items-center mb-4 bg-white dark:bg-gray-800 rounded-xl shadow-md p-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Items per page:</span>
                        <Dropdown
                            v-model="filters.per_page"
                            :options="perPageOptions"
                            optionLabel="label"
                            optionValue="value"
                            @change="searchBadges"
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
                        v-model:selection="selectedAttendees"
                        :value="attendees.data"
                        stripedRows
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

        <!-- Progress Dialog -->
        <Dialog
            v-model:visible="showProgressDialog"
            modal
            :closable="false"
            :draggable="false"
            header="Downloading Badges"
            :style="{ width: '450px' }"
        >
            <div class="flex flex-col gap-4 py-4">
                <div class="text-center">
                    <i class="pi pi-download text-6xl text-blue-500 mb-4"></i>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        {{ progressMessage }}
                    </p>
                </div>
                <ProgressBar :value="progressPercent" :showValue="true" class="h-6">
                    <template #default="{ value }">
                        <span class="text-white font-semibold">{{ value }}%</span>
                    </template>
                </ProgressBar>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                    Please wait while we prepare your badges...
                </p>
            </div>
        </Dialog>

        <!-- Batch Downloads Dialog -->
        <Dialog
            v-model:visible="showBatchDialog"
            modal
            header="Batch Downloads"
            :style="{ width: '900px' }"
            :maximizable="true"
        >
            <div v-if="batchDownloads.length === 0" class="text-center py-8">
                <i class="pi pi-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No batch downloads yet. Start a batch download to see progress here.</p>
            </div>

            <div v-else class="space-y-4">
                <Card v-for="batch in batchDownloads" :key="batch.id" class="shadow-sm">
                    <template #content>
                        <div class="space-y-3">
                            <!-- Header -->
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-bold text-lg">Batch #{{ batch.id }}</h3>
                                        <Tag
                                            :value="batch.status.toUpperCase()"
                                            :severity="getStatusSeverity(batch.status)"
                                        />
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ batch.event_name || 'All Events' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                        Started: {{ formatDate(batch.created_at) }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <CustomButton
                                        v-if="batch.is_ready_for_download"
                                        icon="pi-download"
                                        label="Download ZIP"
                                        severity="success"
                                        size="small"
                                        @click="downloadBatchFile(batch.id)"
                                    />
                                    <CustomButton
                                        icon="pi-trash"
                                        severity="danger"
                                        size="small"
                                        text
                                        @click="deleteBatch(batch.id)"
                                        v-tooltip.top="'Delete this batch'"
                                    />
                                </div>
                            </div>

                            <!-- Progress -->
                            <div v-if="batch.status === 'processing' || batch.status === 'pending'">
                                <div class="flex justify-between text-sm mb-2">
                                    <span>Progress: {{ batch.processed_attendees }} / {{ batch.total_attendees }} badges</span>
                                    <span class="font-semibold">{{ batch.progress_percentage }}%</span>
                                </div>
                                <ProgressBar :value="batch.progress_percentage" :showValue="false" class="h-4" />
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-3 gap-4 text-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ batch.total_attendees }}</p>
                                    <p class="text-xs text-gray-500">Total</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-green-600">{{ batch.successful_badges }}</p>
                                    <p class="text-xs text-gray-500">Success</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-red-600">{{ batch.failed_badges }}</p>
                                    <p class="text-xs text-gray-500">Failed</p>
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div v-if="batch.error_message" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                <p class="text-sm text-red-800 dark:text-red-200">
                                    <i class="pi pi-exclamation-triangle mr-2"></i>
                                    {{ batch.error_message }}
                                </p>
                            </div>

                            <!-- Completed Message -->
                            <div v-if="batch.status === 'completed'" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    <i class="pi pi-check-circle mr-2"></i>
                                    Batch completed successfully! Click "Download ZIP" to get your badges.
                                </p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <template #footer>
                <div class="flex justify-between items-center">
                    <p class="text-sm text-gray-500">
                        <i class="pi pi-info-circle mr-2"></i>
                        Batches are processed in chunks of 50 to prevent timeouts.
                    </p>
                    <CustomButton
                        label="Close"
                        icon="pi-times"
                        severity="secondary"
                        @click="showBatchDialog = false"
                    />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
