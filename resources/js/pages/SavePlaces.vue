<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Saved Places',
    href: '/save',
  },
];

interface Place {
  id: number;
  name: string;
  description: string;
  location: string;
  image: string;
  rating: number;
  type: string;
  reviewCount: number;
}

interface Props {
  allPlaces: Place[];
}

const props = defineProps<Props>();

const savedPlaceIds = ref<number[]>([]);
const sortBy = ref('name');
const showRemoveModal = ref(false);
const placeToRemove = ref<any>(null);

// Load saved places from database
const loadSavedPlaces = async () => {
  try {
    const response = await axios.get('/api/saved-places');
    savedPlaceIds.value = response.data;
    localStorage.setItem('savedPlaces', JSON.stringify(response.data));
  } catch (error) {
    console.error('Failed to load:', error);
    const cached = localStorage.getItem('savedPlaces');
    if (cached) savedPlaceIds.value = JSON.parse(cached);
  }
};

onMounted(() => {
  // Load from localStorage first for immediate display
  const cached = localStorage.getItem('savedPlaces');
  if (cached) savedPlaceIds.value = JSON.parse(cached);
  
  // Sync with database
  loadSavedPlaces();
});

// Updated confirmRemove function
const confirmRemove = async () => {
  if (!placeToRemove.value) {
    console.log('No place to remove');
    return;
  }

  console.log('Removing place:', placeToRemove.value.name);
  
  try {
    await axios.post(`/api/saved-places/toggle/${placeToRemove.value.id}`);
    savedPlaceIds.value = savedPlaceIds.value.filter(id => id !== placeToRemove.value.id);
    localStorage.setItem('savedPlaces', JSON.stringify(savedPlaceIds.value));
    console.log('Place removed successfully');
  } catch (error) {
    console.error('Failed to remove:', error);
    // Still remove from UI even if API fails
    savedPlaceIds.value = savedPlaceIds.value.filter(id => id !== placeToRemove.value.id);
    localStorage.setItem('savedPlaces', JSON.stringify(savedPlaceIds.value));
  } finally {
    showRemoveModal.value = false;
    placeToRemove.value = null;
  }
};

// Close modal function
const closeModal = () => {
  showRemoveModal.value = false;
  placeToRemove.value = null;
  console.log('Modal closed');
};

// Get saved places data with dynamic ratings
const savedPlaces = computed(() => {
  return props.allPlaces.filter(place => savedPlaceIds.value.includes(place.id));
});

// Sort places
const sortedPlaces = computed(() => {
  const places = [...savedPlaces.value];
  
  if (sortBy.value === 'name') {
    return places.sort((a, b) => a.name.localeCompare(b.name));
  } else if (sortBy.value === 'rating') {
    return places.sort((a, b) => b.rating - a.rating);
  } else if (sortBy.value === 'type') {
    return places.sort((a, b) => a.type.localeCompare(b.type));
  }
  
  return places;
});

// Stats
const averageRating = computed(() => {
  if (savedPlaces.value.length === 0) return 0;
  const sum = savedPlaces.value.reduce((acc, place) => acc + place.rating, 0);
  return (sum / savedPlaces.value.length).toFixed(1);
});

const uniqueCategories = computed(() => {
  return new Set(savedPlaces.value.map(p => p.type)).size;
});

// Updated handleRemoveClick function
const handleRemoveClick = (place: any) => {
  console.log('Remove button clicked for:', place.name);
  placeToRemove.value = place;
  showRemoveModal.value = true;
};

const handleExportList = () => {
  const exportData = savedPlaces.value.map(p => ({
    name: p.name,
    type: p.type,
    location: p.location,
    rating: p.rating,
    reviewCount: p.reviewCount
  }));
  const dataStr = JSON.stringify(exportData, null, 2);
  const dataBlob = new Blob([dataStr], { type: 'application/json' });
  const url = URL.createObjectURL(dataBlob);
  const link = document.createElement('a');
  link.href = url;
  link.download = 'my-saved-places.json';
  link.click();
  URL.revokeObjectURL(url);
};
</script>

