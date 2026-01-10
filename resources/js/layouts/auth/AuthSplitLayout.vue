<script setup lang="ts">
import { home } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();

const resorts = [
  {
    id: 1,
    name: 'Tulaan Beach Resort',
    location: 'Barangay Bacong, Babatngon',
    image: 'https://cdns.app/wgsdkw2F/assets/image/big/777a27a17c917b8b4d5a5d712d6a7145_1660367236.jpg',
  },
  {
    id: 2,
    name: 'Balay ni Tatay',
    location: 'Barangay Villa Magsaysay, Babatngon',
    image: 'https://www.syramay.com/wp-content/uploads/2022/01/balay-ni-tatay-resort-1-768x434.jpg',
  },
  {
    id: 3,
    name: 'Busay Falls',
    location: 'Barangay District III, Babatngon',
    image: 'https://tse1.mm.bing.net/th/id/OIP.ikTtPHIwpDxg8XAXNcGTbgHaEK?pid=Api&P=0&h=220',
  },
  {
    id: 4,
    name: 'Aplaya Beach',
    location: 'Fishport, Babatngon',
    image: 'https://i.ytimg.com/vi/Ypc1qBH3zJw/maxresdefault.jpg?sqp=-oaymwEmCIAKENAF8quKqQMa8AEB-AH-CYAC0AWKAgwIABABGFogXyhlMA8=&rs=AOn4CLAfBMnarUhnFz-D7ClGvCrL9aZMlg',
  },
  {
    id: 5,
    name: 'Tulaan Beach',
    location: 'Barangay Bacong, Babatngon',
    image: 'https://iamtravelinglight.com/wp-content/uploads/2012/05/326-tulaans-shore.jpg',
  },
  {
    id: 6,
    name: 'Busay Resort',
    location: 'Barangay District III, Babatngon',
    image: 'https://media-cdn.tripadvisor.com/media/photo-s/03/02/08/d5/busay-falls.jpg',
  }
];

const currentSlide = ref(0);
let slideInterval: number;

onMounted(() => {
  slideInterval = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % resorts.length;
  }, 4000); // Change slide every 4 seconds
});

onUnmounted(() => {
  if (slideInterval) {
    clearInterval(slideInterval);
  }
});
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <div
            class="relative hidden h-full flex-col bg-muted p-10 text-white lg:flex dark:border-r overflow-hidden"
        >
            <!-- Slideshow Background -->
            <div class="absolute inset-0">
                <div
                    v-for="(resort, index) in resorts"
                    :key="resort.id"
                    class="absolute inset-0 transition-opacity duration-1000"
                    :class="currentSlide === index ? 'opacity-100' : 'opacity-0'"
                >
                    <img
                        :src="resort.image"
                        :alt="resort.name"
                        class="h-full w-full object-cover"
                    />
                    <!-- Dark overlay -->
                    <div class="absolute inset-0 bg-black/50"></div>
                </div>
            </div>

            <!-- Logo and Name -->
            <Link
                :href="home()"
                class="relative z-20 flex items-center text-lg font-medium"
            >
                <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-slate-700 to-slate-900 shadow-md">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-xl font-bold">Babatngon Explorer</span>
            </Link>

            <!-- Resort Info (Bottom) -->
            <div class="relative z-20 mt-auto">
                <div
                    v-for="(resort, index) in resorts"
                    :key="resort.id"
                    class="transition-opacity duration-1000"
                    :class="currentSlide === index ? 'opacity-100' : 'opacity-0 absolute'"
                >
                    <h3 class="text-2xl font-bold mb-2">{{ resort.name }}</h3>
                    <div class="flex items-start gap-2 text-sm text-neutral-200">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ resort.location }}</span>
                    </div>
                </div>

                <!-- Slide indicators -->
                <div class="flex gap-2 mt-6">
                    <button
                        v-for="(resort, index) in resorts"
                        :key="resort.id"
                        @click="currentSlide = index"
                        class="h-1.5 rounded-full transition-all duration-300"
                        :class="currentSlide === index ? 'bg-white w-8' : 'bg-white/50 w-1.5'"
                    ></button>
                </div>
            </div>
        </div>
        <div class="lg:p-8">
            <div
                class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]"
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>