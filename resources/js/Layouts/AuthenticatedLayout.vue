<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import MobileBottomNav from '@/Components/MobileBottomNav.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav
                class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>

                                <!-- Admin Only: Events Management -->
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'admin'"
                                    :href="route('events.index')"
                                    :active="route().current('events.*')"
                                >
                                    Events
                                </NavLink>

                                <!-- Admin & Event Manager: Attendees -->
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'event_manager'"
                                    :href="route('attendees.index')"
                                    :active="route().current('attendees.*')"
                                >
                                    Attendees
                                </NavLink>

                                <!-- Admin & Event Manager: Badges -->
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'event_manager'"
                                    :href="route('badges.index')"
                                    :active="route().current('badges.*')"
                                >
                                    Badges
                                </NavLink>

                                <!-- All Users: Check-In -->
                                <NavLink
                                    :href="route('checkin.index')"
                                    :active="route().current('checkin.*')"
                                >
                                    Check-In
                                </NavLink>

                                <!-- Admin & Event Manager: Import -->
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'event_manager'"
                                    :href="route('import.index')"
                                    :active="route().current('import.*')"
                                >
                                    Import
                                </NavLink>

                                <!-- Admin & Event Manager: User Management -->
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'event_manager'"
                                    :href="$page.props.auth.user.role === 'admin' ? route('users.index') : route('event.users.index')"
                                    :active="route().current('users.*') || route().current('event.users.*')"
                                >
                                    Users
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-3">
                            <!-- Logout Button -->
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-150"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </Link>

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>

                        <!-- Admin Only: Events Management -->
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('events.index')"
                            :active="route().current('events.*')"
                        >
                            Events
                        </ResponsiveNavLink>

                        <!-- Admin & Event Manager: Attendees -->
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'event_manager'"
                            :href="route('attendees.index')"
                            :active="route().current('attendees.*')"
                        >
                            Attendees
                        </ResponsiveNavLink>

                        <!-- Admin & Event Manager: Badges -->
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'event_manager'"
                            :href="route('badges.index')"
                            :active="route().current('badges.*')"
                        >
                            Badges
                        </ResponsiveNavLink>

                        <!-- All Users: Check-In -->
                        <ResponsiveNavLink
                            :href="route('checkin.index')"
                            :active="route().current('checkin.*')"
                        >
                            Check-In
                        </ResponsiveNavLink>

                        <!-- Admin & Event Manager: Import -->
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'event_manager'"
                            :href="route('import.index')"
                            :active="route().current('import.*')"
                        >
                            Import
                        </ResponsiveNavLink>

                        <!-- Admin & Event Manager: User Management -->
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'event_manager'"
                            :href="$page.props.auth.user.role === 'admin' ? route('users.index') : route('event.users.index')"
                            :active="route().current('users.*') || route().current('event.users.*')"
                        >
                            Users
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow dark:bg-gray-800"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="pb-20 md:pb-0">
                <slot />
            </main>

            <!-- Mobile Bottom Navigation -->
            <MobileBottomNav />
        </div>
    </div>
</template>
