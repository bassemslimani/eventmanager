<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import { ref } from 'vue';

const form = useForm({
    name: '',
    name_ar: '',
    date: new Date(),
    location: '',
    location_ar: '',
    description: '',
    description_ar: '',
    logo: null as File | null,
    status: 'draft',
});

const logoPreview = ref<string | null>(null);

const statusOptions = [
    { label: 'Draft', value: 'draft' },
    { label: 'Active', value: 'active' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
];

const onLogoSelect = (event: any) => {
    form.logo = event.files[0];
    logoPreview.value = URL.createObjectURL(event.files[0]);
};

const submit = () => {
    form.post('/events', {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Create Event" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <Button
                        label="Back to Events"
                        icon="pi pi-arrow-left"
                        severity="secondary"
                        text
                        @click="router.visit('/events')"
                        class="mb-3"
                    />
                    <h1 class="text-3xl font-bold text-gradient">Create New Event</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Add a new event to the system
                    </p>
                </div>

                <!-- Form -->
                <Card class="glass-card">
                    <template #content>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Event Name (English) -->
                            <div>
                                <label for="name" class="block text-sm font-medium mb-2">
                                    Event Name (English) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g. Tech Conference 2025"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.name }"
                                />
                                <small v-if="form.errors.name" class="p-error">{{ form.errors.name }}</small>
                            </div>

                            <!-- Event Name (Arabic) -->
                            <div>
                                <label for="name_ar" class="block text-sm font-medium mb-2">
                                    Event Name (Arabic)
                                </label>
                                <InputText
                                    id="name_ar"
                                    v-model="form.name_ar"
                                    placeholder="e.g. مؤتمر التقنية 2025"
                                    class="w-full"
                                    dir="rtl"
                                />
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium mb-2">
                                    Event Date <span class="text-red-500">*</span>
                                </label>
                                <Calendar
                                    id="date"
                                    v-model="form.date"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.date }"
                                />
                                <small v-if="form.errors.date" class="p-error">{{ form.errors.date }}</small>
                            </div>

                            <!-- Event Logo -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Event Logo
                                </label>
                                <FileUpload
                                    mode="basic"
                                    accept="image/*"
                                    :maxFileSize="2000000"
                                    @select="onLogoSelect"
                                    :auto="false"
                                    chooseLabel="Choose Logo"
                                    class="w-full"
                                />
                                <small class="text-gray-500">Logo will appear on badges (max 2MB)</small>

                                <!-- Logo Preview -->
                                <div v-if="logoPreview" class="mt-3">
                                    <img :src="logoPreview" alt="Logo preview" class="h-24 rounded-lg shadow-md" />
                                </div>
                            </div>

                            <!-- Location (English) -->
                            <div>
                                <label for="location" class="block text-sm font-medium mb-2">
                                    Location (English) <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="location"
                                    v-model="form.location"
                                    placeholder="e.g. Riyadh Convention Center"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.location }"
                                />
                                <small v-if="form.errors.location" class="p-error">{{ form.errors.location }}</small>
                            </div>

                            <!-- Location (Arabic) -->
                            <div>
                                <label for="location_ar" class="block text-sm font-medium mb-2">
                                    Location (Arabic)
                                </label>
                                <InputText
                                    id="location_ar"
                                    v-model="form.location_ar"
                                    placeholder="e.g. مركز الرياض للمؤتمرات"
                                    class="w-full"
                                    dir="rtl"
                                />
                            </div>

                            <!-- Description (English) -->
                            <div>
                                <label for="description" class="block text-sm font-medium mb-2">
                                    Description (English)
                                </label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    placeholder="Enter event description..."
                                    class="w-full"
                                />
                            </div>

                            <!-- Description (Arabic) -->
                            <div>
                                <label for="description_ar" class="block text-sm font-medium mb-2">
                                    Description (Arabic)
                                </label>
                                <Textarea
                                    id="description_ar"
                                    v-model="form.description_ar"
                                    rows="4"
                                    placeholder="أدخل وصف الحدث..."
                                    class="w-full"
                                    dir="rtl"
                                />
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium mb-2">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="status"
                                    v-model="form.status"
                                    :options="statusOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select status"
                                    class="w-full"
                                />
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-3 pt-4 border-t">
                                <Button
                                    type="submit"
                                    label="Create Event"
                                    icon="pi pi-check"
                                    class="gradient-btn flex-1"
                                    :loading="form.processing"
                                />
                                <Button
                                    type="button"
                                    label="Cancel"
                                    icon="pi pi-times"
                                    severity="secondary"
                                    @click="router.visit('/events')"
                                />
                            </div>
                        </form>
                    </template>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
