<script setup lang="ts">
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <header class="flex h-16 shrink-0 items-center justify-between gap-2 border-b px-4">
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <Separator orientation="vertical" class="mr-2 h-4" />
            <Breadcrumb v-if="breadcrumbs.length > 0">
                <BreadcrumbList>
                    <template v-for="(breadcrumb, index) in breadcrumbs" :key="index">
                        <BreadcrumbItem v-if="index < breadcrumbs.length - 1" class="hidden md:block">
                            <BreadcrumbLink as-child>
                                <Link :href="breadcrumb.href">
                                    {{ breadcrumb.title }}
                                </Link>
                            </BreadcrumbLink>
                        </BreadcrumbItem>
                        <BreadcrumbItem v-else>
                            <BreadcrumbPage>{{ breadcrumb.title }}</BreadcrumbPage>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" class="hidden md:block" />
                    </template>
                </BreadcrumbList>
            </Breadcrumb>
        </div>
        
        <Badge variant="secondary" class="hidden sm:flex">
            Admin
        </Badge>
    </header>
</template>