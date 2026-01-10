<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('admin.password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Confirm Password" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 dark:bg-gray-900 sm:px-6 lg:px-8">
        <Card class="w-full max-w-md">
            <CardHeader class="space-y-1 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                    <ShieldCheck class="h-6 w-6 text-primary" />
                </div>
                <CardTitle class="text-2xl font-bold">Confirm Password</CardTitle>
                <CardDescription>
                    This is a secure area of the application. Please confirm your password before continuing.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            :disabled="form.processing"
                            autofocus
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Confirming...' : 'Confirm' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>