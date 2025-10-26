<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import QrScanner from 'qr-scanner';

const videoElement = ref<HTMLVideoElement | null>(null);
const scanner = ref<QrScanner | null>(null);
const isScanning = ref(false);
const lastScannedCode = ref('');
const scanResult = ref<{success: boolean, message: string, attendee?: any} | null>(null);

const startScanning = async () => {
    if (!videoElement.value) return;

    try {
        scanner.value = new QrScanner(
            videoElement.value,
            result => handleScan(result.data),
            {
                highlightScanRegion: true,
                highlightCodeOutline: true,
            }
        );

        await scanner.value.start();
        isScanning.value = true;
        scanResult.value = null;
    } catch (error) {
        console.error('Error starting scanner:', error);
        alert('Failed to start camera. Please check permissions.');
    }
};

const stopScanning = () => {
    if (scanner.value) {
        scanner.value.stop();
        scanner.value.destroy();
        scanner.value = null;
        isScanning.value = false;
    }
};

const handleScan = async (code: string) => {
    // Prevent duplicate scans
    if (code === lastScannedCode.value) return;

    lastScannedCode.value = code;

    // Stop scanning temporarily
    stopScanning();

    try {
        const response = await fetch('/check-in/scan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ qr_code: code }),
        });

        const data = await response.json();
        scanResult.value = data;

        // Clear result and resume scanning after 3 seconds
        setTimeout(() => {
            scanResult.value = null;
            lastScannedCode.value = '';
            startScanning();
        }, 3000);
    } catch (error) {
        console.error('Error processing scan:', error);
        scanResult.value = {
            success: false,
            message: 'Error processing QR code.',
        };

        setTimeout(() => {
            scanResult.value = null;
            lastScannedCode.value = '';
            startScanning();
        }, 3000);
    }
};

onMounted(() => {
    startScanning();
});

onUnmounted(() => {
    stopScanning();
});
</script>

<template>
    <Head title="QR Code Scanner" />

    <AuthenticatedLayout>
        <div class="min-h-screen gradient-mesh p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <h1 class="text-4xl font-bold text-gradient mb-2">QR Code Scanner</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Scan attendee badges to check them in
                    </p>
                </div>

                <!-- Scanner Card -->
                <Card class="glass-card mb-6">
                    <template #content>
                        <div class="relative aspect-video bg-black rounded-lg overflow-hidden">
                            <video
                                ref="videoElement"
                                class="w-full h-full object-cover"
                            ></video>

                            <!-- Scanning Overlay -->
                            <div
                                v-if="isScanning"
                                class="absolute inset-0 flex items-center justify-center pointer-events-none"
                            >
                                <div class="w-64 h-64 border-4 border-emerald-500 rounded-lg animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Controls -->
                        <div class="flex justify-center gap-4 mt-4">
                            <Button
                                v-if="!isScanning"
                                label="Start Scanning"
                                icon="pi pi-camera"
                                class="gradient-btn"
                                @click="startScanning"
                            />
                            <Button
                                v-else
                                label="Stop Scanning"
                                icon="pi pi-stop"
                                severity="danger"
                                @click="stopScanning"
                            />
                        </div>
                    </template>
                </Card>

                <!-- Scan Result -->
                <Card
                    v-if="scanResult"
                    :class="[
                        'glass-card',
                        scanResult.success ? 'border-emerald-500' : 'border-red-500',
                        'border-2'
                    ]"
                >
                    <template #content>
                        <div class="text-center">
                            <i
                                :class="[
                                    'pi text-6xl mb-4',
                                    scanResult.success ? 'pi-check-circle text-emerald-500' : 'pi-times-circle text-red-500'
                                ]"
                            ></i>
                            <h3 class="text-2xl font-bold mb-2">
                                {{ scanResult.message }}
                            </h3>
                            <div v-if="scanResult.attendee" class="mt-4 text-left">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Name</p>
                                        <p class="font-semibold">{{ scanResult.attendee.name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Type</p>
                                        <p class="font-semibold capitalize">{{ scanResult.attendee.type }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-semibold">{{ scanResult.attendee.email }}</p>
                                    </div>
                                    <div v-if="scanResult.attendee.company">
                                        <p class="text-sm text-gray-500">Company</p>
                                        <p class="font-semibold">{{ scanResult.attendee.company }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button
                        class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4"
                        @click="router.visit('/check-in/manual')"
                    >
                        <i class="pi pi-pencil text-2xl"></i>
                        <span class="font-semibold">Manual Check-in</span>
                    </button>

                    <button
                        class="bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4"
                        @click="router.visit('/check-in/history')"
                    >
                        <i class="pi pi-history text-2xl"></i>
                        <span class="font-semibold">Check-in History</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
