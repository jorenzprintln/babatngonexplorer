<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import { Settings } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const getRoute = (name: string): string => {
    if (typeof window !== 'undefined' && typeof window.route === 'function') {
        try {
            return window.route(name);
        } catch {
            return '#';
        }
    }
    return '#';
};

const adminFooterItems = [
    { title: 'Settings', href: getRoute('admin.settings'), icon: Settings },
];
</script>

<template>
    <SidebarGroup class="group-data-[collapsible=icon]:p-0">
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in adminFooterItems" :key="item.title">
                    <SidebarMenuButton
                        class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>