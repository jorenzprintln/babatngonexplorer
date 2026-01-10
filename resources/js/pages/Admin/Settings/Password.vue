<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { computed } from 'vue';

import InputError from '@/components/InputError.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminSidebarLayout from '@/layouts/app/AdminSidebarLayout.vue';
import AdminSettingsLayout from '@/layouts/settings/AdminSettingsLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
    {
        title: 'Password Settings',
        href: route('admin.password.edit'),
    },
]);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('admin.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            // Reset password fields on error
            form.reset('current_password', 'password', 'password_confirmation');
        }
    });
};
</script>

<template>
    <Head title="Admin Password Settings" />

    <AdminSidebarLayout :breadcrumbs="breadcrumbItems">
        <AdminSettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Update password"
                    description="Ensure your account is using a long, random password to stay secure"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password">Current password</Label>
                        <Input
                            id="current_password"
                            v-model="form.current_password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="Current password"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">New password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="New password"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            data-test="update-password-button"
                        >
                            {{ form.processing ? 'Saving...' : 'Save password' }}
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="form.recentlySuccessful"
                                class="text-sm text-green-600 font-medium"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </AdminSettingsLayout>
    </AdminSidebarLayout>
</template>