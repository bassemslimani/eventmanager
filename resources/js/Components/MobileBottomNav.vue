<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const showMenu = ref(false);

const route = (name: string) => {
    return window.route ? window.route(name) : `/${name}`;
};

const currentRoute = computed(() => {
    return window.location.pathname;
});

const isActive = (path: string) => {
    return currentRoute.value === path || currentRoute.value.startsWith(path);
};

const userRole = computed(() => {
    return page.props.auth?.user?.role;
});

const canAccessAttendees = computed(() => {
    return userRole.value === 'admin' || userRole.value === 'event_manager';
});

const canAccessBadges = computed(() => {
    return userRole.value === 'admin' || userRole.value === 'event_manager';
});

const toggleMenu = () => {
    showMenu.value = !showMenu.value;
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <nav class="mobile-bottom-nav">
        <Link :href="route('dashboard')" class="nav-item" :class="{ active: isActive('/dashboard') }">
            <i class="pi pi-home"></i>
            <span>Home</span>
        </Link>

        <!-- Admin & Event Manager only -->
        <Link v-if="canAccessAttendees" :href="route('attendees.index')" class="nav-item" :class="{ active: isActive('/attendees') }">
            <i class="pi pi-users"></i>
            <span>Attendees</span>
        </Link>

        <Link :href="route('checkin.index')" class="nav-item scan-button" :class="{ active: isActive('/check-in') }">
            <div class="scan-icon-wrapper">
                <i class="pi pi-qrcode"></i>
            </div>
            <span>Scan</span>
        </Link>

        <!-- Admin & Event Manager only -->
        <Link v-if="canAccessBadges" :href="route('badges.index')" class="nav-item" :class="{ active: isActive('/badges') }">
            <i class="pi pi-id-card"></i>
            <span>Badges</span>
        </Link>

        <!-- More Menu Button -->
        <button @click="toggleMenu" class="nav-item" :class="{ active: showMenu }">
            <i class="pi pi-ellipsis-h"></i>
            <span>More</span>
        </button>

        <!-- Popup Menu -->
        <div v-if="showMenu" class="menu-popup" @click="showMenu = false">
            <div class="menu-backdrop"></div>
            <div class="menu-content" @click.stop>
                <div class="menu-header">
                    <h3 class="menu-title">Menu</h3>
                    <button @click="showMenu = false" class="menu-close">
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <div class="menu-items">
                    <Link :href="route('profile.edit')" class="menu-item" @click="showMenu = false">
                        <i class="pi pi-user"></i>
                        <span>Profile</span>
                    </Link>

                    <Link v-if="canAccessAttendees" :href="route('import.index')" class="menu-item" @click="showMenu = false">
                        <i class="pi pi-upload"></i>
                        <span>Import Data</span>
                    </Link>

                    <button @click="logout" class="menu-item logout-item">
                        <i class="pi pi-sign-out"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.mobile-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid rgba(37, 99, 235, 0.1);
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 8px 0 max(8px, env(safe-area-inset-bottom));
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    z-index: 1000;
    transition: transform 0.3s ease;
}

.dark .mobile-bottom-nav {
    background: rgba(17, 24, 39, 0.95);
    border-top-color: rgba(37, 99, 235, 0.2);
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    text-decoration: none;
    color: #6B7280 !important;
    transition: all 0.2s ease;
    position: relative;
    flex: 1;
    max-width: 80px;
    background: transparent;
    border: none;
    cursor: pointer;
}

.dark .nav-item {
    color: #9CA3AF !important;
}

.nav-item span,
.nav-item i {
    color: inherit !important;
}

.nav-item i {
    font-size: 20px;
    margin-bottom: 4px;
    transition: all 0.2s ease;
}

.nav-item span {
    font-size: 11px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-item:active {
    transform: scale(0.95);
}

.nav-item.active {
    color: #2563eb !important;
}

.dark .nav-item.active {
    color: #3B82F6 !important;
}

.nav-item.active span,
.nav-item.active i {
    color: inherit !important;
}

.nav-item.active i {
    transform: translateY(-2px);
}

.nav-item.active span {
    font-weight: 600;
}

/* Special styling for scan button (center) */
.scan-button {
    position: relative;
    margin-top: -20px;
}

.scan-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #6366f1 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
    margin-bottom: 4px;
}

.scan-icon-wrapper i,
.scan-button .scan-icon-wrapper i,
.nav-item.scan-button .scan-icon-wrapper i {
    color: white !important;
    font-size: 24px;
    margin: 0;
}

.scan-button span {
    color: inherit !important;
}

.scan-button.active .scan-icon-wrapper {
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.6);
    transform: scale(1.05);
}

/* Menu Popup */
.menu-popup {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 2000;
    display: flex;
    align-items: flex-end;
}

.menu-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease;
}

.menu-content {
    position: relative;
    background: white;
    border-radius: 20px 20px 0 0;
    width: 100%;
    max-height: 80vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
}

.dark .menu-content {
    background: #1f2937;
}

.menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.dark .menu-header {
    border-bottom-color: #374151;
}

.menu-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.dark .menu-title {
    color: #f9fafb;
}

.menu-close {
    background: #f3f4f6;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    transition: all 0.2s ease;
}

.dark .menu-close {
    background: #374151;
    color: #9ca3af;
}

.menu-close:active {
    transform: scale(0.95);
}

.menu-items {
    padding: 8px;
    padding-bottom: max(8px, env(safe-area-inset-bottom));
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 4px;
    text-decoration: none;
    color: #374151 !important;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.2s ease;
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
}

.dark .menu-item {
    color: #e5e7eb !important;
}

.menu-item span,
.menu-item i {
    color: inherit !important;
}

.menu-item:active {
    background: #f3f4f6;
    transform: scale(0.98);
}

.dark .menu-item:active {
    background: #374151;
}

.menu-item i {
    font-size: 20px;
    width: 24px;
    text-align: center;
}

.logout-item {
    color: #dc2626 !important;
}

.dark .logout-item {
    color: #ef4444 !important;
}

.logout-item span,
.logout-item i {
    color: inherit !important;
}

.logout-item:active {
    background: #fee2e2;
}

.dark .logout-item:active {
    background: #7f1d1d;
}

/* Hide on desktop */
@media (min-width: 768px) {
    .mobile-bottom-nav {
        display: none;
    }
}

/* Animations */
@keyframes bounce {
    0%, 100% {
        transform: translateY(-2px);
    }
    50% {
        transform: translateY(-6px);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}

.nav-item.active i {
    animation: bounce 0.5s ease;
}
</style>
