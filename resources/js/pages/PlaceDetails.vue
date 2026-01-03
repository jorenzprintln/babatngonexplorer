<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';

declare global {
  interface Window {
    google: any;
  }
}

const google: any = window.google;

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
  user_id: number;
  user_name: string;
  rating: number;
  comment: string;
  photos: string[];
  created_at: string;
  reports_count: number;
  has_reported: boolean;
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

const props = defineProps<Props>();

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

// Photo upload state
const selectedPhotos = ref<File[]>([]);
const photoPreviewUrls = ref<string[]>([]);
const photoInput = ref<HTMLInputElement | null>(null);

// Report modal state
const showReportModal = ref<boolean>(false);
const reportingReviewId = ref<number | null>(null);
const reportReason = ref<string>('');
const reportDescription = ref<string>('');
const isSubmittingReport = ref<boolean>(false);

// Photo viewer state
const showPhotoViewer = ref<boolean>(false);
const viewerPhotos = ref<string[]>([]);
const viewerCurrentIndex = ref<number>(0);

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

const placeLocation = computed<{ lat: number; lng: number }>(() => ({
  lat: props.place.latitude || 14.6760,
  lng: props.place.longitude || 121.0437
}));

// Image Gallery Methods
const nextImage = (): void => {
  currentImageIndex.value = (currentImageIndex.value + 1) % props.place.images.length;
};

const prevImage = (): void => {
  currentImageIndex.value = (currentImageIndex.value - 1 + props.place.images.length) % props.place.images.length;
};

// Photo Upload Methods
const handlePhotoSelect = (event: Event): void => {
  const target = event.target as HTMLInputElement;
  const files = target.files;
  
  if (!files) return;
  
  const newFiles = Array.from(files).slice(0, 5 - selectedPhotos.value.length);
  
  newFiles.forEach(file => {
    if (file.size > 5 * 1024 * 1024) {
      alert(`${file.name} is too large. Maximum size is 5MB.`);
      return;
    }
    
    selectedPhotos.value.push(file);
    
    const reader = new FileReader();
    reader.onload = (e) => {
      if (e.target?.result) {
        photoPreviewUrls.value.push(e.target.result as string);
      }
    };
    reader.readAsDataURL(file);
  });
  
  // Reset input
  if (target) target.value = '';
};

const removePhoto = (index: number): void => {
  selectedPhotos.value.splice(index, 1);
  photoPreviewUrls.value.splice(index, 1);
};

// Photo Viewer Methods
const openPhotoViewer = (photos: string[], index: number): void => {
  viewerPhotos.value = photos;
  viewerCurrentIndex.value = index;
  showPhotoViewer.value = true;
};

const closePhotoViewer = (): void => {
  showPhotoViewer.value = false;
  viewerPhotos.value = [];
  viewerCurrentIndex.value = 0;
};

const nextViewerPhoto = (): void => {
  viewerCurrentIndex.value = (viewerCurrentIndex.value + 1) % viewerPhotos.value.length;
};

const prevViewerPhoto = (): void => {
  viewerCurrentIndex.value = (viewerCurrentIndex.value - 1 + viewerPhotos.value.length) % viewerPhotos.value.length;
};

// Report Methods
const openReportModal = (reviewId: number): void => {
  reportingReviewId.value = reviewId;
  showReportModal.value = true;
  reportReason.value = '';
  reportDescription.value = '';
};

const closeReportModal = (): void => {
  showReportModal.value = false;
  reportingReviewId.value = null;
  reportReason.value = '';
  reportDescription.value = '';
};

