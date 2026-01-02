<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Saved Places',
        href: '/save',
    },
];
</script>

<template>
    <Head title="Saved Places" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 p-4 md:p-8 dark:from-gray-900 dark:to-gray-950">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Saved Places
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Your bookmarked destinations
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-gray-600 dark:text-gray-400">
                        Saved places content coming soon...
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>