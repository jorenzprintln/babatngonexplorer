<script setup lang="ts">
import AdminLayout from '@/layouts/app/AdminSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import type { BreadcrumbItemType } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Place {
    id: number;
    name: string;
    location: string;
}

interface Review {
    id: number;
    rating: number;
    comment: string;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    updated_at: string;
    user: User;
    place: Place;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface ReviewsData {
    data: Review[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

interface Stats {
    total_reviews: number;
    pending_reviews: number;
    approved_reviews: number;
    average_rating: number;
}

const props = defineProps<{
    reviews: ReviewsData;
    places: Array<{ id: number; name: string }>;
    filters?: {
        search?: string;
        rating?: number;
        status?: string;
        place_id?: number;
    };
    stats: Stats;
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Reviews', href: '/admin/reviews' },
];

// Local state
const searchQuery = ref(props.filters?.search || '');
const selectedRating = ref(props.filters?.rating || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedPlace = ref(props.filters?.place_id || '');
const showDeleteModal = ref(false);
const reviewToDelete = ref<Review | null>(null);
const selectedReviews = ref<number[]>([]);
const showBulkActions = ref(false);

// Ratings for filter
const ratings = [1, 2, 3, 4, 5];

// Apply filters
const applyFilters = () => {
    router.get('/admin/reviews', {
        search: searchQuery.value,
        rating: selectedRating.value,
        status: selectedStatus.value,
        place_id: selectedPlace.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedRating.value = '';
    selectedStatus.value = '';
    selectedPlace.value = '';
    router.get('/admin/reviews', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Delete review
const confirmDelete = (review: Review) => {
    reviewToDelete.value = review;
    showDeleteModal.value = true;
};

const deleteReview = () => {
    if (reviewToDelete.value) {
        router.delete(`/admin/reviews/${reviewToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                reviewToDelete.value = null;
            },
        });
    }
};

// Approve/Reject review
const approveReview = (reviewId: number) => {
    router.post(`/admin/reviews/${reviewId}/approve`, {}, {
        preserveScroll: true,
    });
};

const rejectReview = (reviewId: number) => {
    router.post(`/admin/reviews/${reviewId}/reject`, {}, {
        preserveScroll: true,
    });
};

// Bulk actions
const toggleSelectAll = () => {
    if (selectedReviews.value.length === props.reviews.data.length) {
        selectedReviews.value = [];
    } else {
        selectedReviews.value = props.reviews.data.map(r => r.id);
    }
};

const bulkApprove = () => {
    router.post('/admin/reviews/bulk-update-status', {
        review_ids: selectedReviews.value,
        status: 'approved',
    }, {
        onSuccess: () => {
            selectedReviews.value = [];
        },
    });
};

const bulkReject = () => {
    router.post('/admin/reviews/bulk-update-status', {
        review_ids: selectedReviews.value,
        status: 'rejected',
    }, {
        onSuccess: () => {
            selectedReviews.value = [];
        },
    });
};

const bulkDelete = () => {
    if (confirm(`Are you sure you want to delete ${selectedReviews.value.length} reviews?`)) {
        router.post('/admin/reviews/bulk-delete', {
            review_ids: selectedReviews.value,
        }, {
            onSuccess: () => {
                selectedReviews.value = [];
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
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Render stars
const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => i < rating);
};
</script>

<template>
    <Head title="Manage Reviews" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-blue-50 p-4 md:p-8 dark:from-gray-900 dark:to-gray-950">
            <div class="mx-auto max-w-7xl space-y-6">
                
                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reviews Management</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Moderate and manage user reviews
                        </p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Reviews</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_reviews }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-blue-500 to-teal-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                                <p class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.pending_reviews }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Approved</p>
                                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.approved_reviews }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg Rating</p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.average_rating }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Search Reviews
                            </label>
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search by comment, user, or place..."
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 pl-10 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                    @keyup.enter="applyFilters"
                                />
                                <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Rating
                            </label>
                            <select
                                v-model="selectedRating"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">All Ratings</option>
                                <option v-for="rating in ratings" :key="rating" :value="rating">
                                    {{ rating }} Star{{ rating > 1 ? 's' : '' }}
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
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>

                        <!-- Place Filter -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Place
                            </label>
                            <select
                                v-model="selectedPlace"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">All Places</option>
                                <option v-for="place in places" :key="place.id" :value="place.id">
                                    {{ place.name }}
                                </option>
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
                <div v-if="selectedReviews.length > 0" class="rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            {{ selectedReviews.length }} review{{ selectedReviews.length > 1 ? 's' : '' }} selected
                        </span>
                        <div class="flex gap-2">
                            <button
                                @click="bulkApprove"
                                class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-green-700"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Approve
                            </button>
                            <button
                                @click="bulkReject"
                                class="inline-flex items-center gap-2 rounded-lg bg-yellow-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-yellow-700"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reject
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

                <!-- Reviews Table -->
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Table Header -->
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                All Reviews ({{ reviews.total }})
                            </h2>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="reviews.data.length === 0" class="p-12 text-center">
                        <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">No reviews found</h3>
                        <p class="text-gray-600 dark:text-gray-400">Reviews will appear here once users submit them</p>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="selectedReviews.length === reviews.data.length"
                                            @change="toggleSelectAll"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                        />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Review
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Rating
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr
                                    v-for="review in reviews.data"
                                    :key="review.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                >
                                    <!-- Checkbox -->
                                    <td class="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            :value="review.id"
                                            v-model="selectedReviews"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                        />
                                    </td>

                                    <!-- Review Info -->
                                    <td class="px-6 py-4">
                                        <div class="max-w-md">
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ review.user.name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ review.place.name }}</p>
                                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 line-clamp-2">{{ review.comment }}</p>
                                        </div>
                                    </td>

                                    <!-- Rating -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1">
                                            <svg
                                                v-for="(filled, index) in renderStars(review.rating)"
                                                :key="index"
                                                :class="filled ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                                class="h-4 w-4"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize', getStatusColor(review.status)]">
                                            {{ review.status }}
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(review.created_at) }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                v-if="review.status !== 'approved'"
                                                @click="approveReview(review.id)"
                                                class="rounded-lg p-2 text-green-600 transition-colors hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20"
                                                title="Approve"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="review.status !== 'rejected'"
                                                @click="rejectReview(review.id)"
                                                class="rounded-lg p-2 text-yellow-600 transition-colors hover:bg-yellow-50 dark:text-yellow-400 dark:hover:bg-yellow-900/20"
                                                title="Reject"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                            <a
                                                :href="`/admin/reviews/${review.id}`"
                                                class="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                                                title="View"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <button
                                                @click="confirmDelete(review)"
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
                    <div v-if="reviews.data.length > 0" class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Showing {{ (reviews.current_page - 1) * reviews.per_page + 1 }} to 
                                {{ Math.min(reviews.current_page * reviews.per_page, reviews.total) }} of 
                                {{ reviews.total }} reviews
                            </div>
                            <div class="flex gap-2">
                                <button
                                    v-for="link in reviews.links"
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
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Delete Review</h3>
                </div>
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this review from <strong>{{ reviewToDelete?.user.name }}</strong>? This action cannot be undone.
                </p>
                <div class="flex gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteReview"
                        class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 font-semibold text-white transition-colors hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>