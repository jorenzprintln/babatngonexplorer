<script setup lang="ts">
const scrollToResorts = () => {
  const resortsSection = document.getElementById('resorts');
  if (resortsSection) {
    resortsSection.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<template>
  <section class="relative h-screen min-h-[600px] w-full overflow-hidden">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0">
      <img
        src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1920&q=80"
        alt="Babatngon Beach"
        class="h-full w-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-b from-blue-900/40 via-blue-800/30 to-teal-900/50"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 flex h-full items-center justify-center px-6">
      <div class="max-w-4xl text-center">
        <!-- Animated Title -->
          <h1 class="mb-6 animate-fade-in-up text-5xl font-bold text-white md:text-6xl lg:text-7xl">
          Discover Babatngon's
          <span class="block bg-gradient-to-r from-blue-200 via-blue-300 to-sky-300 bg-clip-text text-transparent">
            Hidden Gems
          </span>
        </h1>
        
        <!-- Subtitle -->
        <p class="mb-8 animate-fade-in-up text-lg text-white/90 md:text-xl lg:text-2xl" style="animation-delay: 0.2s">
          Experience pristine beaches, stunning resorts, and unforgettable adventures in Leyte's best-kept secret
        </p>

        <!-- CTA Button -->
           <button
          @click="scrollToResorts"
          class="animate-fade-in-up group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-gradient-to-r from-blue-600 to-teal px-8 py-4 text-lg font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:bg-blue-700 hover:shadow-2xl"
          style="animation-delay: 0.4s"
        >
          <span class="relative z-10">Explore Resorts & Beaches</span>
        </button>

        <!-- Scroll Indicator -->
        <div class="mt-16 animate-bounce">
          <svg class="mx-auto h-8 w-8 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Decorative Wave -->
    <div class="absolute bottom-0 left-0 right-0">
      <svg class="w-full" viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
          fill="#FDFDFC"
          class="dark:hidden"
        />
        <path
          d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
          fill="#0a0a0a"
          class="hidden dark:block"
        />
      </svg>
    </div>
  </section>
</template>

<style scoped>
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fade-in-up 0.8s ease-out forwards;
  opacity: 0;
}
</style>