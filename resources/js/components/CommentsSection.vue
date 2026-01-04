<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Review {
  id: number;
  name: string;
  rating: number;
  date: string;
  comment: string;
  location: string;
  place_name?: string;
}

interface Props {
  reviews?: Review[];
}

const props = withDefaults(defineProps<Props>(), {
  reviews: () => []
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

// Use dynamic reviews
const displayReviews = computed(() => props.reviews);
const hasReviews = computed(() => displayReviews.value.length > 0);

// Get user initials from name (same as PlaceDetails)
const getUserInitials = (name: string): string => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const handleExploreClick = (event: MouseEvent) => {
  if (!isAuthenticated.value) {
    event.preventDefault();
    window.location.href = '/places';
  }
};
</script>

<template>
  <section class="bg-white px-6 py-20 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl">
      <!-- Section Header -->
      <div class="mb-16 text-center">
        <h2 class="mb-4 text-4xl font-bold text-gray-900 md:text-5xl dark:text-white">
          What Visitors
          <span class="bg-gradient-to-r from-blue-600 to-teal-500 bg-clip-text text-transparent">Are Saying</span>
        </h2>
        <p class="mx-auto max-w-2xl text-lg text-gray-600 dark:text-gray-300">
          Real experiences from travelers who discovered the magic of Babatngon
        </p>
      </div>

      <!-- Empty State -->
      <div v-if="!hasReviews" class="text-center py-16">
        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
          <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
          </svg>
        </div>
        <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">No Reviews Yet</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Be the first to share your experience!</p>
        <a
          href="/places"
          @click="handleExploreClick"
          class="inline-block rounded-lg bg-gradient-to-r from-blue-600 to-teal-500 px-6 py-3 font-semibold text-white transition hover:from-blue-700 hover:to-teal-600 cursor-pointer"
        >
          Explore Places and Reviews
        </a>
      </div>

      <!-- Reviews Grid -->
      <div v-else class="grid gap-8 md:grid-cols-2">
        <div
          v-for="review in displayReviews"
          :key="review.id"
          class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-blue-50/50 p-8 shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:from-gray-800 dark:to-gray-800/50"
        >
          <!-- Quote Icon -->
          <div class="absolute right-6 top-6 opacity-10">
            <svg class="h-16 w-16 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
            </svg>
          </div>

          <!-- Avatar and User Info -->
          <div class="mb-6 flex items-start gap-4">
            <!-- Avatar with Initials - Same as PlaceDetails -->
            <div class="relative flex-shrink-0">
              <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-teal-400 to-blue-500 text-white font-bold text-lg shadow-lg ring-4 ring-white dark:ring-gray-800 transition-transform duration-300 group-hover:scale-110">
                {{ getUserInitials(review.name) }}
              </div>
            </div>
            
            <div class="flex-1">
              <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                {{ review.name }}
              </h4>
              <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ review.location }}</span>
              </div>
              <p v-if="review.place_name" class="mt-1 text-xs text-teal-600 dark:text-teal-400 font-medium">
                 {{ review.place_name }}
              </p>
            </div>
          </div>

          <!-- Rating Stars -->
          <div class="mb-4 flex items-center gap-1">
            <svg
              v-for="i in 5"
              :key="i"
              class="h-5 w-5"
              :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ review.date }}</span>
          </div>

          <!-- Comment -->
          <p class="relative z-10 text-gray-700 leading-relaxed dark:text-gray-300">
            "{{ review.comment }}"
          </p>

          <!-- Verified Badge -->
          <div class="mt-6 flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">Verified Visitor</span>
          </div>
        </div>
      </div>

      <!-- View All Reviews Button -->
      <div v-if="hasReviews" class="mt-12 text-center">
        <a
          href="/places"
          @click="handleExploreClick"
          class="inline-block rounded-lg bg-gradient-to-r from-blue-600 to-teal-500 px-6 py-3 font-semibold text-white transition hover:from-blue-700 hover:to-teal-600 hover:shadow-lg cursor-pointer"
        >
          Explore Places and Reviews
        </a>
      </div>
    </div>
  </section>
</template>