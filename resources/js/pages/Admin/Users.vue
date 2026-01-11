<script setup lang="ts">
import AdminLayout from '@/layouts/app/AdminSidebarLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import type { BreadcrumbItemType } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'user';
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    reviews_count: number;
    saved_places_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface UsersData {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

interface Stats {
    total_users: number;
    admin_users: number;
    regular_users: number;
    verified_users: number;
}

const props = defineProps<{
    users: UsersData;
    filters?: {
        search?: string;
        role?: string;
        status?: string;
    };
    stats: Stats;
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Users', href: '/admin/users' },
];

// Local state
const searchQuery = ref(props.filters?.search || '');
const selectedRole = ref(props.filters?.role || '');
const selectedStatus = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const userToDelete = ref<User | null>(null);
const selectedUsers = ref<number[]>([]);

// Apply filters
const applyFilters = () => {
    router.get('/admin/users', {
        search: searchQuery.value,
        role: selectedRole.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedRole.value = '';
    selectedStatus.value = '';
    router.get('/admin/users', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Delete user
const confirmDelete = (user: User) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(`/admin/users/${userToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                userToDelete.value = null;
            },
        });
    }
};

// Toggle role
const toggleRole = (userId: number) => {
    router.post(`/admin/users/${userId}/toggle-role`, {}, {
        preserveScroll: true,
    });
};

// Verify email
const verifyEmail = (userId: number) => {
    router.post(`/admin/users/${userId}/verify-email`, {}, {
        preserveScroll: true,
    });
};

// Bulk actions
const toggleSelectAll = () => {
    if (selectedUsers.value.length === props.users.data.length) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = props.users.data.map(u => u.id);
    }
};

const bulkMakeAdmin = () => {
    router.post('/admin/users/bulk-update-role', {
        user_ids: selectedUsers.value,
        role: 'admin',
    }, {
        onSuccess: () => {
            selectedUsers.value = [];
        },
    });
};

const bulkMakeUser = () => {
    router.post('/admin/users/bulk-update-role', {
        user_ids: selectedUsers.value,
        role: 'user',
    }, {
        onSuccess: () => {
            selectedUsers.value = [];
        },
    });
};

const bulkDelete = () => {
    if (confirm(`Are you sure you want to delete ${selectedUsers.value.length} users?`)) {
        router.post('/admin/users/bulk-delete', {
            user_ids: selectedUsers.value,
        }, {
            onSuccess: () => {
                selectedUsers.value = [];
            },
        });
    }
};

// Pagination
const goToPage = (url: string | null) => {
    if (url) {
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

// Role badge color
const getRoleColor = (role: string) => {
    return role === 'admin'
        ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
};

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Manage Users" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-blue-50 p-4 md:p-8 dark:from-gray-900 dark:to-gray-950">
            <div class="mx-auto max-w-7xl space-y-6">
                
                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Users Management</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Manage and monitor user accounts
                        </p>
                    </div>
                    <Link
                        :href="route('admin.users.create')"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New User
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_users }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Admins</p>
                                <p class="mt-2 text-3xl font-bold text-purple-600 dark:text-purple-400">{{ stats.admin_users }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Regular Users</p>
                                <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.regular_users }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Verified</p>
                                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.verified_users }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Search Users
                            </label>
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search by name or email..."
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 pl-10 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                    @keyup.enter="applyFilters"
                                />
                                <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Role Filter -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Role
                            </label>
                            <select
                                v-model="selectedRole"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>
                            <select
                                v-model="selectedStatus"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="mt-4 flex gap-3">
                        <button
                            @click="applyFilters"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Apply Filters
                        </button>
                        <button
                            @click="clearFilters"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions Bar -->
                <div v-if="selectedUsers.length > 0" class="rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            {{ selectedUsers.length }} user{{ selectedUsers.length > 1 ? 's' : '' }} selected
                        </span>
                        <div class="flex gap-2">
                            <button
                                @click="bulkMakeAdmin"
                                class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-purple-700"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Make Admin
                            </button>
                            <button
                                @click="bulkMakeUser"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Make User
                            </button>
                            <button
                                @click="bulkDelete"
                                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-700"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Table Header -->
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                All Users ({{ users.total }})
                            </h2>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="users.data.length === 0" class="p-12 text-center">
                        <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">No users found</h3>
                        <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters or add a new user</p>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="selectedUsers.length === users.data.length"
                                            @change="toggleSelectAll"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                        />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Activity
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Joined
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                >
                                    <!-- Checkbox -->
                                    <td class="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            :value="user.id"
                                            v-model="selectedUsers"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                        />
                                    </td>

                                    <!-- User Info -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-500 text-white font-semibold">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</p>
                                                <div v-if="!user.email_verified_at" class="mt-1">
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                        Unverified
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Role -->
                                    <td class="px-6 py-4">
                                        <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize', getRoleColor(user.role)]">
                                            {{ user.role }}
                                        </span>
                                    </td>

                                    <!-- Activity -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                            <div class="flex items-center gap-1">
                                                <svg class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                <span>{{ user.reviews_count }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                                </svg>
                                                <span>{{ user.saved_places_count }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Joined Date -->
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(user.created_at) }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                v-if="!user.email_verified_at"
                                                @click="verifyEmail(user.id)"
                                                class="rounded-lg p-2 text-green-600 transition-colors hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20"
                                                title="Verify Email"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="toggleRole(user.id)"
                                                class="rounded-lg p-2 text-purple-600 transition-colors hover:bg-purple-50 dark:text-purple-400 dark:hover:bg-purple-900/20"
                                                :title="user.role === 'admin' ? 'Make User' : 'Make Admin'"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                            </button>
                                            <Link
                                                :href="`/admin/users/${user.id}/edit`"
                                                class="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                                                title="Edit"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </Link>
                                            <button
                                                @click="confirmDelete(user)"
                                                class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                                title="Delete"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.data.length > 0" class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Showing {{ (users.current_page - 1) * users.per_page + 1 }} to 
                                {{ Math.min(users.current_page * users.per_page, users.total) }} of 
                                {{ users.total }} users
                            </div>
                            <div class="flex gap-2">
                                <button
                                    v-for="link in users.links"
                                    :key="link.label"
                                    @click="goToPage(link.url)"
                                    :disabled="!link.url || link.active"
                                    :class="[
                                        'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : link.url
                                            ? 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                                            : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="showDeleteModal = false"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center gap-3">
                    <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/20">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Delete User</h3>
                </div>
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>? This action cannot be undone and will remove all their reviews and saved places.
                </p>
                <div class="flex gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteUser"
                        class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 font-semibold text-white transition-colors hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>