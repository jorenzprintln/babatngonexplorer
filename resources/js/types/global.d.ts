declare global {
    interface Window {
        route: (name: string, params?: Record<string, any>, absolute?: boolean) => string;
    }
    
    var route: (name: string, params?: Record<string, any>, absolute?: boolean) => string;
}

export {};