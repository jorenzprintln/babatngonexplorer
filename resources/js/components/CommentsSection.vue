<script setup lang="ts">
import { ref } from 'vue';

const reviews = ref([
  {
    id: 1,
    name: 'Maria Santos',
    avatar: 'https://i.pravatar.cc/150?img=1',
    rating: 5,
    date: 'December 2024',
    comment: 'Absolutely breathtaking! The beaches are pristine and the locals are so welcoming. Paradise Cove Resort exceeded all our expectations.',
    location: 'Manila, Philippines'
  },
  {
    id: 2,
    name: 'John Torres',
    avatar: 'https://i.pravatar.cc/150?img=12',
    rating: 5,
    date: 'November 2024',
    comment: 'Hidden Falls is a must-visit! The trek was worth every step. The natural beauty of Babatngon is truly underrated.',
    location: 'Cebu City, Philippines'
  },
  {
    id: 3,
    name: 'Lisa Chen',
    avatar: 'https://i.pravatar.cc/150?img=5',
    rating: 4,
    date: 'October 2024',
    comment: 'Amazing snorkeling at Coral Bay! Saw so many colorful fish and corals. The water is crystal clear. Highly recommend!',
    location: 'Singapore'
  },
  {
    id: 4,
    name: 'Miguel Reyes',
    avatar: 'https://i.pravatar.cc/150?img=13',
    rating: 5,
    date: 'October 2024',
    comment: 'Perfect family vacation spot! Blue Lagoon Resort has everything - great food, friendly staff, and amazing beach access.',
    location: 'Davao City, Philippines'
  }
]);
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

      <!-- Reviews Grid -->
      <div class="grid gap-8 md:grid-cols-2">
        <div
          v-for="review in reviews"
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
            <div class="relative">
              <img
                :src="review.avatar"
                :alt="review.name"
                class="h-14 w-14 rounded-full object-cover ring-4 ring-blue-100 transition-transform duration-300 group-hover:scale-110 dark:ring-blue-900"
              />
              <!-- Online Badge -->
              <div class="absolute bottom-0 right-0 h-4 w-4 rounded-full border-2 border-white bg-green-500 dark:border-gray-800"></div>
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

    </div>
  </section>
</template>