<template>
  <Head title="Saved Places - Babatngon Explorer">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  </Head>

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950">
      <div class="mx-auto max-w-7xl px-4 py-8 md:px-8 md:py-12">
        
        <!-- Header -->
        <div class="mb-8">
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <h1 class="mb-2 text-4xl font-bold text-gray-900 md:text-5xl dark:text-white">
                Saved
                <span class="ml-2 bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">
                  Places
                </span>
              </h1>
              <p class="text-lg text-gray-600 dark:text-gray-400">
                Your bookmarked destinations • {{ savedPlaces.length }} {{ savedPlaces.length === 1 ? 'place' : 'places' }}
              </p>
            </div>

            <div v-if="savedPlaces.length > 0" class="flex gap-3">
              <button
                @click="handleExportList"
                class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export List
              </button>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div v-if="savedPlaces.length > 0" class="mb-8 grid gap-4 md:grid-cols-3">
          <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
              <div>
                <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">Total Saved</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ savedPlaces.length }}</p>
              </div>
              <div class="rounded-xl bg-gradient-to-br from-blue-500 to-teal-500 p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
              <div>
                <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">Avg Rating</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ averageRating }}</p>
              </div>
              <div class="rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 p-3">
                <svg class="h-6 w-6 fill-current text-white" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
              <div>
                <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">Categories</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ uniqueCategories }}</p>
              </div>
              <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Sort Options -->
        <div v-if="savedPlaces.length > 0" class="mb-6 flex items-center gap-3">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</span>
          <div class="flex gap-2">
            <button
              v-for="option in ['name', 'rating', 'type']"
              :key="option"
              @click="sortBy = option"
              :class="[
                'rounded-lg px-4 py-2 text-sm font-medium transition',
                sortBy === option
                  ? 'bg-teal-600 text-white shadow-md'
                  : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
              ]"
            >
              {{ option.charAt(0).toUpperCase() + option.slice(1) }}
            </button>
          </div>
        </div>

        <!-- Places Grid -->
        <div v-if="savedPlaces.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="place in sortedPlaces"
            :key="place.id"
            class="group relative overflow-hidden rounded-2xl bg-white shadow-md transition-all duration-300 hover:-translate-y-2 hover:shadow-xl dark:bg-gray-800"
          >
            <!-- Image Container -->
            <div class="relative h-56 overflow-hidden">
              <img
                :src="place.image"
                :alt="place.name"
                class="h-full w-full object-cover"
              />
              
              <!-- Type Badge -->
              <div class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-sm font-semibold text-gray-800 shadow-md backdrop-blur-sm dark:bg-gray-900/95 dark:text-white">
                {{ place.type }}
              </div>

              <!-- Remove Button with X icon -->
              <button
                @click.stop="handleRemoveClick(place)"
                type="button"
                class="absolute right-4 top-4 z-10 rounded-full bg-red-500 p-2 text-white shadow-md transition-all duration-300 hover:scale-110 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                title="Remove from saved places"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <!-- Saved Badge -->
              <div class="absolute bottom-4 right-4 rounded-full bg-green-500 px-3 py-1 text-xs font-semibold text-white shadow-lg">
                Saved
              </div>

              <!-- Gradient Overlay -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
            </div>

            <!-- Content -->
            <div class="p-5">
              <!-- Rating -->
              <div class="mb-3 flex items-center gap-2">
                <div class="flex items-center">
                  <svg
                    v-for="i in 5"
                    :key="i"
                    class="h-4 w-4"
                    :class="i <= Math.floor(place.rating) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
                <span class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ place.rating > 0 ? place.rating.toFixed(1) : 'N/A' }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">({{ place.reviewCount }})</span>
              </div>

              <!-- Title -->
              <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">
                {{ place.name }}
              </h3>

              <!-- Description -->
              <p class="mb-4 line-clamp-2 text-sm text-gray-600 dark:text-gray-400">
                {{ place.description }}
              </p>

              <!-- Location -->
              <div class="mb-4 flex items-start gap-2 text-sm text-gray-500 dark:text-gray-400">
                <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="line-clamp-1">{{ place.location }}</span>
              </div>

              <!-- View Details Button -->
              <Link
                :href="`/places/${place.id}`"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:from-teal-700 hover:to-blue-700 hover:shadow-lg"
              >
                View Details
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </Link>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="rounded-2xl bg-white p-16 text-center shadow-sm dark:bg-gray-800">
          <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
            <svg class="h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg>
          </div>
          <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">No Saved Places Yet</h3>
          <p class="mb-6 text-gray-600 dark:text-gray-400">
            Start exploring and bookmark your favorite destinations
          </p>
          <Link
            href="/places"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-blue-600 px-6 py-3 text-sm font-semibold text-white transition-all duration-300 hover:from-teal-700 hover:to-blue-700 hover:shadow-lg"
          >
            Explore Places
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </Link>
        </div>

        <!-- Quick Tips -->
        <div v-if="savedPlaces.length > 0" class="mt-8 rounded-2xl border border-teal-200 bg-gradient-to-r from-teal-50 to-blue-50 p-6 dark:border-teal-800 dark:from-teal-900/20 dark:to-blue-900/20">
          <div class="flex items-start gap-4">
            <div class="rounded-full bg-teal-600 p-2">
              <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h4 class="mb-2 font-semibold text-gray-900 dark:text-white">Quick Tip</h4>
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Click the X icon to remove places from your saved list. You can also export your list to share with friends or keep as a reference!
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Remove Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="showRemoveModal && placeToRemove" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="closeModal"
      >
        <div 
          class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800"
          @click.stop
        >
          <div class="mb-4 flex items-center gap-3">
            <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/30">
              <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900 dark:text-white">Remove from Saved?</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">This action can be undone by saving again</p>
            </div>
          </div>
          <p class="mb-6 text-gray-700 dark:text-gray-300">
            Are you sure you want to remove <span class="font-semibold">{{ placeToRemove.name }}</span> from your saved places?
          </p>
          <div class="flex gap-3">
            <button
              @click="closeModal"
              type="button"
              class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
              Cancel
            </button>
            <button
              @click="confirmRemove"
              type="button"
              class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
              Remove
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<style scoped>
* {
  font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style>