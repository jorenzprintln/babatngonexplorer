<script setup lang="ts">
import { send } from '@/routes/verification';
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminSidebarLayout from '@/layouts/app/AdminSidebarLayout.vue';
import AdminSettingsLayout from '@/layouts/settings/AdminSettingsLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user;

// Admin breadcrumbs
const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
    {
        title: 'Profile Settings',
        href: route('admin.profile.edit'),
    },
]);

// Create form using useForm for admin profile
const form = useForm({
    name: user.name || '',
    email: user.email || '',
});

const submit = () => {
    // Submit to ADMIN profile update route using PATCH method
    form.patch(route('admin.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Admin profile updated successfully');
        },
        onError: (errors) => {
            console.error('Admin profile update errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Admin Profile Settings" />

    <AdminSidebarLayout :breadcrumbs="breadcrumbItems">
        <AdminSettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your name and email address"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full"
                            type="text"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                            :disabled="form.processing"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                            :disabled="form.processing"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            data-test="update-profile-button"
                        >
                            {{ form.processing ? 'Saving...' : 'Save' }}
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
                                Saved successfully
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </AdminSettingsLayout>
    </AdminSidebarLayout>
</template>