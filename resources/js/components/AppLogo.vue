<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex items-center gap-2">
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-slate-700 to-slate-900 shadow-md">
            <AppLogoIcon class="h-5 w-5 text-white" />
        </div>
        <div class="grid flex-1 text-left text-sm">
            <span class="truncate leading-tight font-semibold">
                Babatngon Explorer
            </span>
        </div>
    </div>
</template>