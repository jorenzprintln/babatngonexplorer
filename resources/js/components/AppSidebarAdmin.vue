<script setup lang="ts">
import AdminNavMain from '@/components/AdminNavMain.vue';
import AdminNavUser from '@/components/AdminNavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import type { User } from '@/types';

// Get the authenticated user from Inertia page props
const page = usePage<{ auth: { user: User } }>();

// Helper to safely get routes
const getRoute = (name: string): string => {
    if (typeof window !== 'undefined' && typeof window.route === 'function') {
        try {
            return window.route(name);
        } catch {
            return '/admin/dashboard';
        }
    }
    return '/admin/dashboard';
};

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
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <AdminNavMain />
        </SidebarContent>

        <SidebarFooter>
            <!-- Pass the user prop to AdminNavUser -->
            <AdminNavUser v-if="page.props.auth?.user" :user="page.props.auth.user" key="admin-nav-user" />
        </SidebarFooter>
    </Sidebar>
</template>