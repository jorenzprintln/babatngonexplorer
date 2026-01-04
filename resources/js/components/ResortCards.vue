<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

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
  places?: Place[];
}

// Accept places as a prop (will be passed from parent component/page)
const props = defineProps<Props>();

// Get page props - usePage will use the global PageProps type
const page = usePage();

// Check if user is authenticated
const isAuthenticated = computed(() => {
  const user = page.props.auth?.user;
  return user !== null && user !== undefined;
});

// Use dynamic places data or empty array as fallback
const resorts = computed(() => props.places || []);
</script>

<template>
  <section id="resorts" class="bg-gradient-to-b from-[#FDFDFC] to-blue-50 px-6 py-20 dark:from-[#0a0a0a] dark:to-gray-900">
    <div class="mx-auto max-w-7xl">
      <!-- Section Header -->
      <div class="mb-16 text-center">
        <h2 class="mb-4 text-4xl font-bold text-gray-900 md:text-5xl dark:text-white">
          Explore Our
          <span class="bg-gradient-to-r from-blue-600 to-teal-500 bg-clip-text text-transparent">Destinations</span>
        </h2>
        <p class="mx-auto max-w-2xl text-lg text-gray-600 dark:text-gray-300">
          From pristine beaches to hidden waterfalls, discover the natural wonders that make Babatngon unforgettable
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="!resorts.length" class="text-center py-12">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-blue-600 border-r-transparent"></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Loading destinations...</p>
      </div>

      <!-- Cards Grid -->
      <div v-else class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="resort in resorts"
          :key="resort.id"
          class="group relative overflow-hidden rounded-2xl bg-white shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl dark:bg-gray-800"
        >
          <!-- Image Container -->
          <div class="relative h-64 overflow-hidden">
            <img
              :src="resort.image"
              :alt="resort.name"
              class="h-full w-full object-cover"
            />
            <!-- Type Badge -->
            <div class="absolute right-4 top-4 rounded-full bg-white/90 px-3 py-1 text-sm font-semibold text-gray-800 backdrop-blur-sm dark:bg-gray-900/90 dark:text-white">
              {{ resort.type }}
            </div>
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
          </div>

          <!-- Content -->
          <div class="p-6">
            <!-- Rating -->
            <div class="mb-3 flex items-center gap-2">
              <div class="flex items-center">
                <svg
                  v-for="i in 5"
                  :key="i"
                  class="h-5 w-5"
                  :class="i <= Math.floor(resort.rating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ resort.rating > 0 ? resort.rating.toFixed(1) : 'N/A' }}
              </span>
              <span v-if="resort.reviewCount > 0" class="text-xs text-gray-500 dark:text-gray-400">
                ({{ resort.reviewCount }})
              </span>
            </div>

            <!-- Title -->
            <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">
              {{ resort.name }}
            </h3>

            <!-- Description -->
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
              {{ resort.description }}
            </p>

            <!-- Location -->
            <div class="flex items-start gap-2 text-sm text-gray-500 dark:text-gray-400">
              <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>{{ resort.location }}</span>
            </div>

            <!-- Learn More Button -->
            <a 
              :href="`/places/${resort.id}`"
              class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:from-blue-700 hover:to-teal-600 hover:shadow-lg"
            >
              Learn More
              <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>