<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { ChevronsUpDown, LogOut, Settings } from 'lucide-vue-next';

interface Props {
    user: User;
}

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

const handleLogout = () => {
    router.post('/logout', {}, {
        onFinish: () => {
            window.location.href = '/';
        }
    });
};

defineProps<Props>();
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                    >
                        <UserInfo :user="user" :show-email="false" />
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                    side="bottom"
                    align="end"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="p-0 font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <UserInfo :user="user" :show-email="true" />
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuItem as-child>
                            <!-- Use admin.profile.edit route instead of user edit() -->
                            <Link :href="getRoute('admin.profile.edit')">
                                <Settings class="mr-2 h-4 w-4" />
                                Settings
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuGroup>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem>
                        <button
                            class="flex w-full items-center"
                            @click="handleLogout"
                            data-test="logout-button"
                            type="button"
                        >
                            <LogOut class="mr-2 h-4 w-4" />
                            Log out
                        </button>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>