<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { index as remindersIndex } from '@/routes/vehicle-service-reminders';
import { index as serviceTypesIndex } from '@/routes/vehicle-service-types';
import { create, destroy, edit, index } from '@/routes/vehicle-services';
import type { Vehicle, VehicleService } from '@/types';

defineProps<{
    services: VehicleService[];
    vehicles: Vehicle[];
    selectedVehicleId: string | null;
}>();

const { t } = useI18n();
const { formatDate } = useDateFormat();

const showConfirm = ref(false);
const pendingService = ref<VehicleService | null>(null);

function promptDelete(service: VehicleService): void {
    pendingService.value = service;
    showConfirm.value = true;
}

function doDelete(): void {
    if (pendingService.value) {
        router.delete(destroy.url({ vehicle_service: pendingService.value.id }));
    }
}

function onVehicleFilter(value: string | null): void {
    router.get(index.url(), { vehicle_id: value || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head :title="t('services.title')" />

    <ConfirmDialog
        v-model="showConfirm"
        :message="t('services.delete') + '?'"
        @confirm="doDelete"
    />

    <v-container>
        <h1 class="text-h5 mb-4">{{ t('services.title') }}</h1>

        <v-btn color="primary" variant="elevated" block class="mb-2" prepend-icon="mdi-cog" @click="router.visit(serviceTypesIndex.url())">
            {{ t('services.manage_types') }}
        </v-btn>

        <v-btn color="primary" variant="elevated"  block class="mb-4" prepend-icon="mdi-bell" @click="router.visit(remindersIndex.url())">
            {{ t('reminders.title') }}
        </v-btn>

        <v-divider class="my-6" />

        <v-select
            :label="t('services.vehicle')"
            :model-value="selectedVehicleId"
            :items="[{ title: t('services.all_vehicles'), value: null }, ...vehicles.map(v => ({ title: `${v.brand} ${v.model} (${v.plate_number})`, value: v.id }))]"
            item-title="title"
            item-value="value"
            variant="outlined"
            class="mb-2"
            clearable
            @update:model-value="onVehicleFilter"
        />

        <v-btn color="primary" block class="mb-4" prepend-icon="mdi-plus" @click="router.visit(create.url())">
            {{ t('common.add') }}
        </v-btn>

        <v-alert v-if="services.length === 0" type="info" variant="tonal">
            {{ t('services.no_services') }}
        </v-alert>

        <v-card v-for="service in services" :key="service.id" class="mb-3" rounded="lg">
            <div style="min-width: 0;">
                <v-card-title class="d-flex align-center ga-2 text-wrap">
                    <v-icon v-if="service.vehicle_service_type?.icon">{{ service.vehicle_service_type.icon }}</v-icon>
                    {{ service.vehicle_service_type?.label }}
                </v-card-title>
                <v-card-text>
                    <v-chip label :color="service.vehicle.color" size="small">{{ service.vehicle.brand }} {{ service.vehicle.model }} ({{ service.vehicle.plate_number }})</v-chip>
                </v-card-text>
                <v-card-subtitle class="text-wrap">
                    {{ formatDate(service.date) }}
                </v-card-subtitle>
                <v-card-text>
                    <v-row density="comfortable">
                        <v-col cols="6">
                            <div class="text-caption text-medium-emphasis">{{ t('services.total_paid') }}</div>
                            <div class="font-weight-medium">€ {{ service.total_paid }}</div>
                        </v-col>
                        <v-col cols="6">
                            <div class="text-caption text-medium-emphasis">{{ t('services.odometer') }}</div>
                            <div class="font-weight-medium">{{ service.odometer }} km</div>
                        </v-col>
                    </v-row>
                    <div v-if="service.location" class="mt-2">
                        <div class="text-caption text-medium-emphasis">{{ t('services.location') }}</div>
                        <div class="font-weight-medium">{{ service.location }}</div>
                    </div>
                    <div v-if="service.notes" class="mt-2">
                        <div class="text-caption text-medium-emphasis">{{ t('services.notes') }}</div>
                        <div class="font-weight-medium">{{ service.notes }}</div>
                    </div>
                </v-card-text>
            </div>
            <div class="d-flex pa-2 gap-2">
                <v-btn class="" style="flex: 1" prepend-icon="mdi-pencil" rounded="lg" variant="tonal" @click="router.visit(edit.url({ vehicle_service: service.id }))">
                    {{ t('common.edit') }}
                </v-btn>
                &nbsp;
                <v-btn style="flex: 1" prepend-icon="mdi-delete" rounded="lg" variant="elevated" color="error" @click="promptDelete(service)">
                    {{ t('common.delete') }}
                </v-btn>
            </div>
        </v-card>
    </v-container>
</template>
