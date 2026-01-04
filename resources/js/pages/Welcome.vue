<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';
import HeroSection from '@/components/HeroSection.vue';
import ResortCards from '@/components/ResortCards.vue';
import CommentsSection from '@/components/CommentsSection.vue';
import Footer from '@/components/Footer.vue';
import { computed } from 'vue';

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

interface Review {
    id: number;
    name: string;
    avatar: string;
    rating: number;
    date: string;
    comment: string;
    location: string;
    place_name?: string;
}

interface Props {
    canRegister: boolean;
    places: Place[];
    reviews: Review[];
}

const props = withDefaults(defineProps<Props>(), {
    canRegister: true,
    places: () => [],
    reviews: () => [],
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

const reloadPage = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => {
        window.location.reload();
    }, 300);
};
</script>

<template>
    <Head title="Babatngon Explorer - Discover Hidden Gems">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>
    
    <div class="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-white">
        <!-- Navigation Header -->
        <header class="fixed top-0 z-50 w-full bg-white/80 backdrop-blur-md shadow-sm dark:bg-gray-900/80">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <!-- Logo -->
                <div @click="reloadPage" class="flex items-center gap-3 cursor-pointer">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-slate-700 to-slate-900 shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-slate-800 dark:text-slate-100">
                        Babatngon Explorer
                    </span>
                </div>

                <!-- Auth Buttons -->
                <div class="flex items-center gap-3">
                    <Link
                        v-if="isAuthenticated"
                        :href="dashboard()"
                        class="rounded-full bg-slate-800 px-6 py-2 text-sm font-semibold text-white transition-all hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-full px-6 py-2 text-sm font-semibold text-slate-700 transition-all hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-full bg-slate-800 px-6 py-2 text-sm font-semibold text-white shadow-md transition-all hover:bg-slate-700 hover:shadow-lg dark:bg-blue-700 dark:hover:bg-teal-600"
                        >
                            Register
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main>
            <HeroSection />
            <ResortCards :places="places" />
            <CommentsSection :reviews="reviews" />
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<style scoped>
* {
    font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style>