<script setup lang="ts">
import AdminLayout from '@/layouts/app/AdminSidebarLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
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

const props = defineProps<{
    report: Report;
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: route('admin.dashboard') },
    { title: 'Reports', href: route('admin.reports.index') },
    { title: `Report #${props.report.id}`, href: route('admin.reports.show', props.report.id) },
];

const adminNotes = ref(props.report.admin_notes || '');
const isUpdating = ref(false);

const updateStatus = (status: string) => {
    if (isUpdating.value) return;
    
    const message = status === 'resolved' 
        ? 'Are you sure you want to mark this report as resolved?'
        : status === 'dismissed'
        ? 'Are you sure you want to dismiss this report?'
        : 'Update report status?';
    
    if (confirm(message)) {
        isUpdating.value = true;
        router.patch(route('admin.reports.update-status', props.report.id), {
            status,
            admin_notes: adminNotes.value,
        }, {
            preserveScroll: true,
            onFinish: () => {
                isUpdating.value = false;
            },
        });
    }
};

const deleteReport = () => {
    if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
        router.delete(route('admin.reports.destroy', props.report.id), {
            onSuccess: () => {
                router.visit(route('admin.reports.index'));
            },
        });
    }
};

const getStatusColor = (color: string) => {
    const colors: Record<string, string> = {
        yellow: 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:border-yellow-800',
        blue: 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:border-blue-800',
        green: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-200 dark:border-green-800',
        gray: 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600',
    };
    return colors[color] || colors.gray;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Report #${report.id}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Report #{{ report.id }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Reported on {{ formatDate(report.created_at) }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('admin.reports.index')"
                        class="rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                        Back to Reports
                    </Link>
                    <button
                        @click="deleteReport"
                        class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                    >
                        Delete Report
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Report Details Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Report Details</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                                <p class="mt-1 text-gray-900 dark:text-white">{{ report.type_label }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reason</label>
                                <p class="mt-1 text-gray-900 dark:text-white capitalize">
                                    {{ report.reason.replace('_', ' ') }}
                                </p>
                            </div>

                            <div v-if="report.description">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <p class="mt-1 text-gray-900 dark:text-white">{{ report.description }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Current Status</label>
                                <div class="mt-2">
                                    <span :class="['inline-flex rounded-full border px-3 py-1 text-sm font-semibold', getStatusColor(report.status_color)]">
                                        {{ report.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reported Content Card -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Reported Content</h2>
                        
                        <div v-if="report.review" class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Review for</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ report.review.place.name }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ report.review.place.location }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Rating</label>
                                <div class="mt-1 flex items-center gap-1">
                                    <svg v-for="i in 5" :key="i" class="h-5 w-5" :class="i <= report.review.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ report.review.rating }}/5</span>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Comment</label>
                                <p class="mt-1 rounded-md bg-gray-50 p-3 text-gray-900 dark:bg-gray-700 dark:text-white">
                                    {{ report.review.comment }}
                                </p>
                            </div>

                            <Link
                                :href="route('admin.reviews.show', report.review.id)"
                                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 dark:text-blue-400"
                            >
                                View Full Review
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>

                        <div v-else-if="report.place" class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Place</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ report.place.name }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ report.place.location }}
                                </p>
                            </div>

                            <Link
                                :href="route('admin.places.show', report.place.id)"
                                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 dark:text-blue-400"
                            >
                                View Place
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>

                        <div v-else class="text-center text-gray-500 dark:text-gray-400">
                            No specific content associated with this report
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Admin Notes</h2>
                        
                        <textarea
                            v-model="adminNotes"
                            rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Add notes about this report..."
                        ></textarea>

                        <div v-if="report.resolver && report.resolved_at" class="mt-4 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Resolved by <span class="font-medium text-gray-900 dark:text-white">{{ report.resolver.name }}</span>
                                on {{ formatDate(report.resolved_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Reporter Info -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Reporter</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <p class="mt-1 text-gray-900 dark:text-white">{{ report.user.name }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <p class="mt-1 text-gray-900 dark:text-white">{{ report.user.email }}</p>
                            </div>

                            <Link
                                :href="route('admin.users.show', report.user.id)"
                                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 dark:text-blue-400"
                            >
                                View Profile
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Actions</h2>
                        
                        <div class="space-y-3">
                            <button
                                v-if="report.status === 'pending'"
                                @click="updateStatus('reviewing')"
                                :disabled="isUpdating"
                                class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                            >
                                Mark as Reviewing
                            </button>

                            <button
                                v-if="report.status !== 'resolved'"
                                @click="updateStatus('resolved')"
                                :disabled="isUpdating"
                                class="w-full rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-50"
                            >
                                Mark as Resolved
                            </button>

                            <button
                                v-if="report.status !== 'dismissed'"
                                @click="updateStatus('dismissed')"
                                :disabled="isUpdating"
                                class="w-full rounded-md bg-gray-600 px-4 py-2 text-white hover:bg-gray-700 disabled:opacity-50"
                            >
                                Dismiss Report
                            </button>

                            <button
                                v-if="report.status !== 'pending'"
                                @click="updateStatus('pending')"
                                :disabled="isUpdating"
                                class="w-full rounded-md bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700 disabled:opacity-50"
                            >
                                Mark as Pending
                            </button>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Timeline</h2>
                        
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                        <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Report Created</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatDate(report.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="report.resolved_at" class="flex gap-3">
                                <div class="flex-shrink-0">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                        <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ report.status === 'resolved' ? 'Resolved' : 'Dismissed' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatDate(report.resolved_at) }}
                                    </p>
                                    <p v-if="report.resolver" class="text-xs text-gray-500 dark:text-gray-400">
                                        by {{ report.resolver.name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>