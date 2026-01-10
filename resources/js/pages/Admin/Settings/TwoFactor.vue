<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ShieldCheck, ShieldBan } from 'lucide-vue-next';
import { computed, onUnmounted, ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AdminSidebarLayout from '@/layouts/app/AdminSidebarLayout.vue';
import AdminSettingsLayout from '@/layouts/settings/AdminSettingsLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useForm } from '@inertiajs/vue3';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
    {
        title: 'Two-Factor Authentication',
        href: route('admin.two-factor.show'),
    },
]);

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

// Forms for enable/disable
const enableForm = useForm({});
const disableForm = useForm({});

const enableTwoFactor = () => {
    enableForm.post(route('admin.two-factor.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSetupModal.value = true;
        },
    });
};

const disableTwoFactor = () => {
    disableForm.delete(route('admin.two-factor.destroy'), {
        preserveScroll: true,
    });
};

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <Head title="Admin Two-Factor Authentication" />

    <AdminSidebarLayout :breadcrumbs="breadcrumbItems">
        <AdminSettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Two-Factor Authentication"
                    description="Manage your two-factor authentication settings"
                />

                <!-- Two-Factor Disabled State -->
                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="destructive">Disabled</Badge>

                    <p class="text-muted-foreground">
                        When you enable two-factor authentication, you will be
                        prompted for a secure pin during login. This pin can be
                        retrieved from a TOTP-supported application on your
                        phone.
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck class="mr-2 h-4 w-4" />
                            Continue Setup
                        </Button>
                        <Button
                            v-else
                            @click="enableTwoFactor"
                            :disabled="enableForm.processing"
                        >
                            <ShieldCheck class="mr-2 h-4 w-4" />
                            {{ enableForm.processing ? 'Enabling...' : 'Enable 2FA' }}
                        </Button>
                    </div>
                </div>

                <!-- Two-Factor Enabled State -->
                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="default">Enabled</Badge>

                    <p class="text-muted-foreground">
                        With two-factor authentication enabled, you will be
                        prompted for a secure, random pin during login, which
                        you can retrieve from the TOTP-supported application on
                        your phone.
                    </p>

                    <TwoFactorRecoveryCodes />

                    <div class="relative inline">
                        <Button
                            variant="destructive"
                            @click="disableTwoFactor"
                            :disabled="disableForm.processing"
                        >
                            <ShieldBan class="mr-2 h-4 w-4" />
                            {{ disableForm.processing ? 'Disabling...' : 'Disable 2FA' }}
                        </Button>
                    </div>
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </AdminSettingsLayout>
    </AdminSidebarLayout>
</template>