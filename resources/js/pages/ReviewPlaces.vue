<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'My Reviews',
        href: '/review',
    },
];

interface Review {
  id: number;
  place_id: number;
  place_name: string;
  place_image: string;
  place_type: string;
  rating: number;
  comment: string;
  photos: string[];
  created_at: string;
  updated_at: string;
  reports_count: number;
}

interface Props {
  reviews: Review[];
}

const props = defineProps<Props>();

// State
const sortBy = ref('recent');
const showDeleteModal = ref(false);
const reviewToDelete = ref<Review | null>(null);
const showPhotoViewer = ref(false);
const viewerPhotos = ref<string[]>([]);
const viewerCurrentIndex = ref(0);

// Computed
const sortedReviews = computed(() => {
  const reviews = [...props.reviews];
  
  if (sortBy.value === 'recent') {
    return reviews.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
  } else if (sortBy.value === 'rating-high') {
    return reviews.sort((a, b) => b.rating - a.rating);
  } else if (sortBy.value === 'rating-low') {
    return reviews.sort((a, b) => a.rating - b.rating);
  } else if (sortBy.value === 'place') {
    return reviews.sort((a, b) => a.place_name.localeCompare(b.place_name));
  }
  
  return reviews;
});

const averageRating = computed(() => {
  if (props.reviews.length === 0) return 0;
  const sum = props.reviews.reduce((acc, review) => acc + review.rating, 0);
  return (sum / props.reviews.length).toFixed(1);
});

const totalPhotos = computed(() => {
  return props.reviews.reduce((acc, review) => acc + (review.photos?.length || 0), 0);
});

// Methods
const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const openDeleteModal = (review: Review): void => {
  reviewToDelete.value = review;
  showDeleteModal.value = true;
};

const closeDeleteModal = (): void => {
  showDeleteModal.value = false;
  reviewToDelete.value = null;
};

const confirmDelete = (): void => {
  if (!reviewToDelete.value) return;
  
  router.delete(`/reviews/${reviewToDelete.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
    },
    onError: (errors: any) => {
      alert('Failed to delete review. Please try again.');
    }
  });
};

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
</script>

<template>
    <Head title="My Reviews - Babatngon Explorer">
      <link rel="preconnect" href="https://fonts.googleapis.com" />
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950">
            <div class="mx-auto max-w-7xl px-4 py-8 md:px-8 md:py-12">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="mb-2 text-4xl font-bold text-gray-900 md:text-5xl dark:text-white">
                        My
                        <span class="ml-2 bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">
                            Reviews
                        </span>
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400">
                        Your contributions and experiences • {{ reviews.length }} {{ reviews.length === 1 ? 'review' : 'reviews' }}
                    </p>
                </div>

                <!-- Stats Cards -->
                <div v-if="reviews.length > 0" class="mb-8 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">Total Reviews</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ reviews.length }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-blue-500 to-teal-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
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
                                <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">Photos Shared</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ totalPhotos }}</p>
                            </div>
                            <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sort Options -->
                <div v-if="reviews.length > 0" class="mb-6 flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</span>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="option in [
                                { value: 'recent', label: 'Most Recent' },
                                { value: 'rating-high', label: 'Highest Rating' },
                                { value: 'rating-low', label: 'Lowest Rating' },
                                { value: 'place', label: 'Place Name' }
                            ]"
                            :key="option.value"
                            @click="sortBy = option.value"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-medium transition',
                                sortBy === option.value
                                    ? 'bg-teal-600 text-white shadow-md'
                                    : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                            ]"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <!-- Reviews List -->
                <div v-if="sortedReviews.length > 0" class="space-y-6">
                    <div
                        v-for="review in sortedReviews"
                        :key="review.id"
                        class="group overflow-hidden rounded-2xl bg-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:bg-gray-800"
                    >
                        <div class="flex flex-col md:flex-row">
                            <!-- Place Image -->
                            <Link
                                :href="`/places/${review.place_id}`"
                                class="relative h-48 md:h-auto md:w-64 overflow-hidden"
                            >
                                <img
                                    :src="review.place_image"
                                    :alt="review.place_name"
                                    class="h-full w-full object-cover transition-transform duration-300"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="mb-2 inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                        {{ review.place_type }}
                                    </div>
                                    <h3 class="text-lg font-bold text-white">{{ review.place_name }}</h3>
                                </div>
                            </Link>

                            <!-- Review Content -->
                            <div class="flex-1 p-6">
                                <div class="mb-4 flex items-start justify-between">
                                    <div>
                                        <div class="mb-2 flex items-center gap-2">
                                            <div class="flex items-center">
                                                <svg
                                                    v-for="i in 5"
                                                    :key="i"
                                                    class="h-5 w-5"
                                                    :class="i <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ review.rating.toFixed(1) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Reviewed on {{ formatDate(review.created_at) }}
                                        </p>
                                        <p v-if="review.reports_count > 0" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            ⚠️ This review has {{ review.reports_count }} {{ review.reports_count === 1 ? 'report' : 'reports' }}
                                        </p>
                                    </div>
                                    <button
                                        @click="openDeleteModal(review)"
                                        class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                        title="Delete review"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <p class="mb-4 text-gray-700 dark:text-gray-300">
                                    {{ review.comment }}
                                </p>

                                <!-- Review Photos -->
                                <div v-if="review.photos && review.photos.length > 0" class="mb-4 flex flex-wrap gap-2">
                                    <div
                                        v-for="(photo, index) in review.photos"
                                        :key="index"
                                        class="relative h-20 w-20 cursor-pointer overflow-hidden rounded-lg"
                                        @click="openPhotoViewer(review.photos, index)"
                                    >
                                        <img
                                            :src="photo"
                                            :alt="`Review photo ${index + 1}`"
                                            class="h-full w-full object-cover transition-none"
                                        />
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3">
                                    <Link
                                        :href="`/places/${review.place_id}`"
                                        class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:from-teal-700 hover:to-blue-700"
                                    >
                                        View Place
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-2xl bg-white p-16 text-center shadow-sm dark:bg-gray-800">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">No Reviews Yet</h3>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">
                        Start exploring places and share your experiences
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

                <!-- Tips -->
                <div v-if="reviews.length > 0" class="mt-8 rounded-2xl border border-teal-200 bg-gradient-to-r from-teal-50 to-blue-50 p-6 dark:border-teal-800 dark:from-teal-900/20 dark:to-blue-900/20">
                    <div class="flex items-start gap-4">
                        <div class="rounded-full bg-teal-600 p-2">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="mb-2 font-semibold text-gray-900 dark:text-white">Your Reviews Matter</h4>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Your honest feedback helps other travelers discover amazing places and helps businesses improve their services. Thank you for contributing to our community!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="showDeleteModal && reviewToDelete"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="closeDeleteModal"
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
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete Review?</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">This action cannot be undone</p>
                        </div>
                    </div>
                    <p class="mb-6 text-gray-700 dark:text-gray-300">
                        Are you sure you want to delete your review for <span class="font-semibold">{{ reviewToDelete.place_name }}</span>?
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="closeDeleteModal"
                            type="button"
                            class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmDelete"
                            type="button"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                            Delete
                        </button>
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