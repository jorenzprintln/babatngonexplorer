<script setup lang="ts">
import { ref, computed } from 'vue';
import ApplicationLogo from '@/components/AppLogo.vue';
import Dropdown from '@/components/Dropdown.vue';
import DropdownLink from '@/components/DropdownLink.vue';
import NavLink from '@/components/NavLink.vue';
import ResponsiveNavLink from '@/components/ResponsiveNavLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

// Safe route helper with fallback
const route = (name?: string) => {
    if (typeof window !== 'undefined' && window.route) {
        return name ? window.route(name) : window.route();
    }
    // Fallback for development/SSR
    return name || '/';
};

// Helper for checking current route
const routeCurrent = (name?: string) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route().current(name);
    }
    return false;
};

const showingNavigationDropdown = ref(false);
const page = usePage<PageProps>();

const isAdmin = computed(() => {
    return page.props.auth?.user?.role === 'admin';
});

// Get the dashboard URL based on user role
const dashboardUrl = computed(() => {
    return isAdmin.value ? '/admin/dashboard' : '/dashboard';
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="dashboardUrl">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <template v-if="isAdmin">
                                    <NavLink :href="route('admin.dashboard')" :active="routeCurrent('admin.dashboard')">
                                        Dashboard
                                    </NavLink>
                                    <NavLink :href="route('admin.places.index')" :active="routeCurrent('admin.places.*')">
                                        Places
                                    </NavLink>
                                    <NavLink :href="route('admin.reviews.index')" :active="routeCurrent('admin.reviews.*')">
                                        Reviews
                                    </NavLink>
                                    <NavLink :href="route('admin.reports.index')" :active="routeCurrent('admin.reports.*')">
                                        Reports
                                    </NavLink>
                                    <NavLink :href="route('admin.users.index')" :active="routeCurrent('admin.users.*')">
                                        Users
                                    </NavLink>
                                </template>
                                <template v-else>
                                    <NavLink :href="route('dashboard')" :active="routeCurrent('dashboard')">
                                        Dashboard
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
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
                                                <span v-if="isAdmin" class="ml-2 px-2 py-0.5 text-xs font-semibold text-blue-600 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                                    Admin
                                                </span>

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
    <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
    <DropdownLink href="/logout" method="post" as="button">
        Log Out
    </DropdownLink>
</template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
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
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <template v-if="isAdmin">
                            <ResponsiveNavLink :href="route('admin.dashboard')" :active="routeCurrent('admin.dashboard')">
                                Dashboard
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.places.index')" :active="routeCurrent('admin.places.*')">
                                Places
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.reviews.index')" :active="routeCurrent('admin.reviews.*')">
                                Reviews
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.reports.index')" :active="routeCurrent('admin.reports.*')">
                                Reports
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.users.index')" :active="routeCurrent('admin.users.*')">
                                Users
                            </ResponsiveNavLink>
                        </template>
                        <template v-else>
                            <ResponsiveNavLink :href="route('dashboard')" :active="routeCurrent('dashboard')">
                                Dashboard
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800 dark:text-gray-200">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                            <span v-if="isAdmin" class="mt-1 inline-block px-2 py-0.5 text-xs font-semibold text-blue-600 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                Admin
                            </span>
                        </div>

                        <div class="mt-3 space-y-1">
    <ResponsiveNavLink :href="route('profile.edit')"> Profile </ResponsiveNavLink>
    <ResponsiveNavLink href="/logout" method="post" as="button">
        Log Out
    </ResponsiveNavLink>
</div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow dark:bg-gray-800">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>