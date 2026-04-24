<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DatePickerField from '@/components/DatePickerField.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { create, destroy, edit, index } from '@/routes/vehicle-refuels';
import type { Paginated, Vehicle, VehicleRefuel } from '@/types';

const props = defineProps<{
    refuels: Paginated<VehicleRefuel>;
    vehicles: Vehicle[];
    selectedVehicleId: string | null;
    selectedFrom: string | null;
    selectedTo: string | null;
}>();

const { t } = useI18n();
const { formatDate } = useDateFormat();

const showConfirm = ref(false);
const pendingRefuel = ref<VehicleRefuel | null>(null);

const fromDate = ref<string>(props.selectedFrom ?? '');
const toDate = ref<string>(props.selectedTo ?? '');

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

        <v-row class="mb-2">
            <v-col cols="6" class="pe-1">
                <DatePickerField
                    :model-value="fromDate"
                    :label="t('refuels.from')"
                    @update:model-value="onFromFilter"
                />
            </v-col>
            <v-col cols="6" class="ps-1">
                <DatePickerField
                    :model-value="toDate"
                    :label="t('refuels.to')"
                    @update:model-value="onToFilter"
                />
            </v-col>
        </v-row>

        <v-btn color="primary" block class="mb-4" prepend-icon="mdi-plus" @click="router.visit(create.url())">
            {{ t('common.add') }}
        </v-btn>

        <v-alert v-if="refuels.meta.total === 0" type="info" variant="tonal">
            {{ t('refuels.no_refuels') }}
        </v-alert>

        <v-card v-for="refuel in refuels.data" :key="refuel.id" class="mb-3" rounded="lg">
            <div style="min-width: 0;">
                <v-card-title class="text-wrap">{{ formatDate(refuel.date) }}</v-card-title>
                <v-card-text class="pb-1">
                    <v-chip label :color="refuel.vehicle.color" size="small">{{ refuel.vehicle.brand }} {{ refuel.vehicle.model }} ({{ refuel.vehicle.plate_number }})</v-chip>
                </v-card-text>
                <v-card-text>
                    <v-row density="comfortable">
                        <v-col cols="4">
                            <div class="text-caption text-medium-emphasis">{{ t('refuels.total_price') }}</div>
                            <div class="font-weight-medium">€ {{ refuel.total_price }}</div>
                        </v-col>
                        <v-divider vertical/>
                        <v-col cols="3">
                            <div class="text-caption text-medium-emphasis">{{ t('refuels.liters') }}</div>
                            <div class="font-weight-medium">{{ refuel.liters }} L</div>
                        </v-col>
                        <v-col cols="4">
                            <div class="text-caption text-medium-emphasis">{{ t('refuels.unit_price') }}</div>
                            <div class="font-weight-medium">€ {{ refuel.unit_price }}</div>
                        </v-col>
                    </v-row>
                </v-card-text>
            </div>
            <div class="d-flex pa-2 gap-2">
                <v-btn class="" style="flex: 1" prepend-icon="mdi-pencil" rounded="lg" variant="tonal" @click="router.visit(edit.url({ vehicle_refuel: refuel.id }))">
                    {{ t('common.edit') }}
                </v-btn>
                &nbsp;
                <v-btn style="flex: 1" prepend-icon="mdi-delete" rounded="lg" variant="elevated" color="error" @click="promptDelete(refuel)">
                    {{ t('common.delete') }}
                </v-btn>
            </div>
        </v-card>

        <v-pagination
            v-if="refuels.meta.last_page > 1"
            :model-value="refuels.meta.current_page"
            :length="refuels.meta.last_page"
            class="mt-2"
            @update:model-value="goToPage"
        />
    </v-container>
</template>
