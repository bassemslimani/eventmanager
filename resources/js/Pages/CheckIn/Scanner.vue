<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import Button from 'primevue/button';
import CustomButton from '@/Components/CustomButton.vue';
import Card from 'primevue/card';
import QrScanner from 'qr-scanner';

const videoElement = ref<HTMLVideoElement | null>(null);
const scanner = ref<QrScanner | null>(null);
const isScanning = ref(false);
const lastScannedCode = ref('');
const scanResult = ref<{success: boolean, message: string, attendee?: any} | null>(null);

// Auto-stop timer
const SCAN_TIMEOUT = 10; // seconds
const remainingTime = ref(SCAN_TIMEOUT);
let scanTimeout: number | undefined;
let countdownInterval: number | undefined;

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

        // Reset and start countdown
        remainingTime.value = SCAN_TIMEOUT;
        startCountdown();

        // Auto-stop after 10 seconds
        scanTimeout = window.setTimeout(() => {
            stopScanning();
            console.log('Scanner auto-stopped after 10 seconds');
        }, SCAN_TIMEOUT * 1000);
    } catch (error) {
        console.error('Error starting scanner:', error);
        alert('Failed to start camera. Please check permissions.');
    }
};

const startCountdown = () => {
    // Clear any existing countdown
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }

    countdownInterval = window.setInterval(() => {
        remainingTime.value--;
        if (remainingTime.value <= 0) {
            clearInterval(countdownInterval);
        }
    }, 1000);
};

const stopScanning = () => {
    // Clear timers
    if (scanTimeout) {
        clearTimeout(scanTimeout);
        scanTimeout = undefined;
    }
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = undefined;
    }

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
        console.log('Scanning QR code:', code);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.error('CSRF token not found');
            throw new Error('CSRF token not found. Please refresh the page.');
        }

        const response = await fetch('/check-in/scan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ qr_code: code }),
        });

        console.log('Response status:', response.status);

        // Handle CSRF token mismatch (419)
        if (response.status === 419) {
            scanResult.value = {
                success: false,
                message: 'Session expired. Please refresh the page and try again.',
            };
            // Optionally auto-refresh after a delay
            setTimeout(() => {
                window.location.reload();
            }, 3000);
            return;
        }

        // Handle non-OK responses
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
            console.error('Server error:', errorData);
            scanResult.value = {
                success: false,
                message: errorData.message || `Server error (${response.status})`,
            };
        } else {
            const data = await response.json();
            console.log('Scan result:', data);
            scanResult.value = data;
        }
    } catch (error) {
        console.error('Error processing scan:', error);
        const errorMessage = error instanceof Error ? error.message : 'Error processing QR code.';
        scanResult.value = {
            success: false,
            message: errorMessage,
        };
    }
};

const closeScanResult = () => {
    scanResult.value = null;
    lastScannedCode.value = '';
    startScanning();
};

// Refresh CSRF token periodically to prevent expiration
const refreshCSRFToken = async () => {
    try {
        const response = await fetch('/sanctum/csrf-cookie');
        if (response.ok) {
            console.log('CSRF token refreshed');
        }
    } catch (error) {
        console.error('Failed to refresh CSRF token:', error);
    }
};

let tokenRefreshInterval: number | undefined;

onMounted(() => {
    startScanning();

    // Refresh CSRF token every 10 minutes
    tokenRefreshInterval = window.setInterval(refreshCSRFToken, 10 * 60 * 1000);
});

onUnmounted(() => {
    stopScanning();
    if (tokenRefreshInterval) {
        clearInterval(tokenRefreshInterval);
    }
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});
</script>

