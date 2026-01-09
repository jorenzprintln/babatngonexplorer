<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { User, Lock, Shield, Palette } from 'lucide-vue-next';
import AdminSidebarLayout from '@/layouts/app/AdminSidebarLayout.vue';

const page = usePage();

// Helper to safely get routes
const getRoute = (name: string, fallback: string = '#'): string => {
    if (typeof window !== 'undefined' && typeof window.route === 'function') {
        try {
            return window.route(name);
        } catch {
            console.warn(`Route ${name} not found, using fallback`);
            return fallback;
        }
    }
    return fallback;
};

// Admin settings sidebar items
const sidebarNavItems = computed<NavItem[]>(() => {
    return [
        {
            title: 'Profile',
            href: getRoute('admin.profile.edit', '/admin/settings/profile'),
            icon: User,
        },
        {
            title: 'Password',
            href: getRoute('admin.password.edit', '/admin/settings/password'),
            icon: Lock,
        },
        {
            title: 'Two-Factor Auth',
            href: getRoute('admin.two-factor.show', '/admin/settings/two-factor'),
            icon: Shield,
        },
        {
            title: 'Appearance',
            href: getRoute('admin.appearance.edit', '/admin/settings/appearance'),
            icon: Palette,
        },
    ];
});

// Breadcrumbs
const breadcrumbItems = computed(() => [
    {
        title: 'Admin Dashboard',
        href: getRoute('admin.dashboard', '/admin/dashboard'),
    },
    {
        title: 'Settings',
        href: getRoute('admin.profile.edit', '/admin/settings/profile'),
    },
]);

// Use page.url for current path which updates reactively with Inertia navigation
const currentPath = computed(() => page.url);
</script>

<template>
    <AdminSidebarLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <Heading
                title="Admin Settings"
                description="Manage your admin profile and account settings"
            />

            <div class="flex flex-col lg:flex-row lg:space-x-12 lg:space-y-0 space-y-6 mt-6">
                <aside class="w-full lg:w-48 shrink-0">
                    <nav class="flex flex-col space-y-1">
                        <Button
                            v-for="item in sidebarNavItems"
                            :key="item.href"
                            variant="ghost"
                            :class="[
                                'w-full justify-start',
                                urlIsActive(item.href, currentPath) 
                                    ? 'bg-muted hover:bg-muted' 
                                    : 'hover:bg-transparent hover:underline',
                            ]"
                            as-child
                        >
                            <Link :href="item.href">
                                <component v-if="item.icon" :is="item.icon" class="mr-2 h-4 w-4" />
                                {{ item.title }}
                            </Link>
                        </Button>
                    </nav>
                </aside>

                <Separator orientation="vertical" class="hidden lg:block h-auto" />
                <Separator class="lg:hidden" />

                <div class="flex-1 lg:max-w-2xl">
                    <section class="space-y-6">
                        <slot />
                    </section>
                </div>
            </div>
        </div>
    </AdminSidebarLayout>
</template>