import type { Config } from 'ziggy-js';

// ============================================
// User Types
// ============================================
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

// ============================================
// Place Types
// ============================================
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

// ============================================
// Review Types
// ============================================
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

// ============================================
// Navigation Types
// ============================================
export interface BreadcrumbItemType {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: any;
    isActive?: boolean;
    items?: {
        title: string;
        href: string;
    }[];
}

// ============================================
// Page Props Types
// ============================================
export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    ziggy?: Config & { location: string };
};

export type AppPageProps = PageProps & {
    name?: string;
    quote?: { message: string; author: string };
    sidebarOpen?: boolean;
};

// ============================================
// Dashboard Specific Types
// ============================================
export interface DashboardStats {
    total_places: number;
    total_reviews: number;
    total_users: number;
    pending_reviews: number;
}

export interface DashboardPlace {
    id: number;
    name: string;
    location: string;
    created_at: string;
}

export interface DashboardReview {
    id: number;
    comment: string;
    rating: number;
    user: {
        name: string;
    };
    place: {
        name: string;
    };
    created_at: string;
}