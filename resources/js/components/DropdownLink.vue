<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Method } from '@inertiajs/core';

defineProps<{
    href?: string;
    as?: string;
    method?: Method;
}>();
</script>

<template>
    <Link
        :href="href"
        :as="as"
        :method="method"
        class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-800 dark:focus:bg-gray-800"
    >
        <slot />
    </Link>
</template>