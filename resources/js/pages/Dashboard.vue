<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItemType, type PageProps } from '@/types';
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

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Properly type the page with PageProps
const page = usePage<PageProps>();
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
    <!-- Rest of your template stays the same -->
    <Head title="Dashboard">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- ... rest of your template ... -->
    </AppLayout>
</template>