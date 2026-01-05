<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/auth/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Stats {
    total_places: number;
    total_reviews: number;
    total_users: number;
    pending_reviews: number;
}

interface Place {
    id: number;
    name: string;
    location: string;
    created_at: string;
}

interface Review {
    id: number;
    comment: string;
    rating: number;
    user: {
        name: string;
    };
    place: {
        name: string;
    };
    created_at: string;
}

defineProps<{
    stats: Stats;
    recentPlaces: Place[];
    recentReviews: Review[];
}>();
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Places -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-10">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Places</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total_places }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Reviews -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-10">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Reviews</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total_reviews }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Users -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-10">
                                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.total_users }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Reviews -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-500 bg-opacity-10">
                                <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Reviews</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stats.pending_reviews }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Recent Places -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Places</h3>
                        </div>
                        <div class="p-6">
                            <div v-if="recentPlaces.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No places yet
                            </div>
                            <ul v-else class="space-y-4">
                                <li v-for="place in recentPlaces" :key="place.id" class="flex items-start">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ place.name }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ place.location }}</p>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ new Date(place.created_at).toLocaleDateString() }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Reviews -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Reviews</h3>
                        </div>
                        <div class="p-6">
                            <div v-if="recentReviews.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No reviews yet
                            </div>
                            <ul v-else class="space-y-4">
                                <li v-for="review in recentReviews" :key="review.id" class="border-b border-gray-100 dark:border-gray-700 pb-4 last:border-0">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white text-sm">{{ review.user.name }}</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ review.place.name }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ review.rating }}</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ review.comment }}</p>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">
                                        {{ new Date(review.created_at).toLocaleDateString() }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>