const submitReport = (): void => {
  if (!reportReason.value) {
    alert('Please select a reason for reporting');
    return;
  }
  
  isSubmittingReport.value = true;
  
  router.post(
    `/reviews/${reportingReviewId.value}/report`,
    {
      reason: reportReason.value,
      description: reportDescription.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        closeReportModal();
        alert('Thank you for your report. We will review it shortly.');
        router.reload({ only: ['reviews'] });
      },
      onError: (errors: any) => {
        if (errors.report) {
          alert(errors.report);
        } else {
          alert('Failed to submit report. Please try again.');
        }
      },
      onFinish: () => {
        isSubmittingReport.value = false;
      },
    }
  );
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

  if (userComment.value.trim().length < 10) {
    alert('Comment must be at least 10 characters long');
    return;
  }

  isSubmitting.value = true;

  const formData = new FormData();
  formData.append('rating', userRating.value.toString());
  formData.append('comment', userComment.value);
  
  selectedPhotos.value.forEach((photo, index) => {
    formData.append(`photos[${index}]`, photo);
  });

  router.post(
    `/places/${props.place.id}/reviews`,
    formData,
    {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        userRating.value = 0;
        userComment.value = '';
        selectedPhotos.value = [];
        photoPreviewUrls.value = [];
        alert('Thank you for sharing your experience! Your review helps others discover this amazing place.');
        router.reload({ only: ['place', 'reviews'] });
      },
      onError: (errors: any) => {
        console.error('Error submitting review:', errors);
        if (errors.review) {
          alert(errors.review);
        } else {
          alert('Failed to submit review. Please check your input and try again.');
        }
      },
      onFinish: () => {
        isSubmitting.value = false;
      },
    }
  );
};

// Map Methods
const initMap = (): void => {
  if (!mapContainer.value) return;

  map.value = new google.maps.Map(mapContainer.value, {
    center: placeLocation.value,
    zoom: 15,
    mapTypeControl: true,
    streetViewControl: true,
    fullscreenControl: true,
  });

  new google.maps.Marker({
    position: placeLocation.value,
    map: map.value,
    title: props.place.name,
    icon: {
      url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
    }
  });

  directionsService.value = new google.maps.DirectionsService();
  directionsRenderer.value = new google.maps.DirectionsRenderer({
    map: map.value,
    suppressMarkers: false,
  });
};

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

const showDirections = (): void => {
  if (!userLocation.value) {
    openExternalDirections();
    return;
  }

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
      
      const route = result.routes[0];
      if (route.legs[0]) {
        distance.value = route.legs[0].distance?.text || '';
        duration.value = route.legs[0].duration?.text || '';
      }

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
      openExternalDirections();
      locationError.value = 'Could not calculate route via API — opened external Google Maps.';
    }
  });
};

