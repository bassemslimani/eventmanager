<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import CustomButton from '@/Components/CustomButton.vue';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import { useToast } from 'primevue/usetoast';

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
const toast = useToast();
const page = usePage();

// Password verification modal
const showPasswordDialog = ref(false);
const selectedEvent = ref<Event | null>(null);
const adminPassword = ref('');
const isClearing = ref(false);
const passwordError = ref('');

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

const openClearCheckinsDialog = (event: Event) => {
    // Check if user is admin
    const user = page.props.auth?.user as any;
    if (!user || user.role !== 'admin') {
        toast.add({
            severity: 'error',
            summary: 'Unauthorized',
            detail: 'Only administrators can clear check-ins.',
            life: 3000,
        });
        return;
    }

    selectedEvent.value = event;
    adminPassword.value = '';
    passwordError.value = '';
    showPasswordDialog.value = true;
};

const clearAllCheckins = async () => {
    if (!selectedEvent.value) return;

    if (!adminPassword.value) {
        passwordError.value = 'Password is required';
        return;
    }

    isClearing.value = true;
    passwordError.value = '';

    try {
        const response = await axios.post(`/events/${selectedEvent.value.id}/check-ins/clear`, {
            password: adminPassword.value,
        });

        if (response.data.success) {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: response.data.message,
                life: 5000,
            });
            showPasswordDialog.value = false;
            adminPassword.value = '';
            selectedEvent.value = null;
        }
    } catch (error: any) {
        if (error.response?.status === 422) {
            passwordError.value = error.response.data.errors?.password?.[0] || 'Invalid password';
        } else if (error.response?.status === 403) {
            toast.add({
                severity: 'error',
                summary: 'Unauthorized',
                detail: 'Only administrators can clear check-ins.',
                life: 3000,
            });
            showPasswordDialog.value = false;
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.response?.data?.message || 'Failed to clear check-ins',
                life: 3000,
            });
        }
    } finally {
        isClearing.value = false;
    }
};
</script>

<template>
    <Head title="Events" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-3 sm:p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 sm:mb-6">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">Events</h1>
                        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                            Manage your events and conferences
                        </p>
                    </div>
                    <CustomButton
                        label="Create Event"
                        icon="pi-plus"
                        severity="primary"
                        class="w-full sm:w-auto"
                        @click="router.visit('/events/create')"
                    />
                </div>

                <!-- Mobile View - Cards -->
                <div class="sm:hidden space-y-3 mb-4">
                    <Card v-for="event in events.data" :key="event.id">
                        <template #content>
                            <div class="space-y-3">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-base mb-1">{{ event.name }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            {{ event.location }}
                                        </p>
                                    </div>
                                    <Tag
                                        :value="event.status"
                                        :severity="getStatusSeverity(event.status)"
                                        class="ml-2"
                                    />
                                </div>

                                <div class="text-sm">
                                    <span class="text-gray-500">Date:</span>
                                    <span class="ml-2 font-medium">{{ new Date(event.date).toLocaleDateString() }}</span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2">
                                    <Button
                                        icon="pi pi-id-card"
                                        label="Badges"
                                        severity="success"
                                        size="small"
                                        class="text-xs"
                                        @click="router.visit(`/events/${event.id}/badge-designer`)"
                                    />
                                    <Button
                                        icon="pi pi-refresh"
                                        label="Clear Check-ins"
                                        severity="danger"
                                        size="small"
                                        class="text-xs"
                                        v-if="$page.props.auth?.user?.role === 'admin'"
                                        @click="openClearCheckinsDialog(event)"
                                    />
                                    <Button
                                        icon="pi pi-pencil"
                                        label="Edit"
                                        severity="warning"
                                        size="small"
                                        class="text-xs"
                                        @click="router.visit(`/events/${event.id}/edit`)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        label="Delete"
                                        severity="danger"
                                        size="small"
                                        outlined
                                        class="text-xs"
                                        @click="deleteEvent(event.id)"
                                    />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <div v-if="events.data.length === 0" class="text-center py-12">
                        <i class="pi pi-calendar text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">No events found</p>
                    </div>
                </div>

                <!-- Desktop View - Table -->
                <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
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

                        <Column header="Actions" style="width: 250px">
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
                                        v-if="$page.props.auth?.user?.role === 'admin'"
                                        icon="pi pi-refresh"
                                        severity="danger"
                                        size="small"
                                        v-tooltip.top="'Clear All Check-ins'"
                                        @click="openClearCheckinsDialog(slotProps.data)"
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

        <!-- Password Verification Dialog -->
        <Dialog
            v-model:visible="showPasswordDialog"
            modal
            :closable="!isClearing"
            :draggable="false"
            :style="{ width: '450px' }"
            class="p-fluid"
        >
            <template #header>
                <div class="flex items-center gap-2">
                    <i class="pi pi-exclamation-triangle text-red-500 text-2xl"></i>
                    <span class="font-bold">Clear All Check-ins</span>
                </div>
            </template>

            <div class="space-y-4">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <p class="text-sm text-red-800 dark:text-red-200">
                        <strong>Warning:</strong> This will permanently delete all check-in records for
                        <strong>{{ selectedEvent?.name }}</strong> and reset all attendee check-in statuses.
                    </p>
                    <p class="text-sm text-red-700 dark:text-red-300 mt-2">
                        This action cannot be undone!
                    </p>
                </div>

                <div class="field">
                    <label for="adminPassword" class="block font-medium mb-2">
                        Enter your admin password to confirm:
                    </label>
                    <Password
                        id="adminPassword"
                        v-model="adminPassword"
                        placeholder="Admin Password"
                        :feedback="false"
                        toggleMask
                        :disabled="isClearing"
                        :class="{ 'p-invalid': passwordError }"
                        @keyup.enter="clearAllCheckins"
                    />
                    <small v-if="passwordError" class="p-error">{{ passwordError }}</small>
                </div>
            </div>

            <template #footer>
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    text
                    severity="secondary"
                    :disabled="isClearing"
                    @click="showPasswordDialog = false"
                />
                <Button
                    label="Clear All Check-ins"
                    icon="pi pi-trash"
                    severity="danger"
                    :loading="isClearing"
                    @click="clearAllCheckins"
                />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
