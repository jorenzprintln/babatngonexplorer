<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';

// Extend Window interface for Google Maps
declare global {
  interface Window {
    google: any;
  }
}

// Get google from window
const google: any = window.google;

// Interfaces
interface Place {
  id: number;
  name: string;
  type: string;
  location: string;
  description: string;
  rating: number;
  review_count: number;
  images: string[];
  entrance_fee: string;
  opening_hours: string;
  best_for: string[];
  facilities: string[];
  latitude?: number;
  longitude?: number;
}

interface Review {
  id: number;
  user_name: string;
  rating: number;
  comment: string;
  created_at: string;
}

interface AuthUser {
  id: number;
  name: string;
}

interface Auth {
  user: AuthUser | null;
}

interface Props {
  place: Place;
  reviews: Review[];
  auth: Auth;
}

// Props from backend
const props = defineProps<Props>();

// Breadcrumbs
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Explore Places',
    href: '/places',
  },
  {
    title: props.place.name,
    href: '#',
  },
]);

// State
const isSaved = ref<boolean>(false);
const currentImageIndex = ref<number>(0);
const userRating = ref<number>(0);
const userComment = ref<string>('');
const isSubmitting = ref<boolean>(false);
const mapContainer = ref<HTMLElement | null>(null);
const map = ref<any | null>(null);
const userLocation = ref<{ lat: number; lng: number } | null>(null);
const isLoadingLocation = ref<boolean>(false);
const locationError = ref<string>('');
const directionsService = ref<any | null>(null);
const directionsRenderer = ref<any | null>(null);
const distance = ref<string>('');
const duration = ref<string>('');

