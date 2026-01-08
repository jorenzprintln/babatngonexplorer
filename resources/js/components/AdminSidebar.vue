<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, MapPin, Star, Users, Settings, FileText } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

// Get route function safely - now properly typed to return string
const getRoute = (name: string): string => {
    if (typeof window !== 'undefined' && window.route) {
        try {
            // Call the route function and ensure it returns a string
            const result = window.route(name);
            return typeof result === 'string' ? result : String(result);
        } catch {
            return '/admin/dashboard';
        }
    }
    return '/admin/dashboard';
};

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: getRoute('admin.dashboard'),
        icon: LayoutGrid,
    },
    // Uncomment when routes are ready
    // {
    //     title: 'Places',
    //     href: getRoute('admin.places.index'),
    //     icon: MapPin,
    // },
    // {
    //     title: 'Reviews',
    //     href: getRoute('admin.reviews.index'),
    //     icon: Star,
    // },
    // {
    //     title: 'Users',
    //     href: getRoute('admin.users.index'),
    //     icon: Users,
    // },
    // {
    //     title: 'Reports',
    //     href: getRoute('admin.reports.index'),
    //     icon: FileText,
    // },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Settings',
    //     href: getRoute('admin.settings'),
    //     icon: Settings,
    // },
];

const dashboardHref = getRoute('admin.dashboard');
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardHref">
                            <AppLogo />
                            <div class="flex flex-col gap-0.5 leading-none">
                                <span class="font-semibold">Admin Panel</span>
                                <span class="text-xs text-muted-foreground">Babatngon</span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>