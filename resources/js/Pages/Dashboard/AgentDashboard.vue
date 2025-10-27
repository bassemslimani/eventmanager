<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { ref } from 'vue';

interface Event {
    id: number;
    name: string;
    date: string;
    location: string;
}

interface Props {
    todayCheckIns: number;
    myEvents: Event[];
    userRole: string;
}

const props = defineProps<Props>();

const selectedEvent = ref<Event | null>(props.myEvents[0] || null);

const goToScanner = () => {
    router.visit('/check-in');
};

const goToManualCheckIn = () => {
    router.visit('/check-in/manual');
};
</script>

<template>
    <Head title="Scanner Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                        Scanner Dashboard
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Quick access to check-in attendees
                    </p>
                </div>

                <!-- Today's Stats -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
                    <div class="text-center">
                        <i class="pi pi-check-circle text-6xl text-green-500 mb-4"></i>
                        <h3 class="text-5xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ todayCheckIns }}
                        </h3>
                        <p class="text-lg text-gray-600 dark:text-gray-400">
                            Check-ins Today
                        </p>
                    </div>
                </div>

                <!-- My Events -->
                <div v-if="myEvents.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">My Events</h3>
                        <div class="space-y-3">
                            <div
                                v-for="event in myEvents"
                                :key="event.id"
                                class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-lg">{{ event.name }}</h4>
                                        <p class="text-sm text-gray-500">
                                            <i class="pi pi-calendar mr-2"></i>
                                            {{ event.date }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            <i class="pi pi-map-marker mr-2"></i>
                                            {{ event.location }}
                                        </p>
                                    </div>
                                    <i class="pi pi-calendar-check text-3xl text-blue-600"></i>
                                </div>
                            </div>
                        </div>
                </div>

                <!-- No Events Warning -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6 border-2 border-yellow-500">
                    <div class="text-center">
                        <i class="pi pi-exclamation-triangle text-5xl text-yellow-500 mb-3"></i>
                        <h3 class="text-xl font-semibold mb-2">No Events Assigned</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Please contact your Event Manager to be assigned to events.
                        </p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- QR Scanner Button -->
                    <button
                        class="gradient-btn magnetic-btn flex flex-col items-center justify-center gap-3 py-8 relative overflow-hidden group"
                        :disabled="myEvents.length === 0"
                        @click="goToScanner"
                    >
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <i class="pi pi-qrcode text-6xl relative z-10"></i>
                        <div class="text-center relative z-10">
                            <span class="font-bold text-xl block">QR Scanner</span>
                            <span class="text-sm opacity-90">Scan attendee badges</span>
                        </div>
                    </button>

                    <!-- Manual Check-in Button -->
                    <button
                        class="gradient-btn magnetic-btn flex flex-col items-center justify-center gap-3 py-8 relative overflow-hidden group"
                        :disabled="myEvents.length === 0"
                        @click="goToManualCheckIn"
                    >
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <i class="pi pi-pencil text-6xl relative z-10"></i>
                        <div class="text-center relative z-10">
                            <span class="font-bold text-xl block">Manual Check-in</span>
                            <span class="text-sm opacity-90">Enter ticket number</span>
                        </div>
                    </button>
                </div>

                <!-- Instructions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mt-6">
                    <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">
                        <i class="pi pi-info-circle mr-2"></i>
                        Instructions
                    </h3>
                    <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                    1
                                </div>
                                <div>
                                    <h4 class="font-semibold">Use QR Scanner</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Click "QR Scanner" to scan attendee badges with your device camera
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                                    2
                                </div>
                                <div>
                                    <h4 class="font-semibold">Manual Entry</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Use "Manual Check-in" to enter ticket numbers manually if QR code is not working
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                    3
                                </div>
                                <div>
                                    <h4 class="font-semibold">Verify Attendance</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Confirm attendee details before completing check-in
                                    </p>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.gradient-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.gradient-btn:disabled:hover {
    transform: none;
}
</style>