<template>
    <Head title="QR Code Scanner" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-3 sm:p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-4 sm:mb-6 text-center">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">QR Code Scanner</h1>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                        Scan attendee badges to check them in
                    </p>
                </div>

                <!-- Scanner Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-3 sm:p-6 mb-4 sm:mb-6">
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
                            <div class="w-40 h-40 sm:w-64 sm:h-64 border-4 border-blue-600 rounded-lg animate-pulse"></div>
                        </div>

                        <!-- Countdown Timer -->
                        <div
                            v-if="isScanning"
                            class="absolute top-4 right-4 bg-black/70 backdrop-blur-sm rounded-lg px-4 py-2"
                        >
                            <div class="flex items-center gap-2">
                                <i class="pi pi-clock text-white text-lg"></i>
                                <span class="text-white font-bold text-xl">{{ remainingTime }}s</span>
                            </div>
                            <div class="mt-1 bg-gray-600 rounded-full h-1 overflow-hidden">
                                <div
                                    class="bg-blue-500 h-full transition-all duration-1000 ease-linear"
                                    :style="{ width: `${(remainingTime / SCAN_TIMEOUT) * 100}%` }"
                                ></div>
                            </div>
                        </div>

                        <!-- Camera Inactive Message -->
                        <div
                            v-if="!isScanning"
                            class="absolute inset-0 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm"
                        >
                            <div class="text-center px-4">
                                <i class="pi pi-camera text-white text-5xl mb-3 opacity-50"></i>
                                <p class="text-white text-lg font-medium">Camera Stopped</p>
                                <p class="text-gray-300 text-sm mt-2">Click "Start Scanning" to activate camera</p>
                            </div>
                        </div>
                    </div>

                    <!-- Controls -->
                    <div class="flex justify-center gap-3 sm:gap-4 mt-4">
                        <CustomButton
                            v-if="!isScanning && !scanResult"
                            label="Start Scanning"
                            icon="pi-camera"
                            severity="primary"
                            class="w-full sm:w-auto"
                            @click="startScanning"
                        />
                        <CustomButton
                            v-else-if="isScanning"
                            label="Stop Scanning"
                            icon="pi-stop"
                            severity="danger"
                            class="w-full sm:w-auto"
                            @click="stopScanning"
                        />
                        <div
                            v-else-if="scanResult"
                            class="text-center text-gray-500 dark:text-gray-400 w-full py-3"
                        >
                            <i class="pi pi-info-circle mr-2"></i>
                            <span class="text-sm">Click "Continue Scanning" below to scan next badge</span>
                        </div>
                    </div>
                </div>

                <!-- Scan Result -->
                <div
                    v-if="scanResult"
                    :class="[
                        'bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 sm:p-6 mb-4 sm:mb-6',
                        scanResult.success ? 'border-green-500' : 'border-red-500',
                        'border-2'
                    ]"
                >
                    <div class="text-center">
                        <i
                            :class="[
                                'pi text-5xl sm:text-6xl mb-4',
                                scanResult.success ? 'pi-check-circle text-green-500' : 'pi-times-circle text-red-500'
                            ]"
                        ></i>
                        <h3 class="text-xl sm:text-2xl font-bold mb-2 px-2">
                            {{ scanResult.message }}
                        </h3>
                        <div v-if="scanResult.attendee" class="mt-4 text-left">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Name</p>
                                    <p class="font-semibold text-sm sm:text-base break-words">{{ scanResult.attendee.name }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Type</p>
                                    <p class="font-semibold text-sm sm:text-base capitalize">{{ scanResult.attendee.type }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg sm:col-span-2">
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="font-semibold text-sm sm:text-base break-all">{{ scanResult.attendee.email }}</p>
                                </div>
                                <div v-if="scanResult.attendee.company" class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg sm:col-span-2">
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Company</p>
                                    <p class="font-semibold text-sm sm:text-base break-words">{{ scanResult.attendee.company }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Close Button -->
                        <div class="mt-6">
                            <CustomButton
                                label="Continue Scanning"
                                icon="pi-check"
                                class="w-full"
                                :severity="scanResult.success ? 'success' : 'secondary'"
                                size="large"
                                @click="closeScanResult"
                            />
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <button
                        class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl shadow-lg hover:shadow-xl active:scale-95 sm:hover:scale-105 transition-all duration-200 flex items-center justify-center gap-3 py-4 px-4"
                        @click="router.visit('/check-in/manual')"
                    >
                        <i class="pi pi-pencil text-xl sm:text-2xl"></i>
                        <span class="font-semibold text-sm sm:text-base">Manual Check-in</span>
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
