<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';

interface Event {
    id: number;
    name: string;
    name_ar: string | null;
    date: string;
    location: string;
    location_ar: string | null;
    description: string | null;
    description_ar: string | null;
    status: string;
}

interface Props {
    event: Event;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.event.name,
    name_ar: props.event.name_ar || '',
    date: new Date(props.event.date),
    location: props.event.location,
    location_ar: props.event.location_ar || '',
    description: props.event.description || '',
    description_ar: props.event.description_ar || '',
    status: props.event.status,
});

const statusOptions = [
    { label: 'Draft', value: 'draft' },
    { label: 'Active', value: 'active' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
];

const submit = () => {
    form.put(`/events/${props.event.id}`);
};
</script>

<template>
    <Head :title="`Edit ${event.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
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
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Event</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Update event information
                    </p>
                </div>

                <!-- Form -->
                <Card class="bg-white dark:bg-gray-800 rounded-xl shadow-md">
                    <template #content>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Event Name (English) -->
                            <div>
                                <label for="name" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
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
                                <label for="name_ar" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
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
                                <label for="date" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
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

                            <!-- Location (English) -->
                            <div>
                                <label for="location" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
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
                                <label for="location_ar" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
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
                                <label for="description" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
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
                                <label for="description_ar" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
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
                                <label for="status" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
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
                                    label="Update Event"
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
