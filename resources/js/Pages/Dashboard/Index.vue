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
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(234, 179, 8, 0.8)'
                ],
                borderColor: [
                    '#10B981',
                    '#3B82F6',
                    '#EAB308'
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
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gradient mb-2">
                        Welcome to QRMH
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Modern Event Badge Management System
                    </p>
                </div>

                <!-- Bento Grid Stats -->
                <div class="bento-grid mb-8">
                    <!-- Total Attendees -->
                    <Card class="bento-item glass-card hover:shadow-xl transition-all">
                        <template #content>
                            <div class="flex items-center justify-between p-2">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        Total Attendees
                                    </p>
                                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.total_attendees }}
                                    </h3>
                                </div>
                                <div class="bg-emerald-100 dark:bg-emerald-900/30 p-3 rounded-full">
                                    <i class="pi pi-users text-3xl text-emerald-600 dark:text-emerald-400"></i>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Checked In Today -->
                    <Card class="bento-item glass-card hover:shadow-xl transition-all">
                        <template #content>
                            <div class="flex items-center justify-between p-2">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        Checked In Today
                                    </p>
                                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.checked_in_today }}
                                    </h3>
                                </div>
                                <div class="bg-emerald-100 dark:bg-emerald-900/30 p-3 rounded-full">
                                    <i class="pi pi-check-circle text-3xl text-emerald-600 dark:text-emerald-400"></i>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Badges Generated -->
                    <Card class="bento-item glass-card hover:shadow-xl transition-all">
                        <template #content>
                            <div class="flex items-center justify-between p-2">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        Badges Generated
                                    </p>
                                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.badges_generated }}
                                    </h3>
                                </div>
                                <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-full">
                                    <i class="pi pi-id-card text-3xl text-blue-600 dark:text-blue-400"></i>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Active Events -->
                    <Card class="bento-item glass-card hover:shadow-xl transition-all">
                        <template #content>
                            <div class="flex items-center justify-between p-2">
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
                        </template>
                    </Card>

                    <!-- Chart - Wide -->
                    <Card class="bento-item-wide glass-card">
                        <template #header>
                            <h3 class="text-xl font-bold p-4 text-gray-900 dark:text-white">Attendee Distribution</h3>
                        </template>
                        <template #content>
                            <div class="h-64">
                                <Chart type="doughnut" :data="chartData" :options="chartOptions" />
                            </div>
                        </template>
                    </Card>

                    <!-- Recent Check-ins - Tall -->
                    <Card class="bento-item-tall glass-card">
                        <template #header>
                            <h3 class="text-xl font-bold p-4 text-gray-900 dark:text-white">Recent Activity</h3>
                        </template>
                        <template #content>
                            <div v-if="recentCheckIns.length > 0" class="space-y-3">
                                <div
                                    v-for="checkIn in recentCheckIns"
                                    :key="checkIn.id"
                                    class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800"
                                >
                                    <i class="pi pi-check-circle text-emerald-500 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="font-semibold text-sm">{{ checkIn.attendee?.name || 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ new Date(checkIn.scanned_at).toLocaleString() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center text-gray-500 py-8">
                                <i class="pi pi-inbox text-4xl mb-2"></i>
                                <p>No recent check-ins</p>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button
                        class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-6"
                        @click="$inertia.visit('/check-in')"
                    >
                        <i class="pi pi-qrcode text-2xl"></i>
                        <span class="font-semibold">Scan QR Code</span>
                    </button>

                    <button
                        class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-6"
                        @click="$inertia.visit('/import')"
                    >
                        <i class="pi pi-upload text-2xl"></i>
                        <span class="font-semibold">Import Attendees</span>
                    </button>

                    <button
                        class="bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-6"
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
