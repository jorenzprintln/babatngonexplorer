<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

interface Place {
  id: number;
  name: string;
  location: string;
  image: string;
  type: string;
  rating: number;
}

interface Stats {
  savedPlaces: number;
  reviewsSubmitted: number;
  recentlyViewed: number;
}

interface Activity {
  action: string;
  place: string;
  time: string;
}

interface Props {
  stats: Stats;
  recentPlaces: Place[];
  memberSince: string;
  reviewedPlacesCount: number;
  recentActivity: Activity[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name || 'User');

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

// Dynamic stats
const statsCards = computed(() => [
  { 
    id: 1, 
    label: 'Saved Places', 
    value: props.stats.savedPlaces.toString(), 
    color: 'from-blue-500 to-teal-500',
    icon: 'bookmark'
  },
  { 
    id: 2, 
    label: 'Reviews Submitted', 
    value: props.stats.reviewsSubmitted.toString(), 
    color: 'from-teal-500 to-emerald-500',
    icon: 'message'
  },
  { 
    id: 3, 
    label: 'Recently Viewed', 
    value: props.stats.recentlyViewed.toString(), 
    color: 'from-emerald-500 to-green-500',
    icon: 'eye'
  }
]);

// Get activity icon based on action type
const getActivityIcon = (action: string) => {
  if (action === 'Reviewed') return 'message';
  if (action === 'Saved') return 'bookmark';
  if (action === 'Viewed') return 'eye';
  return 'activity';
};

// Get activity color based on action type
const getActivityColor = (action: string) => {
  if (action === 'Reviewed') return 'bg-teal-50 dark:bg-teal-900/30';
  if (action === 'Saved') return 'bg-blue-50 dark:bg-blue-900/30';
  if (action === 'Viewed') return 'bg-purple-50 dark:bg-purple-900/30';
  return 'bg-gray-50 dark:bg-gray-900/30';
};

const getActivityIconColor = (action: string) => {
  if (action === 'Reviewed') return 'text-teal-600 dark:text-teal-400';
  if (action === 'Saved') return 'text-blue-600 dark:text-blue-400';
  if (action === 'Viewed') return 'text-purple-600 dark:text-purple-400';
  return 'text-gray-600 dark:text-gray-400';
};
</script>

<template>
    <Head title="Dashboard">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
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
                                {{ greeting }}, {{ userName }}
                            </h1>
                            <p :class="['text-lg drop-shadow-md', dateColor]">
                                Ready to discover more hidden gems in Babatngon?
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

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
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
                                <!-- Bookmark Icon -->
                                <svg v-if="stat.icon === 'bookmark'" class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                                <!-- Message Icon -->
                                <svg v-if="stat.icon === 'message'" class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                <!-- Eye Icon -->
                                <svg v-if="stat.icon === 'eye'" class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    
                    <!-- Recently Viewed - Takes 2 columns -->
                    <div class="space-y-6 lg:col-span-2">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Recently Viewed</h2>
                            <a href="/places" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                View All →
                            </a>
                        </div>

                        <!-- Empty State -->
                        <div v-if="recentPlaces.length === 0" class="rounded-2xl bg-white p-12 text-center shadow-sm dark:bg-gray-800">
                            <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">No places viewed yet</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">Start exploring amazing destinations in Babatngon</p>
                            <a href="/places" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-6 py-3 font-semibold text-white transition hover:bg-teal-700">
                                Explore Places
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                        <!-- Recently Viewed Places - Now displays 4 items -->
                        <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <a
                                v-for="place in recentPlaces"
                                :key="place.id"
                                :href="`/places/${place.id}`"
                                class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900"
                            >
                                <!-- Image -->
                                <div class="relative h-48 overflow-hidden">
                                    <img
                                        :src="place.image"
                                        :alt="place.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-sm font-semibold text-gray-800 backdrop-blur-sm dark:bg-gray-900/90 dark:text-white">
                                        {{ place.type }}
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                </div>

                                <!-- Content -->
                                <div class="p-5">
                                    <div class="mb-2 flex items-center gap-2">
                                        <div class="flex items-center">
                                            <svg
                                                v-for="i in 5"
                                                :key="i"
                                                :class="i <= Math.floor(place.rating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                                class="h-4 w-4"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ place.rating.toFixed(1) }}</span>
                                    </div>

                                    <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">{{ place.name }}</h3>
                                    
                                    <div class="flex items-start gap-2 text-sm text-gray-500 dark:text-gray-400">
                                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>{{ place.location }}</span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Explore More Card -->
                        <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-teal-500 p-8 text-white shadow-lg">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="mb-3 flex items-center gap-2">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                        <h3 class="text-2xl font-bold">Discover More</h3>
                                    </div>
                                    <p class="mb-6 max-w-md text-blue-50">
                                        Explore pristine beaches, hidden waterfalls, and serene resorts waiting to be discovered in Babatngon.
                                    </p>
                                    <a href="/places" 
                                        class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-semibold text-blue-600 transition-colors duration-300 hover:bg-blue-50">
                                            Explore Babatngon
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                                <div class="hidden md:block">
                                    <svg class="h-32 w-32 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Sidebar -->
                    <div class="space-y-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Recent Activity</h2>
                        
                        <!-- Dynamic Recent Activity -->
                        <div v-if="recentActivity.length > 0" class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <template v-for="(activity, index) in recentActivity" :key="index">
                                <div>
                                    <div class="flex items-start gap-4">
                                        <div :class="['rounded-lg p-2', getActivityColor(activity.action)]">
                                            <!-- Reviewed Icon -->
                                            <svg v-if="getActivityIcon(activity.action) === 'message'" :class="['h-5 w-5', getActivityIconColor(activity.action)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                            </svg>
                                            <!-- Saved Icon -->
                                            <svg v-else-if="getActivityIcon(activity.action) === 'bookmark'" :class="['h-5 w-5', getActivityIconColor(activity.action)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                            </svg>
                                            <!-- Viewed Icon -->
                                            <svg v-else-if="getActivityIcon(activity.action) === 'eye'" :class="['h-5 w-5', getActivityIconColor(activity.action)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ activity.action }} <span class="font-normal text-gray-600 dark:text-gray-400">{{ activity.place }}</span>
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">{{ activity.time }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="index < recentActivity.length - 1" class="mt-4 border-b border-gray-100 dark:border-gray-800"></div>
                            </template>
                        </div>

                        <!-- Empty Activity State -->
                        <div v-else class="rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-gray-600 dark:text-gray-400">No recent activity</p>
                        </div>

                        <!-- Your Journey Stats - Updated with reviewedPlacesCount -->
                        <div class="rounded-2xl bg-gradient-to-br from-teal-600 to-blue-600 p-6 shadow-lg text-white">
                            <h3 class="mb-4 text-lg font-bold">Your Journey</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-teal-50">Places Explored</span>
                                    <span class="text-2xl font-bold">{{ reviewedPlacesCount }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-teal-50">Total Reviews</span>
                                    <span class="text-2xl font-bold">{{ stats.reviewsSubmitted }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-teal-50">Member Since</span>
                                    <span class="font-semibold">{{ memberSince }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>