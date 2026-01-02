<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { Link } from '@inertiajs/vue3';
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

// Filter and search state
const searchQuery = ref('');
const selectedType = ref('All');
const savedPlaces = ref<number[]>([]); // Store IDs of saved places

// All places data
const places = ref([
  {
    id: 1,
    name: 'Tulaan Beach Resort',
    description: 'Clean environment with rooms for overnight stay and a swimming pool.',
    location: 'Barangay Bacong, Babatngon',
    image: 'https://cdns.app/wgsdkw2F/assets/image/big/777a27a17c917b8b4d5a5d712d6a7145_1660367236.jpg',
    rating: 4.8,
    type: 'Resort',
    reviewCount: 127
  },
  {
    id: 2,
    name: 'Balay ni Tatay',
    description: 'Mountain-side resort with a pool, mini zoo, and hiking trails.',
    location: 'Barangay Villa Magsaysay, Babatngon',
    image: 'https://www.syramay.com/wp-content/uploads/2022/01/balay-ni-tatay-resort-1-768x434.jpg',
    rating: 4.6,
    type: 'Resort',
    reviewCount: 89
  },
  {
    id: 3,
    name: 'Busay Falls',
    description: 'Fresh waterfall flowing directly from the mountain, ideal for nature lovers.',
    location: 'Barangay District III, Babatngon',
    image: 'https://tse1.mm.bing.net/th/id/OIP.ikTtPHIwpDxg8XAXNcGTbgHaEK?pid=Api&P=0&h=220',
    rating: 4.7,
    type: 'Falls',
    reviewCount: 156
  },
  {
    id: 4,
    name: 'Aplaya Beach',
    description: 'Rocky sea area near the highway, perfect for quick visits and photos.',
    location: 'Fishport, Babatngon',
    image: 'https://i.ytimg.com/vi/Ypc1qBH3zJw/maxresdefault.jpg?sqp=-oaymwEmCIAKENAF8quKqQMa8AEB-AH-CYAC0AWKAgwIABABGFogXyhlMA8=&rs=AOn4CLAfBMnarUhnFz-D7ClGvCrL9aZMlg',
    rating: 4.9,
    type: 'Beach',
    reviewCount: 203
  },
  {
    id: 5,
    name: 'Tulaan Beach',
    description: 'Part of Tulaan Resort, with clear seawater and a relaxing beach environment.',
    location: 'Barangay Bacong, Babatngon',
    image: 'https://iamtravelinglight.com/wp-content/uploads/2012/05/326-tulaans-shore.jpg',
    rating: 4.8,
    type: 'Beach',
    reviewCount: 142
  },
  {
    id: 6,
    name: 'Busay Resort',
    description: 'Resort with a pool, waterfall, and slide for fun activities.',
    location: 'Barangay District III, Babatngon',
    image: 'https://scontent.fmnl4-8.fna.fbcdn.net/v/t39.30808-6/494899259_680194271484041_1610373663631108083_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=833d8&_nc_eui2=AeGGalUxo0dz0HhfhvE5QgVLhjT2qepivFCGNPap6mK8UGM7Jo1hlDX5IvvZ_YIdEgSy-FgisMhaOwsrxjebiMPy&_nc_ohc=i8exqn1kmFIQ7kNvwHe8pUI&_nc_oc=AdnPul01IyAOooK7hUJHqz9y3XBlvFdYeVuDJ7WPhTR9iWuJBbQLKlzhz8odUYqtC7s&_nc_zt=23&_nc_ht=scontent.fmnl4-8.fna&_nc_gid=R4r_7lfD3tPPn1AWGnXSgA&oh=00_AfoC7kaRsKuqjzOUbxDe2cXl9fplu92rmPJUwGIKavjU1Q&oe=695C0FC9',
    rating: 4.5,
    type: 'Resort',
    reviewCount: 67
  }
]);

// Get unique types for filter
const types = computed(() => {
  const uniqueTypes = ['All', ...new Set(places.value.map(p => p.type))];
  return uniqueTypes;
});

// Filtered places based on search and type
const filteredPlaces = computed(() => {
  return places.value.filter(place => {
    const matchesSearch = place.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         place.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         place.location.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchesType = selectedType.value === 'All' || place.type === selectedType.value;
    
    return matchesSearch && matchesType;
  });
});

// Toggle save/bookmark
const toggleSave = (placeId: number) => {
  const index = savedPlaces.value.indexOf(placeId);
  if (index > -1) {
    savedPlaces.value.splice(index, 1);
  } else {
    savedPlaces.value.push(placeId);
  }
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
                @click.stop="toggleSave(place.id)"
                :class="[
                  'absolute right-4 top-4 rounded-full p-2 shadow-md backdrop-blur-sm transition-all duration-300',
                  isSaved(place.id)
                    ? 'bg-red-500 text-white scale-110'
                    : 'bg-white/95 text-gray-600 hover:bg-white hover:scale-110 dark:bg-gray-900/95 dark:text-gray-300'
                ]"
              >
                <svg
                  class="h-5 w-5"
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
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ place.rating }}</span>
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

        <!-- Saved Places Counter (Optional) -->
        <div v-if="savedPlaces.length > 0" class="mt-8 rounded-2xl bg-teal-50 p-6 dark:bg-teal-900/20">
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
            <button class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700">
              View Saved
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
* {
    font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style>