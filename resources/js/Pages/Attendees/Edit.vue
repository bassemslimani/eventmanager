<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import CustomButton from '@/Components/CustomButton.vue';

interface Event {
    id: number;
    name: string;
}

interface Attendee {
    id: number;
    event_id: number | null;
    type: string;
    name: string;
    name_ar: string | null;
    email: string;
    mobile: string | null;
    company: string | null;
    company_ar: string | null;
    category: string | null;
    role: string | null;
    department: string | null;
}

interface Props {
    attendee: Attendee;
    events: Event[];
}

const props = defineProps<Props>();

const form = useForm({
    event_id: props.attendee.event_id,
    type: props.attendee.type,
    name: props.attendee.name,
    name_ar: props.attendee.name_ar || '',
    email: props.attendee.email,
    mobile: props.attendee.mobile || '',
    company: props.attendee.company || '',
    company_ar: props.attendee.company_ar || '',
    category: props.attendee.category,
    role: props.attendee.role || '',
    department: props.attendee.department || '',
});

const typeOptions = [
    { label: 'Exhibitor', value: 'exhibitor' },
    { label: 'Guest', value: 'guest' },
    { label: 'Organizer', value: 'organizer' },
    { label: 'Visitor', value: 'visitor' },
];

const categoryOptions = [
    { label: 'Freelancer', value: 'freelancer' },
    { label: 'Company', value: 'company' },
    { label: 'Visitor', value: 'visitor' },
];

const submit = () => {
    form.put(`/attendees/${props.attendee.id}`);
};
</script>

<template>
    <Head title="Edit Attendee" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Edit Attendee</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Update attendee information
                    </p>
                </div>

                <!-- Form (same structure as Create.vue) -->
                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 space-y-6">
                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Type *</label>
                        <Dropdown
                            v-model="form.type"
                            :options="typeOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select type"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.type }"
                        />
                        <small v-if="form.errors.type" class="text-red-500">{{ form.errors.type }}</small>
                    </div>

                    <!-- Event -->
                    <div v-if="events.length > 0">
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
                            Event <span class="text-red-500">*</span>
                        </label>
                        <Dropdown
                            v-model="form.event_id"
                            :options="events"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select event (required)"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.event_id }"
                        />
                        <small v-if="form.errors.event_id" class="text-red-500">{{ form.errors.event_id }}</small>
                    </div>
                    <div v-else class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                        <p class="text-sm">⚠️ No active events found. Please create an event first.</p>
                    </div>

                    <!-- Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Name (English) *</label>
                            <InputText
                                v-model="form.name"
                                placeholder="John Doe"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.name }"
                            />
                            <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Name (Arabic)</label>
                            <InputText
                                v-model="form.name_ar"
                                placeholder="جون دو"
                                class="w-full"
                                dir="rtl"
                            />
                        </div>
                    </div>

                    <!-- Email & Mobile -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Email *</label>
                            <InputText
                                v-model="form.email"
                                type="email"
                                placeholder="john@example.com"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.email }"
                            />
                            <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Mobile</label>
                            <InputText
                                v-model="form.mobile"
                                placeholder="+966 50 123 4567"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <!-- Company (for Exhibitors and Guests) -->
                    <div v-if="form.type !== 'organizer'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Company (English)</label>
                            <InputText
                                v-model="form.company"
                                placeholder="Company Name"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Company (Arabic)</label>
                            <InputText
                                v-model="form.company_ar"
                                placeholder="اسم الشركة"
                                class="w-full"
                                dir="rtl"
                            />
                        </div>
                    </div>

                    <!-- Category (for Exhibitors) -->
                    <div v-if="form.type === 'exhibitor'">
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Category</label>
                        <Dropdown
                            v-model="form.category"
                            :options="categoryOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select category"
                            class="w-full"
                        />
                    </div>

                    <!-- Role & Department (for Organizers) -->
                    <div v-if="form.type === 'organizer'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Role</label>
                            <InputText
                                v-model="form.role"
                                placeholder="Event Manager"
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Department</label>
                            <InputText
                                v-model="form.department"
                                placeholder="Operations"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-end">
                        <CustomButton
                            label="Cancel"
                            severity="secondary"
                            @click="$inertia.visit('/attendees')"
                            type="button"
                        />
                        <CustomButton
                            label="Update Attendee"
                            icon="pi-check"
                            severity="primary"
                            type="submit"
                            :disabled="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
