<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import { ref } from 'vue';

interface Event {
    id: number;
    name: string;
    status: string;
}

interface Props {
    events: Event[];
    userRole: string;
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'agent',
    event_ids: [] as number[],
});

const roleOptions = ref([
    { label: 'Agent (Scanner Only)', value: 'agent' },
    { label: 'Event Manager', value: 'event_manager' },
]);

const submit = () => {
    if (props.userRole === 'admin') {
        form.post('/users');
    } else {
        form.post('/event-users');
    }
};

const goBack = () => {
    if (props.userRole === 'admin') {
        router.visit('/users');
    } else {
        router.visit('/event-users');
    }
};
</script>

<template>
    <Head title="Create User" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <Button
                        label="Back"
                        icon="pi pi-arrow-left"
                        severity="secondary"
                        text
                        @click="goBack"
                        class="mb-3"
                    />
                    <h1 class="text-3xl font-bold text-gradient">Create New User</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Add a new Event Manager or Agent to your events
                    </p>
                </div>

                <!-- Form -->
                <Card class="glass-card">
                    <template #content>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Enter full name"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.name }"
                                />
                                <small v-if="form.errors.name" class="p-error">{{ form.errors.name }}</small>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Enter email address"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.email }"
                                />
                                <small v-if="form.errors.email" class="p-error">{{ form.errors.email }}</small>
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium mb-2">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <Password
                                    id="password"
                                    v-model="form.password"
                                    toggleMask
                                    :feedback="true"
                                    placeholder="Enter password (min. 8 characters)"
                                    class="w-full"
                                    inputClass="w-full"
                                    :class="{ 'p-invalid': form.errors.password }"
                                />
                                <small v-if="form.errors.password" class="p-error">{{ form.errors.password }}</small>
                            </div>

                            <!-- Role -->
                            <div>
                                <label for="role" class="block text-sm font-medium mb-2">
                                    User Role <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="role"
                                    v-model="form.role"
                                    :options="roleOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select role"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.role }"
                                />
                                <small class="text-gray-500 block mt-1">
                                    <strong>Agent:</strong> Can only access scanner interface<br>
                                    <strong>Event Manager:</strong> Can manage attendees and create agents
                                </small>
                                <small v-if="form.errors.role" class="p-error">{{ form.errors.role }}</small>
                            </div>

                            <!-- Events -->
                            <div>
                                <label for="events" class="block text-sm font-medium mb-2">
                                    Assign to Events <span class="text-red-500">*</span>
                                </label>
                                <MultiSelect
                                    id="events"
                                    v-model="form.event_ids"
                                    :options="events"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Select events"
                                    :maxSelectedLabels="3"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.event_ids }"
                                />
                                <small class="text-gray-500 block mt-1">
                                    User will have access to selected events only
                                </small>
                                <small v-if="form.errors.event_ids" class="p-error">{{ form.errors.event_ids }}</small>
                            </div>

                            <!-- No Events Warning -->
                            <div v-if="events.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-lg">
                                <div class="flex items-center">
                                    <i class="pi pi-exclamation-triangle text-yellow-600 dark:text-yellow-400 mr-3"></i>
                                    <div>
                                        <p class="font-semibold text-yellow-800 dark:text-yellow-300">No Events Available</p>
                                        <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                            {{ userRole === 'admin' ? 'Please create an event first before adding users.' : 'You have not been assigned to any events yet.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-3 pt-4 border-t">
                                <Button
                                    type="submit"
                                    label="Create User"
                                    icon="pi pi-check"
                                    class="gradient-btn flex-1"
                                    :loading="form.processing"
                                    :disabled="events.length === 0"
                                />
                                <Button
                                    type="button"
                                    label="Cancel"
                                    icon="pi pi-times"
                                    severity="secondary"
                                    @click="goBack"
                                />
                            </div>
                        </form>
                    </template>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
