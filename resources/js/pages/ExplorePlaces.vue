<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Explore Places',
    href: '/places',
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
  places: Place[];
}

const props = defineProps<Props>();

// Filter and search state
const searchQuery = ref('');
const selectedType = ref('All');
const savedPlaces = ref<number[]>([]); // Store IDs of saved places

// Load saved places from database on mount
onMounted(async () => {
  await loadSavedPlaces();
});

// Load saved places from database
const loadSavedPlaces = async () => {
  try {
    const response = await axios.get('/api/saved-places');
    savedPlaces.value = response.data;
    // Also update localStorage for offline access
    localStorage.setItem('savedPlaces', JSON.stringify(response.data));
  } catch (error) {
    console.error('Failed to load saved places:', error);
    // Fallback to localStorage if API fails
    const cached = localStorage.getItem('savedPlaces');
    if (cached) {
      savedPlaces.value = JSON.parse(cached);
    }
  }
};

// Get unique types for filter
const types = computed(() => {
  const uniqueTypes = ['All', ...new Set(props.places.map(p => p.type))];
  return uniqueTypes;
});

// Filtered places based on search and type
const filteredPlaces = computed(() => {
  return props.places.filter(place => {
    const matchesSearch = place.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         place.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         place.location.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchesType = selectedType.value === 'All' || place.type === selectedType.value;
    
    return matchesSearch && matchesType;
  });
});

// Toggle save/bookmark with database sync
const showSaveToast = ref(false);
const toastMessage = ref('');

const toggleSave = async (placeId: number, placeName: string) => {
  try {
    // Call API to toggle save state
    const response = await axios.post(`/api/saved-places/toggle/${placeId}`);
    
    // Update local state based on API response
    if (response.data.saved) {
      savedPlaces.value.push(placeId);
      toastMessage.value = `Added ${placeName} to saved places`;
    } else {
      const index = savedPlaces.value.indexOf(placeId);
      if (index > -1) {
        savedPlaces.value.splice(index, 1);
      }
      toastMessage.value = `Removed ${placeName} from saved places`;
    }
    
    // Update localStorage
    localStorage.setItem('savedPlaces', JSON.stringify(savedPlaces.value));
    
  } catch (error) {
    console.error('Failed to toggle save:', error);
    toastMessage.value = 'Failed to update saved places. Please try again.';
  }
  
  // Show toast notification
  showSaveToast.value = true;
  setTimeout(() => {
    showSaveToast.value = false;
  }, 3000);
};

const isSaved = (placeId: number) => {
  return savedPlaces.value.includes(placeId);
};

</script>

<template>
  <Head title="Explore Places - Babatngon Explorer">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  </Head>
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950">
      <div class="mx-auto max-w-7xl px-4 py-8 md:px-8 md:py-12">
        
        <!-- Header Section -->
        <div class="mb-8 text-center">
          <h1 class="mb-3 text-4xl font-bold text-gray-900 md:text-5xl dark:text-white">
            Explore
            <span class="bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">
              Babatngon
            </span>
          </h1>
          <p class="mx-auto max-w-2xl text-lg text-gray-600 dark:text-gray-400">
            Discover pristine beaches, hidden waterfalls, and amazing resorts in the heart of Leyte
          </p>
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <!-- Search Bar -->
          <div class="relative flex-1 md:max-w-md">
            <svg
              class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search places..."
              class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-10 pr-4 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
            />
          </div>

          <!-- Type Filter -->
          <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0">
            <button
              v-for="type in types"
              :key="type"
              @click="selectedType = type"
              :class="[
                'whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition',
                selectedType === type
                  ? 'bg-teal-600 text-white shadow-md'
                  : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
              ]"
            >
              {{ type }}
            </button>
          </div>
        </div>

        <!-- Results Count -->
        <div class="mb-6 text-sm text-gray-600 dark:text-gray-400">
          Showing {{ filteredPlaces.length }} {{ filteredPlaces.length === 1 ? 'place' : 'places' }}
        </div>

        <!-- Places Grid -->
        <div v-if="filteredPlaces.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="place in filteredPlaces"
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

              <!-- Save/Bookmark Button -->
              <button
                @click.prevent="toggleSave(place.id, place.name)"
                :class="[
                  'absolute right-4 top-4 z-10 rounded-full p-2 shadow-md backdrop-blur-sm transition-all duration-300 cursor-pointer',
                  isSaved(place.id)
                    ? 'bg-red-500 text-white scale-110 hover:bg-red-600 hover:scale-125'
                    : 'bg-white/95 text-gray-600 hover:bg-white hover:scale-110 dark:bg-gray-900/95 dark:text-gray-300 dark:hover:bg-gray-800'
                ]"
              >
                <svg
                  class="h-5 w-5 transition-all duration-300"
                  :class="{ 'fill-current': isSaved(place.id) }"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                  />
                </svg>
              </button>

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
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
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
                <svg
                  class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </Link>
            </div>
          </div>
        </div>

        <!-- No Results -->
        <div v-else class="rounded-2xl bg-white p-12 text-center shadow-sm dark:bg-gray-800">
          <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">No places found</h3>
          <p class="text-gray-600 dark:text-gray-400">Try adjusting your search or filters</p>
        </div>

        <!-- Saved Places Counter -->
        <div v-if="savedPlaces.length > 0" class="mt-8 rounded-2xl bg-gradient-to-r from-teal-50 to-blue-50 p-6 dark:from-teal-900/20 dark:to-blue-900/20">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="rounded-full bg-teal-600 p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                  />
                </svg>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 dark:text-white">
                  {{ savedPlaces.length }} {{ savedPlaces.length === 1 ? 'Place' : 'Places' }} Saved
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  View your bookmarked destinations anytime
                </p>
              </div>
            </div>
            <Link
              href="/save"
              class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700"
            >
              View Saved
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
      >
        <div
          v-if="showSaveToast"
          class="fixed bottom-8 left-1/2 z-50 -translate-x-1/2 transform"
        >
          <div class="flex items-center gap-3 rounded-lg bg-gray-900 px-6 py-3 text-white shadow-2xl dark:bg-gray-100 dark:text-gray-900">
            <svg class="h-5 w-5 text-green-400 dark:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="font-medium">{{ toastMessage }}</span>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AppLayout>
</template>

<style scoped>
* {
    font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style>