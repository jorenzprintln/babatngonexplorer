import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    let url = toUrl(urlToCheck);
    
    // Extract just the pathname if it's a full URL
    if (url.startsWith('http://') || url.startsWith('https://')) {
        try {
            url = new URL(url).pathname;
        } catch {
            // If URL parsing fails, use as is
        }
    }
    
    // Ensure currentUrl is just the pathname
    let current = currentUrl;
    if (current.startsWith('http://') || current.startsWith('https://')) {
        try {
            current = new URL(current).pathname;
        } catch {
            // If URL parsing fails, use as is
        }
    }
    
    // Exact match
    if (url === current) {
        return true;
    }
    
    // Partial match - check if current URL starts with the navigation URL
    // This makes parent routes active when on child pages
    // Example: /admin/places is active when on /admin/places/edit/1
    if (current.startsWith(url + '/')) {
        return true;
    }
    
    return false;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}