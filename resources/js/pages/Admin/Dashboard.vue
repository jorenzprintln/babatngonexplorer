<script setup lang="ts">
import AdminLayout from '@/layouts/app/AdminSidebarLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import type { BreadcrumbItemType } from '@/types';

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

const props = defineProps<{
    stats: Stats;
    recentPlaces: Place[];
    recentReviews: Review[];
}>();

// Safe route helper
const getRoute = (name: string): string => {
    if (typeof window !== 'undefined' && typeof window.route === 'function') {
        try {
            return window.route(name);
        } catch {
            return '/admin/dashboard';
        }
    }
    return '/admin/dashboard';
};

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Dashboard',
        href: getRoute('admin.dashboard'),
    },
];

// Time-based greeting and background
const currentTime = ref(new Date());
const greeting = computed(() => {
    const hour = currentTime.value.getHours();
    if (hour >= 5 && hour < 12) return 'Good Morning';
    if (hour >= 12 && hour < 18) return 'Good Afternoon';
    return 'Good Evening';
});

const timeOfDay = computed(() => {
    const hour = currentTime.value.getHours();
    if (hour >= 5 && hour < 12) return 'morning';
    if (hour >= 12 && hour < 18) return 'afternoon';
    return 'night';
});

const backgroundStyle = computed(() => {
    const tod = timeOfDay.value;
    let bgImage = '';
    
    if (tod === 'morning') {
        bgImage = 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80';
    } else if (tod === 'afternoon') {
        bgImage = 'https://images.unsplash.com/photo-1601297183305-6df142704ea2?w=1200&q=80';
    } else {
        bgImage = 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1200&q=80';
    }
    
    return {
        backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(${bgImage})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat'
    };
});

const textColor = computed(() => 'text-white');
const dateColor = computed(() => 'text-white/90');

const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        timeZone: 'Asia/Manila'
    });
});

const formattedTime = computed(() => {
    return currentTime.value.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
        timeZone: 'Asia/Manila'
    });
});

let timeInterval: number;

onMounted(() => {
    timeInterval = window.setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timeInterval) {
        clearInterval(timeInterval);
    }
});

// Dynamic stats cards
const statsCards = computed(() => [
    { 
        id: 1, 
        label: 'Total Places', 
        value: props.stats.total_places.toString(), 
        color: 'from-blue-500 to-teal-500',
        icon: 'location'
    },
    { 
        id: 2, 
        label: 'Total Reviews', 
        value: props.stats.total_reviews.toString(), 
        color: 'from-teal-500 to-emerald-500',
        icon: 'star'
    },
    { 
        id: 3, 
        label: 'Total Users', 
        value: props.stats.total_users.toString(), 
        color: 'from-emerald-500 to-green-500',
        icon: 'users'
    },
    { 
        id: 4, 
        label: 'Pending Reviews', 
        value: props.stats.pending_reviews.toString(), 
        color: 'from-orange-500 to-red-500',
        icon: 'clock'
    }
]);
</script>

<template>
    <Head title="Admin Dashboard">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-blue-50 p-4 md:p-8 dark:from-gray-900 dark:to-gray-950" style="font-family: 'Poppins', sans-serif;">
            <div class="mx-auto max-w-7xl space-y-8">
                
                <!-- Welcome Section with Time-based Background -->
                <div 
                    :style="backgroundStyle"
                    class="rounded-2xl p-8 shadow-2xl transition-all duration-1000 relative overflow-hidden"
                >
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
                        <!-- Left side - Greeting -->
                        <div>
                            <h1 :class="['mb-2 text-3xl font-bold md:text-4xl drop-shadow-lg', textColor]">
                                {{ greeting }}, Admin
                            </h1>
                            <p :class="['text-lg drop-shadow-md', dateColor]">
                                Monitor and manage your platform activities
                            </p>
                        </div>
                        
                        <!-- Right side - Date and Time -->
                        <div :class="['text-right drop-shadow-md', dateColor]">
                            <div class="text-sm font-medium opacity-90 mb-1">Today is</div>
                            <div class="text-lg font-semibold">{{ formattedDate }}</div>
                            <div class="text-3xl font-bold mt-2">{{ formattedTime }}</div>
                            <div class="text-xs font-medium opacity-75 mt-1">Philippine Time (GMT+8)</div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards Grid -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="stat in statsCards"
                        :key="stat.id"
                        class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">{{ stat.label }}</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stat.value }}</p>
                            </div>
                            <div :class="`rounded-xl bg-gradient-to-br p-3 ${stat.color}`">
                                <!-- Location Icon -->
                                <svg v-if="stat.icon === 'location'" class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <!-- Star Icon -->
                                <svg v-if="stat.icon === 'star'" class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <!-- Users Icon -->
                                <svg v-if="stat.icon === 'users'" class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <!-- Clock Icon -->
                                <svg v-if="stat.icon === 'clock'" class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    
                    <!-- Recent Places -->
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Recent Places</h2>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <!-- Empty State -->
                            <div v-if="recentPlaces.length === 0" class="p-12 text-center">
                                <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">No places yet</h3>
                                <p class="text-gray-600 dark:text-gray-400">Places will appear here once added</p>
                            </div>

                            <!-- Places List -->
                            <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                                <div
                                    v-for="place in recentPlaces"
                                    :key="place.id"
                                    class="p-6 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">{{ place.name }}</h3>
                                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>{{ place.location }}</span>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap ml-4">
                                            {{ new Date(place.created_at).toLocaleDateString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Reviews -->
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Recent Reviews</h2>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <!-- Empty State -->
                            <div v-if="recentReviews.length === 0" class="p-12 text-center">
                                <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">No reviews yet</h3>
                                <p class="text-gray-600 dark:text-gray-400">Reviews will appear here once submitted</p>
                            </div>

                            <!-- Reviews List -->
                            <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                                <div
                                    v-for="review in recentReviews"
                                    :key="review.id"
                                    class="p-6 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ review.user.name }}</p>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">reviewed</span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ review.place.name }}</p>
                                        </div>
                                        <div class="flex items-center gap-1 ml-4">
                                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ review.rating }}</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">{{ review.comment }}</p>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ new Date(review.created_at).toLocaleDateString() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-teal-500 p-8 text-white shadow-lg">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="mb-3 flex items-center gap-2">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <h3 class="text-2xl font-bold">Quick Actions</h3>
                            </div>
                            <p class="mb-6 max-w-md text-blue-50">
                                Manage places, reviews, and users efficiently from your admin panel.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <button class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 font-semibold text-blue-600 transition-colors duration-300 hover:bg-blue-50">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Place
                                </button>
                                <button class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-5 py-2.5 font-semibold text-white backdrop-blur-sm transition-colors duration-300 hover:bg-white/20">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    View Reports
                                </button>
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <svg class="h-32 w-32 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>