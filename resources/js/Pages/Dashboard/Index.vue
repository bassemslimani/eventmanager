<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import { ref, onMounted } from 'vue';

interface Stats {
    total_attendees: number;
    total_exhibitors: number;
    total_guests: number;
    total_organizers: number;
    checked_in_today: number;
    badges_generated: number;
    active_events: number;
}

interface Props {
    stats: Stats;
    recentCheckIns: any[];
    upcomingEvents: any[];
}

const props = defineProps<Props>();

const chartData = ref();
const chartOptions = ref();

onMounted(() => {
    chartData.value = {
        labels: ['Exhibitors', 'Guests', 'Organizers'],
        datasets: [
            {
                data: [
                    props.stats.total_exhibitors,
                    props.stats.total_guests,
                    props.stats.total_organizers
                ],
                backgroundColor: [
                    'rgba(37, 99, 235, 0.8)',    // Blue for Exhibitors
                    'rgba(139, 92, 246, 0.8)',    // Purple for Guests
                    'rgba(245, 158, 11, 0.8)'     // Amber for Organizers
                ],
                borderColor: [
                    '#2563eb',
                    '#8b5cf6',
                    '#f59e0b'
                ],
                borderWidth: 2
            }
        ]
    };

    chartOptions.value = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    };
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                        Welcome to QRMH
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Modern Event Badge Management System
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Total Attendees -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                    Total Attendees
                                </p>
                                <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.total_attendees }}
                                </h3>
                            </div>
                            <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-full">
                                <i class="pi pi-users text-3xl text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Checked In Today -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                    Checked In Today
                                </p>
                                <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.checked_in_today }}
                                </h3>
                            </div>
                            <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-full">
                                <i class="pi pi-check-circle text-3xl text-green-600 dark:text-green-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Badges Generated -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                    Badges Generated
                                </p>
                                <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.badges_generated }}
                                </h3>
                            </div>
                            <div class="bg-amber-100 dark:bg-amber-900/30 p-3 rounded-full">
                                <i class="pi pi-id-card text-3xl text-amber-600 dark:text-amber-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Active Events -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                    Active Events
                                </p>
                                <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.active_events }}
                                </h3>
                            </div>
                            <div class="bg-purple-100 dark:bg-purple-900/30 p-3 rounded-full">
                                <i class="pi pi-calendar text-3xl text-purple-600 dark:text-purple-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Chart -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Attendee Distribution</h3>
                        <div class="h-64">
                            <Chart type="doughnut" :data="chartData" :options="chartOptions" />
                        </div>
                    </div>

                    <!-- Recent Check-ins -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Recent Activity</h3>
                        <div v-if="recentCheckIns.length > 0" class="space-y-3 max-h-64 overflow-y-auto">
                            <div
                                v-for="checkIn in recentCheckIns"
                                :key="checkIn.id"
                                class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700"
                            >
                                <i class="pi pi-check-circle text-green-500 mt-1"></i>
                                <div class="flex-1">
                                    <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ checkIn.attendee?.name || 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ new Date(checkIn.scanned_at).toLocaleString() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-500 py-12">
                            <i class="pi pi-inbox text-4xl mb-2"></i>
                            <p>No recent check-ins</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button
                        class="bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-6"
                        @click="$inertia.visit('/check-in')"
                    >
                        <i class="pi pi-qrcode text-2xl"></i>
                        <span class="font-semibold">Scan QR Code</span>
                    </button>

                    <button
                        class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-6"
                        @click="$inertia.visit('/import')"
                    >
                        <i class="pi pi-upload text-2xl"></i>
                        <span class="font-semibold">Import Attendees</span>
                    </button>

                    <button
                        class="bg-gradient-to-r from-amber-600 to-orange-600 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-6"
                        @click="$inertia.visit('/badges')"
                    >
                        <i class="pi pi-id-card text-2xl"></i>
                        <span class="font-semibold">Generate Badges</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
