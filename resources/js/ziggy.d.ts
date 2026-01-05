declare module 'ziggy-js' {
    export function route(): {
        (name?: string): string;
        current(name?: string): boolean;
    };
}