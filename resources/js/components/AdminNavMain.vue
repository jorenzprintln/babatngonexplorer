<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, MapPin, Star, Users, FileText } from 'lucide-vue-next';

// Helper to safely get routes
const getRoute = (name: string, fallback: string = '#'): string => {
    if (typeof window !== 'undefined' && typeof window.route === 'function') {
        try {
            return window.route(name);
        } catch {
            console.warn(`Route ${name} not found, using fallback: ${fallback}`);
            return fallback;
        }
    }
    return fallback;
};

// Admin navigation items
const adminItems = [
    {
        title: 'Dashboard',
        href: getRoute('admin.dashboard', '/admin/dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Places',
        href: getRoute('admin.places.index', '/admin/places'),
        icon: MapPin,
    },
    {
        title: 'Reviews',
        href: getRoute('admin.reviews.index', '/admin/reviews'),
        icon: Star,
    },
    {
        title: 'Users',
        href: getRoute('admin.users.index', '/admin/users'),
        icon: Users,
    },
    {
        title: 'Reports',
        href: getRoute('admin.reports.index', '/admin/reports'),
        icon: FileText,
    },
];

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Admin Panel</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in adminItems" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>