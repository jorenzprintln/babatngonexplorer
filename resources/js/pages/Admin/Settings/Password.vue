<script setup lang="ts">
import AdminSettingsLayout from '@/Layouts/AdminSettingsLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('admin.password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <AdminSettingsLayout>
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <h3 class="text-lg font-medium">Update Password</h3>
                <p class="text-sm text-muted-foreground">
                    Ensure your account is using a long, random password to stay secure.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <Label for="current_password">Current Password</Label>
                    <Input
                        id="current_password"
                        v-model="form.current_password"
                        type="password"
                        required
                    />
                    <span v-if="form.errors.current_password" class="text-sm text-destructive">
                        {{ form.errors.current_password }}
                    </span>
                </div>

                <div>
                    <Label for="password">New Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                    />
                    <span v-if="form.errors.password" class="text-sm text-destructive">
                        {{ form.errors.password }}
                    </span>
                </div>

                <div>
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                    />
                </div>
            </div>

            <Button type="submit" :disabled="form.processing">
                Update Password
            </Button>
        </form>
    </AdminSettingsLayout>
</template>