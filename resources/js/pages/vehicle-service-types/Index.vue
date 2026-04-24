<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { create, destroy, edit, index } from '@/routes/vehicle-service-types';
import { index as servicesIndex } from '@/routes/vehicle-services';
import type { Paginated, VehicleServiceType } from '@/types';

const props = defineProps<{
    serviceTypes: Paginated<VehicleServiceType>;
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

function goToPage(page: number): void {
    router.get(index.url(), { page }, { preserveState: true, replace: true });
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

        <v-alert v-if="serviceTypes.meta.total === 0" type="info" variant="tonal">
            {{ t('service_types.no_types') }}
        </v-alert>

        <v-card v-for="serviceType in serviceTypes.data" :key="serviceType.id" class="mb-3" rounded="lg">
            <div class="d-flex align-start">
                <div style="min-width: 0; flex: 1;">
                    <v-card-title class="d-flex align-center ga-2 text-wrap">
                        <v-icon>{{ serviceType.icon }}</v-icon>
                        {{ serviceType.label }}
                    </v-card-title>
                </div>
                <div class="d-flex pa-1 ga-2 align-start pt-2">
                    <v-btn icon="mdi-pencil" variant="tonal" size="small" @click="router.visit(edit.url({ vehicle_service_type: serviceType.id }))" />
                    <v-btn icon="mdi-delete" variant="tonal" color="error" size="small" @click="promptDelete(serviceType)" />
                </div>
            </div>
        </v-card>

        <v-pagination
            v-if="serviceTypes.meta.last_page > 1"
            :model-value="serviceTypes.meta.current_page"
            :length="serviceTypes.meta.last_page"
            class="mt-2"
            @update:model-value="goToPage"
        />
    </v-container>
</template>
