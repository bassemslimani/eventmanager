<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import AutoComplete from 'primevue/autocomplete';
import Message from 'primevue/message';

interface Attendee {
    id: number;
    name: string;
    email: string;
    mobile: string | null;
    company: string | null;
    type: string;
    checked_in_at: string | null;
}

interface Event {
    id: number;
    name: string;
}

interface Props {
    events: Event[];
    flash?: {
        success?: string;
        error?: string;
    };
}

const props = defineProps<Props>();

const selectedAttendee = ref<Attendee | null>(null);
const attendeeSearch = ref('');
const filteredAttendees = ref<Attendee[]>([]);
const isSearching = ref(false);

const form = useForm({
    attendee_id: null as number | null,
    event_id: props.events[0]?.id || null,
});

const searchAttendees = async (event: any) => {
    const query = event.query;
    if (query.length < 2) {
        filteredAttendees.value = [];
        return;
    }

    isSearching.value = true;

    try {
        const response = await fetch(`/api/attendees/search?q=${encodeURIComponent(query)}`, {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (response.ok) {
            const data = await response.json();
            filteredAttendees.value = data;
        }
    } catch (error) {
        console.error('Error searching attendees:', error);
    } finally {
        isSearching.value = false;
    }
};

const onAttendeeSelect = (event: any) => {
    selectedAttendee.value = event.value;
    form.attendee_id = event.value.id;
};

const submit = () => {
    if (!form.attendee_id) {
        return;
    }

    form.post('/check-in/manual', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            selectedAttendee.value = null;
            attendeeSearch.value = '';
        },
    });
};
</script>

<template>
    <Head title="Manual Check-in" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-3 sm:p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-4 sm:mb-6">
                    <div class="flex items-center gap-2 sm:gap-4 mb-3 sm:mb-4">
                        <Button
                            icon="pi pi-arrow-left"
                            text
                            size="large"
                            @click="router.visit('/check-in')"
                        />
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Manual Check-in</h1>
                    </div>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 px-1">
                        Search and check in attendees manually
                    </p>
                </div>

                <!-- Flash Messages -->
                <Message v-if="props.flash?.success" severity="success" class="mb-4">
                    {{ props.flash.success }}
                </Message>
                <Message v-if="props.flash?.error" severity="error" class="mb-4">
                    {{ props.flash.error }}
                </Message>

                <!-- Manual Check-in Form -->
                <Card class="mb-4 sm:mb-6">
                    <template #content>
                        <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
                            <!-- Event Selection -->
                            <div v-if="events.length > 1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Event
                                </label>
                                <select
                                    v-model="form.event_id"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white p-3 text-base"
                                >
                                    <option v-for="event in events" :key="event.id" :value="event.id">
                                        {{ event.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Attendee Search -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Search Attendee
                                </label>
                                <AutoComplete
                                    v-model="attendeeSearch"
                                    :suggestions="filteredAttendees"
                                    optionLabel="name"
                                    placeholder="Type name or email..."
                                    class="w-full"
                                    inputClass="w-full p-3 text-base"
                                    @complete="searchAttendees"
                                    @item-select="onAttendeeSelect"
                                >
                                    <template #option="slotProps">
                                        <div class="flex flex-col py-2">
                                            <span class="font-semibold text-base">{{ slotProps.option.name }}</span>
                                            <span class="text-sm text-gray-500 break-all">{{ slotProps.option.email }}</span>
                                            <span v-if="slotProps.option.mobile" class="text-sm text-gray-500">
                                                <i class="pi pi-phone text-xs mr-1"></i>{{ slotProps.option.mobile }}
                                            </span>
                                            <span v-if="slotProps.option.company" class="text-xs text-gray-400">
                                                {{ slotProps.option.company }}
                                            </span>
                                        </div>
                                    </template>
                                </AutoComplete>
                            </div>

                            <!-- Selected Attendee Info -->
                            <div v-if="selectedAttendee" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <h3 class="font-semibold text-base sm:text-lg mb-3">Selected Attendee</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg">
                                        <p class="text-xs sm:text-sm text-gray-500">Name</p>
                                        <p class="font-semibold text-sm sm:text-base break-words">{{ selectedAttendee.name }}</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg">
                                        <p class="text-xs sm:text-sm text-gray-500">Type</p>
                                        <p class="font-semibold text-sm sm:text-base capitalize">{{ selectedAttendee.type }}</p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded-lg sm:col-span-2">
                                        <p class="text-xs sm:text-sm text-gray-500">Email</p>
                                        <p class="font-semibold text-sm sm:text-base break-all">{{ selectedAttendee.email }}</p>
                                    </div>
                                    <div v-if="selectedAttendee.mobile" class="bg-white dark:bg-gray-800 p-3 rounded-lg sm:col-span-2">
                                        <p class="text-xs sm:text-sm text-gray-500">Phone Number</p>
                                        <p class="font-semibold text-sm sm:text-base">{{ selectedAttendee.mobile }}</p>
                                    </div>
                                    <div v-if="selectedAttendee.company" class="bg-white dark:bg-gray-800 p-3 rounded-lg sm:col-span-2">
                                        <p class="text-xs sm:text-sm text-gray-500">Company</p>
                                        <p class="font-semibold text-sm sm:text-base break-words">{{ selectedAttendee.company }}</p>
                                    </div>
                                </div>

                                <!-- Already Checked In Warning -->
                                <Message v-if="selectedAttendee.checked_in_at" severity="warn" class="mt-4 text-sm">
                                    Already checked in at {{ new Date(selectedAttendee.checked_in_at).toLocaleString() }}
                                </Message>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-2">
                                <Button
                                    type="submit"
                                    label="Check In"
                                    icon="pi pi-check"
                                    class="gradient-btn w-full sm:w-auto"
                                    size="large"
                                    :disabled="!selectedAttendee || form.processing"
                                    :loading="form.processing"
                                />
                            </div>
                        </form>
                    </template>
                </Card>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <button
                        class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl shadow-lg hover:shadow-xl active:scale-95 sm:hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-4"
                        @click="router.visit('/check-in')"
                    >
                        <i class="pi pi-camera text-xl sm:text-2xl"></i>
                        <span class="font-semibold text-sm sm:text-base">QR Code Scanner</span>
                    </button>

                    <button
                        class="bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl shadow-lg hover:shadow-xl active:scale-95 sm:hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-4"
                        @click="router.visit('/check-in/history')"
                    >
                        <i class="pi pi-history text-xl sm:text-2xl"></i>
                        <span class="font-semibold text-sm sm:text-base">Check-in History</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
