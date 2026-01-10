<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import AdminSidebarLayout from '@/layouts/app/AdminSidebarLayout.vue';
import AdminSettingsLayout from '@/layouts/settings/AdminSettingsLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    theme?: string;
    language?: string;
}

withDefaults(defineProps<Props>(), {
    theme: 'system',
    language: 'en',
});

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
    {
        title: 'Appearance Settings',
        href: route('admin.appearance.edit'),
    },
]);
</script>

<template>
    <Head title="Admin Appearance Settings" />

    <AdminSidebarLayout :breadcrumbs="breadcrumbItems">
        <AdminSettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Appearance settings"
                    description="Update your account's appearance settings"
                />
                <AppearanceTabs />
            </div>
        </AdminSettingsLayout>
    </AdminSidebarLayout>
</template>