<script setup lang="ts">
import AdminLayout from '@/layouts/app/AdminSidebarLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
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
    comment: string;
    rating: number;
    place: Place;
}

interface Report {
    id: number;
    type: string;
    reason: string;
    description: string | null;
    status: string;
    status_color: string;
    type_label: string;
    admin_notes: string | null;
    created_at: string;
    resolved_at: string | null;
    user: User;
    review: Review | null;
    place: Place | null;
    resolver: User | null;
}

interface Stats {
    total: number;
    pending: number;
    reviewing: number;
    resolved: number;
    dismissed: number;
}

interface Filters {
    status?: string;
    type?: string;
    search?: string;
}

const props = defineProps<{
    reports: {
        data: Report[];
        links: any[];
        meta: any;
    };
    stats: Stats;
    filters: Filters;
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: route('admin.dashboard') },
    { title: 'Reports', href: route('admin.reports.index') },
];

const selectedReports = ref<number[]>([]);
const showBulkActions = ref(false);
const bulkStatus = ref('');
const bulkNotes = ref('');
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const typeFilter = ref(props.filters.type || '');

const allSelected = computed(() => {
    return props.reports.data.length > 0 && 
           selectedReports.value.length === props.reports.data.length;
});

const toggleAll = () => {
    if (allSelected.value) {
        selectedReports.value = [];
    } else {
        selectedReports.value = props.reports.data.map(r => r.id);
    }
};

const applyFilters = () => {
    router.get(route('admin.reports.index'), {
        search: searchQuery.value,
        status: statusFilter.value,
        type: typeFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    typeFilter.value = '';
    router.get(route('admin.reports.index'));
};

const updateStatus = (reportId: number, status: string, notes?: string) => {
    router.patch(route('admin.reports.update-status', reportId), {
        status,
        admin_notes: notes,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedReports.value = [];
        },
    });
};

const deleteReport = (reportId: number) => {
    if (confirm('Are you sure you want to delete this report?')) {
        router.delete(route('admin.reports.destroy', reportId), {
            preserveScroll: true,
        });
    }
};

const bulkUpdateStatus = () => {
    if (selectedReports.value.length === 0) return;
    
    router.post(route('admin.reports.bulk-update-status'), {
        report_ids: selectedReports.value,
        status: bulkStatus.value,
        admin_notes: bulkNotes.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedReports.value = [];
            showBulkActions.value = false;
            bulkStatus.value = '';
            bulkNotes.value = '';
        },
    });
};

const bulkDelete = () => {
    if (selectedReports.value.length === 0) return;
    
    if (confirm(`Are you sure you want to delete ${selectedReports.value.length} reports?`)) {
        router.post(route('admin.reports.bulk-delete'), {
            report_ids: selectedReports.value,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedReports.value = [];
            },
        });
    }
};

const getStatusColor = (color: string) => {
    const colors: Record<string, string> = {
        yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        green: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        gray: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    };
    return colors[color] || colors.gray;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Reports Management" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reports Management</h1>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Reports</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total }}</div>
                </div>
                <div class="rounded-lg bg-yellow-50 p-4 shadow dark:bg-yellow-900/20">
                    <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Pending</div>
                    <div class="mt-1 text-2xl font-semibold text-yellow-900 dark:text-yellow-100">{{ stats.pending }}</div>
                </div>
                <div class="rounded-lg bg-blue-50 p-4 shadow dark:bg-blue-900/20">
                    <div class="text-sm font-medium text-blue-800 dark:text-blue-200">Reviewing</div>
                    <div class="mt-1 text-2xl font-semibold text-blue-900 dark:text-blue-100">{{ stats.reviewing }}</div>
                </div>
                <div class="rounded-lg bg-green-50 p-4 shadow dark:bg-green-900/20">
                    <div class="text-sm font-medium text-green-800 dark:text-green-200">Resolved</div>
                    <div class="mt-1 text-2xl font-semibold text-green-900 dark:text-green-100">{{ stats.resolved }}</div>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 shadow dark:bg-gray-700">
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Dismissed</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.dismissed }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search reports..."
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @keyup.enter="applyFilters"
                    />
                    <select
                        v-model="statusFilter"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="reviewing">Reviewing</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                    </select>
                    <select
                        v-model="typeFilter"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">All Types</option>
                        <option value="review">Review</option>
                        <option value="place">Place</option>
                        <option value="user">User</option>
                        <option value="other">Other</option>
                    </select>
                    <div class="flex gap-2">
                        <button
                            @click="applyFilters"
                            class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                        >
                            Apply
                        </button>
                        <button
                            @click="clearFilters"
                            class="rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedReports.length > 0" class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                        {{ selectedReports.length }} report(s) selected
                    </span>
                    <div class="flex gap-2">
                        <button
                            @click="showBulkActions = !showBulkActions"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
                        >
                            Update Status
                        </button>
                        <button
                            @click="bulkDelete"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700"
                        >
                            Delete Selected
                        </button>
                    </div>
                </div>

                <div v-if="showBulkActions" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <select
                        v-model="bulkStatus"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="reviewing">Reviewing</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                    </select>
                    <input
                        v-model="bulkNotes"
                        type="text"
                        placeholder="Admin notes (optional)"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                    <button
                        @click="bulkUpdateStatus"
                        :disabled="!bulkStatus"
                        class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-50"
                    >
                        Apply Changes
                    </button>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    @change="toggleAll"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Reporter
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Reason
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Subject
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr v-for="report in reports.data" :key="report.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4">
                                <input
                                    type="checkbox"
                                    :value="report.id"
                                    v-model="selectedReports"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ report.user.name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ report.user.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    {{ report.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ report.reason.replace('_', ' ') }}
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="report.review" class="text-sm">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ report.review.place.name }}</div>
                                    <div class="text-gray-500 dark:text-gray-400">Review #{{ report.review.id }}</div>
                                </div>
                                <div v-else-if="report.place" class="text-sm">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ report.place.name }}</div>
                                </div>
                                <div v-else class="text-sm text-gray-500 dark:text-gray-400">N/A</div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['inline-flex rounded-full px-2 text-xs font-semibold leading-5', getStatusColor(report.status_color)]">
                                    {{ report.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(report.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-2">
                                    <Link
                                        :href="route('admin.reports.show', report.id)"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        View
                                    </Link>
                                    <button
                                        v-if="report.status === 'pending'"
                                        @click="updateStatus(report.id, 'reviewing')"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Review
                                    </button>
                                    <button
                                        @click="deleteReport(report.id)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="reports.data.length === 0" class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No reports found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="reports.links.length > 3" class="flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing {{ reports.meta.from }} to {{ reports.meta.to }} of {{ reports.meta.total }} results
                </div>
                <div class="flex gap-2">
                    <Link
                        v-for="link in reports.links"
                        :key="link.label"
                        :href="link.url || ''"
                        :class="[
                            'px-4 py-2 rounded-md text-sm',
                            link.active
                                ? 'bg-blue-600 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                            !link.url && 'opacity-50 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>