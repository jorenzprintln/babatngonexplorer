<script setup lang="ts">
import AdminLayout from '@/layouts/app/AdminSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import type { BreadcrumbItemType } from '@/types';

interface Place {
    id: number;
    name: string;
    description: string;
    location: string;
    category: string;
    image_url?: string;
    status: 'active' | 'inactive';
    average_rating: number;
    reviews_count: number;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PlacesData {
    data: Place[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    places: PlacesData;
    filters?: {
        search?: string;
        category?: string;
        status?: string;
    };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Places', href: '/admin/places' },
];

// Local state
const searchQuery = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');
const selectedStatus = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const placeToDelete = ref<Place | null>(null);

// Categories (you can fetch these from backend)
const categories = [
    'Restaurant',
    'Cafe',
    'Park',
    'Museum',
    'Beach',
    'Shopping',
    'Hotel',
    'Entertainment',
    'Other'
];

// Search and filter
const applyFilters = () => {
    router.get('/admin/places', {
        search: searchQuery.value,
        category: selectedCategory.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedStatus.value = '';
    router.get('/admin/places', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Delete place
const confirmDelete = (place: Place) => {
    placeToDelete.value = place;
    showDeleteModal.value = true;
};

const deletePlace = () => {
    if (placeToDelete.value) {
        router.delete(`/admin/places/${placeToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                placeToDelete.value = null;
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

// Status badge color
const getStatusColor = (status: string) => {
    return status === 'active' 
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Manage Places" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-blue-50 p-4 md:p-8 dark:from-gray-900 dark:to-gray-950">
            <div class="mx-auto max-w-7xl space-y-6">
                
                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Places Management</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Manage all places in your platform
                        </p>
                    </div>
                    <a
                        href="/admin/places/create"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-teal-500 px-6 py-3 font-semibold text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Place
                    </a>
                </div>

                <!-- Filters Card -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Search Places
                            </label>
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search by name or location..."
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 pl-10 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                    @keyup.enter="applyFilters"
                                />
                                <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category
                            </label>
                            <select
                                v-model="selectedCategory"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
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

                <!-- Places Table -->
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Table Header -->
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                All Places ({{ places.total }})
                            </h2>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="places.data.length === 0" class="p-12 text-center">
                        <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">No places found</h3>
                        <p class="mb-4 text-gray-600 dark:text-gray-400">Get started by creating your first place</p>
                        <a
                            href="/admin/places/create"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-700"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New Place
                        </a>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Place
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Rating
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Created
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr
                                    v-for="place in places.data"
                                    :key="place.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                >
                                    <!-- Place Info -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700">
                                                <img
                                                    v-if="place.image_url"
                                                    :src="place.image_url"
                                                    :alt="place.name"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div v-else class="flex h-full w-full items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate font-semibold text-gray-900 dark:text-white">
                                                    {{ place.name }}
                                                </p>
                                                <p class="truncate text-sm text-gray-600 dark:text-gray-400">
                                                    <svg class="inline h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    </svg>
                                                    {{ place.location }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ place.category }}
                                        </span>
                                    </td>

                                    <!-- Rating -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1">
                                            <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="font-semibold text-gray-900 dark:text-white">
                                                {{ place.average_rating.toFixed(1) }}
                                            </span>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                                ({{ place.reviews_count }})
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-semibold', getStatusColor(place.status)]">
                                            {{ place.status }}
                                        </span>
                                    </td>

                                    <!-- Created Date -->
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(place.created_at) }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a
                                                :href="`/admin/places/${place.id}`"
                                                class="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                                                title="View"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a
                                                :href="`/admin/places/${place.id}/edit`"
                                                class="rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800"
                                                title="Edit"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <button
                                                @click="confirmDelete(place)"
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
                    <div v-if="places.data.length > 0" class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Showing {{ (places.current_page - 1) * places.per_page + 1 }} to 
                                {{ Math.min(places.current_page * places.per_page, places.total) }} of 
                                {{ places.total }} places
                            </div>
                            <div class="flex gap-2">
                                <button
                                    v-for="link in places.links"
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
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Delete Place</h3>
                </div>
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete <strong>{{ placeToDelete?.name }}</strong>? This action cannot be undone and will also delete all associated reviews.
                </p>
                <div class="flex gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deletePlace"
                        class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 font-semibold text-white transition-colors hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>