const clearDirections = (): void => {
  if (directionsRenderer.value) {
    directionsRenderer.value.setDirections({ routes: [] } as any);
  }
  userLocation.value = null;
  distance.value = '';
  duration.value = '';
  locationError.value = '';
  
  if (map.value) {
    map.value.setCenter(placeLocation.value);
    map.value.setZoom(15);
  }
};

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
        
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
        
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

        <div class="absolute bottom-4 right-4 rounded-full bg-black/50 px-3 py-1 text-sm text-white backdrop-blur-sm">
          <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          {{ currentImageIndex + 1 }} / {{ place.images.length }}
        </div>

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
                class="flex items-center gap-2
                rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Clear Directions
              </button>
            </div>
          </div>

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

          <div v-if="locationError" class="mb-4 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
            <div class="flex items-center gap-2">
              <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ locationError }}</p>
            </div>
          </div>

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
                    <div class="flex items-center gap-2">
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
                      
                      <!-- Report Button -->
                      <button
                        v-if="auth.user && auth.user.id !== review.user_id && !review.has_reported"
                        @click="openReportModal(review.id)"
                        class="flex items-center gap-1 text-sm text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition"
                        title="Report this review"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                        <span>Report</span>
                      </button>
                      
                      <span v-if="review.has_reported" class="text-xs text-gray-400 dark:text-gray-500">
                        Reported
                      </span>
                    </div>
                  </div>
                  <p class="text-gray-700 dark:text-gray-300 mb-3">
                    {{ review.comment }}
                  </p>
                  
                  <!-- Review Photos -->
                  <div v-if="review.photos && review.photos.length > 0" class="flex flex-wrap gap-2">
                    <div
                      v-for="(photo, index) in review.photos"
                      :key="index"
                      class="relative h-24 w-24 cursor-pointer overflow-hidden rounded-lg"
                      @click="openPhotoViewer(review.photos, index)"
                    >
                      <img
                        :src="photo"
                        :alt="`Review photo ${index + 1}`"
                        class="h-full w-full object-cover transition hover:scale-110"
                      />
                    </div>
                  </div>
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
            
            <!-- Photo Upload -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Add Photos (Optional - Max 5 photos, 5MB each)
              </label>
              
              <input
                ref="photoInput"
                type="file"
                accept="image/jpeg,image/jpg,image/png"
                multiple
                @change="handlePhotoSelect"
                class="hidden"
              />
              
              <div class="flex flex-wrap gap-3">
                <!-- Photo Previews -->
                <div
                  v-for="(preview, index) in photoPreviewUrls"
                  :key="index"
                  class="relative h-24 w-24 overflow-hidden rounded-lg"
                >
                  <img
                    :src="preview"
                    alt="Preview"
                    class="h-full w-full object-cover"
                  />
                  <button
                    @click="removePhoto(index)"
                    type="button"
                    class="absolute top-1 right-1 rounded-full bg-red-500 p-1 text-white hover:bg-red-600 transition"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                
                <!-- Add Photo Button -->
                <button
                  v-if="selectedPhotos.length < 5"
                  @click="photoInput?.click()"
                  type="button"
                  class="flex h-24 w-24 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 hover:border-teal-500 transition dark:border-gray-600"
                  :disabled="isSubmitting"
                >
                  <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
              
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ selectedPhotos.length }} / 5 photos selected
              </p>
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
    
    <!-- Report Modal -->
    <Teleport to="body">
      <div
        v-if="showReportModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="closeReportModal"
      >
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
              Report Review
            </h3>
            <button
              @click="closeReportModal"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="space-y-4">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Reason for reporting
              </label>
              <select
                v-model="reportReason"
                class="w-full rounded-lg border border-gray-300 p-3 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Select a reason</option>
                <option value="spam">Spam</option>
                <option value="inappropriate">Inappropriate content</option>
                <option value="offensive">Offensive language</option>
                <option value="misleading">Misleading information</option>
                <option value="other">Other</option>
              </select>
            </div>
            
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Additional details (optional)
              </label>
              <textarea
                v-model="reportDescription"
                rows="3"
                class="w-full rounded-lg border border-gray-300 p-3 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Provide more context..."
                maxlength="500"
              />
            </div>
            
            <div class="flex gap-3">
              <button
                @click="closeReportModal"
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
              >
                Cancel
              </button>
              <button
                @click="submitReport"
                :disabled="isSubmittingReport || !reportReason"
                class="flex-1 rounded-lg bg-red-600 px-4 py-2 font-semibold text-white transition hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ isSubmittingReport ? 'Submitting...' : 'Submit Report' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
    
    <!-- Photo Viewer Modal -->
    <Teleport to="body">
      <div
        v-if="showPhotoViewer"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
        @click.self="closePhotoViewer"
      >
        <button
          @click="closePhotoViewer"
          class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white backdrop-blur-sm transition hover:bg-white/20"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        
        <button
          v-if="viewerPhotos.length > 1"
          @click="prevViewerPhoto"
          class="absolute left-4 rounded-full bg-white/10 p-2 text-white backdrop-blur-sm transition hover:bg-white/20"
        >
          <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        
        <img
          :src="viewerPhotos[viewerCurrentIndex]"
          alt="Full size photo"
          class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain"
        />
        
        <button
          v-if="viewerPhotos.length > 1"
          @click="nextViewerPhoto"
          class="absolute right-4 rounded-full bg-white/10 p-2 text-white backdrop-blur-sm transition hover:bg-white/20"
        >
          <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        
        <div v-if="viewerPhotos.length > 1" class="absolute bottom-4 rounded-full bg-black/50 px-4 py-2 text-white backdrop-blur-sm">
          {{ viewerCurrentIndex + 1 }} / {{ viewerPhotos.length }}
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