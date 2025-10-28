<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CustomButton from '@/Components/CustomButton.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';

interface Attendee {
    id: number;
    name: string;
    email: string;
    company: string | null;
    type: string;
}

interface User {
    id: number;
    name: string;
}

interface Event {
    id: number;
    name: string;
}

interface CheckIn {
    id: number;
    scanned_at: string;
    location: string | null;
    attendee: Attendee;
    event: Event;
    scanner: User;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedCheckIns {
    data: CheckIn[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

interface Props {
    checkIns: PaginatedCheckIns;
    events: Event[];
    filters: {
        event_id?: number;
        date?: string;
    };
}

const props = defineProps<Props>();

const selectedEvent = ref(props.filters.event_id || null);
const selectedDate = ref(props.filters.date ? new Date(props.filters.date) : null);

const applyFilters = () => {
    const params: any = {};

    if (selectedEvent.value) {
        params.event_id = selectedEvent.value;
    }

    if (selectedDate.value) {
        const year = selectedDate.value.getFullYear();
        const month = String(selectedDate.value.getMonth() + 1).padStart(2, '0');
        const day = String(selectedDate.value.getDate()).padStart(2, '0');
        params.date = `${year}-${month}-${day}`;
    }

    router.get('/check-in/history', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    selectedEvent.value = null;
    selectedDate.value = null;
    router.get('/check-in/history');
};

const formatDateTime = (datetime: string) => {
    return new Date(datetime).toLocaleString();
};

const formatTime = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString();
};

const goToPage = (url: string | null) => {
    if (url) {
        router.visit(url, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const eventOptions = computed(() => {
    return [
        { label: 'All Events', value: null },
        ...props.events.map(event => ({ label: event.name, value: event.id }))
    ];
});
</script>

<template>
    <Head title="Check-in History" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-3 sm:p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-4 sm:mb-6">
                    <div class="flex items-center gap-2 sm:gap-4 mb-3 sm:mb-4">
                        <CustomButton
                            icon="pi-arrow-left"
                            size="large"
                            @click="router.visit('/check-in')"
                        />
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Check-in History</h1>
                    </div>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 px-1">
                        View all check-in records
                    </p>
                </div>

                <!-- Filters -->
                <Card class="mb-4 sm:mb-6">
                    <template #content>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Event
                                    </label>
                                    <Dropdown
                                        v-model="selectedEvent"
                                        :options="eventOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="All Events"
                                        class="w-full"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Date
                                    </label>
                                    <Calendar
                                        v-model="selectedDate"
                                        placeholder="Select date"
                                        class="w-full"
                                        dateFormat="yy-mm-dd"
                                    />
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <CustomButton
                                    label="Apply"
                                    icon="pi-filter"
                                    severity="primary"
                                    class="flex-1 sm:flex-none"
                                    @click="applyFilters"
                                />
                                <CustomButton
                                    label="Clear"
                                    icon="pi-times"
                                    severity="secondary"
                                    class="flex-1 sm:flex-none"
                                    @click="clearFilters"
                                />
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Statistics -->
                <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-4 sm:mb-6">
                    <Card>
                        <template #content>
                            <div class="text-center py-2 sm:py-4">
                                <div class="text-xl sm:text-2xl md:text-3xl font-bold text-blue-600">{{ checkIns.total }}</div>
                                <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Total</div>
                            </div>
                        </template>
                    </Card>

                    <Card>
                        <template #content>
                            <div class="text-center py-2 sm:py-4">
                                <div class="text-xl sm:text-2xl md:text-3xl font-bold text-green-600">{{ checkIns.data.length }}</div>
                                <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">This Page</div>
                            </div>
                        </template>
                    </Card>

                    <Card>
                        <template #content>
                            <div class="text-center py-2 sm:py-4">
                                <div class="text-xl sm:text-2xl md:text-3xl font-bold text-purple-600">{{ checkIns.last_page }}</div>
                                <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Pages</div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Check-ins List/Table -->
                <div v-if="checkIns.data.length === 0" class="text-center py-12">
                    <i class="pi pi-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No check-ins found</p>
                </div>

                <!-- Mobile View - Cards -->
                <div v-else class="space-y-3 sm:hidden mb-4">
                    <Card v-for="checkIn in checkIns.data" :key="checkIn.id">
                        <template #content>
                            <div class="space-y-3">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-base mb-1">{{ checkIn.attendee.name }}</h3>
                                        <p class="text-xs text-gray-500 break-all">{{ checkIn.attendee.email }}</p>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold capitalize ml-2"
                                          :class="{
                                              'bg-blue-100 text-blue-800': checkIn.attendee.type === 'attendee',
                                              'bg-purple-100 text-purple-800': checkIn.attendee.type === 'vip',
                                              'bg-green-100 text-green-800': checkIn.attendee.type === 'speaker',
                                              'bg-orange-100 text-orange-800': checkIn.attendee.type === 'sponsor',
                                          }">
                                        {{ checkIn.attendee.type }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div v-if="checkIn.attendee.company">
                                        <p class="text-xs text-gray-500">Company</p>
                                        <p class="font-medium break-words">{{ checkIn.attendee.company }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Event</p>
                                        <p class="font-medium break-words">{{ checkIn.event.name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Time</p>
                                        <p class="font-medium">{{ formatTime(checkIn.scanned_at) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Scanned By</p>
                                        <p class="font-medium">{{ checkIn.scanner.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Desktop View - Table -->
                <Card class="hidden sm:block">
                    <template #content>
                        <div class="overflow-x-auto">
                            <DataTable
                                :value="checkIns.data"
                                stripedRows
                                responsiveLayout="scroll"
                            >
                                <Column field="attendee.name" header="Attendee Name" sortable>
                                    <template #body="slotProps">
                                        <div>
                                            <div class="font-semibold">{{ slotProps.data.attendee.name }}</div>
                                            <div class="text-sm text-gray-500">{{ slotProps.data.attendee.email }}</div>
                                        </div>
                                    </template>
                                </Column>

                                <Column field="attendee.type" header="Type" sortable>
                                    <template #body="slotProps">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold capitalize"
                                              :class="{
                                                  'bg-blue-100 text-blue-800': slotProps.data.attendee.type === 'attendee',
                                                  'bg-purple-100 text-purple-800': slotProps.data.attendee.type === 'vip',
                                                  'bg-green-100 text-green-800': slotProps.data.attendee.type === 'speaker',
                                                  'bg-orange-100 text-orange-800': slotProps.data.attendee.type === 'sponsor',
                                              }">
                                            {{ slotProps.data.attendee.type }}
                                        </span>
                                    </template>
                                </Column>

                                <Column field="attendee.company" header="Company" sortable />

                                <Column field="event.name" header="Event" sortable />

                                <Column field="scanned_at" header="Check-in Time" sortable>
                                    <template #body="slotProps">
                                        <div>
                                            <div class="font-medium">{{ formatTime(slotProps.data.scanned_at) }}</div>
                                            <div class="text-xs text-gray-500">{{ formatDateTime(slotProps.data.scanned_at) }}</div>
                                        </div>
                                    </template>
                                </Column>

                                <Column field="scanner.name" header="Scanned By" sortable />

                                <Column field="location" header="Location">
                                    <template #body="slotProps">
                                        {{ slotProps.data.location || '-' }}
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </template>
                </Card>

                <!-- Pagination -->
                <div v-if="checkIns.last_page > 1" class="flex flex-wrap justify-center mt-4 sm:mt-6 gap-1 sm:gap-2">
                    <CustomButton
                        v-for="link in checkIns.links"
                        :key="link.label"
                        :label="link.label.replace('&laquo;', '«').replace('&raquo;', '»')"
                        :disabled="!link.url || link.active"
                        :severity="link.active ? 'primary' : 'secondary'"
                        size="small"
                        class="min-w-[2.5rem]"
                        @click="goToPage(link.url)"
                    />
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mt-4 sm:mt-6">
                    <button
                        class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl shadow-lg hover:shadow-xl active:scale-95 sm:hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-4"
                        @click="router.visit('/check-in')"
                    >
                        <i class="pi pi-camera text-xl sm:text-2xl"></i>
                        <span class="font-semibold text-sm sm:text-base">QR Code Scanner</span>
                    </button>

                    <button
                        class="bg-gradient-to-r from-green-500 to-teal-500 text-white rounded-xl shadow-lg hover:shadow-xl active:scale-95 sm:hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-4"
                        @click="router.visit('/check-in/manual')"
                    >
                        <i class="pi pi-pencil text-xl sm:text-2xl"></i>
                        <span class="font-semibold text-sm sm:text-base">Manual Check-in</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