// Computed
const displayReviews = computed<Review[]>(() => props.reviews);
const userInitials = computed<string>(() => {
  if (!props.auth.user) return 'G';
  return props.auth.user.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

// Place coordinates (you should get these from backend)
const placeLocation = computed<{ lat: number; lng: number }>(() => ({
  lat: props.place.latitude || 14.6760,
  lng: props.place.longitude || 121.0437
}));

// Methods
const nextImage = (): void => {
  currentImageIndex.value = (currentImageIndex.value + 1) % props.place.images.length;
};

const prevImage = (): void => {
  currentImageIndex.value = (currentImageIndex.value - 1 + props.place.images.length) % props.place.images.length;
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const getUserAvatar = (userName: string): string => {
  return userName
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const handleSubmitReview = (): void => {
  if (!props.auth.user) {
    alert('Please login to submit a review');
    return;
  }

  if (userRating.value === 0) {
    alert('Please select a rating');
    return;
  }

  if (!userComment.value.trim()) {
    alert('Please write a comment');
    return;
  }

  isSubmitting.value = true;

  router.post(
    `/places/${props.place.id}/reviews`,
    {
      rating: userRating.value,
      comment: userComment.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        userRating.value = 0;
        userComment.value = '';
        alert('Review submitted successfully!');
      },
      onError: (errors: any) => {
        console.error('Error submitting review:', errors);
        alert('Failed to submit review. Please try again.');
      },
      onFinish: () => {
        isSubmitting.value = false;
      },
    }
  );
};

const toggleSave = (): void => {
  isSaved.value = !isSaved.value;
  
  if (isSaved.value) {
    console.log('Place saved!');
  } else {
    console.log('Place unsaved!');
  }
};

// Initialize Google Map
const initMap = (): void => {
  if (!mapContainer.value) return;

  map.value = new google.maps.Map(mapContainer.value, {
    center: placeLocation.value,
    zoom: 15,
    mapTypeControl: true,
    streetViewControl: true,
    fullscreenControl: true,
  });

  // Add marker for the place
  new google.maps.Marker({
    position: placeLocation.value,
    map: map.value,
    title: props.place.name,
    icon: {
      url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
    }
  });

  // Initialize directions service
  directionsService.value = new google.maps.DirectionsService();
  directionsRenderer.value = new google.maps.DirectionsRenderer({
    map: map.value,
    suppressMarkers: false,
  });
};

// Get user's current location
const getUserLocation = (): void => {
  if (!navigator.geolocation) {
    locationError.value = 'Geolocation is not supported by your browser';
    return;
  }

  isLoadingLocation.value = true;
  locationError.value = '';

  navigator.geolocation.getCurrentPosition(
    (position: GeolocationPosition) => {
      userLocation.value = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };
      isLoadingLocation.value = false;
      
      // Show directions automatically when location is obtained
      if (userLocation.value) {
        showDirections();
      }
    },
    (error: GeolocationPositionError) => {
      isLoadingLocation.value = false;
      switch(error.code) {
        case error.PERMISSION_DENIED:
          locationError.value = 'Location access denied. Please enable location services.';
          break;
        case error.POSITION_UNAVAILABLE:
          locationError.value = 'Location information unavailable.';
          break;
        case error.TIMEOUT:
          locationError.value = 'Location request timed out.';
          break;
        default:
          locationError.value = 'An unknown error occurred.';
      }
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    }
  );
};

// Ask user and open Google Maps directions in a new tab (uses popup opened during click)
const handleGetDirectionsClick = (): void => {
  const confirmOpen = confirm('Open directions in Google Maps (will open a new tab)?');

  if (!confirmOpen) {
    // User declined — proceed with in-page location/directions flow
    getUserLocation();
    return;
  }

  // Open popup synchronously (allowed because this is in a click handler)
  const popup = window.open('', '_blank');
  if (!popup) {
    alert('Popup blocked. Please allow popups for this site to open directions.');
    return;
  }

  // Try to get geolocation, then set popup location to Google Maps directions URL.
  // If geolocation fails or times out, open directions with destination only.
  const dest = `${placeLocation.value.lat},${placeLocation.value.lng}`;
  const openMapsWithOrigin = (origin?: string) => {
    const params = new URLSearchParams();
    if (origin) params.set('origin', origin);
    params.set('destination', dest);
    params.set('travelmode', 'driving');
    popup.location.href = `https://www.google.com/maps/dir/?api=1&${params.toString()}`;
  };

  if (!navigator.geolocation) {
    openMapsWithOrigin();
    return;
  }

  isLoadingLocation.value = true;
  navigator.geolocation.getCurrentPosition(
    (position: GeolocationPosition) => {
      isLoadingLocation.value = false;
      const origin = `${position.coords.latitude},${position.coords.longitude}`;
      openMapsWithOrigin(origin);
    },
    () => {
      isLoadingLocation.value = false;
      openMapsWithOrigin();
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
};

// Fallback: open Google Maps directions in a new tab
const openExternalDirections = (): void => {
  const dest = `${placeLocation.value.lat},${placeLocation.value.lng}`;
  const params = new URLSearchParams();
  if (userLocation.value) {
    params.set('origin', `${userLocation.value.lat},${userLocation.value.lng}`);
  }
  params.set('destination', dest);
  params.set('travelmode', 'driving');
  const url = `https://www.google.com/maps/dir/?api=1&${params.toString()}`;
  window.open(url, '_blank');
};

// Show directions from user location to place
const showDirections = (): void => {
  // If we don't have a user location yet, open external directions (Google Maps can use device location)
  if (!userLocation.value) {
    openExternalDirections();
    return;
  }

  // If DirectionsService/Renderer aren't available (e.g. billing disabled), fallback
  if (!directionsService.value || !directionsRenderer.value) {
    openExternalDirections();
    return;
  }


  const request: any = {
    origin: userLocation.value,
    destination: placeLocation.value,
    travelMode: google.maps.TravelMode.DRIVING,
  };

  directionsService.value.route(request, (result: any, status: string) => {
    if (status === 'OK' && result) {
      directionsRenderer.value?.setDirections(result);
      
      // Get distance and duration
      const route = result.routes[0];
      if (route.legs[0]) {
        distance.value = route.legs[0].distance?.text || '';
        duration.value = route.legs[0].duration?.text || '';
      }

      // Add custom marker for user location
      new google.maps.Marker({
        position: userLocation.value!,
        map: map.value,
        title: 'Your Location',
        icon: {
          url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
        }
      });
    } else {
      console.warn('Directions request failed:', status, result);
      // fallback to external Google Maps directions
      openExternalDirections();
      locationError.value = 'Could not calculate route via API — opened external Google Maps.';
    }
  });
};

// Clear directions and reset map
const clearDirections = (): void => {
  if (directionsRenderer.value) {
    directionsRenderer.value.setDirections({ routes: [] } as any);
  }
  userLocation.value = null;
  distance.value = '';
  duration.value = '';
  locationError.value = '';
  
  // Re-center map on place
  if (map.value) {
    map.value.setCenter(placeLocation.value);
    map.value.setZoom(15);
  }
};

// Load Google Maps script
const loadGoogleMapsScript = (): Promise<void> => {
  return new Promise<void>((resolve, reject) => {
    if (window.google && window.google.maps) {
      resolve();
      return;
    }

    const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;
    if (!apiKey) {
      reject(new Error('Google Maps API key is not configured'));
      return;
    }

    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`;
    script.async = true;
    script.defer = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error('Failed to load Google Maps'));
    document.head.appendChild(script);
  });
};

onMounted(async () => {
  try {
    await loadGoogleMapsScript();
    initMap();
  } catch (error) {
    console.error('Error loading Google Maps:', error);
    locationError.value = 'Failed to load map. Please refresh the page.';
  }
});
</script>

<template>
  <Head :title="`${place.name} - Place Details`">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  </Head>
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
      <!-- Hero Gallery Section -->
      <div class="relative h-96 md:h-[500px] overflow-hidden bg-gray-900">
        <img
          :src="place.images[currentImageIndex]"
          :alt="place.name"
          class="w-full h-full object-cover"
        />
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
        
        <!-- Navigation Arrows -->
        <template v-if="place.images.length > 1">
          <button
            @click="prevImage"
            class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/30 p-2 backdrop-blur-sm transition hover:bg-white/50"
          >
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            @click="nextImage"
            class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/30 p-2 backdrop-blur-sm transition hover:bg-white/50"
          >
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </template>

        <!-- Image Counter -->
        <div class="absolute bottom-4 right-4 rounded-full bg-black/50 px-3 py-1 text-sm text-white backdrop-blur-sm">
          <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          {{ currentImageIndex + 1 }} / {{ place.images.length }}
        </div>

        <!-- Place Info Overlay -->
        <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
          <div class="mx-auto max-w-6xl">
            <div class="flex items-start justify-between">
              <div>
                <div class="mb-2 inline-block rounded-full bg-white/20 px-3 py-1 text-sm font-medium text-white backdrop-blur-sm">
                  {{ place.type }}
                </div>
                <h1 class="text-3xl md:text-5xl font-bold text-white mb-2">
                  {{ place.name }}
                </h1>
                <div class="flex items-center gap-2 text-white/90">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="text-lg">{{ place.location }}</span>
                </div>
              </div>
              
              <!-- Save Button -->
              <button
                @click="toggleSave"
                :class="[
                  'rounded-full p-3 backdrop-blur-sm transition-all duration-300 transform hover:scale-110',
                  isSaved
                    ? 'bg-red-500 text-white shadow-lg shadow-red-500/50'
                    : 'bg-white/20 text-white hover:bg-white/30'
                ]"
              >
                <svg 
                  class="h-6 w-6 transition-all duration-300" 
                  :class="{ 'fill-current': isSaved }" 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="mx-auto max-w-6xl px-4 md:px-8 py-8 md:py-12">
        <!-- Overview Section -->
        <div class="mb-12 rounded-2xl bg-white p-6 md:p-8 shadow-sm dark:bg-gray-800">
          <div class="mb-6 flex items-center gap-4">
            <div class="flex items-center gap-2">
              <svg
                v-for="star in 5"
                :key="star"
                class="h-6 w-6"
                :class="star <= Math.floor(place.rating) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <span class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ place.rating.toFixed(1) }}
            </span>
            <span class="text-gray-600 dark:text-gray-400">
              ({{ place.review_count }} reviews)
            </span>
          </div>
          
          <p class="text-lg leading-relaxed text-gray-700 dark:text-gray-300">
            {{ place.description }}
          </p>
        </div>

        <!-- Details & Amenities -->
        <div class="mb-12 grid gap-6 md:grid-cols-2">
          <!-- Quick Info -->
          <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">
              Quick Information
            </h2>
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="rounded-lg bg-teal-50 p-2 dark:bg-teal-900/20">
                  <svg class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Entrance Fee</p>
                  <p class="font-semibold text-gray-900 dark:text-white">{{ place.entrance_fee }}</p>
                </div>
              </div>
              
              <div class="flex items-center gap-3">
                <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-900/20">
                  <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Opening Hours</p>
                  <p class="font-semibold text-gray-900 dark:text-white">{{ place.opening_hours }}</p>
                </div>
              </div>
              
              <div class="flex items-center gap-3">
                <div class="rounded-lg bg-purple-50 p-2 dark:bg-purple-900/20">
                  <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Best For</p>
                  <div class="flex flex-wrap gap-2 mt-1">
                    <span
                      v-for="tag in place.best_for"
                      :key="tag"
                      class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                    >
                      {{ tag }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Facilities -->
          <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">
              Facilities & Amenities
            </h2>
            <div class="grid grid-cols-2 gap-3">
              <div
                v-for="facility in place.facilities"
                :key="facility"
                class="flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-700"
              >
                <div class="h-2 w-2 rounded-full bg-teal-500" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                  {{ facility }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Location with Google Maps -->
        <div class="mb-12 rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
              Location & Directions
            </h2>
            <div class="flex gap-2">
              <button
                v-if="!userLocation"
                @click="getUserLocation"
                :disabled="isLoadingLocation"
                class="flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="!isLoadingLocation" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isLoadingLocation ? 'Getting Location...' : 'Get Directions' }}
              </button>
              <button
                v-else
                @click="clearDirections"
                class="flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Clear Directions
              </button>
            </div>
          </div>

          <!-- Distance and Duration Info -->
          <div v-if="distance && duration" class="mb-4 flex gap-4">
            <div class="flex items-center gap-2 rounded-lg bg-teal-50 px-4 py-2 dark:bg-teal-900/20">
              <svg class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
              <span class="font-semibold text-gray-900 dark:text-white">{{ distance }}</span>
            </div>
            <div class="flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 dark:bg-blue-900/20">
              <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="font-semibold text-gray-900 dark:text-white">{{ duration }}</span>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="locationError" class="mb-4 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
            <div class="flex items-center gap-2">
              <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ locationError }}</p>
            </div>
          </div>

          <!-- Map Container -->
          <div ref="mapContainer" class="h-96 rounded-lg overflow-hidden mb-4"></div>
          
          <p class="flex items-start gap-2 text-gray-700 dark:text-gray-300">
            <svg class="h-5 w-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ place.location }}
          </p>
        </div>

        <!-- Reviews Section -->
        <div class="mb-12 rounded-2xl bg-white p-6 md:p-8 shadow-sm dark:bg-gray-800">
          <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
            Reviews & Comments
          </h2>
          
          <div v-if="displayReviews.length === 0" class="text-center py-8">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
            <p class="text-gray-500 dark:text-gray-400">No reviews yet. Be the first to review!</p>
          </div>

          <div v-else class="space-y-6">
            <div
              v-for="review in displayReviews"
              :key="review.id"
              class="border-b border-gray-200 pb-6 last:border-0 dark:border-gray-700"
            >
              <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-teal-400 to-blue-500 text-white font-semibold">
                  {{ getUserAvatar(review.user_name) }}
                </div>
                <div class="flex-1">
                  <div class="mb-2 flex items-center justify-between">
                    <div>
                      <h4 class="font-semibold text-gray-900 dark:text-white">
                        {{ review.user_name }}
                      </h4>
                      <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ formatDate(review.created_at) }}
                      </p>
                    </div>
                    <div class="flex items-center gap-1">
                      <svg
                        v-for="star in 5"
                        :key="star"
                        class="h-4 w-4"
                        :class="star <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                    </div>
                  </div>
                  <p class="text-gray-700 dark:text-gray-300">
                    {{ review.comment }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Review Form -->
        <div class="rounded-2xl bg-white p-6 md:p-8 shadow-sm dark:bg-gray-800">
          <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
            Leave a Review
          </h2>

          <div v-if="!auth.user" class="text-center py-8">
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              Please login to leave a review
            </p>
            <a
              href="/login"
              class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-6 py-3 font-semibold text-white transition hover:bg-teal-700"
            >
              Login to Review
            </a>
          </div>

          <div v-else class="space-y-6">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Your Rating
              </label>
              <div class="flex gap-2">
                <button
                  v-for="star in 5"
                  :key="star"
                  @click="userRating = star"
                  type="button"
                  class="transition hover:scale-110"
                  :disabled="isSubmitting"
                >
                  <svg
                    class="h-8 w-8"
                    :class="star <= userRating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </button>
              </div>
            </div>
            
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Your Comment
              </label>
              <textarea
                v-model="userComment"
                rows="4"
                class="w-full rounded-lg border border-gray-300 p-3 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Share your experience..."
                :disabled="isSubmitting"
              />
            </div>
            
            <button
              @click="handleSubmitReview"
              :disabled="isSubmitting"
              class="flex items-center gap-2 rounded-lg bg-teal-600 px-6 py-3 font-semibold text-white transition hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="!isSubmitting" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
              </svg>
              <svg v-else class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isSubmitting ? 'Submitting...' : 'Submit Review' }}
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