<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DatePickerField from '@/components/DatePickerField.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { index as remindersIndex } from '@/routes/vehicle-service-reminders';
import { index as serviceTypesIndex } from '@/routes/vehicle-service-types';
import { create, destroy, edit, index } from '@/routes/vehicle-services';
import type { Paginated, Vehicle, VehicleService } from '@/types';

const props = defineProps<{
    services: Paginated<VehicleService>;
    vehicles: Vehicle[];
    selectedVehicleId: string | null;
    selectedFrom: string | null;
    selectedTo: string | null;
}>();

const { t } = useI18n();
const { formatDate } = useDateFormat();

const showConfirm = ref(false);
const pendingService = ref<VehicleService | null>(null);

const fromDate = ref<string>(props.selectedFrom ?? '');
const toDate = ref<string>(props.selectedTo ?? '');

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
    router.get(index.url(), { vehicle_id: value || undefined, from: fromDate.value || undefined, to: toDate.value || undefined }, { preserveState: true, replace: true });
}

function onFromFilter(value: string): void {
    fromDate.value = value;
    router.get(index.url(), { vehicle_id: props.selectedVehicleId || undefined, from: value || undefined, to: toDate.value || undefined }, { preserveState: true, replace: true });
}

function onToFilter(value: string): void {
    toDate.value = value;
    router.get(index.url(), { vehicle_id: props.selectedVehicleId || undefined, from: fromDate.value || undefined, to: value || undefined }, { preserveState: true, replace: true });
}

function goToPage(page: number): void {
    router.get(index.url(), { page, vehicle_id: props.selectedVehicleId || undefined, from: props.selectedFrom || undefined, to: props.selectedTo || undefined }, { preserveState: true, replace: true });
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

        <v-row class="mb-2">
            <v-col cols="6" class="pe-1">
                <DatePickerField
                    :model-value="fromDate"
                    :label="t('services.from')"
                    @update:model-value="onFromFilter"
                />
            </v-col>
            <v-col cols="6" class="ps-1">
                <DatePickerField
                    :model-value="toDate"
                    :label="t('services.to')"
                    @update:model-value="onToFilter"
                />
            </v-col>
        </v-row>

        <v-btn color="primary" block class="mb-4" prepend-icon="mdi-plus" @click="router.visit(create.url())">
            {{ t('common.add') }}
        </v-btn>

        <v-alert v-if="services.meta.total === 0" type="info" variant="tonal">
            {{ t('services.no_services') }}
        </v-alert>

        <v-card v-for="service in services.data" :key="service.id" class="mb-3" rounded="lg">
            <div class="d-flex align-start">
            <div style="min-width: 0; flex: 1;">
                <v-card-title class="text-wrap">
                    {{ formatDate(service.date) }}
                </v-card-title>
                <v-card-title class="text-wrap">
                    <v-icon v-if="service.vehicle_service_type?.icon">{{ service.vehicle_service_type.icon }}</v-icon>
                    {{ service.vehicle_service_type?.label }}
                </v-card-title>
                <v-card-text>
                    <v-chip label :color="service.vehicle.color" size="small">{{ service.vehicle.brand }} {{ service.vehicle.model }} ({{ service.vehicle.plate_number }})</v-chip>
                </v-card-text>
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
            <div class="d-flex pa-1 ga-2 align-start pt-2">
                <v-btn icon="mdi-pencil" variant="tonal" size="small" @click="router.visit(edit.url({ vehicle_service: service.id }))" />
                <v-btn icon="mdi-delete" variant="tonal" color="error" size="small" @click="promptDelete(service)" />
            </div>
            </div>
        </v-card>

        <v-pagination
            v-if="services.meta.last_page > 1"
            :model-value="services.meta.current_page"
            :length="services.meta.last_page"
            class="mt-2"
            @update:model-value="goToPage"
        />
    </v-container>
</template>
