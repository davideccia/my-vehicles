<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { create, destroy, edit } from '@/routes/vehicle-service-types';
import { index as servicesIndex } from '@/routes/vehicle-services';
import type { VehicleServiceType } from '@/types';

defineProps<{
    serviceTypes: VehicleServiceType[];
}>();

const { t } = useI18n();

const showConfirm = ref(false);
const pendingType = ref<VehicleServiceType | null>(null);

function promptDelete(serviceType: VehicleServiceType): void {
    pendingType.value = serviceType;
    showConfirm.value = true;
}

function doDelete(): void {
    if (pendingType.value) {
        router.delete(destroy.url({ vehicle_service_type: pendingType.value.id }));
    }
}
</script>

<template>
    <Head :title="t('service_types.title')" />

    <ConfirmDialog
        v-model="showConfirm"
        :message="t('service_types.delete') + ' ' + (pendingType?.label ?? '') + '?'"
        @confirm="doDelete"
    />

    <v-container>
        <div class="d-flex align-center ga-2 mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="router.visit(servicesIndex.url())" />
            <h1 class="text-h5 flex-grow-1">{{ t('service_types.title') }}</h1>
        </div>

        <v-btn color="primary" block class="mb-4" prepend-icon="mdi-plus" @click="router.visit(create.url())">
            {{ t('common.add') }}
        </v-btn>

        <v-alert v-if="serviceTypes.length === 0" type="info" variant="tonal">
            {{ t('service_types.no_types') }}
        </v-alert>

        <v-card v-for="serviceType in serviceTypes" :key="serviceType.id" class="mb-3" rounded="lg">
            <div style="min-width: 0;">
                <v-card-title class="d-flex align-center ga-2 text-wrap">
                    <v-icon>{{ serviceType.icon }}</v-icon>
                    {{ serviceType.label }}
                </v-card-title>
            </div>
            <v-card-actions>
                <v-btn icon="mdi-pencil" size="small" variant="outlined" @click="router.visit(edit.url({ vehicle_service_type: serviceType.id }))" />
                <v-spacer />
                <v-btn icon="mdi-delete" size="small" variant="elevated" color="error" @click="promptDelete(serviceType)" />
            </v-card-actions>
        </v-card>
    </v-container>
</template>
