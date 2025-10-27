<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import FileUpload from 'primevue/fileupload';
import Dropdown from 'primevue/dropdown';
import ProgressBar from 'primevue/progressbar';

interface Event {
    id: number;
    name: string;
}

interface Props {
    events: Event[];
}

const props = defineProps<Props>();

const selectedEvent = ref<number | null>(null);
const uploadedFile = ref<File | null>(null);
const importLogId = ref<number | null>(null);
const isUploading = ref(false);
const isProcessing = ref(false);
const importResult = ref<any>(null);

const onFileSelect = (event: any) => {
    uploadedFile.value = event.files[0];
};

const uploadFile = async () => {
    if (!uploadedFile.value) {
        alert('Please select a file first.');
        return;
    }

    isUploading.value = true;
    const formData = new FormData();
    formData.append('file', uploadedFile.value);
    if (selectedEvent.value) {
        formData.append('event_id', selectedEvent.value.toString());
    }

    try {
        const response = await fetch('/import/upload', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            importLogId.value = data.import_log_id;
            processImport();
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Failed to upload file.');
    } finally {
        isUploading.value = false;
    }
};

const processImport = async () => {
    if (!importLogId.value) return;

    isProcessing.value = true;

    try {
        const response = await fetch('/import/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                import_log_id: importLogId.value,
                event_id: selectedEvent.value,
            }),
        });

        const data = await response.json();
        importResult.value = data;

        if (data.success) {
            setTimeout(() => {
                router.visit('/attendees');
            }, 3000);
        }
    } catch (error) {
        console.error('Process error:', error);
        alert('Failed to process import.');
    } finally {
        isProcessing.value = false;
    }
};

const downloadTemplate = () => {
    window.location.href = '/attendees/import/template';
};
</script>

<template>
    <Head title="Import Attendees" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Import Attendees</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Upload an Excel file to import multiple attendees
                    </p>
                </div>

                <!-- Template Download -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold mb-2">Download Template</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Download the Excel template with sample data
                            </p>
                        </div>
                        <Button
                            label="Download Template"
                            icon="pi pi-download"
                            class="gradient-btn"
                            @click="downloadTemplate"
                        />
                    </div>
                </div>

                <!-- Upload Form -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
                        <div class="space-y-6">
                            <!-- Event Selection -->
                            <div v-if="events.length > 0">
                                <label class="block text-sm font-medium mb-2">
                                    Event (Optional)
                                </label>
                                <Dropdown
                                    v-model="selectedEvent"
                                    :options="events"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Select an event"
                                    class="w-full"
                                />
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Upload Excel File
                                </label>
                                <FileUpload
                                    mode="basic"
                                    accept=".xlsx,.xls,.csv"
                                    :maxFileSize="10000000"
                                    @select="onFileSelect"
                                    :auto="false"
                                    chooseLabel="Choose File"
                                />
                            </div>

                            <!-- Upload Button -->
                            <Button
                                label="Upload and Import"
                                icon="pi pi-upload"
                                class="gradient-btn w-full"
                                :loading="isUploading || isProcessing"
                                :disabled="!uploadedFile"
                                @click="uploadFile"
                            />

                            <!-- Progress -->
                            <div v-if="isProcessing" class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Processing import...</p>
                                <ProgressBar mode="indeterminate" />
                            </div>

                            <!-- Result -->
                            <div v-if="importResult" class="mt-4 border-2 rounded-xl p-6" :class="importResult.success ? 'border-green-500' : 'border-red-500'">
                                <div class="text-center">
                                    <i :class="['pi text-4xl mb-2', importResult.success ? 'pi-check-circle text-green-500' : 'pi-times-circle text-red-500']"></i>
                                    <p class="font-semibold">{{ importResult.message }}</p>
                                    <div v-if="importResult.import_log" class="mt-4 text-sm text-left">
                                        <p>Total Records: {{ importResult.import_log.total_records }}</p>
                                        <p>Processed: {{ importResult.import_log.processed }}</p>
                                        <p>Failed: {{ importResult.import_log.failed }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button
                        class="bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4"
                        @click="router.visit('/import/history')"
                    >
                        <i class="pi pi-history text-2xl"></i>
                        <span class="font-semibold">Import History</span>
                    </button>

                    <button
                        class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4"
                        @click="router.visit('/attendees')"
                    >
                        <i class="pi pi-users text-2xl"></i>
                        <span class="font-semibold">View Attendees</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
