<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

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

const canAccessImport = computed(() => {
    return userRole.value === 'admin' || userRole.value === 'event_manager';
});
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

        <!-- Admin & Event Manager only -->
        <Link v-if="canAccessImport" :href="route('import.index')" class="nav-item" :class="{ active: isActive('/import') }">
            <i class="pi pi-upload"></i>
            <span>Import</span>
        </Link>

        <!-- Profile for agents (when other links are hidden) -->
        <Link v-if="!canAccessAttendees" :href="route('profile.edit')" class="nav-item" :class="{ active: isActive('/profile') }">
            <i class="pi pi-user"></i>
            <span>Profile</span>
        </Link>
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
    color: #6B7280;
    transition: all 0.2s ease;
    position: relative;
    flex: 1;
    max-width: 80px;
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
    color: #2563eb;
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

.scan-button i {
    color: white;
    font-size: 24px;
    margin: 0;
}

.scan-button.active .scan-icon-wrapper {
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.6);
    transform: scale(1.05);
}

/* Hide on desktop */
@media (min-width: 768px) {
    .mobile-bottom-nav {
        display: none;
    }
}

/* Animation for active state */
@keyframes bounce {
    0%, 100% {
        transform: translateY(-2px);
    }
    50% {
        transform: translateY(-6px);
    }
}

.nav-item.active i {
    animation: bounce 0.5s ease;
}
</style>
