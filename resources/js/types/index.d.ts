import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    role: 'admin' | 'user';
    created_at: string;
    updated_at: string;
}

export interface Auth {
    user: User;
}

export interface Place {
    id: number;
    name: string;
    description: string;
    location: string;
    image: string;
    rating: number;
    type: string;
    latitude?: number;
    longitude?: number;
    review_count?: number;
    average_rating?: number;
    created_at: string;
    updated_at: string;
}

export interface Review {
    id: number;
    user_id: number;
    place_id: number;
    rating: number;
    comment: string;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    updated_at: string;
    user?: User;
    place?: Place;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Main PageProps type
export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy?: Config & { location: string };
};

// App-specific PageProps (if you need additional props)
export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = PageProps<T> & {
    name?: string;
    quote?: { message: string; author: string };
    sidebarOpen?: boolean;
};

// Global declarations
declare global {
    interface Window {
        route: (
            name?: string,
            params?: Record<string, any> | any,
            absolute?: boolean
        ) => string & {
            current(name?: string): boolean;
        };
    }

    // Global route helper
    function route(
        name?: string,
        params?: Record<string, any> | any,
        absolute?: boolean
    ): string;

    function route(): {
        current(name?: string): boolean;
    };
}