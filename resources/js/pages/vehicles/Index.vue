<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { create, destroy, edit } from '@/routes/vehicles';
import type { Vehicle } from '@/types';

defineProps<{
    vehicles: Vehicle[];
}>();

const { t } = useI18n();
const { formatDate } = useDateFormat();

const showConfirm = ref(false);
const pendingVehicle = ref<Vehicle | null>(null);

function promptDelete(vehicle: Vehicle): void {
    pendingVehicle.value = vehicle;
    showConfirm.value = true;
}

function doDelete(): void {
    if (pendingVehicle.value) {
        router.delete(destroy.url(pendingVehicle.value));
    }
}
</script>

<template>
    <Head :title="t('vehicles.title')" />

    <ConfirmDialog
        v-model="showConfirm"
        :message="t('vehicles.delete') + ' ' + (pendingVehicle?.plate_number ?? '') + '?'"
        @confirm="doDelete"
    />

    <v-container>
        <h1 class="text-h5 mb-4">{{ t('vehicles.title') }}</h1>

        <v-btn color="primary" block class="mb-4" prepend-icon="mdi-plus" @click="router.visit(create.url())">
            {{ t('common.add') }}
        </v-btn>

        <v-alert v-if="vehicles.length === 0" type="info" variant="tonal" color="">
            {{ t('vehicles.no_vehicles') }}
        </v-alert>

        <v-card v-for="vehicle in vehicles" :key="vehicle.id" class="mb-3 d-flex" rounded="lg">
            <div class="flex-grow-1" style="min-width: 0;">
                <v-card-text class="pb-1">
                    <v-chip variant="elevated" label color="primary" size="small">{{ vehicle.plate_number }}</v-chip>
                </v-card-text>
                <v-card-title class="text-wrap">{{ vehicle.brand }} {{ vehicle.model }}</v-card-title>
                <v-card-subtitle class="text-wrap pb-3">{{ vehicle.year }}</v-card-subtitle>
                <v-card-subtitle class="text-wrap pb-3">{{ vehicle.current_odometer }} km</v-card-subtitle>
                <v-card-text v-if="vehicle.purchase_date">
                    {{ t('vehicles.purchase_date') }}: {{ formatDate(vehicle.purchase_date) }}
                </v-card-text>
            </div>
            <div class="d-flex flex-column align-center justify-center pa-2 border-s">
                <div class="pb-2">
                    <v-btn icon="mdi-pencil" size="small" variant="outlined" @click="router.visit(edit.url(vehicle))" />
                </div>
                <div class="pt-2">
                <v-btn icon="mdi-delete" size="small" variant="elevated" color="error" @click="promptDelete(vehicle)" />
                </div>
            </div>
        </v-card>
    </v-container>
</template>
