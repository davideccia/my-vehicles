<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { create, destroy, edit, index } from '@/routes/vehicle-refuels';
import type { Vehicle, VehicleRefuel } from '@/types';

defineProps<{
    refuels: VehicleRefuel[];
    vehicles: Vehicle[];
    selectedVehicleId: string | null;
}>();

const { t } = useI18n();
const { formatDate } = useDateFormat();

const showConfirm = ref(false);
const pendingRefuel = ref<VehicleRefuel | null>(null);

function promptDelete(refuel: VehicleRefuel): void {
    pendingRefuel.value = refuel;
    showConfirm.value = true;
}

function doDelete(): void {
    if (pendingRefuel.value) {
        router.delete(destroy.url({ vehicle_refuel: pendingRefuel.value.id }));
    }
}

function onVehicleFilter(value: string | null): void {
    router.get(index.url(), { vehicle_id: value || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head :title="t('refuels.title')" />

    <ConfirmDialog
        v-model="showConfirm"
        :message="t('refuels.delete') + '?'"
        @confirm="doDelete"
    />

    <v-container>
        <h1 class="text-h5 mb-4">{{ t('refuels.title') }}</h1>

        <v-select
            :label="t('refuels.vehicle')"
            :model-value="selectedVehicleId"
            :items="[{ title: t('refuels.all_vehicles'), value: null }, ...vehicles.map(v => ({ title: `${v.brand} ${v.model} (${v.plate_number})`, value: v.id }))]"
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

        <v-alert v-if="refuels.length === 0" type="info" variant="tonal">
            {{ t('refuels.no_refuels') }}
        </v-alert>

        <v-card v-for="refuel in refuels" :key="refuel.id" class="mb-3 d-flex" rounded="lg">
            <div class="flex-grow-1" style="min-width: 0;">
                <v-card-title class="text-wrap">{{ formatDate(refuel.date) }}</v-card-title>
                <v-card-subtitle v-if="refuel.vehicle" class="text-wrap">
                    {{ refuel.vehicle.brand }} {{ refuel.vehicle.model }} ({{ refuel.vehicle.plate_number }})
                </v-card-subtitle>
                <v-card-text>
                    <v-row density="comfortable">
                        <v-col cols="4">
                            <div class="text-caption text-medium-emphasis">{{ t('refuels.liters') }}</div>
                            <div class="font-weight-medium">{{ refuel.liters }} L</div>
                        </v-col>
                        <v-col cols="4">
                            <div class="text-caption text-medium-emphasis">{{ t('refuels.unit_price') }}</div>
                            <div class="font-weight-medium">€ {{ refuel.unit_price }}</div>
                        </v-col>
                        <v-col cols="4">
                            <div class="text-caption text-medium-emphasis">{{ t('refuels.total_price') }}</div>
                            <div class="font-weight-medium">€ {{ refuel.total_price }}</div>
                        </v-col>
                    </v-row>
                </v-card-text>
            </div>
            <div class="d-flex flex-column align-center justify-center pa-2 border-s">
                <div class="pb-2">
                    <v-btn icon="mdi-pencil" size="small" variant="outlined" @click="router.visit(edit.url({ vehicle_refuel: refuel.id }))" />
                </div>
                <div class="pt-2">
                    <v-btn icon="mdi-delete" size="small" variant="elevated" color="error" @click="promptDelete(refuel)" />
                </div>
            </div>
        </v-card>
    </v-container>
</template>
