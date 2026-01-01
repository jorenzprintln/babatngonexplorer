<script setup lang="ts">
import { ref } from 'vue';

const resorts = ref([
  {
    id: 1,
    name: 'Tulaan Beach Resort',
    description: 'Clean environment with rooms for overnight stay and a swimming pool.',
    location: 'Barangay Bacong, Babatngon',
    image: 'https://cdns.app/wgsdkw2F/assets/image/big/777a27a17c917b8b4d5a5d712d6a7145_1660367236.jpg',
    rating: 4.8,
    type: 'Resort'
  },
  {
    id: 2,
    name: 'Balay ni Tatay',
    description: 'Mountain-side resort with a pool, mini zoo, and hiking trails.',
    location: 'Barangay Villa Magsaysay, Babatngon',
    image: 'https://www.syramay.com/wp-content/uploads/2022/01/balay-ni-tatay-resort-1-768x434.jpg',
    rating: 4.6,
    type: 'Resort'
  },
  {
    id: 3,
    name: 'Busay Falls',
    description: 'Fresh waterfall flowing directly from the mountain, ideal for nature lovers.',
    location: 'Barangay District III, Babatngon',
    image: 'https://tse1.mm.bing.net/th/id/OIP.ikTtPHIwpDxg8XAXNcGTbgHaEK?pid=Api&P=0&h=220',
    rating: 4.7,
    type: 'Falls'
  },
  {
    id: 4,
    name: 'Aplaya Beach',
    description: 'Rocky sea area near the highway, perfect for quick visits and photos.',
    location: 'Fishport, Babatngon',
    image: 'https://i.ytimg.com/vi/Ypc1qBH3zJw/maxresdefault.jpg?sqp=-oaymwEmCIAKENAF8quKqQMa8AEB-AH-CYAC0AWKAgwIABABGFogXyhlMA8=&rs=AOn4CLAfBMnarUhnFz-D7ClGvCrL9aZMlg',
    rating: 4.9,
    type: 'Beach'
  },
  {
    id: 5,
    name: 'Tulaan Beach',
    description: 'Part of Tulaan Resort, with clear seawater and a relaxing beach environment.',
    location: 'Barangay Bacong, Babatngon',
    image: 'https://iamtravelinglight.com/wp-content/uploads/2012/05/326-tulaans-shore.jpg',
    rating: 4.8,
    type: 'Beach'
  },
  {
    id: 6,
    name: 'Busay Resort',
    description: 'Resort with a pool, waterfall, and slide for fun activities.',
    location: 'Barangay District III, Babatngon',
    image: 'https://scontent.fmnl4-8.fna.fbcdn.net/v/t39.30808-6/494899259_680194271484041_1610373663631108083_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=833d8&_nc_eui2=AeGGalUxo0dz0HhfhvE5QgVLhjT2qepivFCGNPap6mK8UGM7Jo1hlDX5IvvZ_YIdEgSy-FgisMhaOwsrxjebiMPy&_nc_ohc=i8exqn1kmFIQ7kNvwHe8pUI&_nc_oc=AdnPul01IyAOooK7hUJHqz9y3XBlvFdYeVuDJ7WPhTR9iWuJBbQLKlzhz8odUYqtC7s&_nc_zt=23&_nc_ht=scontent.fmnl4-8.fna&_nc_gid=R4r_7lfD3tPPn1AWGnXSgA&oh=00_AfoC7kaRsKuqjzOUbxDe2cXl9fplu92rmPJUwGIKavjU1Q&oe=695C0FC9',
    rating: 4.5,
    type: 'Resort'
  }
]);
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

      <!-- Cards Grid -->
      <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
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
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ resort.rating }}</span>
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
            <button class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:from-blue-700 hover:to-teal-600 hover:shadow-lg">
              Learn More
              <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>