<script setup lang="ts">
import { ref, onMounted } from 'vue';

const showPrompt = ref(false);
const deferredPrompt = ref<any>(null);

onMounted(() => {
    // Check if the user has already dismissed the prompt
    const dismissed = localStorage.getItem('pwa-install-dismissed');

    if (dismissed) {
        return;
    }

    // Listen for the beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Stash the event so it can be triggered later
        deferredPrompt.value = e;
        // Show the install prompt
        showPrompt.value = true;
    });

    // Check if app is already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        showPrompt.value = false;
    }
});

const installApp = async () => {
    if (!deferredPrompt.value) {
        return;
    }

    // Show the install prompt
    deferredPrompt.value.prompt();

    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.value.userChoice;

    if (outcome === 'accepted') {
        console.log('User accepted the install prompt');
    } else {
        console.log('User dismissed the install prompt');
    }

    // Clear the deferred prompt
    deferredPrompt.value = null;
    showPrompt.value = false;
};

const dismissPrompt = () => {
    showPrompt.value = false;
    localStorage.setItem('pwa-install-dismissed', 'true');
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-if="showPrompt"
            class="fixed bottom-24 md:bottom-4 left-0 right-0 md:left-auto md:right-4 md:max-w-md z-50 px-4 md:px-0"
        >
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-5 md:p-5">
                <!-- Mobile Layout -->
                <div class="flex flex-col md:hidden gap-4">
                    <div class="flex items-start gap-4">
                        <!-- App Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                Install QRCH
                            </h3>
                            <p class="text-base text-gray-600 dark:text-gray-400">
                                Get quick access to event management tools
                            </p>
                        </div>

                        <!-- Close button -->
                        <button
                            @click="dismissPrompt"
                            class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Buttons -->
                    <div class="flex flex-col gap-3">
                        <button
                            @click="installApp"
                            class="w-full inline-flex justify-center items-center gap-3 px-6 py-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold text-lg rounded-xl transition-colors duration-150 shadow-lg"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Install App
                        </button>
                        <button
                            @click="dismissPrompt"
                            class="w-full px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-600 font-medium text-base rounded-xl transition-colors duration-150"
                        >
                            Maybe Later
                        </button>
                    </div>
                </div>

                <!-- Desktop Layout -->
                <div class="hidden md:flex items-start gap-4">
                    <!-- App Icon -->
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            Install QRCH
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Install our app on your desktop for quick and easy access to event management tools.
                        </p>

                        <!-- Desktop Buttons -->
                        <div class="flex gap-3">
                            <button
                                @click="installApp"
                                class="flex-1 inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors duration-150 shadow-sm"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Install
                            </button>
                            <button
                                @click="dismissPrompt"
                                class="px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors duration-150"
                            >
                                Not now
                            </button>
                        </div>
                    </div>

                    <!-- Close button -->
                    <button
                        @click="dismissPrompt"
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
