<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { User, Lock, Shield, Palette } from 'lucide-vue-next';

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

// Admin settings sidebar items - using admin routes
const sidebarNavItems = computed<NavItem[]>(() => {
    return [
        {
            title: 'Profile',
            href: getRoute('admin.profile.edit'),
            icon: User,
        },
        {
            title: 'Password',
            href: getRoute('admin.password.edit'),
            icon: Lock,
        },
        {
            title: 'Two-Factor Auth',
            href: getRoute('admin.two-factor.show'),
            icon: Shield,
        },
        {
            title: 'Appearance',
            href: getRoute('admin.appearance.edit'),
            icon: Palette,
        },
    ];
});

// Use page.url for current path which updates reactively with Inertia navigation
const currentPath = computed(() => page.url);

// Improved active check function for admin routes
const isActiveRoute = (href: string): boolean => {
    // Get the current URL without query params
    const currentUrl = currentPath.value.split('?')[0];
    
    // Normalize both URLs by removing trailing slashes
    const normalizedHref = href.replace(/\/$/, '');
    const normalizedCurrent = currentUrl.replace(/\/$/, '');
    
    // Direct match
    if (normalizedCurrent === normalizedHref) {
        return true;
    }
    
    // Check if window.route().current() matches the route name
    if (typeof window !== 'undefined' && typeof window.route === 'function') {
        try {
            const currentRoute = window.route();
            // Check each admin route
            if (href.includes('/profile') && currentRoute.current('admin.profile.edit')) {
                return true;
            }
            if (href.includes('/password') && currentRoute.current('admin.password.edit')) {
                return true;
            }
            if (href.includes('/two-factor') && currentRoute.current('admin.two-factor.show')) {
                return true;
            }
            if (href.includes('/appearance') && currentRoute.current('admin.appearance.edit')) {
                return true;
            }
        } catch (e) {
            console.warn('Route check failed:', e);
        }
    }
    
    return false;
};
</script>

<template>
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
                            isActiveRoute(item.href) 
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
</template>